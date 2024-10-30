const express = require('express');
const bodyParser = require('body-parser');
const redis = require('redis');
const app = express();

// Use Railway's dynamic port assignment, with fallback for local development
const port = process.env.PORT;
const TIMEOUT_DURATION = 60000; // Timeout of 1 minute

// Set up Redis client
const redisClient = redis.createClient({
    url: process.env.REDIS_URL,  // Use Redis URL from Railway environment variables
    socket: {
        reconnectStrategy: () => 1000, // Reconnect every 1 second on connection loss
    }
});

// Connect to Redis
redisClient.connect().catch(console.error);

app.use(bodyParser.json());

// Endpoint to receive data from ESP32
app.post('/status', async (req, res) => {
    try {
        const { status, temperature, humidity, soil } = req.body;

        // Validate incoming data
        if (!status || temperature === undefined || humidity === undefined || soil === undefined) {
            throw new Error('Invalid data format');
        }

        // Store data in Redis as a hash
        await redisClient.hSet('lastStatus', {
            status,
            temperature,
            humidity,
            soilMoisture: soil,
            timestamp: Date.now()
        });

        console.log(`Data received: Status: ${status}, Temperature: ${temperature}, Humidity: ${humidity}, Soil Moisture: ${soil}`);

        // Respond to ESP32
        res.status(200).json({ message: 'Status received successfully' });
    } catch (error) {
        console.error(`Error receiving data from ESP32: ${error.message}`);
        res.status(400).json({ error: 'Invalid data format or missing fields' });
    }
});

// Endpoint to get the latest status from ESP32
app.get('/status', async (req, res) => {
    try {
        // Retrieve the last status from Redis
        const lastStatus = await redisClient.hGetAll('lastStatus');

        // If no data is found in Redis
        if (!lastStatus || Object.keys(lastStatus).length === 0) {
            return res.json({
                status: "No Data",
                temperature: null,
                humidity: null,
                soilMoisture: null,
                message: "No data received yet"
            });
        }

        // Calculate time difference
        const currentTime = Date.now();
        const timeDifference = currentTime - parseInt(lastStatus.timestamp, 10);

        // If data is older than TIMEOUT_DURATION
        if (timeDifference > TIMEOUT_DURATION) {
            res.json({
                status: "Data Lost",
                temperature: null,
                humidity: null,
                soilMoisture: null,
                message: "No data received from ESP32 within the last 60 seconds"
            });
        } else {
            // Send the cached status from Redis
            res.json({
                status: lastStatus.status,
                temperature: parseFloat(lastStatus.temperature),
                humidity: parseFloat(lastStatus.humidity),
                soilMoisture: parseFloat(lastStatus.soilMoisture),
                timestamp: parseInt(lastStatus.timestamp, 10)
            });
        }
    } catch (error) {
        console.error(`Error retrieving data: ${error.message}`);
        res.status(500).json({ error: 'Error retrieving data from Redis' });
    }
});

// Start the server
app.listen(port, () => {
    console.log(`Server running on port ${port}`);
});
