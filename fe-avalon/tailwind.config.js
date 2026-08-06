import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
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
          "accent-content": "#4A2E2B", // Coklat tua, kontras 8.2:1 di atas peach (WCAG AA)

          "neutral": "#6B6B6B", // Warna netral - Abu-abu, kontras 5.3:1 di atas putih (WCAG AA)
          "neutral-content": "#FFFFFF", // Kontras dengan warna putih

          "base-100": "#FFFFFF", // Warna dasar terang
          "base-200": "#FEC7B4", // Warna dasar menengah - Peach lembut
          "base-300": "#FC819E", // Warna dasar gelap - Pink lembut

          "base-content": "#6B6B6B", // Konten utama - Abu-abu gelap, kontras 5.3:1 di atas putih

          "info": "#F7418F", // Warna informasi - Pink cerah
          "info-content": "#FFFFFF", // Kontras dengan warna putih

          "success": "#FEC7B4", // Warna sukses - Peach lembut
          "success-content": "#4A2E2B", // Coklat tua, kontras 8.2:1 di atas peach (WCAG AA)

          "warning": "#FFF9E2", // Warna peringatan - Kuning pucat
          "warning-content": "#6B6B6B", // Kontras 5.0:1 di atas kuning pucat (WCAG AA)

          "error": "#DC2626", // Warna kesalahan - Merah, kontras 4.8:1 dengan putih (WCAG AA)
          "error-content": "#FFFFFF" // Kontras dengan warna putih
        },
      },
    ],
  },
};
