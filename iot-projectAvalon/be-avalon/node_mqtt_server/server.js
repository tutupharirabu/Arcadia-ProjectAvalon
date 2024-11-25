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

// Fungsi untuk memeriksa apakah perangkat sudah ada di Laravel
async function checkDeviceExist(deviceId) {
    try {
        const response = await axios.get(`${process.env.LARAVEL_API_URL}/device/check/${deviceId}`);
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

let currentDeviceId = null;
let currentDeviceType = null;

// Fungsi untuk memproses antrian pesan
async function processQueue() {
    if (isProcessingQueue) return; // Jangan mulai proses baru jika masih ada yang berjalan
    isProcessingQueue = true;

    try {
        while (messageQueue.length > 0) {
            const { topic, message } = messageQueue.shift();

            try {
                const payload = message.toString().trim();

                if (topic === process.env.TOPIC_DEVICE_ID) {
                    currentDeviceId = payload;
                    console.log(`Device ID diterima: ${currentDeviceId}`);
                }

                if (topic === process.env.TOPIC_DEVICE_TYPE) {
                    currentDeviceType = payload;
                    console.log(`Tipe perangkat diterima: ${currentDeviceType}`);
                }

                if (currentDeviceId && currentDeviceType) {
                    await saveDeviceIfNotExist(currentDeviceId, currentDeviceType);
                } else {
                    console.log("Device ID atau tipe perangkat belum lengkap. Menunggu.");
                }
            } catch (error) {
                console.error("Terjadi kesalahan saat memproses pesan MQTT:", error.message);
            }
        }

        console.log("[SELESAI] Loop antrian pesan selesai."); // Penanda bahwa loop sudah selesai
        console.log("---------------------------------------------\n")
    } finally {
        isProcessingQueue = false; // Set flag ke false setelah semua pesan selesai diproses
    }
}

// MQTT Setup
mqttClient.on("connect", () => {
    console.log("Terhubung ke broker MQTT.");
    console.log("");

    mqttClient.subscribe(process.env.TOPIC_DEVICE_ID);
    mqttClient.subscribe(process.env.TOPIC_DEVICE_TYPE);
});

mqttClient.on("message", (topic, message) => {
    messageQueue.push({ topic, message }); // Tambahkan pesan ke antrian
    processQueue(); // Proses antrian
});

// Endpoint untuk menyimpan token JWT
app.post("/api/store-token", async (req, res) => {
    const { token, users_id } = req.body;

    if (!token || !users_id) {
        return res.status(400).json({ message: "Token dan Users ID diperlukan." });
    }

    try {
        await redisClient.set(`jwt:user:${users_id}`, token, "EX", 86400);
        console.log(`Token untuk pengguna ${users_id} berhasil disimpan.`);
        res.status(200).json({ message: "Token berhasil disimpan." });
    } catch (error) {
        console.error("Gagal menyimpan token:", error.message);
        res.status(500).json({ message: "Gagal menyimpan token." });
    }
});

// Start server
app.listen(PORT, () => {
    console.log(`Server berjalan di http://localhost:${PORT}`);
});
