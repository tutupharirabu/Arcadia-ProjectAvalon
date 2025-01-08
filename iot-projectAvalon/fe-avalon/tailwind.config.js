import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
import daisyui from 'daisyui';

export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Montserrat Alternates', 'sans-serif'],
      },
      maxHeight: {
        '128': '32rem',
      }
    },
  },
  plugins: [
    daisyui, // Menggunakan import untuk plugin DaisyUI
  ],
  daisyui: {
    themes: [
      {
        "professionaltheme": {
          "primary": "#F7418F", // Warna utama - Pink cerah, menarik perhatian
          "primary-content": "#FFFFFF", // Kontras dengan warna putih

          "secondary": "#FC819E", // Warna pendukung - Pink lembut
          "secondary-content": "#FFFFFF", // Kontras dengan warna putih

          "accent": "#FEC7B4", // Warna aksen - Peach lembut
          "accent-content": "#FFFFFF", // Kontras dengan warna putih

          "neutral": "#8B8B8B", // Warna netral - Abu-abu gelap
          "neutral-content": "#FFFFFF", // Kontras dengan warna putih

          "base-100": "#FFFFFF", // Warna dasar terang
          "base-200": "#FEC7B4", // Warna dasar menengah - Peach lembut
          "base-300": "#FC819E", // Warna dasar gelap - Pink lembut

          "base-content": "#8B8B8B", // Konten utama - Abu-abu gelap

          "info": "#F7418F", // Warna informasi - Pink cerah
          "info-content": "#FFFFFF", // Kontras dengan warna putih

          "success": "#FEC7B4", // Warna sukses - Peach lembut
          "success-content": "#FFFFFF", // Kontras dengan warna putih

          "warning": "#FFF9E2", // Warna peringatan - Kuning pucat
          "warning-content": "#8B8B8B", // Kontras dengan abu-abu gelap

          "error": "#FC819E", // Warna kesalahan - Pink lembut
          "error-content": "#FFFFFF" // Kontras dengan warna putih
        },
      },
    ],
  },
};
