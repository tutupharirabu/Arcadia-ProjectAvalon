<template>
  <nav class="w-full py-4 px-6 bg-transparent absolute top-0 z-50">
    <div class="container mx-auto flex items-center justify-between">
      <!-- Logo -->
      <div class="flex items-center">
        <img src="@/assets/frame 4.png" alt="Logo" class="h-10">
        <span class="ml-2 text-xl font-bold text-pink-800">Arcadia</span>
      </div>

      <!-- Navigation Links -->
      <ul class="hidden md:flex space-x-8 text-dark font-medium">
        <li><a href="#home" class="hover:text-pink-300">Home</a></li>
        <li><a href="#about" class="hover:text-pink-300">About Us</a></li>
        <li><a href="#product" class="hover:text-pink-300">Products</a></li>
        <li><a href="#team" class="hover:text-pink-300">Teams</a></li>
        <li><a href="#contact" class="hover:text-pink-300">Contact</a></li>
      </ul>

      <!-- Button -->
      <button class="hidden md:block px-7 py-3 bg-pink-600 text-white rounded-full shadow hover:bg-pink-700 transition duration-300">
        Login
      </button>
    </div>
  </nav>
</template>

  
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
</style>

  