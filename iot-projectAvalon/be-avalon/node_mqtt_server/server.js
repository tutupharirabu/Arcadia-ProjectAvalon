require("dotenv").config();
const express = require("express");
const mqtt = require("mqtt");
const bodyParser = require("body-parser");
const helmet = require("helmet");
const cors = require("cors");
const axios = require("axios");
const Redis = require("ioredis");
const { v4: uuidv4 } = require("uuid"); // Untuk validasi dan konversi UUID

// Inisialisasi aplikasi Express
const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(bodyParser.json());
app.use(helmet());
app.use(cors());

// Konfigurasi Redis (Railway)
const redisClient = new Redis(process.env.REDIS_URL);
redisClient.on("connect", () => {
    console.log("Terhubung ke Redis Railway.");
});
redisClient.on("error", (err) => {
    console.error("Redis Client Error:", err);
});

// MQTT Configuration
const mqttOptions = {
    username: process.env.MQTT_USERNAME,
    password: process.env.MQTT_KEY,
};
const mqttClient = mqtt.connect(process.env.MQTT_BROKER_URL, mqttOptions);

// Variabel untuk menyimpan JWT token
let jwtToken = null;

// Endpoint untuk menerima token JWT dari Laravel
app.post("/api/store-token", (req, res) => {
    const { token } = req.body;

    if (!token) {
        return res.status(400).json({ message: "Token diperlukan." });
    }

    jwtToken = token; // Simpan token JWT di memori
    console.log("Token diterima dari Laravel:", token);

    res.status(200).json({ message: "Token berhasil disimpan." });
});

// Fungsi untuk menyimpan data ke Redis
async function saveToRedis(topic, parameter, value, deviceId) {
    const key = `mqtt:${deviceId}:${parameter}`;
    const data = JSON.stringify({ value, timestamp: new Date().toISOString() });

    try {
        await redisClient.set(key, data, "EX", 10);
        console.log(`Data disimpan ke Redis dengan kunci: ${key}`);
    } catch (err) {
        console.error("Gagal menyimpan ke Redis:", err.message);
    }
}

// Fungsi untuk mengirim data dari Redis ke Laravel
async function sendToLaravelFromRedis(deviceId, parameter) {
    const key = `mqtt:${deviceId}:${parameter}`;
    try {
        const data = await redisClient.get(key);
        if (!data) {
            console.error("Data tidak ditemukan di Redis untuk kunci:", key);
            return;
        }

        const parsedData = JSON.parse(data);

        const response = await axios.post(
            `${process.env.LARAVEL_API_URL}/historical-data`,
            {
                parameters: {
                    [parameter]: parsedData.value,
                },
                waktu_diambil: parsedData.timestamp,
                devices_id: deviceId,
            },
            {
                headers: {
                    Authorization: `Bearer ${jwtToken}`,
                },
            }
        );

        console.log(`Data dari Redis berhasil dikirim ke Laravel: ${response.data.message}`);
    } catch (error) {
        console.error("Gagal mengirim data dari Redis ke Laravel:", error.response?.data || error.message);
    }
}

// Fungsi untuk menyimpan perangkat ke Laravel
async function saveDeviceToLaravel(deviceId, deviceType) {
    try {
        const response = await axios.post(
            `${process.env.LARAVEL_API_URL}/device`,
            {
                devices_id: deviceId,
                device_type: deviceType,
                status: "active",
            },
            {
                headers: {
                    Authorization: `Bearer ${jwtToken}`,
                },
            }
        );

        console.log(`Perangkat berhasil disimpan ke Laravel: ${response.data.message}`);
    } catch (error) {
        console.error("Gagal menyimpan perangkat ke Laravel:", error.response?.data || error.message);
    }
}

// MQTT Setup
mqttClient.on("connect", () => {
    console.log("Terhubung ke broker MQTT.");

    // Subscribe ke semua topik yang diperlukan
    mqttClient.subscribe(process.env.TOPIC_DEVICE_ID);
    mqttClient.subscribe(process.env.TOPIC_DEVICE_TYPE);
    mqttClient.subscribe(process.env.TOPIC_TEMPERATURE);
    mqttClient.subscribe(process.env.TOPIC_HUMIDITY);
    mqttClient.subscribe(process.env.TOPIC_SOIL_MOISTURE);
});

// Variabel untuk menyimpan device ID dan tipe perangkat
let currentDeviceId = null;
let currentDeviceType = null;

mqttClient.on("message", (topic, message) => {
    const payload = message.toString();
    console.log(`Pesan diterima dari topik ${topic}: ${payload}`);

    if (topic === process.env.TOPIC_DEVICE_ID) {
        // Validasi atau konversi device ID ke UUID
        if (uuidv4().test(payload)) {
            currentDeviceId = payload; // Jika valid UUID, gunakan langsung
        } else {
            currentDeviceId = uuidv4(); // Jika tidak valid, buat UUID baru
        }
        console.log(`Device ID diperbarui: ${currentDeviceId}`);
    } else if (topic === process.env.TOPIC_DEVICE_TYPE) {
        currentDeviceType = payload;
        console.log(`Device Type diperbarui: ${currentDeviceType}`);

        // Simpan perangkat ke Laravel jika ID dan tipe tersedia
        if (currentDeviceId && currentDeviceType) {
            saveDeviceToLaravel(currentDeviceId, currentDeviceType);
        }
    } else if (currentDeviceId) {
        // Tentukan parameter berdasarkan topik
        let parameter = "";
        if (topic === process.env.TOPIC_TEMPERATURE) parameter = "temperature";
        if (topic === process.env.TOPIC_HUMIDITY) parameter = "humidity";
        if (topic === process.env.TOPIC_SOIL_MOISTURE) parameter = "soil_moisture";

        if (parameter) {
            // Simpan ke Redis
            saveToRedis(topic, parameter, payload, currentDeviceId);

            // Kirim data ke Laravel dari Redis
            sendToLaravelFromRedis(currentDeviceId, parameter);
        }
    } else {
        console.error("Device ID belum tersedia. Data diabaikan.");
    }
});

// Start server
app.listen(PORT, () => {
    console.log(`Server berjalan di http://localhost:${PORT}`);
});
