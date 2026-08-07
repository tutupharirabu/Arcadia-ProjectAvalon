require("dotenv").config();
const express = require("express");
const mqtt = require("mqtt");
const bodyParser = require("body-parser");
const helmet = require("helmet");
const cors = require("cors");
const axios = require("axios");
const schedule = require("node-schedule");
const { rateLimit } = require("express-rate-limit");
const { randomUUID } = require("crypto");
const QRCode = require("qrcode");

const cloudinary = require("./cloudinaryConfig");
const redisClient = require("./redisClient");
const { authenticateToken, requireSharedSecret } = require("./middleware/authMiddleware");

// Hardening runtime: tangani rejection/exception global agar proses jembatan MQTT
// tidak mati diam-diam karena satu promise gagal.
process.on("unhandledRejection", (reason, promise) => {
    console.error("[ERROR] Unhandled promise rejection:", reason instanceof Error ? reason.stack : reason);
    // Log + lanjutkan: rejection belum tentu merusak state; state kritis sudah
    // dilindungi try/catch per-pesan di processQueue.
});

process.on("uncaughtException", (err) => {
    console.error("[FATAL] Uncaught exception:", err.stack);
    // State bisa korup setelah exception tak tertangani — hentikan proses dengan jelas
    // agar process manager (systemd/PM2) me-restart servis.
    setImmediate(() => process.exit(1));
});

// Inisialisasi aplikasi Express
const app = express();
const PORT = process.env.PORT_MONITOR || 3000;

// Middleware
app.use(bodyParser.json());
app.use(helmet());

// CORS: pin origin dari env (FRONTEND_URL, boleh comma-separated); default localhost:5173
const CORS_ORIGINS = process.env.FRONTEND_URL
    ? process.env.FRONTEND_URL.split(",").map((origin) => origin.trim())
    : ["http://localhost:5173"];
app.use(cors({ origin: CORS_ORIGINS }));

// Konfigurasi MQTT
const mqttOptions = {
    username: process.env.MQTT_USERNAME,
    password: process.env.MQTT_KEY,
};
const mqttClient = mqtt.connect(process.env.MQTT_BROKER_URL, mqttOptions);

// Konstanta
const HTTP_TIMEOUT_MS = 8000; // Timeout semua panggilan HTTP ke Laravel
const MAX_QUEUE_SIZE = 1000; // Cap antrian pesan MQTT
const MAX_BUFFER_LEN = 100; // Cap nilai per parameter di buffer (cegah memori membengkak)
const MAX_FLUSH_ATTEMPTS = 5; // Batas percobaan kirim data historis sebelum dead-letter
const DEVICE_CACHE_TTL_MS = 5 * 60 * 1000; // TTL cache keberadaan device (5 menit)
const ERROR_CACHE_TTL_MS = 30 * 1000; // TTL cache pendek saat pengecekan gagal (Laravel down)
const DEVICE_STATE_TTL_MS = 10 * 60 * 1000; // TTL state per-device (10 menit)

// Validasi Device ID: hanya alfanumerik, tanda hubung, dan underscore (cegah SSRF/path traversal)
const DEVICE_ID_RE = /^[A-Za-z0-9_-]{1,64}$/;

// Rate limiting untuk endpoint API (per IP)
const apiLimiter = rateLimit({
    windowMs: 15 * 60 * 1000, // jendela 15 menit
    limit: 60, // maks 60 permintaan per jendela
    standardHeaders: "draft-7",
    legacyHeaders: false,
    message: { message: "Terlalu banyak permintaan. Silakan coba lagi nanti." },
});

// Fungsi untuk mengirim token ke Redis
async function storeUserToken(users_id, token) {
    if (!users_id || !token) {
        throw new Error("[ERROR] Users ID dan token diperlukan untuk menyimpan token.");
    }

    try {
        await redisClient.set(`jwt:user:${users_id}`, token, "EX", 3600);
        console.log(`[POST] Token untuk pengguna ${users_id} berhasil disimpan.`);
        return { status: "success", message: "Token berhasil disimpan." };
    } catch (error) {
        console.error("[ERROR] Gagal menyimpan token untuk pengguna:", users_id, "->", error.message);
        throw new Error("[ERROR] Gagal menyimpan token ke Redis.");
    }
}

