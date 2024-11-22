<template>
    <footer class="bg-black text-white py-12">
      <div class="container mx-auto flex flex-col md:flex-row items-center justify-between">
        <!-- Section Kiri -->
        <div class="md:w-1/2 space-y-6 text-center md:text-left">
          <!-- Logo dan Nama -->
          <div class="flex items-center justify-center md:justify-start space-x-3">
            <img src="@/assets/logogede.png" alt="Logo" class="h-12">
            <span class="text-4xl font-bold">Arcadia Flora Tech</span> <!-- Memperbesar ukuran font -->
          </div>
          <!-- Informasi Alamat -->
          <div class="space-y-4">
            <p class="text-xl font-semibold">Our Office</p> <!-- Memperbesar ukuran font -->
            <p class="text-lg">
              Belum ada<br>
              West Java, Indonesia.
            </p>
            <p class="text-lg leading-relaxed">
              Berkebun Cerdas<br>
              Menebar Keindahan<br>
              <a href="mailto:arcadiafloratech@gmail.com" class="text-pink-500 hover:underline">arcadiafloratech@gmail.com</a>.
            </p>
          </div>
          <!-- Tombol Login -->
          <div class="flex justify-center md:justify-start">
            <button class="px-8 py-4 bg-transparent text-white border border-white rounded-full flex items-center space-x-2 hover:bg-white hover:text-black transition text-lg">
              <i class="fas fa-envelope"></i>
              <span>Login</span>
            </button>
          </div>
        </div>
  
        <!-- Section Kanan -->
        <div class="md:w-1/2 mt-8 md:mt-0 flex justify-center relative">
          <img src="@/assets/parabunga.png" alt="Farmers Illustration" class="h-80 object-contain mx-auto md:mx-0 md:ml-12">
        </div>
      </div>
  
      <!-- Copyright -->
      <div class="text-center mt-12 text-lg text-gray-400"> <!-- Memperbesar ukuran font -->
        © 2024 Arcadia. All rights reserved.
      </div>
    </footer>
  </template>

<style scoped>
footer {
  background-color: black;
  color: white;
}

a:hover {
  color: #fbb6ce; /* Warna hover */
}

button:hover {
  background-color: white;
  color: black;
}

img {
  object-fit: contain; /* Menjaga proporsi gambar */
}
</style>
