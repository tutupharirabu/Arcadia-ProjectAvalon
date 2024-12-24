require("dotenv").config();
const express = require("express");
const mqtt = require("mqtt");
const bodyParser = require("body-parser");
const helmet = require("helmet");
const cors = require("cors");
const axios = require("axios");
const Redis = require("ioredis");
const schedule = require("node-schedule");
const { v4: uuidv4 } = require("uuid");
const QRCode = require("qrcode");

const cloudinary = require("./cloudinaryConfig");
const authenticateToken = require("./middleware/authMiddleware");

// Inisialisasi aplikasi Express
const app = express();
const PORT = process.env.PORT_MONITOR || 3000;

// Middleware
app.use(bodyParser.json());
app.use(helmet());
app.use(cors());

// Konfigurasi Redis
const redisClient = new Redis(process.env.REDIS_URL);
redisClient.on("connect", () => console.log("[INFO] Terhubung ke Redis Railway."));
redisClient.on("error", (err) => console.error("[INFO] Kesalahan Redis Client:", err.message));

// Konfigurasi MQTT
const mqttOptions = {
    username: process.env.MQTT_USERNAME,
    password: process.env.MQTT_KEY,
};
const mqttClient = mqtt.connect(process.env.MQTT_BROKER_URL, mqttOptions);

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
        console.error(`[ERROR] Gagal menyimpan token untuk pengguna ${users_id}:`, error.message);
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

// Fungsi untuk memeriksa apakah perangkat sudah ada di Laravel
async function checkDeviceExist(deviceId) {
    try {
        const response = await axios.get(`${process.env.LARAVEL_API_URL}/device/check-public/${deviceId}`);
        if (response.data.status === "success") {
            return true;
        }
    } catch (error) {
        if (error.response && error.response.status === 404) {
            console.log(`[ERROR] Perangkat dengan Device ID ${deviceId} belum ada.`);
            return false;
        }
        console.error("[ERROR] Gagal memeriksa perangkat di Laravel:", error.message);
        throw error;
    }
}