// Fungsi untuk generate QR Code
async function generateQRCode(deviceId) {
    try {
        // Generate QR Code ke dalam Data URL
        const qrCodeDataURL = await QRCode.toDataURL(deviceId);
        console.log("[INFO] QR Code berhasil dibuat untuk Device ID:", deviceId);
        return qrCodeDataURL;
    } catch (error) {
        console.error("[ERROR] Gagal generate QR Code:", error.message);
        throw error;
    }
}

// Fungsi untuk mengunggah QR Code ke Cloudinary
async function uploadQRCodeToCloudinary(qrCodeDataURL, deviceId) {
    try {
        const result = await cloudinary.uploader.upload(qrCodeDataURL, {
            folder: "arcadia-qr-code", // Folder di Cloudinary
            public_id: `qrcode_${deviceId}`, // Nama file di Cloudinary
            overwrite: true,
        });

        console.log("[POST] QR Code berhasil diunggah ke Cloudinary:", result.secure_url);
        return result.secure_url; // Kembalikan URL gambar
    } catch (error) {
        console.error("[ERROR] Gagal mengunggah QR Code ke Cloudinary:", error.message);
        throw error;
    }
}

// Cache hasil pengecekan device di memori (per proses) agar tidak HTTP ke Laravel per pesan MQTT.
// Key: deviceId, value: { known: boolean|null, checkedAt: number, ttlMs: number }
// known: true = terdaftar, false = 404, null = pengecekan gagal (Laravel down) — jangan registrasi.
const deviceKnownCache = new Map();

function setDeviceKnown(deviceId, known, ttlMs = DEVICE_CACHE_TTL_MS) {
    deviceKnownCache.set(deviceId, { known, checkedAt: Date.now(), ttlMs });
}

// Mengembalikan nilai cache jika masih fresh, null jika belum ada/kadaluwarsa
function getCachedDeviceKnown(deviceId) {
    const entry = deviceKnownCache.get(deviceId);
    if (!entry) return null;
    if (Date.now() - entry.checkedAt > entry.ttlMs) {
        deviceKnownCache.delete(deviceId);
        return null;
    }
    return entry.known;
}

// Fungsi untuk memeriksa apakah perangkat sudah ada di Laravel (dengan cache TTL)
async function checkDeviceExist(deviceId) {
    const cached = getCachedDeviceKnown(deviceId);
    if (cached !== null) {
        return cached;
    }

    try {
        const response = await axios.get(`${process.env.LARAVEL_API_URL}/device/check-public/${deviceId}`, {
            timeout: HTTP_TIMEOUT_MS,
        });
        if (response.data.status === "success") {
            setDeviceKnown(deviceId, true);
            return true;
        }
        return false;
    } catch (error) {
        if (error.response && error.response.status === 404) {
            console.log(`[ERROR] Perangkat dengan Device ID ${deviceId} belum ada.`);
            setDeviceKnown(deviceId, false);
            return false;
        }
        console.error("[ERROR] Gagal memeriksa perangkat di Laravel:", error.message);
        // Cache pendek hasil gagal agar tidak menghajar Laravel per pesan saat down
        setDeviceKnown(deviceId, null, ERROR_CACHE_TTL_MS);
        throw error;
    }
}

// Fungsi untuk menyimpan perangkat ke Laravel jika belum ada
async function saveDeviceToLaravel(deviceId, deviceType, qrCodeUrl) {
    try {
        await axios.post(
            `${process.env.LARAVEL_API_URL}/device`,
            {
                devices_id: deviceId,
                device_type: deviceType,
                qrcode_url: qrCodeUrl,
            },
            { timeout: HTTP_TIMEOUT_MS }
        );
        console.log(`[POST] Perangkat berhasil disimpan ke Laravel: ${deviceId}`);
    } catch (error) {
        console.error("[ERROR] Gagal menyimpan perangkat ke Laravel:", error.message);
        throw error;
    }
}

