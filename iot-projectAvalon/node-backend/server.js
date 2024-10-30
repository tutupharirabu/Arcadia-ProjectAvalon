const express = require('express');
const bodyParser = require('body-parser');
const redis = require('redis');

const app = express();
const port = process.env.PORT || 3301;
const TIMEOUT_DURATION = 60000; // Timeout 1 menit

// Initialize Redis Client with Redis URL from environment variables
const redisClient = redis.createClient({
    url: 'redis://default:klWgjYtQIJVwoTBphsEZRtzjzkNrGiIt@junction.proxy.rlwy.net:45140',
});

redisClient.connect()
    .then(() => console.log("Connected to Redis"))
    .catch((err) => console.error("Redis connection error:", err));

app.use(bodyParser.json());

// Endpoint untuk menerima data dari ESP32
app.post('/status', async (req, res) => {
    try {
        const { status, temperature, humidity, soil } = req.body;

        if (!status || temperature === undefined || humidity === undefined || soil === undefined) {
            throw new Error('Invalid data format');
        }

        const lastStatus = {
            status,
            temperature,
            humidity,
            soilMoisture: soil,
            timestamp: Date.now()
        };

        await redisClient.hSet('lastStatus', lastStatus);

        console.log(`Status received from ESP32: ${status}, Temperature: ${temperature}, Humidity: ${humidity}, Soil Moisture: ${soil}`);
        res.status(200).json({ message: 'Status received successfully' });
    } catch (error) {
        console.error(`Error receiving data from ESP32: ${error.message}`);
        res.status(400).json({ error: 'Invalid data format or missing fields' });
    }
});

// Endpoint untuk mengambil status terakhir dari ESP32
app.get('/status', async (req, res) => {
    try {
        const lastStatus = await redisClient.hGetAll('lastStatus');
        if (!lastStatus || !lastStatus.timestamp || Date.now() - lastStatus.timestamp > TIMEOUT_DURATION) {
            res.json({
                status: "Data Lost",
                temperature: null,
                humidity: null,
                soilMoisture: null,
                message: "No data received from ESP32 within the last 60 seconds"
            });
        } else {
            res.json(lastStatus);
        }
    } catch (error) {
        res.status(500).json({ error: 'Failed to retrieve data from Redis' });
    }
});

app.listen(port, () => {
    console.log(`Server running on http://localhost:${port}`);
});
