const express = require('express');
const bodyParser = require('body-parser');
const app = express();

const port = 3001;
const TIMEOUT_DURATION = 60000; // Timeout 1 menit

let lastStatus = {
    status: "No Data",
    temperature: null,
    humidity: null,
    soilMoisture: null,
    timestamp: Date.now()  // Inisialisasi waktu terakhir
};

app.use(bodyParser.json());

// Endpoint untuk menerima data dari ESP32
app.post('/status', (req, res) => {
    try {
        const { status, temperature, humidity, soil } = req.body;

        // Validasi data yang diterima
        if (!status || temperature === undefined || humidity === undefined || soil === undefined) {
            throw new Error('Invalid data format');
        }

        // Update status terakhir dari ESP32
        lastStatus.status = status;
        lastStatus.temperature = temperature;
        lastStatus.humidity = humidity;
        lastStatus.soilMoisture = soil;
        lastStatus.timestamp = Date.now();  // Perbarui waktu terakhir data diterima

        console.log(`Status received from ESP32: ${status}, Temperature: ${temperature}, Humidity: ${humidity}, Soil Moisture: ${soil}`);

        // Kirim respons ke ESP32
        res.status(200).json({ message: 'Status received successfully' });
    } catch (error) {
        console.error(`Error receiving data from ESP32: ${error.message}`);
        res.status(400).json({ error: 'Invalid data format or missing fields' });
    }
});

// Endpoint untuk mengambil status terakhir dari ESP32
app.get('/status', (req, res) => {
    const currentTime = Date.now();
    const timeDifference = currentTime - lastStatus.timestamp;

    // Jika data terakhir lebih dari TIMEOUT_DURATION (1 menit) tidak diperbarui
    if (timeDifference > TIMEOUT_DURATION) {
        res.json({
            status: "Data Lost",
            temperature: null,
            humidity: null,
            soilMoisture: null,
            message: "No data received from ESP32 within the last 60 seconds"
        });
    } else {
        // Kirim status terakhir yang diterima dari ESP32 ke Laravel
        res.json(lastStatus);
    }
});

// Jalankan server di port yang ditentukan
app.listen(port, () => {
    console.log(`Server running on http://localhost:${port}`);
});