// Fungsi utama
async function handleDevice(deviceId, deviceType) {
    try {
        // Langkah 1: Periksa apakah perangkat sudah ada
        const deviceExist = await checkDeviceExist(deviceId);
        if (deviceExist === true) {
            console.log(`[INFO] Perangkat sudah ada: ${deviceId}`);
            return; // Keluar jika perangkat sudah ada
        }

        if (deviceExist === null) {
            // Pengecekan gagal (mis. Laravel down) — jangan lanjut registrasi, retry di pesan berikutnya
            console.log(`[WARNING] Pengecekan device ${deviceId} gagal. Registrasi ditunda.`);
            return;
        }

        // Langkah 2: Generate QR Code jika perangkat belum ada
        const qrCodeDataURL = await generateQRCode(deviceId);

        // Langkah 3: Unggah QR Code ke Cloudinary
        const qrCodeUrl = await uploadQRCodeToCloudinary(qrCodeDataURL, deviceId);

        // Langkah 4: Simpan perangkat ke Laravel
        await saveDeviceToLaravel(deviceId, deviceType, qrCodeUrl);

        // Force refresh cache: perangkat baru saja terdaftar
        setDeviceKnown(deviceId, true);

        console.log("[INFO] Proses selesai. QR Code URL:", qrCodeUrl);
    } catch (error) {
        console.error("[ERROR] Terjadi kesalahan:", error.message);
    }
}

// Fungsi untuk menyimpan data ke Redis
async function saveToRedis(parameter, value, deviceId) {
    const listKey = `mqtt:log:${deviceId}:${parameter}`;
    const hashKey = `mqtt:current:${deviceId}`;
    const data = JSON.stringify({ value, timestamp: new Date().toISOString() });

    try {
        // Simpan ke LIST untuk histori
        await redisClient.lpush(listKey, data);
        await redisClient.ltrim(listKey, 0, 9);

        // Simpan ke HASH untuk data terkini
        await redisClient.hset(hashKey, parameter, data);
        await redisClient.expire(hashKey, 60); // Set TTL (60 detik)

        console.log(`[POST] Data ${parameter} berhasil disimpan ke Redis untuk Device ID: ${deviceId}`);
    } catch (err) {
        console.error(`[ERROR] Gagal menyimpan data ${parameter} ke Redis untuk Device ID ${deviceId}:`, err.message);
    }
}

// Buffer per-device untuk menampung data sementara (Map keyed by deviceId —
// juga mencegah prototype pollution lewat payload device id)
const dataBuffer = new Map();

// Fungsi untuk menghitung rata-rata
function calculateAverages(buffer) {
    const averages = {};

    for (const parameter in buffer) {
        const values = buffer[parameter].filter((val) => !isNaN(parseFloat(val))); // Hanya nilai valid
        if (values.length > 0) {
            const sum = values.reduce((acc, val) => acc + parseFloat(val), 0);
            averages[parameter] = sum / values.length;
        } else {
            averages[parameter] = null; // Tetapkan null jika tidak ada data valid
        }
    }

    return averages;
}

// Fungsi untuk mengirim data ke Laravel (melempar error agar caller bisa retry)
async function sendDataToLaravel(historyId, averages, deviceId) {
    try {
        await axios.post(
            `${process.env.LARAVEL_API_URL}/historical-data`,
            {
                history_id: historyId,
                parameters: averages,
                devices_id: deviceId,
            },
            { timeout: HTTP_TIMEOUT_MS }
        );

        console.log(`[POST] Data berhasil dikirim ke Laravel untuk Device ID ${deviceId}`);
    } catch (error) {
        console.error(`[ERROR] Gagal mengirim data ke Laravel untuk Device ID ${deviceId}: ${error.response?.data?.message || error.message}`);
        throw error;
    }
}

// historyId yang sedang "in-flight" per device: dipertahankan saat retry agar jika
// attempt sebelumnya sukses tapi respons hilang, Laravel menolak duplikat
// (constraint unique history_id) dan kita bisa reset buffer tanpa membuat baris ganda.
const pendingFlush = new Map(); // key: deviceId, value: { historyId, attempts }

