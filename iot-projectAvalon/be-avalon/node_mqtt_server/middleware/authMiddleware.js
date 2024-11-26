const jwt = require("jsonwebtoken");
const Redis = require("ioredis");

const redisClient = new Redis(process.env.REDIS_URL);

async function authenticateToken(req, res, next) {
    const authHeader = req.headers.authorization;
    const token = authHeader && authHeader.split(" ")[1]; // Ambil token dari header

    if (!token) {
        return res.status(401).json({
            message: "Akses ditolak. Token tidak disediakan.",
        });
    }

    jwt.verify(token, process.env.JWT_SECRET, async (err, decoded) => {
        if (err) {
            return res.status(403).json({
                message: "Token tidak valid.",
            });
        }

        try {
            const redisKey = `jwt:user:${decoded.sub}`;
            const redisToken = await redisClient.get(redisKey);

            if (!redisToken) {
                return res.status(403).json({
                    message: "Token tidak ditemukan di Redis. Silakan login ulang.",
                });
            }

            if (redisToken !== token) {
                return res.status(403).json({
                    message: "Token tidak cocok dengan yang disimpan di Redis. Silakan login ulang.",
                });
            }

            // Simpan data pengguna dari token ke req.user
            req.user = decoded;
            next();
        } catch (error) {
            console.error("Kesalahan saat memeriksa token di Redis:", error.message);
            return res.status(500).json({
                message: "Kesalahan server saat memvalidasi token.",
            });
        }
    });
}

module.exports = authenticateToken;
