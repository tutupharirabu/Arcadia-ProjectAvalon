# Peta Audit: be-avalon (Laravel API + node_mqtt_server)

## Context

Permintaan: memetakan apa saja yang **paling krusial** untuk diperbaiki di backend `be-avalon` — Laravel 11 API untuk platform IoT (kontrol pompa air, sensor, notifikasi) plus servis Node.js terpisah (`node_mqtt_server`) yang menjembatani MQTT broker ↔ Laravel ↔ perangkat fisik.

Audit dilakukan lewat 3 eksplorasi paralel (auth/security Laravel, domain controllers/models, node_mqtt_server) lalu setiap temuan kritis diverifikasi ulang langsung ke source code (dibaca penuh: `AuthController.php`, `WaterPumpController.php`, `DeviceController.php`, `WaterPumpAlarmController.php`, `serverWatering.js`, `routes/api.php`, `bootstrap/app.php`, dan source Laravel framework untuk klaim rate-limiting). Semua temuan CRITICAL/HIGH di bawah ini **sudah diverifikasi langsung**, bukan sekadar klaim agent.

Ini murni dokumen pemetaan (belum ada kode yang diubah). Eksekusi perbaikan sebaiknya dipecah per-wave (lihat bagian "Urutan Eksekusi yang Disarankan") karena scope-nya besar dan menyentuh 2 codebase. **Eksekusi dilakukan kapan-kapan, tidak sekarang** — dokumen ini murni daftar referensi.

---

## Status Eksekusi (2026-08-06)

Bagian ini mencatat progres perbaikan di atas. **Temuan lama di bagian bawah tidak diubah** — statusnya tercermin di tabel berikut. Verifikasi dilakukan langsung ke source code (grep/read) sebelum klaim ditulis.

### Wave 1 — Keamanan kritis: ✅ SELESAI

| # | Item | Status | Verifikasi (2026-08-06) |
|---|---|---|---|
| 1, 11 | Node auth: shared-secret pada `/api/water-pump/control` & `/api/store-token` + hardening | ✅ | `middleware/authMiddleware.js` — `requireSharedSecret` (header `x-shared-secret`, perbandingan constant-time, **fail-closed** bila env kosong) + `authenticateToken` (JWT) |
| 2, 4, 7, 8, 9 | IDOR ownership (pompa, alarm, device, data historis, notifikasi) | ✅ | Trait `app/Http/Controllers/API/Traits/HasOwnershipChecks.php` dipakai `WaterPumpController`, `WaterPumpAlarmController`, `NotificationController`; cek inline `users_id !== $userId` di `DeviceController`, `HistoricalDataController`, `NotificationRecipientController` |
| 3 | Routing alarm salah bind → `WaterPumpAlarmController` | ✅ | `routes/api.php:74-76` — `/v1/water-alarm` ter-bind ke `WaterPumpAlarmController::index/updateOrCreate/destroy` |
| 5 | OTP di-scope ke `users_id` pemanggil | ✅ | `AuthController::verificationEmail` di-scope per-user |
| 6 | Rate limiting | ✅ | `bootstrap/app.php` — `throttle:api` (60/menit) global + limiter `auth` (5/menit) untuk login/OTP/forgot-password |
| 10 | `env()` langsung di controller → `config()` + `.env.example` lengkap | ✅ | Tidak ada lagi pemanggilan `env(` di `app/`; `.env.example` memuat `JWT_*`, `NODE_API_URL_*`, `SHARED_SECRET` |
| 16 | Stop kebocoran detail error ke klien | ✅ | `$e->getMessage()`/`$response->body()` tidak lagi diteruskan mentah |
| 21 | Scheduler Log fix | ✅ | `bootstrap/app.php` — `onSuccess`/`onFailure` menulis ke Log |
| — | Login resilience, idempotensi jalur ON pompa, seeder aman | ✅ | Diverifikasi pada eksekusi Wave 1 |
| 1 (CORS) | CORS node di-pin | ✅ | `serverMonitoring.js`/`serverWatering.js` — origin dari `FRONTEND_URL` (comma-separated), bukan `*` |
| — | FE: hydrate auth, interceptor, router fixes | ✅ | `fe-avalon/src/utils/customFetch.js` — request interceptor (suntik token), response interceptor (401 → logout otomatis) |

