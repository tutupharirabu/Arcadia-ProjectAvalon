# 🌿 Alur Branch & Rilis (SDLC)

## Branch Inti

Repo dikelola dengan **5 branch inti** (branch lain di luar ini dihapus — tidak ada branch sisa):

| Branch | Fungsi | Proteksi |
|---|---|---|
| `main` | **Produksi** — default branch | ✅ Branch protection |
| `canary` | **Staging / pre-produksi** | ✅ Branch protection |
| `dev` | **Integrasi** semua fitur | ❌ Tidak diproteksi |
| `dev-be` | Branch kerja khusus backend | ❌ |
| `dev-fe` | Branch kerja khusus frontend | ❌ |

## Alur Pengembangan

```
improvement/* (fitur/perbaikan — branch kerja sementara)
        └─▶ PR → dev (integrasi & CI)
                  └─▶ PR → canary (staging)
                            └─▶ PR → main (produksi)
```

1. Kerjakan fitur di branch kerja (mis. `improvement/fix-login`), bisa berbasis `dev`, `dev-be`, atau `dev-fe`.
2. Buka PR ke `dev` — CI wajib hijau (3 job: Backend, Frontend, Node bridge).
3. Setelah stabil, **rilis** via PR `dev → canary`, lalu `canary → main`.

## Branch Protection (`main` & `canary`)

| Aturan | Nilai |
|---|---|
| Required approving reviews | **0** (dari 1 — diubah agar tidak ada bottleneck tunggu review; CI tetap wajib) |
| Required status checks | Backend (Laravel), Frontend (Vue), Node bridge — **strict** (branch harus up-to-date) |
| Enforce admins | ✅ aktif (admin pun tidak bisa bypass) |
| Require conversation resolution | ✅ |
| Direct push | ❌ Diblokir — semua perubahan harus lewat PR |

`dev` sengaja tidak diproteksi agar alur pengembangan cepat.

## Pola Rilis (Penting!)

Rilis `dev → canary` dan `canary → main` memakai **squash merge**, sehingga **history antar branch tidak pernah sejajar** — GitHub UI akan selalu menampilkan angka *ahead/behind* meskipun **konten (tree) identik**. Ini normal dan tidak berbahaya.

**Konsekuensi praktis**: karena proteksi `strict: true` (branch harus up-to-date), PR rilis langsung `dev → canary` akan berstatus *behind*. Solusi yang dipakai di repo ini: buat **branch rilis** dari branch sumber, merge branch target ke dalamnya, push, lalu PR dari branch rilis:

```bash
git switch -c release/<nama> origin/dev      # dari branch sumber
git merge origin/canary --no-edit            # gabungkan branch target
# resolve konflik (ambil versi branch sumber — rilis satu arah)
git push -u origin release/<nama>
# buka PR: base=canary, head=release/<nama>
```

## Aturan Praktis

- **Satu arah**: isi mengalir `dev → canary → main`. Jangan pernah merge `main`/`canary` ke `dev` untuk menambah fitur.
- **Jangan force-push** ke branch inti.
- Branch dependabot & branch kerja dihapus setelah PR-nya selesai (merged/closed).
