const crypto = require("crypto");
const jwt = require("jsonwebtoken");
const redisClient = require("../redisClient");

const SHARED_SECRET_HEADER = "x-shared-secret";

// Perbandingan constant-time agar timing tidak membocorkan panjang/isi secret
function safeEqual(a, b) {
    const bufA = Buffer.from(String(a));
    const bufB = Buffer.from(String(b));
    if (bufA.length !== bufB.length) return false;
    return crypto.timingSafeEqual(bufA, bufB);
}

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

// Autentikasi server-to-server memakai shared-secret (header `x-shared-secret`),
// dibandingkan dengan env SHARED_SECRET. Fail-closed: jika env kosong, tolak request.
function requireSharedSecret(req, res, next) {
    const expected = process.env.SHARED_SECRET;
    if (!expected) {
        console.error("[ERROR] SHARED_SECRET belum dikonfigurasi di env — permintaan server-to-server ditolak.");
        return res.status(503).json({
            message: "Layanan belum dikonfigurasi. Coba lagi nanti.",
        });
    }

    const provided = req.headers[SHARED_SECRET_HEADER];
    if (!provided || !safeEqual(provided, expected)) {
        return res.status(401).json({
            message: "Akses ditolak.",
        });
    }

    next();
}

module.exports = { authenticateToken, requireSharedSecret };
