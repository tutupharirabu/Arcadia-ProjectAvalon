const express = require('express');
const bodyParser = require('body-parser');
const redis = require('redis');

const app = express();
const port = process.env.PORT || 8080;
const TIMEOUT_DURATION = 60000; // Timeout 1 menit

// Initialize Redis Client with Redis URL from environment variables
const redisClient = redis.createClient({
    url: 'redis://default:klWgjYtQIJVwoTBphsEZRtzjzkNrGiIt@junction.proxy.rlwy.net:45140',
});

redisClient.connect()
    .then(() => console.log("Connected to Redis"))
    .catch(err => console.error("Redis connection error:", err));

app.use(bodyParser.json());

// Endpoint status
app.get('/status', async (req, res) => {
    try {
        const statusData = {
            status: await redisClient.get('status') || 'OK',
            temperature: await redisClient.get('temperature') || 'N/A',
            humidity: await redisClient.get('humidity') || 'N/A',
            soilMoisture: await redisClient.get('soilMoisture') || 'N/A',
            timestamp: await redisClient.get('timestamp') || Date.now(),
        };
        res.json(statusData);
    } catch (error) {
        res.status(500).json({ status: 'error', message: 'Failed to fetch data from Redis' });
    }
});

app.listen(port, () => {
    console.log(`Server running on https://localhost:${port}`);
});
