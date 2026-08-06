# node_mqtt_server — Bridge MQTT ↔ Laravel

Servis Node.js (Express) yang menjembatani broker MQTT dengan backend Laravel (`be-avalon`) dan frontend Vue (`fe-avalon`). Dua proses independen berbagi satu codebase:

- **`serverMonitoring.js`** — ingest data sensor dari perangkat (MQTT → Laravel `POST /historical-data` + Redis), menyediakan endpoint sensor real-time untuk frontend, dan menyimpan token device.
- **`serverWatering.js`** — kontrol pompa air: menerima perintah dari Laravel/UI, mem-publish perintah MQTT ke firmware, dan mengirim status kembali ke Laravel.

## Requirements

- Node.js **>= 22** (dipin di `engines` `package.json`)
- Redis (untuk token & data real-time)
- MQTT broker (EMQX / Mosquitto) — kredensial dari env

## Setup & Menjalankan

```bash
cd be-avalon/node_mqtt_server
npm install
```

Tidak ada `.env.example` — buat `.env` manual dari daftar di bawah (lihat format `.env` yang sudah ada di mesin dev):

```bash
# Proses monitoring (serverMonitoring.js) — port 3000-an
npm start
# atau eksplisit:
node serverMonitoring.js

# Proses kontrol pompa (serverWatering.js) — port berbeda, terminal/jendela terpisah
node serverWatering.js
```

> Kedua proses harus jalan bersamaan di produksi. Jangan jalankan tanpa env lengkap — server akan gagal/tolak request (fail-closed).

## Environment Variables

| Variable | Deskripsi |
|---|---|
| `PORT_MONITOR` | Port HTTP `serverMonitoring.js` |
| `PORT_WATER` | Port HTTP `serverWatering.js` |
| `REDIS_URL` | Koneksi Redis (token device, data real-time) |
| `MQTT_BROKER_URL` | URL broker MQTT (mis. `mqtts://...`) |
| `MQTT_USERNAME` | Username broker — **sekaligus prefix topik** (lihat catatan firmware) |
| `MQTT_KEY` | Password broker |
| `LARAVEL_API_URL` | Base URL API Laravel (server-to-server, mis. `https://api.domain.com/api/v1` — atau `http://localhost:8000/api/v1` untuk dev) |
| `JWT_SECRET` | Sama dengan `JWT_SECRET` Laravel — dipakai validasi token JWT user (middleware `authenticateToken`) |
| `SHARED_SECRET` | **Harus SAMA dengan `SHARED_SECRET` di `.env` Laravel** — dipakai endpoint server-to-server (`x-shared-secret`) |
| `FRONTEND_URL` | Origin frontend yang diizinkan CORS (boleh comma-separated) |
| `CLOUDINARY_CLOUD_NAME` / `CLOUDINARY_API_KEY` / `CLOUDINARY_API_SECRET` | Upload QR code device |

## Keamanan

- **`requireSharedSecret`** (`middleware/authMiddleware.js`) — endpoint server-to-server (`POST /api/water-pump/control`, `POST /api/store-token`) hanya menerima request dengan header `x-shared-secret` yang cocok dengan env `SHARED_SECRET`. Perbandingan constant-time (tidak membocorkan panjang/isi secret), dan **fail-closed**: jika env kosong, semua request ditolak.
- **`authenticateToken`** — endpoint yang melayani frontend memvalidasi JWT (`Authorization: Bearer ...`) dengan `JWT_SECRET` yang sama dengan Laravel.
- CORS di-pin ke `FRONTEND_URL` (bukan `*`).
- `.env` ter-gitignore — jangan pernah commit kredensial.

## Catatan Firmware (MQTT)

- Server **subscribe** ke topik wildcard: `` `${MQTT_USERNAME}/feeds/+` `` — segmen terakhir topik adalah *feed type* yang menentukan cara payload ditafsirkan (mis. sensor suhu, kelembapan, status pompa).
- Kontrol pompa: server **publish** perintah ke `` `${MQTT_USERNAME}/feeds/proto-one-watering-1.pump-control` `` dengan **payload JSON** (bukan string polos).
- Payload yang tidak dikenali akan dicatat sebagai warning dan diabaikan — perangkat diminta selalu mengirim JSON sesuai skema feed masing-masing.
