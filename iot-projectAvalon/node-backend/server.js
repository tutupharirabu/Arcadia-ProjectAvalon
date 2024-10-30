const express = require('express');
const bodyParser = require('body-parser');
const redis = require('redis');
const app = express();

const port = process.env.PORT;
const TIMEOUT_DURATION = 60000; // Timeout of 1 minute

// Initialize Redis client with Redis URL from environment variables
const redisClient = redis.createClient({
  url: process.env.REDIS_URL // Expected format: redis://:password@host:port
});

redisClient.connect()
  .then(() => console.log("Connected to Redis"))
  .catch(err => console.error("Redis connection error:", err));

app.use(bodyParser.json());

// Endpoint to receive data from ESP32
app.post('/status', async (req, res) => {
  try {
    const { status, temperature, humidity, soil } = req.body;

    // Validate incoming data
    if (!status || temperature === undefined || humidity === undefined || soil === undefined) {
      throw new Error('Invalid data format');
    }

    // Update Redis with latest data
    const lastStatus = {
      status,
      temperature,
      humidity,
      soilMoisture: soil,
      timestamp: Date.now()
    };

    await redisClient.hSet('lastStatus', lastStatus);

    console.log(`Status received: ${JSON.stringify(lastStatus)}`);
    res.status(200).json({ message: 'Status received successfully' });
  } catch (error) {
    console.error(`Error: ${error.message}`);
    res.status(400).json({ error: 'Invalid data format or missing fields' });
  }
});

// Endpoint to get the last status from Redis
app.get('/status', async (req, res) => {
  try {
    const lastStatusFromRedis = await redisClient.hGetAll('lastStatus');

    // If no data in Redis, return "Data Lost"
    if (Object.keys(lastStatusFromRedis).length === 0) {
      return res.json({
        status: "Data Lost",
        temperature: null,
        humidity: null,
        soilMoisture: null,
        message: "No data received recently"
      });
    }

    const timeDifference = Date.now() - parseInt(lastStatusFromRedis.timestamp);
    if (timeDifference > TIMEOUT_DURATION) {
      return res.json({
        status: "Data Lost",
        temperature: null,
        humidity: null,
        soilMoisture: null,
        message: "No data received within the last minute"
      });
    }

    // Return latest data
    res.json({
      status: lastStatusFromRedis.status,
      temperature: parseFloat(lastStatusFromRedis.temperature),
      humidity: parseFloat(lastStatusFromRedis.humidity),
      soilMoisture: parseFloat(lastStatusFromRedis.soilMoisture),
      timestamp: lastStatusFromRedis.timestamp
    });
  } catch (error) {
    console.error(`Error fetching from Redis: ${error.message}`);
    res.status(500).json({ error: "Error fetching data from Redis" });
  }
});

// Start the server
app.listen(port, () => {
  console.log(`Server running on port ${port}`);
});
