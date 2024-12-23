require("dotenv").config();
const mqtt = require("mqtt");
const express = require("express");
const bodyParser = require("body-parser");
const helmet = require("helmet");
const cors = require("cors");
const axios = require("axios");
const QRCode = require("qrcode");

const cloudinary = require("./cloudinaryConfig");

// Inisialisasi aplikasi Express
const app = express();
const PORT = process.env.PORT_WATER || 3001;

// Middleware
app.use(bodyParser.json());
app.use(helmet());
app.use(cors());

// Konfigurasi MQTT
const mqttOptions = {
    username: process.env.MQTT_USERNAME,
    password: process.env.MQTT_KEY,
};
const mqttClient = mqtt.connect(process.env.MQTT_BROKER_URL, mqttOptions);

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

// Feed yang relevan untuk watering
const wateringFeeds = [
    "proto-one-watering-1.device-id",
    "proto-one-watering-1.device-type",
    "proto-one-watering-1.pump-control",
];

let currentDeviceId = null;
let currentDeviceType = null;

let messageQueue = []; // Antrian untuk pesan MQTT
let isProcessingQueue = false; // Flag untuk memproses pesan

// Fungsi untuk memproses antrian pesan
async function processQueue() {
    if (isProcessingQueue) return; // Hindari memproses jika sudah berjalan
    isProcessingQueue = true;

    try {
        while (messageQueue.length > 0) {
            const { topic, message } = messageQueue.shift();
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
                    currentDeviceId = payload;
                    console.log(`[GET] Device ID diterima: ${currentDeviceId}`);
                    break;

                case "proto-one-watering-1.device-type":
                    currentDeviceType = payload;
                    console.log(`[GET] Tipe perangkat diterima: ${currentDeviceType}`);
                    break;

                case "proto-one-watering-1.pump-control":
                    // Tangkap kontrol pompa
                    if (payload === "ON" || payload === "OFF") {
                        console.log(`[ACTION] Kontrol Pompa Air: ${payload}`);
                    } else {
                        console.log(`[WARNING] Perintah pompa air tidak valid: ${payload}`);
                    }
                    break;

                default:
                    console.log(`[WARNING] Feed tidak dikenali: ${feedType}`);
            }

            // Proses perangkat baru
            if (currentDeviceId && currentDeviceType) {
                await handleDevice(currentDeviceId, currentDeviceType);
            }
        }
    } catch (error) {
        console.error("[ERROR] Terjadi kesalahan saat memproses antrian:", error.message);
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

// API untuk kontrol pompa
app.post("/api/water-pump/control", (req, res) => {
    const { device_id, action } = req.body;

    if (!device_id || !["ON", "OFF"].includes(action)) {
        return res.status(400).json({ error: "Invalid device_id or action." });
    }

    const payload = JSON.stringify({ device_id, action });
    mqttClient.publish(`${process.env.MQTT_USERNAME}/feeds/proto-one-watering-1.pump-control`, payload, (err) => {
        if (err) {
            console.error("[ERROR] Gagal mengirim kontrol ke MQTT:", err);
            return res.status(500).json({ error: "Failed to send pump control." });
        }

        console.log(`[INFO] Kontrol pompa berhasil dikirim: ${payload}`);
        res.status(200).json({ message: "Pump control sent successfully." });
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`[INFO] Server berjalan di http://localhost:${PORT}`);
});
