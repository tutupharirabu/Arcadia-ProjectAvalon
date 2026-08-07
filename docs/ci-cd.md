# ⚙️ CI/CD & Otomasi

GitHub Actions dibatasi hanya pada **5 workflow** berikut:

| Workflow | Sumber | Fungsi |
|---|---|---|
| `CI` | `.github/workflows/ci.yml` | Test & build tiap push/PR ke `main`, `canary`, `dev` |
| `CodeQL Advanced` | `.github/workflows/codeql.yml` | Static analysis keamanan (CodeQL) |
| `Copilot` | dynamic agent | Review otomatis PR oleh Copilot |
| `GitHub Advanced Security` | dynamic agent | Code scanning AI findings |
| `Dependabot Updates` | dynamic | Update dependency otomatis |

> ⛔ **Default setup CodeQL dinonaktifkan** (`state=not-configured`). Jangan aktifkan kembali selama workflow `CodeQL Advanced` dipakai — GitHub **menolak** hasil analisis dari advanced configuration saat default setup aktif (error: *"CodeQL analyses from advanced configurations cannot be processed when the default setup is enabled"*).

## CI (`ci.yml`) — 3 Job

| Job | Isi |
|---|---|
| **Backend (Laravel)** | PHP 8.2 + ekstensi (mbstring, pdo_sqlite, dsb.) → `composer install` → lint `php -l` → `php artisan test` (sqlite `:memory:`, `APP_KEY` & `JWT_SECRET` dummy) |
| **Frontend (Vue)** | Node 22 → `npm ci` → `npm run build` → Cypress component tests |
| **Node bridge** | Node 22 → `npm ci` → `node --check` semua file server → `npm audit --audit-level=high` |

- `concurrency: cancel-in-progress` — run lama dibatalkan saat ada push baru di ref yang sama.
- Proteksi `main`/`canary` mewajibkan ketiga job ini **strict** (branch harus up-to-date).

## CodeQL Advanced (`codeql.yml`)

- **Bahasa**: `javascript-typescript` + `actions` (mode `build-mode: none`)
- **Trigger**: push & PR ke `dev`, `canary`, `main` + jadwal mingguan (Kamis `20 17 * * 4` UTC)
- **Catatan**: `php` **tidak lagi didukung** CodeQL action v4 (daftar bahasa resmi: actions, c-cpp, csharp, go, java-kotlin, javascript-typescript, python, ruby, rust, swift). Untuk keamanan dependency PHP, gunakan `composer audit` secara manual/CI.

## Dependabot (`.github/dependabot.yml`)

Tiga ecosystem dengan jadwal **mingguan Senin pagi (Asia/Jakarta)** dan limit 10 PR:

| Ecosystem | Direktori | Group | Major yang di-ignore |
|---|---|---|---|
| composer | `/be-avalon` | `composer-patch-minor` | `predis/predis` ≥3 |
| npm | `/be-avalon/node_mqtt_server` | `mqtt-bridge-patch-minor` | `express` ≥5, `dotenv` ≥17 |
| npm | `/fe-avalon` | `fe-patch-minor` | `tailwindcss` ≥4, `pinia` ≥3, `@schedule-x/vue` ≥3, `@schedule-x/theme-shadcn` ≥3 |

- **Grouping** patch/minor → 1 PR gabungan per ecosystem (bukan per-package).
- Label otomatis: `dependencies` + `backend`/`frontend`; commit prefix `chore(deps)`.
- **Kebijakan triage**: PR patch/minor di-merge otomatis setelah CI hijau; PR **major** ditutup dengan komentar (butuh migrasi terpisah). Major lain yang sudah ditutup: `vue-router` 5, `daisyui` 5, `start-server-and-test` 3, `@schedule-x/events-service` 4.
- **Dependabot security updates & vulnerability alerts AKTIF** (perlu diaktifkan manual di repo settings).

## Copilot & GitHub Advanced Security

- `Copilot` — review otomatis setiap PR (komentar per file/diff). Dapat diminta via `gh pr edit <n> --add-reviewer copilot-pull-request-reviewer` atau otomatis.
- `GitHub Advanced Security` — analisis AI pada PR (dynamic agent). Failure pada run-nya umumnya internal agent error, bukan kode repo.