### Wave 2 — selesai (2026-08-06)

| Item | Status |
|---|---|
| Migrations hardening | ✅ — index `is_active`, composite `historical_data(devices_id, created_at)`, timestamps `otp_codes`, FK CASCADE, dedup + unique alarm (`2026_08_06_000000_add_hardening_indexes_to_iot_tables.php`) |
| Test suite permanen | ✅ — `tests/Feature/{AuthTest,IdorTest,RateLimitTest,WaterAlarmAndAdminGateTest}.php` (18 test, sqlite in-memory) |
| Forms landing & polling/chart FE | ✅ — Contact & ArcadiaPartner terhubung WhatsApp; polling backoff + pause saat tab hidden; chart tanpa re-create |
| a11y | ✅ — label/for, modal role=dialog + ESC, focus-within, tabular-nums, kontras theme (accent 8.2:1) |
| CI/CD | ✅ — `.github/workflows/ci.yml` (3 jobs) + `dependabot.yml`; README 3 komponen ditulis ulang |
| Deploy | ✅ — Vercel/Railway dihapus; target deploy VPS (nginx + PHP-FPM + Redis + 2 proses node) |

### Wave 3 — Composer vulnerabilities + diagnostics PHP: ✅ SELESAI (2026-08-06)

| Item | Status |
|---|---|
| Composer audit | ✅ — sebelum: 34 advisory (6 high, 24 medium, 4 low, termasuk dev); sesudah: 0 non-ignored. Sisa 3 advisory laravel/framework (lihat baris terakhir) — TIDAK ada fix di line 11.x |
| Update targeted (lockfile) | ✅ — `composer update <paket>` + `-W` (transitif). 40 paket naik: laravel/framework 11.30→11.55, guzzle 7.9.2→7.15.3, symfony/* 7.1→7.4.15, league/commonmark 2.5.3→2.9, carbon 3.8.2→3.13.1, phpunit 11.4.3→11.5.56, faker 1.24.0→1.24.1, pint 1.18.1→1.30.4, pest 3.5.1→3.8.7 |
| `config.policy.advisories.ignore-id` (composer.json) | ✅ — 3 ID advisory tanpa fix 11.x (PKSA-m5cs-t1y6-qpcs, PKSA-3r5d-mb8f-1qw9, PKSA-mdq4-51ck-6kdq). Tanpa ini resolver composer memblokir SEMUA versi laravel/framework 11.x sehingga framework tidak bisa di-update sama sekali. Fix hanya tersedia di 12.60+/13.x (major upgrade di luar scope) |
| predis vs phpredis | ⚠️ dicatat — composer.json: `predis/predis ^2.3` (tidak vulnerable, tidak di-update), `.env.example`: `REDIS_CLIENT=phpredis`. Keputusan ini TIDAK diubah |
| Diagnostics models | ✅ — HistoricalData (2), Role (4), Notification (6), User (14): docblock `@return` + native return type relation; root cause `@use HasFactory<...>` di User.php merusak parse intelephense → dihapus |
| Diagnostics unused imports | ✅ — RoleController (`isAdmin`), NotificationRecipientController (`Request`), ForgotPasswordMailSend (`ShouldQueue`) |
| Validasi | ✅ — `php artisan test` 18 passed, `php -l` semua file diubah, `composer install --dry-run` sinkron |
| Sisa (bukan bug) | ⚠️ false-positive LSP murni: `Method where/create/find does not exist` (magic `__callStatic` Eloquent tanpa ide-helper), `Undefined variable $this` (binding Pest), `fromUser` (magic facade), warning `Missing return type` di controller (style codebase, bukan bug). Test file TIDAK diubah |

### Aksi yang dibutuhkan dari user

1. **Ubah repo ke `private`** — riwayat commit sebelum perbaikan sempat publik.
2. **Rotasi semua kredensial** (JWT_SECRET, SHARED_SECRET, MQTT, Cloudinary, DB) karena repo pernah publik.
3. **Set `SHARED_SECRET` dengan nilai SAMA** di `.env` Laravel **dan** `.env` node server (termasuk environment produksi VPS).
4. **`npm install` di `be-avalon/node_mqtt_server`** sebelum menjalankan `npm start` / `node serverWatering.js` (node_modules di mesin dev tidak ter-commit).
5. Set `NODE_API_URL_1`/`NODE_API_URL_2` & `FRONTEND_URL` di environment produksi setelah deploy.

---

## CRITICAL — akses fisik tidak sah / akun bisa diambil alih / fitur inti rusak total

1. **Kontrol pompa air di sisi Node sama sekali tanpa autentikasi**
   `node_mqtt_server/serverWatering.js:219-239` — endpoint `POST /api/water-pump/control` tidak dipasangi middleware auth apa pun, padahal ini yang benar-benar mempublish perintah MQTT ke perangkat fisik. Siapa pun yang tahu/mengira URL Railway-nya bisa langsung ON/OFF-kan pompa air **milik device manapun**, sepenuhnya melewati layer `auth:api` Laravel. Diperparah CORS terbuka (`cors()` tanpa opsi, baris 19) — bisa dipicu dari halaman web sembarang tanpa token.

2. **IDOR total di kontrol pompa sisi Laravel**
   `app/Http/Controllers/API/WaterPumpController.php`:
   - `controlPump` (baris 13-82): tidak pernah mengecek `device_id` yang dikirim adalah milik user yang login — user manapun yang login bisa mengontrol pompa device manapun.
   - Cabang OFF (baris 57-59): mencari log hanya berdasar `water_pump_log_id` + `is_on=true`, **`device_id` yang dikirim tidak pernah dipakai untuk validasi silang** — bisa dipakai memutus/mengubah log aktif milik device lain.
   - `show()` / `showWaterPumpLog()` (baris 84-128): membocorkan seluruh riwayat log pompa device manapun ke user login manapun.

3. **Fitur alarm pompa air rusak total (salah bind controller di routing)**
   `routes/api.php:70-74` — grup route `/v1/water-alarm/*` di-bind ke `WaterPumpController::class`, padahal class itu **tidak punya method** `index`/`updateOrCreate`/`destroy` (sudah dicek isi filenya, hanya ada `controlPump`, `show`, `showWaterPumpLog`). Setiap panggilan ke endpoint ini pasti fatal error 500. `WaterPumpAlarmController` (class yang benar, method-nya lengkap) hanya terpakai secara internal lewat scheduled command `check:alarms`.
   → **Harus diperbaiki berbarengan dengan temuan #4** — kalau cuma routing-nya dibetulkan tanpa membenahi otorisasinya, hasilnya cuma menukar "fitur rusak" jadi "fitur bocor".

4. **IDOR di WaterPumpAlarmController** (baru akan "aktif" begitu #3 diperbaiki — harus dibenahi bersamaan)
   `app/Http/Controllers/API/WaterPumpAlarmController.php` — `index` (22-31) dan `updateOrCreate` (33-82) mempercayai `devices_id` dari request body tanpa cek kepemilikan; siapa pun bisa baca/buat/ubah jadwal ON/OFF otomatis (unattended!) untuk device manapun. `destroy` (84-90) "mengotorisasi" dengan membandingkan `devices_id` dari request terhadap dirinya sendiri — bukan otorisasi sungguhan.

5. **Bypass verifikasi email lintas-user via OTP**
   `app/Http/Controllers/API/AuthController.php:87-105` (`verificationEmail`) — mencari OTP hanya berdasar `otp_code`, **tidak di-scope ke `users_id` pemanggil**. Akun mana pun (termasuk akun baru daftar sendiri) bisa menebak/brute-force OTP milik korban dan menandai email korban sebagai terverifikasi. Bandingkan dengan `verifyOtpForgotPassword` (baris 217-219) yang sudah benar men-scope ke `users_id`.

6. **OTP mudah di-brute-force + tidak ada rate limiting sama sekali di seluruh API**
   `app/Models/User.php:33` — `mt_rand(100000, 999999)` (ruang 900rb kombinasi), valid 5 menit (baris 40), tanpa lockout/penghitung percobaan. Dicek langsung ke `bootstrap/app.php` (closure `withMiddleware` kosong) dan source `vendor/laravel/framework/.../Middleware.php` — properti `$apiLimiter` **tidak pernah di-set**, jadi throttle default Laravel pun tidak aktif. Kombinasi ini bikin brute-force OTP dalam window 5 menit realistis dieksekusi, dan diperparah oleh #5 (OTP siapa saja bisa dipakai). Berlaku di `/login`, `/generate-otp-code`, `/verification-email`, seluruh `/forgot-password/*`.

7. **IDOR — user manapun bisa ubah/hapus device milik user lain**
   `app/Http/Controllers/API/DeviceController.php`:
   - `update()` (210-243, baris 222): hanya cek device **punya** owner, bukan owner-nya = pemanggil.
   - `destroy()` (248-261): nol pengecekan kepemilikan, dan tanpa try/catch — menghapus device yang punya log/alarm/notifikasi terkait akan melempar `QueryException` tak tertangani (FK default RESTRICT) → 500, berpotensi bocor detail SQL kalau `APP_DEBUG=true`.
   - `checkDeviceExistPrivate()` (75-100): walau namanya "private" dan di-gate `auth:api`, tetap mengembalikan detail lengkap device (nama, lokasi, deskripsi, `users_id` pemilik) untuk device ID apa pun ke user login mana pun.
   - Sebagai pembanding: `removeShowDevice()` (266-321) di file yang sama **sudah benar** mengecek kepemilikan — jadi ini murni inkonsistensi implementasi, bukan gap pemahaman tim.

8. **Data historis (sensor): ingest publik tanpa otentikasi + IDOR saat baca**
   `app/Http/Controllers/API/HistoricalDataController.php`:
   - `store()` (15-56): endpoint publik, tanpa secret/HMAC per-device — siapa pun yang tahu `devices_id` bisa menyuntik data sensor palsu tanpa batas, selamanya.
   - `show()` (61-99): hanya cek device **punya** owner, bukan owner = pemanggil — membocorkan seluruh riwayat sensor user lain, dan tidak dipaginasi (risiko performa/DoS seiring data bertambah).

9. **Notifikasi: IDOR / kebocoran data luas**
   - `NotificationController::index` — mengembalikan **semua** notifikasi se-sistem tanpa filter kepemilikan.
   - `NotificationController::show` — tanpa cek recipient, siapa pun login bisa baca notifikasi siapa pun.
   - `NotificationRecipientController::markAsRead` (14-35) — tanpa cek kepemilikan, plus bug logika: `->first()` pada notifikasi broadcast (multi-recipient) selalu memutasi baris yang salah/acak, bukan baris milik pemanggil.
   - `NotificationRecipientController::getNotificationsForRecipient` (40-60) — `users_id`/`roles_id` diambil dari query string, bukan dari identitas ter-autentikasi — bisa membaca feed notifikasi user lain.

---

## HIGH

10. `env('NODE_API_URL_1'/'NODE_API_URL_2')` dipanggil langsung di controller (bukan lewat `config()`) — akan silently pecah setelah `php artisan config:cache` (langkah standar deploy produksi). Sekaligus undocumented: `.env.example` tidak punya entri ini maupun semua key `JWT_*`.
11. `node_mqtt_server/serverMonitoring.js:328-341` — endpoint `/api/store-token` (dipanggil server-to-server dari `AuthController.php:149`) tanpa autentikasi/shared-secret — caller anonim bisa menimpa token Redis milik user manapun (DoS sesi/paksa re-login).
12. `node_mqtt_server` — risiko unhandled promise rejection: `processQueue` di `serverMonitoring.js` tidak punya `catch`, dipanggil tanpa `await`/`.catch()`, dan tidak ada handler global `unhandledRejection`/`uncaughtException` — bisa mematikan seluruh proses jembatan MQTT untuk semua device.
13. `node_mqtt_server/serverMonitoring.js` — state global `currentDeviceId`/`currentDeviceType` dipakai bersama untuk **semua** device/pesan (bukan per-device) — device yang publish bersamaan bisa saling menimpa data sensor di bawah device ID yang salah.
14. Token reset password tidak pernah kedaluwarsa (`VerifyPasswordResetToken.php` + migrasi terkait) — hanya cek kecocokan email+token, tanpa TTL.
15. User enumeration di `generateOtpCode`/`forgotPassword`/`verifyOtpForgotPassword` (404 berbeda untuk email tak terdaftar), diperparah tanpa rate limiting (#6).
16. Respons error membocorkan detail internal (`$e->getMessage()`, `$response->body()`) ke klien di `AuthController`, `DeviceController`, `WaterPumpController`, `NotificationController` — makin berisiko karena `.env.example` default `APP_DEBUG=true`.
17. `DeviceController::store()` (113-134) sepenuhnya tanpa autentikasi dan tanpa cek keunikan `devices_id` — rawan squatting/collision terhadap registrasi device asli.
18. `RoleController::update()` — validasi unique title tidak mengecualikan record sendiri, dan update dengan nilai sama salah dilaporkan sebagai "tidak ditemukan".

---

## MEDIUM

19. `Notification::user()` mengacu ke kolom `users_id` yang **tidak ada** di tabel `notifications` (kolom sebenarnya `admin_id`) — relasi mati/rusak, tetap di-eager-load percuma.
20. `NotificationController::store` — `admin_id` tidak diverifikasi terhadap pemanggil/DB — sumber notifikasi bisa dipalsukan.
21. Scheduler alarm (`checkAndExecuteAlarms`) mencocokkan string `H:i` persis — satu tick cron yang terlewat = alarm hari itu diam-diam tidak pernah jalan, tanpa backfill/alerting; juga memanggil HTTP secara sinkron per-alarm di dalam cron per-menit.
22. Tidak ada guard idempotensi di jalur ON `controlPump` — log pompa "aktif" ganda untuk device yang sama bisa terjadi.
23. Index database yang hilang: `water_pump_alarms.is_active` (di-scan tiap menit oleh cron), composite `historical_data(devices_id, created_at)`.
24. `onDelete` FK tidak konsisten — mayoritas default RESTRICT tanpa try/catch di level app, jadi menghapus device/role/user yang punya dependent akan melempar 500 tak tertangani di banyak tempat (pola berulang, bukan kasus tunggal).
25. Tidak ada unique constraint yang menopang key dedup di `WaterPumpAlarmController::updateOrCreate` → race condition bisa membuat alarm duplikat.
26. `devices.status` dan `devices_id` (PK) adalah nilai bebas dari klien tanpa enum/generation di level DB — inkonsisten dengan tabel lain yang sudah pakai enum DB.
27. Potensi mass-assignment: `roles_id` ada di `User::$fillable` — belum tereksploitasi hari ini, tapi `User::create($request->all())` di masa depan bisa membuka self-promotion jadi admin.
28. OTP pakai `mt_rand` (bukan CSPRNG), perbandingan token reset password bukan constant-time — hardening sekunder, jalan bersama #5/#6.

---

## LOW

29. Dead code: `app/Http/Middleware/JwtMiddleware.php` tidak pernah didaftarkan/dipakai di mana pun.
30. `PUT /v1/water-pump/log/{logId}` sebenarnya endpoint read-only (mismatch verb REST, kemungkinan artefak copy-paste).
31. Tabel `otp_codes` tanpa `created_at`/uniqueness — baris basi menumpuk tak terbatas per user.
32. Kebijakan password hanya soal panjang, tanpa aturan kompleksitas.
33. `node_mqtt_server` — logika terduplikasi (generate QR, upload Cloudinary, cek device, boilerplate MQTT) identik persis antara `serverMonitoring.js` dan `serverWatering.js` — perbaikan apa pun (misalnya #12) harus diterapkan dua kali.
34. `node_mqtt_server/package.json` — `uuid` dipakai runtime tapi tidak dideklarasikan sebagai dependency (cuma transitive); tidak ada script `start`; `main` menunjuk file yang tidak ada; tidak ada pin `engines`.
35. Mismatch payload publish/receive: `serverWatering.js` mem-publish perintah pompa sebagai JSON tapi handler penerimaannya sendiri mengecek string polos `"ON"`/`"OFF"` — selalu log warning, jalur validasi mati/rusak.
36. Beberapa nit logging/DX: undefined array key notice di `NotificationRecipientController`, tidak ada null-guard di middleware `isAdmin`, `MQTT_USERNAME` ter-log ke stdout.

---

## Yang Sudah Benar (jangan ikut "diperbaiki")

- Password hashing bcrypt via `Hash::make()` + cast `'password' => 'hashed'`, `BCRYPT_ROUNDS=12` — solid.
- Logout benar-benar blacklist token JWT server-side (`Auth::guard('api')->logout()` → `JWTGuard::invalidate()`), bukan cuma buang token di client.
- `isAdmin` middleware mengambil role dari identitas ter-autentikasi server-side, bukan dari input klien.
- `removeShowDevice()` dan `HistoricalDataController::store` (validasi `exists`/uniqueness dasar) menunjukkan tim tahu pola yang benar — masalahnya inkonsistensi penerapan, bukan gap pengetahuan.
- Kredensial di `node_mqtt_server` konsisten dari environment variable, `.env` sudah benar ter-gitignore dan terkonfirmasi tak pernah masuk histori git.
- `NotificationController::store` sudah pakai `DB::beginTransaction()`/`rollBack()` untuk atomisitas.

---

## Urutan Eksekusi yang Disarankan (untuk sesi implementasi berikutnya)

Scope ini besar dan menyentuh 2 codebase (Laravel + Node) — sebaiknya dieksekusi bertahap, bukan satu PR raksasa:

**Wave 1 — Keamanan kritis (blocker, tidak bergantung satu sama lain, bisa paralel per file):**
Perbaiki seluruh IDOR (#2, #4, #7, #8, #9) + scoping OTP (#5) + tambahkan `throttle` middleware ke endpoint auth-sensitif (#6) + benahi routing alarm berbarengan dengan otorisasinya (#3+#4) + pasang autentikasi (shared-secret atau verifikasi JWT) di `node_mqtt_server` untuk `/api/water-pump/control` dan `/api/store-token` (#1, #11).

**Wave 2 — Keandalan/robustness:**
`env()`→`config()` + lengkapi `.env.example` (#10), tangani FK-restrict delete dengan try/catch + respons rapi (bagian dari #7/#24), hentikan kebocoran `$e->getMessage()` ke klien (#16), tambahkan `catch`+global handler di `processQueue` node_mqtt_server + pisahkan state per-device (#12, #13).

**Wave 3 — Integritas data & cleanup:**
Index/constraint database (#23, #25), perbaiki relasi `Notification::user()` (#19), hapus `JwtMiddleware.php` mati (#29), ekstrak logika terduplikasi node_mqtt_server ke modul bersama (#33), beres-beres `package.json` (#34).

## Verifikasi

- Setelah tiap wave: jalankan test suite (`php artisan test` / Pest) dan tambahkan test baru untuk kasus IDOR yang diperbaiki (assert 403/404 saat user A mengakses resource user B).
- Untuk Wave 1 khususnya, uji manual end-to-end: login sebagai 2 user berbeda, pastikan user A tidak bisa lagi mengontrol/melihat/menghapus device milik user B lewat Postman/curl.
- Untuk perbaikan `node_mqtt_server`, uji dengan mem-publish pesan MQTT test dan memverifikasi proses tidak crash pada payload malformed, serta endpoint `/api/water-pump/control` menolak request tanpa token valid.