// Fungsi untuk menyimpan perangkat ke Laravel jika belum ada
async function saveDeviceToLaravel(deviceId, deviceType, qrCodeUrl) {
    try {
        const response = await axios.post(`${process.env.LARAVEL_API_URL}/device`, {
            devices_id: deviceId,
            device_type: deviceType,
            qrcode_url: qrCodeUrl,
        });
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
        if (deviceExist) {
            console.log(`[INFO] Perangkat sudah ada: ${deviceId}`);
            return; // Keluar jika perangkat sudah ada
        }

        // Langkah 2: Generate QR Code jika perangkat belum ada
        const qrCodeDataURL = await generateQRCode(deviceId);

        // Langkah 3: Unggah QR Code ke Cloudinary
        const qrCodeUrl = await uploadQRCodeToCloudinary(qrCodeDataURL, deviceId);

        // Langkah 4: Simpan perangkat ke Laravel
        await saveDeviceToLaravel(deviceId, deviceType, qrCodeUrl);

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

// Buffer untuk menampung data sementara
const dataBuffer = {};

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

// Fungsi untuk mengirim data ke Laravel
async function sendDataToLaravel(historyId, averages, deviceId) {
    try {
        const response = await axios.post(`${process.env.LARAVEL_API_URL}/historical-data`, {
            history_id: historyId,
            parameters: averages,
            devices_id: deviceId,
        });

        console.log(`[POST] Data berhasil dikirim ke Laravel untuk Device ID ${deviceId}`);
    } catch (error) {
        console.error(`[ERROR] Gagal mengirim data ke Laravel untuk Device ID ${deviceId}: ${error.response?.data?.message || error.message}`);
        console.error(`[DETAIL] Response Data: ${JSON.stringify(error.response?.data || {})}`);
    }
}

// Scheduler untuk memproses data rata-rata setiap 5 menit
schedule.scheduleJob("*/5 * * * *", async () => {
    console.log("[INFO] Scheduler mulai untuk memproses data rata-rata.");
    for (const deviceId in dataBuffer) {
        const averages = calculateAverages(dataBuffer[deviceId]);
        const historyId = uuidv4();
        try {
            await sendDataToLaravel(historyId, averages, deviceId);
            console.log(`[POST] Data rata-rata untuk Device ID ${deviceId} berhasil dikirim ke Laravel.`);
        } catch (error) {
            console.error(`[ERROR] Gagal mengirim data (Historical) rata-rata untuk Device ID ${deviceId}: ${error.message}`);
        }

        // Reset buffer setelah data diproses
        dataBuffer[deviceId] = { temperature: [], humidity: [], soil_moisture: [] };
    }
    console.log("[INFO] Scheduler selesai memproses data rata-rata.");
});

let currentDeviceId = null;
let currentDeviceType = null;

let messageQueue = []; // Antrian untuk pesan MQTT
let isProcessingQueue = false; // Flag untuk memproses pesan

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
                    currentDeviceId = payload;
                    console.log(`[GET] Device ID diterima: ${currentDeviceId}`);
                    break;

                case "proto-one-monitoring-1.device-type":
                    currentDeviceType = payload;
                    console.log(`[GET] Tipe perangkat diterima: ${currentDeviceType}`);
                    break;

                case "proto-one-monitoring-1.temperature":
                case "proto-one-monitoring-1.humidity":
                case "proto-one-monitoring-1.soil-moisture":
                    if (currentDeviceId) {
                        await saveToRedis(feedType, payload, currentDeviceId);

                        if (!dataBuffer[currentDeviceId]) {
                            dataBuffer[currentDeviceId] = { temperature: [], humidity: [], soil_moisture: [] };
                        }

                        const bufferKey = bufferKeyMap[feedType];
                        if (bufferKey) {
                            dataBuffer[currentDeviceId][bufferKey].push(payload);
                            console.log(`[POST] Data ${bufferKey} diproses ke Buffer untuk Device ID ${currentDeviceId}: ${payload}`);
                        }
                    } else {
                        console.log("[WARNING] Device ID belum tersedia. Data diabaikan.");
                    }
                    break;
            }

            if (currentDeviceId && currentDeviceType) {
                await handleDevice(currentDeviceId, currentDeviceType);
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
            console.log(`[INFO] Berhasil berlangganan wildcard topik: ${wildcardTopic}`);
        }
    });
});

// Event untuk menerima pesan
mqttClient.on("message", (topic, message) => {
    messageQueue.push({ topic, message }); // Tambahkan pesan ke antrian
    processQueue(); // Mulai memproses antrian
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

// Endpoint untuk menyimpan token JWT
app.post("/api/store-token", async (req, res) => {
    const { token, users_id } = req.body;

    if (!token || !users_id) {
        return res.status(400).json({ message: "Token dan Users ID diperlukan." });
    }

    try {
        const result = await storeUserToken(users_id, token);
        res.status(200).json(result);
    } catch (error) {
        res.status(500).json({ message: error.message });
    }
});

// Endpoint untuk menampilkan data parameter ke dashboard dari Redis
app.get("/api/dashboard/:deviceId", authenticateToken, async (req, res) => {
    const { deviceId } = req.params;
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
        const response = await axios.get(`${process.env.LARAVEL_API_URL}/device/check-private/${deviceId}`, {
            headers: {
                Authorization: `Bearer ${token}`,
            },
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
                console.error(`Gagal parse data Redis untuk ${key}:`, err.message);
            }
        }

        // Respon sukses
        res.status(200).json({
            message: `Data terkini untuk Device ID: ${deviceId}`,
            data: parsedData,
        });
    } catch (error) {
        console.error(`Kesalahan saat memproses data dashboard untuk Device ID ${deviceId}:`, error.message);
        res.status(500).json({
            message: "Kesalahan server saat memproses permintaan.",
            error: error.message,
        });
    }
});

// Start server
app.listen(PORT, () => {
    console.log(`[INFO] Server berjalan di https://arcadia-nodeserver-development.up.railway.app:${PORT}`);
});