// Scheduler untuk memproses data rata-rata setiap 5 menit
schedule.scheduleJob("*/5 * * * *", async () => {
    console.log("[INFO] Scheduler mulai untuk memproses data rata-rata.");
    try {
        for (const [deviceId, buffer] of dataBuffer.entries()) {
            const hasData = Object.values(buffer).some((values) => values.length > 0);
            if (!hasData) {
                console.log(`[INFO] Tidak ada data baru untuk Device ID ${deviceId}, dilewati.`);
                continue;
            }

            const averages = calculateAverages(buffer);
            const pending = pendingFlush.get(deviceId) || { historyId: randomUUID(), attempts: 0 };
            pendingFlush.set(deviceId, pending);

            try {
                await sendDataToLaravel(pending.historyId, averages, deviceId);
                // Reset buffer HANYA setelah sukses
                dataBuffer.delete(deviceId);
                pendingFlush.delete(deviceId);
                console.log(`[POST] Data rata-rata untuk Device ID ${deviceId} berhasil dikirim ke Laravel.`);
            } catch (error) {
                // 422 pada field history_id = attempt sebelumnya sukses tapi respons hilang
                const duplicateHistory = error.response?.status === 422 && error.response.data?.errors?.history_id;
                if (duplicateHistory) {
                    console.warn(`[WARNING] Data Device ID ${deviceId} sudah tersimpan (history_id duplikat), buffer di-reset.`);
                    dataBuffer.delete(deviceId);
                    pendingFlush.delete(deviceId);
                    continue;
                }

                pending.attempts += 1;
                if (pending.attempts >= MAX_FLUSH_ATTEMPTS) {
                    // Dead-letter: log isi data lalu buang, agar buffer tidak membengkak selamanya
                    console.error(`[ERROR] [DEAD-LETTER] Data Device ID ${deviceId} gagal dikirim ${MAX_FLUSH_ATTEMPTS} kali, dibuang:`, JSON.stringify(averages));
                    dataBuffer.delete(deviceId);
                    pendingFlush.delete(deviceId);
                } else {
                    console.error(`[ERROR] Gagal mengirim data (Historical) rata-rata untuk Device ID ${deviceId} (percobaan ke-${pending.attempts}/${MAX_FLUSH_ATTEMPTS}). Buffer dipertahankan untuk retry.`);
                }
            }
        }

        // Bersihkan state per-device yang sudah lama tidak terlihat
        const now = Date.now();
        for (const [deviceId, state] of deviceState.entries()) {
            if (now - state.lastSeenAt > DEVICE_STATE_TTL_MS) {
                deviceState.delete(deviceId);
            }
        }
    } catch (error) {
        console.error("[ERROR] Scheduler gagal memproses data rata-rata:", error.message);
    }
    console.log("[INFO] Scheduler selesai memproses data rata-rata.");
});

// State per-device (Map keyed by deviceId) — pengganti variabel global agar device
// yang publish bersamaan tidak saling menimpa state.
// CATATAN: topik MQTT tidak membawa device id per pesan, jadi pesan sensor tetap
// dipetakan ke device yang terakhir mengumumkan device-id (batasan protokol; perbaikan
// penuh butuh sinkron firmware agar device id ikut dalam payload/topic).
const deviceState = new Map(); // key: deviceId, value: { deviceType, lastSeenAt }
let activeDeviceId = null;

let messageQueue = []; // Antrian untuk pesan MQTT
let isProcessingQueue = false; // Flag untuk memproses pesan
let droppedMessageCount = 0; // Hitung pesan yang dibuang karena antrian penuh

const validFeeds = ["proto-one-monitoring-1.device-id", "proto-one-monitoring-1.device-type", "proto-one-monitoring-1.temperature",
    "proto-one-monitoring-1.humidity", "proto-one-monitoring-1.soil-moisture",];

// Pemetaan nama feed ke properti buffer
const bufferKeyMap = {
    "proto-one-monitoring-1.temperature": "temperature",
    "proto-one-monitoring-1.humidity": "humidity",
    "proto-one-monitoring-1.soil-moisture": "soil_moisture"
};

