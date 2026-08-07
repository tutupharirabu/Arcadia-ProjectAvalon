# 🔒 Keamanan

Ringkasan seluruh pekerjaan hardening keamanan yang sudah dikerjakan (2026-08). Status akhir: **0 Dependabot alerts** & **0 CodeQL alerts**.

## 1. Upgrade Laravel 11 → 12.65 (CVE)

**Masalah**: Dependabot melaporkan 2 advisories pada `laravel/framework` v11.55.0:

| Advisory | Severity | Detail |
|---|---|---|
| CVE-2026-48019 | **high** | CRLF injection pada default email rule |
| GHSA-crmm-hgp2-wgrp | moderate | Signed URL path confusion |

Semua versi 11.x terkena (affected `<12.60.0`) — **tidak ada patch untuk 11.x**, sehingga upgrade major menjadi satu-satunya solusi.

**Tindakan** (PR #71 → rilis #73 → #75):

| Package | Sebelum | Sesudah |
|---|---|---|
| `laravel/framework` | v11.55.0 | **v12.65.0** |
| `laravel/sanctum` | v4.0.3 | v4.3.3 |
| `tymon/jwt-auth` | 2.1.1 | 2.3.0 |
| `lcobucci/jwt` | 4.3.0 | 5.6.0 |

**Verifikasi**: `composer audit` → 0 advisories; test suite 18 passed (50 assertions); CI hijau 3/3.

> Saat upgrade major framework: `composer update` satu-persatu bisa gagal karena dependensi terkunci (`sanctum`, `jwt-auth`, `sail` butuh `illuminate ^11`). Solusinya: update **semua dependensi sekaligus** (`composer update --with-all-dependencies`).

## 2. Menutup 8 Alert CodeQL (Node Bridge)

Semua di `be-avalon/node_mqtt_server/` (PR #76 → rilis #77 → #78):

| Alert | Lokasi | Perbaikan |
|---|---|---|
| **SSRF** (critical) ×2 | `serverMonitoring.js:524`, `serverWatering.js:114` | Validasi `deviceId` (regex `^[A-Za-z0-9_-]{1,64}$`) + `encodeURIComponent` sebelum dipakai di URL axios |
| **Externally-controlled format string** (high) ×2 | `serverMonitoring.js:72, 572` | Nilai dari user dipindah ke **argumen terpisah** `console.error(...)` — bukan bagian format string |
| **Clear-text logging sensitive info** (high) ×2 | `serverMonitoring.js:451`, `serverWatering.js:318` | `MQTT_USERNAME` (kredensial) tidak lagi di-log |
| **Missing rate limiting** (high) ×2 | `serverMonitoring.js:501` | `express-rate-limit` v8 — 60 req/15 menit per IP di `/api/dashboard` & `/api/store-token` (respons `429`) |

**Verifikasi**: `node --check` OK; smoke test — 60 request → `401` (auth normal), request ke-61+ → `429` (rate limit bekerja).

## 3. Dependabot Security

- **Security updates diaktifkan** (sebelumnya `enabled: false`) — PR otomatis untuk CVE.
- **Vulnerability alerts diaktifkan**.
- Konfigurasi diperkuat (groups, labels, jadwal, ignore major) — lihat [CI/CD](ci-cd.md).
- Triage 16+ PR dependabot: patch/minor di-merge (mqtt 5.15.2, cors 2.8.6, chart.js 4.5.1, jsonwebtoken 9.0.3, axios 1.18.0, shell-quote 1.10.0, postcss 8.5.26, form-data 4.0.6, vite 8 + laravel-vite-plugin 3.1, minimatch 9.0.9, rollup 4.62.4, glob + sucrase, lodash 4.18.1, predis 2.4.1, group fe-patch-minor), major ditutup dengan komentar triage.

## 4. Perbaikan Conflict Markers (JSON Invalid)

**Masalah**: squash merge PR dependabot #48 menyisakan **Git merge conflict markers** (`<<<<<<< HEAD`) di `be-avalon/node_mqtt_server/package.json` **dan** `package-lock.json` → JSON invalid. `npm ci` hanya lolos karena fallback *hidden lockfile* (`node_modules/.package-lock.json`) — bom waktu untuk install/upgrade.

**Tindakan** (PR #70 & #74): hapus markers dari `package.json`; **regenerate** `package-lock.json` dari `package.json` yang bersih:

```bash
rm -f package-lock.json
npm install --package-lock-only
node -e "JSON.parse(require('fs').readFileSync('package-lock.json','utf8'))"  # verifikasi valid
```

## 5. Proteksi yang Sudah Ada (Laravel)

- Ownership data diverifikasi di semua endpoint (trait `HasOwnershipChecks`) — anti-IDOR (tested).
- Rate limiting Laravel: `throttle:api` (60/menit) + `throttle:auth` (5/menit) untuk login/OTP/forgot-password.
- Endpoint server-to-server node memakai `x-shared-secret` (constant-time, fail-closed).
- JWT di-blacklist saat logout; password bcrypt (12 rounds).
- CORS dipin ke `FRONTEND_URL`; `.env` ter-gitignore.
- Middleware `isAdmin` untuk admin-only routes (tested).

## Major Bumps yang Sengaja Ditunda

Perlu migrasi & pengujian terpisah (di-ignore di `dependabot.yml`; PR-nya ditutup dengan komentar):

- `express` 5.x, `dotenv` 17.x, `predis/predis` 3.x, `tailwindcss` 4.x, `pinia` 4.x, `@schedule-x/*` 4.x, `vue-router` 5.x, `daisyui` 5.x, `start-server-and-test` 3.x

Cara melepas: hapus baris `ignore` yang bersangkutan di `.github/dependabot.yml`, lalu buka task migrasi.
