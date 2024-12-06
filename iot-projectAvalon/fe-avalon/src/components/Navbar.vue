<template>
  <nav class="w-full py-4 px-6 bg-transparent absolute top-0 z-50">
    <div class="container mx-auto flex items-center justify-between">
      <!-- Logo -->
      <div class="flex items-center">
        <img src="@/assets/frame 4.png" alt="Logo" class="h-10">
        <span class="ml-2 text-xl font-bold text-pink-800">Arcadia</span>
      </div>

      <!-- Mobile Menu Button-->
      <div class="md:hidden flex items-center">
        <button @click="toggleMenu" class="text-pink-800">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
      </div>

      <!-- Navigation Links (Desktop) -->
      <ul class="hidden md:flex space-x-8 text-dark font-medium">
        <li><router-link to="/" class="hover:text-pink-300">Home</router-link></li>
        <li><a href="#about" class="hover:text-pink-300">About Us</a></li>
        <li><a href="#team" class="hover:text-pink-300">Teams</a></li>
        <li><a href="#footer" class="hover:text-pink-300">Contact</a></li>
      </ul>

      <!-- Mobile Navigation Links -->
      <ul v-if="isMenuOpen" class="md:hidden absolute top-16 left-0 w-full bg-white shadow-lg text-center space-y-4 py-4">
        <li><router-link to="/" class="block text-pink-600">Home</router-link></li>
        <li><a href="#about" class="block text-pink-600">About Us</a></li>
        <li><a href="#team" class="block text-pink-600">Teams</a></li>
        <li><a href="#footer" class="block text-pink-600">Contact</a></li>
      </ul>

      <!-- Button -->
      <router-link
        to="/login"
        class="hidden md:block px-7 py-3 bg-pink-600 text-white rounded-full shadow hover:bg-pink-700 transition duration-300"
      >
        Login
      </router-link>
    </div>
  </nav>
</template>

<script>
export default {
  data() {
    return {
      isMenuOpen: false, // State to toggle the mobile menu
    };
  },
  methods: {
    toggleMenu() {
      this.isMenuOpen = !this.isMenuOpen;
    },
  },
};
</script>

<style scoped>
/* Tambahkan properti transparansi */
nav {
  background: transparent; /* Navbar transparan */
  position: absolute; /* Navbar tetap berada di atas konten */
  top: 0;
  z-index: 50; /* Pastikan navbar di atas elemen lainnya */
}

body {
  margin-top: 0; /* Hilangkan margin atas agar navbar sejajar dengan bagian atas halaman */
}

/* Gaya link di navbar */
ul li a {
  transition: color 0.3s ease-in-out;
}

ul li a:hover {
  color: #fbb6ce; /* Warna hover */
}

/* Tambahkan gaya bayangan tombol */
button:hover {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Gaya untuk menu mobile */
ul.md\\:hidden {
  display: block;
  width: 100%;
  padding: 0;
  margin: 0;
}

ul.md\\:hidden li a {
  padding: 12px 0;
  display: block;
}
</style>
