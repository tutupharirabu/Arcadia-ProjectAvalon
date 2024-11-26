require("dotenv").config();
const express = require("express");
const mqtt = require("mqtt");
const bodyParser = require("body-parser");
const helmet = require("helmet");
const cors = require("cors");
const axios = require("axios");
const Redis = require("ioredis");
const { v4: uuidv4 } = require("uuid");

// Inisialisasi aplikasi Express
const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(bodyParser.json());
app.use(helmet());
app.use(cors());

// Konfigurasi Redis
const redisClient = new Redis(process.env.REDIS_URL);
redisClient.on("connect", () => console.log("Terhubung ke Redis Railway."));
redisClient.on("error", (err) => console.error("Kesalahan Redis Client:", err.message));

// Konfigurasi MQTT
const mqttOptions = {
    username: process.env.MQTT_USERNAME,
    password: process.env.MQTT_KEY,
};
const mqttClient = mqtt.connect(process.env.MQTT_BROKER_URL, mqttOptions);

let messageQueue = []; // Antrian untuk pesan MQTT
let isProcessingQueue = false; // Flag untuk memproses pesan

async function storeUserToken(users_id, token) {
    if (!users_id || !token) {
        throw new Error("Users ID dan token diperlukan untuk menyimpan token.");
    }

    try {
        // Simpan token ke Redis dengan waktu kedaluwarsa (24 jam = 86400 detik)
        await redisClient.set(`jwt:user:${users_id}`, token, "EX", 86400);
        console.log(`Token untuk pengguna ${users_id} berhasil disimpan.`);
        return { status: "success", message: "Token berhasil disimpan." };
    } catch (error) {
        console.error(`Gagal menyimpan token untuk pengguna ${users_id}:`, error.message);
        throw new Error("Gagal menyimpan token ke Redis.");
    }
}

// Fungsi untuk memeriksa apakah perangkat sudah ada di Laravel
async function checkDeviceExist(deviceId) {
    try {
        const response = await axios.get(`${process.env.LARAVEL_API_URL}/device/check-public/${deviceId}`);
        if (response.data.status === "success") {
            console.log(`Perangkat dengan Device ID ${deviceId} sudah ada.`);
            return true;
        }
    } catch (error) {
        if (error.response && error.response.status === 404) {
            console.log(`Perangkat dengan Device ID ${deviceId} belum ada.`);
            return false;
        }
        console.error("Gagal memeriksa perangkat di Laravel:", error.message);
        throw error;
    }
}

// Fungsi untuk menyimpan perangkat ke Laravel jika belum ada
async function saveDeviceIfNotExist(deviceId, deviceType) {
    try {
        const deviceExist = await checkDeviceExist(deviceId);
        if (!deviceExist) {
            const response = await axios.post(`${process.env.LARAVEL_API_URL}/device`, {
                devices_id: deviceId,
                device_type: deviceType,
            });
            console.log(`Perangkat berhasil disimpan ke Laravel: ${deviceId}`);
        }
    } catch (error) {
        console.error("Gagal menyimpan perangkat ke Laravel:", error.message);
    }
}

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

        // Set TTL untuk HASH (60 detik)
        await redisClient.expire(hashKey, 60);

        console.log(`Data ${parameter} berhasil disimpan ke Redis untuk Device ID: ${deviceId}`);
    } catch (err) {
        console.error(`Gagal menyimpan data ${parameter} ke Redis untuk Device ID ${deviceId}:`, err.message);
    }
}

let currentDeviceId = null;
let currentDeviceType = null;

// Fungsi untuk memproses antrian pesan
async function processQueue() {
    if (isProcessingQueue) return; // Cegah proses ganda
    isProcessingQueue = true;

    try {
        while (messageQueue.length > 0) {
            const { topic, message } = messageQueue.shift(); // Ambil pesan dari antrian
            const payload = message.toString().trim();

            try {
                if (topic === process.env.TOPIC_DEVICE_ID) {
                    currentDeviceId = payload;
                    console.log(`Device ID diterima: ${currentDeviceId}`);
                } else if (topic === process.env.TOPIC_DEVICE_TYPE) {
                    currentDeviceType = payload;
                    console.log(`Tipe perangkat diterima: ${currentDeviceType}`);
                } else {
                    const parameterMap = {
                        [process.env.TOPIC_TEMPERATURE]: "temperature",
                        [process.env.TOPIC_HUMIDITY]: "humidity",
                        [process.env.TOPIC_SOIL_MOISTURE]: "soil_moisture",
                    };

                    const parameter = parameterMap[topic];
                    if (parameter && currentDeviceId) {
                        // Simpan data ke Redis
                        await saveToRedis(parameter, payload, currentDeviceId);
                        console.log(`Data ${parameter} diproses untuk Device ID ${currentDeviceId}`);
                    } else {
                        console.log("Topik atau Device ID tidak valid, data diabaikan.");
                    }
                }

                // Jika Device ID dan Tipe Perangkat tersedia, simpan perangkat
                if (currentDeviceId && currentDeviceType) {
                    await saveDeviceIfNotExist(currentDeviceId, currentDeviceType);
                } else {
                    console.log("Device ID atau tipe perangkat belum lengkap. Menunggu data berikutnya.");
                }
            } catch (error) {
                console.error(`Terjadi kesalahan saat memproses topik ${topic}: ${error.message}`);
            }
        }

        console.log("[SELESAI] Semua pesan dalam antrian telah diproses.");
        console.log("---------------------------------------------\n");
    } finally {
        isProcessingQueue = false; // Reset flag
    }
}

// Setup MQTT
mqttClient.on("connect", () => {
    console.log("Terhubung ke broker MQTT.");
    console.log("");

    // Langganan topik MQTT
    mqttClient.subscribe(process.env.TOPIC_DEVICE_ID);
    mqttClient.subscribe(process.env.TOPIC_DEVICE_TYPE);
    mqttClient.subscribe(process.env.TOPIC_TEMPERATURE);
    mqttClient.subscribe(process.env.TOPIC_HUMIDITY);
    mqttClient.subscribe(process.env.TOPIC_SOIL_MOISTURE);
});

mqttClient.on("message", (topic, message) => {
    messageQueue.push({ topic, message }); // Tambahkan pesan ke antrian
    processQueue(); // Mulai memproses antrian
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

const authenticateToken = require("./middleware/authMiddleware");

// Endpoint untuk menampilkan data parameter dari Redis
app.get("/api/dashboard/:deviceId", authenticateToken, async (req, res) => {
    const { deviceId } = req.params; // Ambil `deviceId` dari parameter URL
    const tokenKey = `jwt:user:${req.user.sub}`; // Gunakan `req.user.id` dari middleware
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
    console.log(`Server berjalan di http://localhost:${PORT}`);
});
