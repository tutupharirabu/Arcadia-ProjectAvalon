import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// Catatan: vite-plugin-vue-devtools sengaja TIDAK dipakai — @vue/devtools-kit-nya
// melempar unhandled rejection di Cypress component test. DevTools browser
// tetap bisa dipakai via extension browser (tanpa plugin).

// https://vite.dev/config/
export default defineConfig({
  server: {
    open: '/beranda',  // URL yang akan dibuka setelah server dijalankan
  },
  plugins: [
    vue(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
})
