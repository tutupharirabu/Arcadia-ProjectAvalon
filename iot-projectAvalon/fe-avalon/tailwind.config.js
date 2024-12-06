/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: "#8b5cf6", // Warna utama
        },
        dark: {
          DEFAULT: "#1c1b22",
          lighter: "#2b2835",
        },
      },
    },
  },
  plugins: [],
};

