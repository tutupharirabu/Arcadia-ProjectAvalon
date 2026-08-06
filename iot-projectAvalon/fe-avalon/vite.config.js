import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  server: {
    open: '/beranda',  // URL yang akan dibuka setelah server dijalankan
  },
  plugins: [
    vue(),
    // Cypress component test tidak kompatibel dengan @vue/devtools-kit (unhandled rejection) —
    // plugin hanya diaktifkan di luar environment test (Cypress men-set NODE_ENV=test).
    // DevTools browser tetap bisa dipakai via extension.
    ...(process.env.NODE_ENV === 'test' ? [] : [vueDevTools()]),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
})
