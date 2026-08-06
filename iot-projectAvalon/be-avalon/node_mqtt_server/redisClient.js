// redisClient.js — satu koneksi Redis bersama untuk seluruh servis
// (serverMonitoring + middleware auth) agar tidak ada koneksi terpisah-pisah.
const Redis = require("ioredis");

if (!process.env.REDIS_URL) {
    console.warn("[WARNING] REDIS_URL tidak diset — Redis akan mencoba localhost:6379.");
}

const redisClient = new Redis(process.env.REDIS_URL, {
    // Backoff eksponensial (200ms, 400ms, ...) dengan cap 5 detik agar tidak spam reconnect
    retryStrategy: (times) => {
        const delay = Math.min(times * 200, 5000);
        console.warn(`[WARNING] Koneksi Redis terputus. Percobaan ulang ke-${times} dalam ${delay}ms.`);
        return delay;
    },
    maxRetriesPerRequest: 3,
    enableReadyCheck: true,
});

redisClient.on("connect", () => console.log("[INFO] Terhubung ke Redis."));
redisClient.on("ready", () => console.log("[INFO] Redis siap menerima perintah."));
redisClient.on("error", (err) => {
    // Jangan crash proses saat Redis down — operasi yang gagal sudah di-handle per-call
    console.error("[INFO] Kesalahan Redis Client:", err.message);
});

module.exports = redisClient;
