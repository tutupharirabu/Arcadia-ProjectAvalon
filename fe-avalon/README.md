# fe-avalon — Frontend Platform IoT

Frontend (Vue 3 + Vite) untuk platform IoT Avalon: dashboard kontrol pompa air, monitoring sensor real-time, notifikasi, dan alarm terjadwal. Berkomunikasi dengan backend Laravel (`be-avalon`) dan node bridge MQTT (`be-avalon/node_mqtt_server`).

## Setup Lokal

```bash
npm install
cp .env.example .env   # set VITE_API_URL / VITE_NODE_URL (opsional — ada default)
npm run dev            # http://localhost:5173
```

## Environment Variables

| Variable | Deskripsi |
|---|---|
| `VITE_API_URL` | Base URL API Laravel. Default jika kosong: `http://localhost:8000/api/v1` |
| `VITE_NODE_URL` | Base URL node server monitoring (data sensor real-time). Default jika kosong: `http://localhost:3000` |

Variabel dipakai di `src/utils/customFetch.js` (axios instance `customFetch` → API, `nodeFetch` → node server). Saat deploy produksi, set keduanya di environment build (mis. `.env.production` atau variabel env VPS) — jangan commit nilai asli di `.env`.

## Script

```bash
npm run dev             # dev server Vite
npm run build           # build produksi ke dist/
npm run preview         # preview hasil build
npm run test:unit       # Cypress component test (headless)
npm run test:unit:dev   # Cypress component test (UI)
npm run test:e2e        # Cypress e2e (butuh backend berjalan)
```

## Struktur Folder

```
fe-avalon/
├── src/
│   ├── components/     # komponen UI (FormAuth, dll.) + __tests__/ (Cypress component)
│   ├── views/          # halaman (dashboard, device, notifikasi, dll.)
│   ├── stores/         # state Pinia
│   ├── router/         # definisi route + guard auth
│   ├── utils/          # customFetch.js (axios + interceptor token/401), listNav.js
│   ├── assets/         # style global
│   ├── App.vue
│   └── main.js
├── cypress/            # e2e spec
└── cypress.config.js
```

Catatan: axios instance di `src/utils/customFetch.js` memakai interceptor — token JWT disisipkan otomatis ke header `Authorization`, dan respons 401 memicu logout otomatis.

## Deploy (VPS)

1. Build: `npm run build` → hasil di `dist/`.
2. Hosting statis dengan nginx: root = `dist/`, tambahkan SPA rewrite `try_files $uri /index.html;` agar route Vue (mis. `/dashboard`) tidak 404.
3. Set `VITE_API_URL` dan `VITE_NODE_URL` saat build — default menunjuk `localhost` untuk dev lokal.
4. Variabel `VITE_*` dibundle saat build — setelah mengubahnya, lakukan re-build + deploy ulang.
5. Pasang SSL (Let's Encrypt) dan proxy API ke `be-avalon` / node server di konfigurasi nginx.
