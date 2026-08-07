# 📚 Wiki — Arcadia Project Avalon

Selamat datang di dokumentasi resmi **Arcadia — Project Avalon**, platform IoT untuk monitoring sensor & kontrol pompa air.

> Dokumen ini dikelola langsung di repositori (`docs/`) agar selalu sinkron dengan kode. Update terakhir mencakup seluruh pekerjaan hardening keamanan, otomasi CI/CD, dan penataan branch.

## Daftar Halaman

| Halaman | Isi |
|---|---|
| [🏗️ Arsitektur](arsitektur.md) | Komponen, alur data, port, dan stack teknologi |
| [🌿 Alur Branch & Rilis (SDLC)](sdlc-branch-strategy.md) | 5 branch inti, aturan proteksi, pola rilis `dev → canary → main` |
| [⚙️ CI/CD & Otomasi](ci-cd.md) | GitHub Actions: CI, CodeQL Advanced, Dependabot, Copilot, GitHub Advanced Security |
| [🔒 Keamanan](keamanan.md) | Hardening yang sudah dikerjakan: CVE Laravel, 8 alert CodeQL, Dependabot, dsb. |
| [🛠️ Troubleshooting](troubleshooting.md) | Masalah yang pernah ditemui & solusinya |

## Status Terkini

| Item | Status |
|---|---|
| Default branch | `main` |
| Posisi branch | `main` = `canary` = `dev` (tree identik) |
| Dependabot alerts | **0 open** |
| CodeQL alerts | **0 open** |
| CI | Hijau (Backend, Frontend, Node bridge) |
| CodeQL Advanced | Hijau (javascript-typescript + actions) |
| Framework | Laravel **12.65** (sebelumnya 11.55) |

## Quick Links

- [README utama](../README.md)
- [Audit & perbaikan backend](../be-avalon/AUDIT-PERBAIKAN.md)
- [README backend](../be-avalon/README.md)
- [README MQTT bridge](../be-avalon/node_mqtt_server/README.md)
- [README frontend](../fe-avalon/README.md)
- [Dokumentasi API (Postman)](https://documenter.getpostman.com/view/36245125/2sAYBREtgh)