// Fungsi untuk memproses antrian pesan
async function processQueue() {
    if (isProcessingQueue) return;
    isProcessingQueue = true;

    try {
        while (messageQueue.length > 0) {
            const { topic, message } = messageQueue.shift();

            try {
                const payload = message.toString().trim();

                console.log(`[INFO] Pesan diterima. Topik: ${topic}, Payload: ${payload}`);

                // Parsing nama feed dari topik
                const feedType = topic.split("/").pop(); // Ambil bagian terakhir dari topik

                // Filter hanya topik yang valid
                if (!validFeeds.includes(feedType)) {
                    console.log(`[WARNING] Topik tidak dikenal: ${feedType}`);
                    continue;
                }

                switch (feedType) {
                    case "proto-one-monitoring-1.device-id":
                        activeDeviceId = payload;
                        if (!deviceState.has(payload)) {
                            deviceState.set(payload, { deviceType: null, lastSeenAt: Date.now() });
                        } else {
                            deviceState.get(payload).lastSeenAt = Date.now();
                        }
                        console.log(`[GET] Device ID diterima: ${activeDeviceId}`);
                        break;

                    case "proto-one-monitoring-1.device-type":
                        if (activeDeviceId && deviceState.has(activeDeviceId)) {
                            deviceState.get(activeDeviceId).deviceType = payload;
                            console.log(`[GET] Tipe perangkat diterima: ${payload} (Device ID ${activeDeviceId})`);
                        } else {
                            console.log("[WARNING] Tipe perangkat diterima sebelum Device ID diketahui. Diabaikan.");
                        }
                        break;

                    case "proto-one-monitoring-1.temperature":
                    case "proto-one-monitoring-1.humidity":
                    case "proto-one-monitoring-1.soil-moisture":
                        if (activeDeviceId) {
                            await saveToRedis(feedType, payload, activeDeviceId);

                            if (!dataBuffer.has(activeDeviceId)) {
                                dataBuffer.set(activeDeviceId, { temperature: [], humidity: [], soil_moisture: [] });
                            }

                            const bufferKey = bufferKeyMap[feedType];
                            if (bufferKey) {
                                const buffer = dataBuffer.get(activeDeviceId);
                                buffer[bufferKey].push(payload);
                                if (buffer[bufferKey].length > MAX_BUFFER_LEN) {
                                    buffer[bufferKey].shift();
                                }
                                console.log(`[POST] Data ${bufferKey} diproses ke Buffer untuk Device ID ${activeDeviceId}: ${payload}`);
                            }
                        } else {
                            console.log("[WARNING] Device ID belum tersedia. Data diabaikan.");
                        }
                        break;
                }

                // Daftarkan perangkat baru bila tipe sudah diketahui dan keberadaannya
                // belum terkonfirmasi (cache). Setelah terkonfirmasi, tidak ada lagi HTTP
                // ke Laravel per pesan.
                if (activeDeviceId) {
                    const state = deviceState.get(activeDeviceId);
                    if (state && state.deviceType && getCachedDeviceKnown(activeDeviceId) !== true) {
                        await handleDevice(activeDeviceId, state.deviceType);
                    }
                }
            } catch (error) {
                // Satu pesan gagal tidak boleh menghentikan pemrosesan pesan lainnya
                console.error("[ERROR] Gagal memproses pesan dari topik:", topic, "->", error.message);
            }
        }
    } finally {
        isProcessingQueue = false;
    }
}

// Setup MQTT dengan wildcard
mqttClient.on("connect", () => {
    console.log("[INFO] Terhubung ke broker MQTT.");

    // Langganan semua topik dengan wildcard
    const wildcardTopic = `${process.env.MQTT_USERNAME}/feeds/+`;
    mqttClient.subscribe(wildcardTopic, (err) => {
        if (err) {
            console.error("[ERROR] Gagal berlangganan wildcard topik:", err.message);
        } else {
            console.log("[INFO] Berhasil berlangganan wildcard topik feeds.");
        }
    });
});

// Event untuk menerima pesan
mqttClient.on("message", (topic, message) => {
    // Cap antrian: drop pesan tertua bila penuh, hitung counter
    if (messageQueue.length >= MAX_QUEUE_SIZE) {
        messageQueue.shift();
        droppedMessageCount += 1;
        console.warn(`[WARNING] Antrian penuh (${MAX_QUEUE_SIZE}). Pesan tertua dibuang. Total dibuang: ${droppedMessageCount}`);
    }
    messageQueue.push({ topic, message }); // Tambahkan pesan ke antrian
    processQueue().catch((error) => {
        // Jaga-jaga bila processQueue melempar di luar try/finally
        console.error("[ERROR] Gagal memproses antrian pesan:", error.message);
    });
});

