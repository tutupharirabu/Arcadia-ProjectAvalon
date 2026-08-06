# Arcadia — Project Avalon (Platform IoT)

Platform IoT untuk **monitoring sensor & kontrol pompa air** secara real-time: dashboard petani, manajemen device (QR/barcode), kalender alarm terjadwal, notifikasi, dan grafik data sensor. Backend di-orchestrasi lewat bridge MQTT ke perangkat fisik.

## Arsitektur

```mermaid
graph LR
    FE[fe-avalon<br/>Vue 3 + Vite + Pinia] -->|REST /api/v1| BE[be-avalon<br/>Laravel 11 API]
    FE -->|REST /api/dashboard| NODE[be-avalon/node_mqtt_server<br/>Node.js + Express]
    BE -->|server-to-server| NODE
    NODE -->|MQTT publish/subscribe| BROKER[MQTT Broker<br/>EMQX / Mosquitto]
    BROKER --> DEV[Perangkat fisik / firmware]
    NODE -->|token & real-time| REDIS[(Redis)]
    BE --> DB[(MySQL)]
```

| Komponen | Stack | Dokumen |
|---|---|---|
| `be-avalon` | Laravel 11 API (JWT auth, scheduler alarm, notifikasi) | [README](iot-projectAvalon/be-avalon/README.md) |
| `be-avalon/node_mqtt_server` | Node.js bridge MQTT ↔ Laravel (2 proses: monitoring & watering) | [README](iot-projectAvalon/be-avalon/node_mqtt_server/README.md) |
| `fe-avalon` | Vue 3 + Vite + Pinia + Tailwind/daisyUI + Chart.js | [README](iot-projectAvalon/fe-avalon/README.md) |

## Alur Branch (SDLC)

```
improvement/* (fitur/perbaikan)
        └─▶ PR → dev (integrasi)
                  └─▶ PR → canary (staging/pre-prod)
                            └─▶ PR → main (produksi)
```

- **`dev`** — branch default; integrasi semua fitur.
- **`canary`** — staging; pratinjau sebelum produksi.
- **`main`** — produksi.

`canary` & `main` dilindungi branch protection: **1 review + CI 3 job hijau** (Backend Laravel, Frontend Vue, Node bridge) + branch up-to-date. CI & Dependabot berjalan otomatis (lihat `.github/`).

## Quick Start (lokal)

```bash
# 1) Backend API (port 8000)
cd be-avalon
cp .env.example .env   # isi DB, JWT_SECRET, SHARED_SECRET, dll.
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve

# 2) Node bridge MQTT (port 3000 & 3001) — butuh Redis + broker MQTT
cd be-avalon/node_mqtt_server
npm install
npm start              # serverMonitoring.js (port 3000)
node serverWatering.js # proses kedua (port 3001)

# 3) Frontend (port 5173)
cd fe-avalon
npm install
cp .env.example .env   # VITE_API_URL / VITE_NODE_URL (default: localhost)
npm run dev
```

> **Penting:** `SHARED_SECRET` harus bernilai **SAMA** di `.env` Laravel dan `.env` node server (endpoint server-to-server fail-closed). Detail lengkap di README masing-masing komponen.

## Deploy (VPS)

- **Backend**: nginx + PHP-FPM (root `public/`), Redis, cron `php artisan schedule:run` tiap menit.
- **Node bridge**: 2 proses Node (port 3000/3001) via systemd/PM2.
- **Frontend**: build statis `npm run build` → root `dist/` + SPA rewrite `try_files $uri /index.html`.
- Set env produksi: `APP_DEBUG=false`, `JWT_SECRET`, `SHARED_SECRET`, `NODE_API_URL_1/2`, `FRONTEND_URL`, `VITE_API_URL`, `VITE_NODE_URL`.

## Keamanan

- Ownership data diverifikasi di semua endpoint (trait `HasOwnershipChecks`) — anti-IDOR.
- Rate limiting: `throttle:api` (60/menit) + `throttle:auth` (5/menit) untuk login/OTP/forgot-password.
- Endpoint server-to-server node memakai `x-shared-secret` (constant-time, fail-closed).
- Token JWT di-blacklist saat logout; password bcrypt (12 rounds).
- CORS dipin ke `FRONTEND_URL`; `.env` ter-gitignore di semua komponen.

## Testing

```bash
cd be-avalon && php artisan test          # 18 test (auth, IDOR, rate limit, alarm, admin-gate)
cd fe-avalon && npm run test:unit         # Cypress component
```

## Status Audit

Riwayat audit & perbaikan terdokumentasi di [`be-avalon/AUDIT-PERBAIKAN.md`](iot-projectAvalon/be-avalon/AUDIT-PERBAIKAN.md).
