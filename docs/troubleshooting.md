# 🛠️ Troubleshooting

Catatan masalah nyata yang pernah ditemui di repo ini beserta solusinya.

## 1. `npm ci` Gagal di CI tapi Sukses di Lokal (Conflict Markers)

**Gejala**: job CI *Node bridge* gagal 7–15 detik di step `Install dependencies`, padahal `npm ci` lokal sukses. CI node bridge sempat "pass" padahal lockfile invalid.

**Akar masalah**: `package.json`/`package-lock.json` berisi **Git merge conflict markers** (`<<<<<<< HEAD`) yang lolos dari squash merge dependabot → JSON invalid. `npm ci` lokal lolos karena fallback *hidden lockfile* (`node_modules/.package-lock.json`) — CI (checkout fresh) seharusnya gagal.

**Deteksi**:
```bash
grep -c "<<<<<<<" package-lock.json
node -e "JSON.parse(require('fs').readFileSync('package-lock.json','utf8'))"
```

**Solusi**: hapus markers dari `package.json` → `rm package-lock.json && npm install --package-lock-only` → verifikasi JSON valid → commit.

## 2. "CodeQL analyses from advanced configurations cannot be processed when the default setup is enabled"

**Gejala**: workflow CodeQL Advanced (manual) gagal di step *Perform CodeQL Analysis* dengan error SARIF tersebut.

**Akar masalah**: **Default setup** CodeQL dan **advanced setup** tidak bisa berjalan bersamaan — GitHub menolak hasil analisis dari advanced config selama default setup aktif.

**Solusi**: nonaktifkan default setup:
```bash
gh api -X PATCH repos/<owner>/<repo>/code-scanning/default-setup -f state=not-configured
```
Workflow dinamis `CodeQL` mungkin masih tampil "active" di daftar workflow — itu **metadata sisa**, tidak berjalan (0 run). Jangan re-enable→disable (berisiko memicu scan tambahan).

## 3. `Did not recognize the following languages: php` (CodeQL)

**Gejala**: workflow CodeQL dengan `languages: php` gagal di step *Initialize CodeQL*.

**Akar masalah**: CodeQL action **tidak lagi mendukung PHP** (daftar resmi: actions, c-cpp, csharp, go, java-kotlin, javascript-typescript, python, ruby, rust, swift).

**Alternatif PHP**: `composer audit` untuk kerentanan dependency; static analysis lain (mis. PHPStan/Larastan) untuk kualitas kode.

## 4. Run CI "Set up job" Gagal / Job Queued Selamanya

**Gejala**: job gagal di step pertama *Set up job* (0 detik) atau antri `queued` sangat lama; `gh run rerun` menolak dengan "workflow file may be broken".

**Akar masalah**: incident runner GitHub Actions (di luar kendali repo) — bukan kode.

**Solusi**: tunggu, lalu:
```bash
gh run rerun <run-id>          # re-run seluruh run
gh run rerun <run-id> --failed # hanya job yang gagal
# atau picu run baru: commit kosong + push
git commit --allow-empty -m "ci: retry"
```

## 5. PR Rilis `dev → canary` Selalu *Behind* (Strict Checks)

**Gejala**: PR rilis berstatus `BLOCKED`/`BEHIND` meski konten sama, karena proteksi `strict: true` (branch harus up-to-date) dan history rilis memakai squash.

**Solusi**: buat **branch rilis** dari branch sumber, merge branch target, push, lalu PR dari branch rilis (lihat [SDLC](sdlc-branch-strategy.md)).

## 6. "Can not approve your own pull request"

**Gejala**: mencoba approve PR sendiri via API/CLI ditolak GitHub.

**Solusi**: request review dari collaborator (`gh pr edit <n> --add-reviewer <login>`), atau (jika tim sepakat) turunkan `required_approving_review_count` di branch protection. Repo ini memakai opsi kedua (0 review, CI tetap wajib).

## 7. Dependabot Update Runs Gagal ("composer in /be-avalon for laravel/framework - Update")

**Gejala**: run *Dependabot Updates* failure saat mencoba bump `laravel/framework`.

**Akar masalah**: constraint `^11.9` menghalangi bump ke 12.x (dependensi terkunci: sanctum/jwt-auth butuh `illuminate ^11`) — run terjadi sebelum upgrade #71.

**Solusi**: sudah ter-resolve oleh upgrade Laravel 12.65. Jika muncul lagi: cek error resolve composer (biasanya dependency yang perlu di-update bersama).

## 8. GitHub API Alerts Kosong / Token Scope

**Catatan**: endpoint `dependabot/alerts` dan `code-scanning/alerts` memerlukan scope token yang sesuai (`security_events` untuk fine-grained). Jika API mengembalikan kosong, verifikasi lewat UI Security tab atau push test (pesan push GitHub menampilkan jumlah vulnerability di default branch).