mqttClient.on("error", (error) => {
    console.error("[ERROR] Kesalahan pada koneksi MQTT:", error.message);
});

mqttClient.on("close", () => {
    console.log("[INFO] Koneksi ke broker MQTT telah ditutup.");
});

mqttClient.on("reconnect", () => {
    console.log("[INFO] Menghubungkan ulang ke broker MQTT...");
});

// Endpoint untuk menyimpan token JWT (server-to-server dari Laravel)
app.post("/api/store-token", apiLimiter, requireSharedSecret, async (req, res) => {
    const { token, users_id } = req.body;

    if (!token || !users_id) {
        return res.status(400).json({ message: "Token dan Users ID diperlukan." });
    }

    try {
        const result = await storeUserToken(users_id, token);
        res.status(200).json(result);
    } catch (error) {
        console.error("[ERROR] Gagal menyimpan token:", error.message);
        res.status(500).json({ message: "Gagal menyimpan token." });
    }
});

// Endpoint untuk menampilkan data parameter ke dashboard dari Redis
app.get("/api/dashboard/:deviceId", apiLimiter, authenticateToken, async (req, res) => {
    const { deviceId } = req.params;

    // Validasi Device ID sebelum dipakai di URL (cegah SSRF)
    if (!DEVICE_ID_RE.test(deviceId)) {
        return res.status(400).json({ message: "Device ID tidak valid." });
    }

    const tokenKey = `jwt:user:${req.user.sub}`;
    let token;

    try {
        // Ambil token dari Redis berdasarkan `req.user.id`
        token = await redisClient.get(tokenKey);
        if (!token) {
            return res.status(403).json({
                message: "Token tidak ditemukan di Redis. Anda harus login ulang.",
            });
        }

        // Periksa TTL dari token di Redis
        const ttl = await redisClient.ttl(tokenKey);
        if (ttl <= 0) {
            return res.status(403).json({
                message: "Token sudah kedaluwarsa. Silakan login ulang.",
            });
        }

        // Validasi apakah perangkat terhubung dengan pengguna
        const response = await axios.get(`${process.env.LARAVEL_API_URL}/device/check-private/${encodeURIComponent(deviceId)}`, {
            headers: {
                Authorization: `Bearer ${token}`,
            },
            timeout: HTTP_TIMEOUT_MS,
        });

        if (
            !response.data ||
            response.data.status !== "success" ||
            response.data.data.users_id !== req.user.sub
        ) {
            return res.status(403).json({
                message: "Perangkat ini tidak terhubung dengan akun Anda.",
            });
        }

        // Ambil data terkini dari Redis
        const hashKey = `mqtt:current:${deviceId}`;
        const redisData = await redisClient.hgetall(hashKey);

        if (!redisData || Object.keys(redisData).length === 0) {
            return res.status(404).json({
                message: `Tidak ada data terkini untuk Device ID: ${deviceId}`,
            });
        }

        // Parse data Redis
        const parsedData = {};
        for (const [key, value] of Object.entries(redisData)) {
            try {
                const parsedValue = JSON.parse(value);
                parsedData[key] = {
                    value: parsedValue.value,
                    timestamp: parsedValue.timestamp,
                };
            } catch (err) {
                console.error("Gagal parse data Redis untuk:", key, "->", err.message);
            }
        }

        // Respon sukses
        res.status(200).json({
            message: `Data terkini untuk Device ID: ${deviceId}`,
            data: parsedData,
        });
    } catch (error) {
        // Detail error hanya untuk log server — jangan bocorkan ke klien
        console.error("Kesalahan saat memproses data dashboard untuk Device ID:", deviceId, "->", error.message);
        res.status(500).json({
            message: "Kesalahan server saat memproses permintaan.",
        });
    }
});

// Start server
app.listen(PORT, () => {
    console.log(`[INFO] serverMonitoring berjalan di port ${PORT}`);
});
