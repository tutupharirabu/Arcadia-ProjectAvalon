require("dotenv").config();
const mqtt = require("mqtt");
const express = require("express");
const bodyParser = require("body-parser");
const helmet = require("helmet");
const cors = require("cors");
const axios = require("axios");
const QRCode = require("qrcode");

const cloudinary = require("./cloudinaryConfig");
const { requireSharedSecret } = require("./middleware/authMiddleware");

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
const PORT = process.env.PORT_WATER || 3001;

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
const DEVICE_CACHE_TTL_MS = 5 * 60 * 1000; // TTL cache keberadaan device (5 menit)
const ERROR_CACHE_TTL_MS = 30 * 1000; // TTL cache pendek saat pengecekan gagal (Laravel down)
const DEVICE_STATE_TTL_MS = 10 * 60 * 1000; // TTL state per-device (10 menit)

// Validasi Device ID: hanya alfanumerik, tanda hubung, dan underscore (cegah SSRF/path traversal)
const DEVICE_ID_RE = /^[A-Za-z0-9_-]{1,64}$/;

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
    // Tolak Device ID yang tidak valid sebelum dipakai di URL (cegah SSRF)
    if (!DEVICE_ID_RE.test(deviceId)) {
        console.warn("[WARNING] Device ID ditolak karena tidak valid:", deviceId);
        return false;
    }

    const cached = getCachedDeviceKnown(deviceId);
    if (cached !== null) {
        return cached;
    }

    try {
        const response = await axios.get(`${process.env.LARAVEL_API_URL}/device/check-public/${encodeURIComponent(deviceId)}`, {
            timeout: HTTP_TIMEOUT_MS,
        });
        if (response.data.status === "success") {
            setDeviceKnown(deviceId, true);
            return true;
        }
        return false;
    } catch (error) {
        if (error.response && error.response.status === 404) {
            console.log("[ERROR] Perangkat dengan Device ID belum ada:", deviceId);
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

// Feed yang relevan untuk watering
const wateringFeeds = [
    "proto-one-watering-1.device-id",
    "proto-one-watering-1.device-type",
    "proto-one-watering-1.pump-control",
];

// State per-device (Map keyed by deviceId) — pengganti variabel global agar device
// yang publish bersamaan tidak saling menimpa state.
const deviceState = new Map(); // key: deviceId, value: { deviceType, lastSeenAt }
let activeDeviceId = null;

let messageQueue = []; // Antrian untuk pesan MQTT
let isProcessingQueue = false; // Flag untuk memproses pesan
let droppedMessageCount = 0; // Hitung pesan yang dibuang karena antrian penuh

// Normalisasi payload kontrol pompa: dukung JSON { action, device_id } ATAU string
// polos "ON"/"OFF" (firmware lama). Firmware baru diharapkan memakai JSON.
function parsePumpAction(payload) {
    try {
        const parsed = JSON.parse(payload);
        if (parsed && typeof parsed.action === "string") {
            return parsed.action.trim().toUpperCase();
        }
    } catch (error) {
        // Bukan JSON — fallback ke string polos
    }
    return typeof payload === "string" ? payload.trim().toUpperCase() : "";
}

// Fungsi untuk memproses antrian pesan
async function processQueue() {
    if (isProcessingQueue) return; // Hindari memproses jika sudah berjalan
    isProcessingQueue = true;

    try {
        while (messageQueue.length > 0) {
            const { topic, message } = messageQueue.shift();

            try {
                const payload = message.toString().trim();

                console.log(`[INFO] Pesan diterima. Topik: ${topic}, Payload: ${payload}`);

                // Parsing nama feed dari topik
                const feedType = topic.split("/").pop(); // Ambil bagian terakhir dari topik

                // Filter hanya feed yang relevan untuk watering
                if (!wateringFeeds.includes(feedType)) {
                    console.log(`[WARNING] Topik tidak relevan untuk watering: ${feedType}`);
                    continue;
                }

                switch (feedType) {
                    case "proto-one-watering-1.device-id":
                        activeDeviceId = payload;
                        if (!deviceState.has(payload)) {
                            deviceState.set(payload, { deviceType: null, lastSeenAt: Date.now() });
                        } else {
                            deviceState.get(payload).lastSeenAt = Date.now();
                        }
                        console.log(`[GET] Device ID diterima: ${activeDeviceId}`);
                        break;

                    case "proto-one-watering-1.device-type":
                        if (activeDeviceId && deviceState.has(activeDeviceId)) {
                            deviceState.get(activeDeviceId).deviceType = payload;
                            console.log(`[GET] Tipe perangkat diterima: ${payload} (Device ID ${activeDeviceId})`);
                        } else {
                            console.log("[WARNING] Tipe perangkat diterima sebelum Device ID diketahui. Diabaikan.");
                        }
                        break;

                    case "proto-one-watering-1.pump-control":
                        // CATATAN FIRMWARE: firmware lama mengirim string polos "ON"/"OFF";
                        // firmware baru diharapkan mengirim JSON { action: "ON"|"OFF", device_id }.
                        // Parser di sini mendukung keduanya (cek JSON dulu, fallback string)
                        // TANPA mengubah topik MQTT. Topik tidak diubah agar tidak perlu
                        // sinkronisasi firmware sekaligus — penyesuaian firmware menyusul.
                        {
                            const action = parsePumpAction(payload);
                            if (action === "ON" || action === "OFF") {
                                console.log(`[ACTION] Kontrol Pompa Air: ${action}`);
                            } else {
                                console.log(`[WARNING] Perintah pompa air tidak valid: ${payload}`);
                            }
                        }
                        break;

                    default:
                        console.log(`[WARNING] Feed tidak dikenali: ${feedType}`);
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

        // Bersihkan state per-device yang sudah lama tidak terlihat
        const now = Date.now();
        for (const [deviceId, state] of deviceState.entries()) {
            if (now - state.lastSeenAt > DEVICE_STATE_TTL_MS) {
                deviceState.delete(deviceId);
            }
        }
    } finally {
        isProcessingQueue = false; // Reset flag proses
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

// API untuk kontrol pompa (server-to-server dari Laravel — wajib shared-secret)
app.post("/api/water-pump/control", requireSharedSecret, async (req, res) => {
    const { device_id, action } = req.body;

    console.log("[INFO] Request kontrol pompa diterima:", { device_id, action });

    if (!device_id || !["ON", "OFF"].includes(action)) {
        console.error("[ERROR] Invalid device_id or action:", { device_id, action });
        return res.status(400).json({ error: "Invalid device_id or action." });
    }

    try {
        // Verifikasi device terdaftar di Laravel sebelum publish (dengan cache).
        // CATATAN: kepemilikan per-user tidak bisa diverifikasi di sini karena Laravel
        // belum meneruskan token user pemanggil — butuh perubahan WaterPumpController
        // (di luar scope Wave 1) agar node bisa cek via /device/check-private.
        const deviceExist = await checkDeviceExist(device_id);
        if (deviceExist === null) {
            // Pengecekan gagal (mis. Laravel down) — jangan publish tanpa verifikasi
            console.error(`[ERROR] Gagal memverifikasi device ${device_id} di Laravel.`);
            return res.status(503).json({ error: "Gagal memverifikasi device. Coba lagi nanti." });
        }
        if (!deviceExist) {
            console.error(`[ERROR] Device ${device_id} tidak ditemukan di Laravel.`);
            return res.status(404).json({ error: "Device tidak ditemukan." });
        }

        // Payload JSON konsisten dengan parser penerimaan (lihat parsePumpAction)
        const payload = JSON.stringify({ device_id, action, sent_at: new Date().toISOString() });
        mqttClient.publish(`${process.env.MQTT_USERNAME}/feeds/proto-one-watering-1.pump-control`, payload, (err) => {
            if (err) {
                console.error("[ERROR] Gagal mengirim kontrol ke MQTT:", err);
                return res.status(500).json({ error: "Failed to send pump control." });
            }

            console.log(`[INFO] Kontrol pompa berhasil dikirim: ${payload}`);
            res.status(200).json({ message: "Pump control sent successfully." });
        });
    } catch (error) {
        // Detail error hanya untuk log server — jangan bocorkan ke klien
        console.error("[ERROR] Gagal memproses kontrol pompa:", error.message);
        res.status(500).json({ error: "Failed to send pump control." });
    }
});

// Start server
app.listen(PORT, () => {
    console.log(`[INFO] serverWatering berjalan di port ${PORT}`);
});
