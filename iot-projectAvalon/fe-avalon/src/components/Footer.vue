<template>
  <footer id="footer" class="bg-black text-white py-12">
    <div class="container mx-auto flex flex-col md:flex-row items-center justify-between">
      <!-- Section Kiri -->
      <div class="md:w-1/2 space-y-6 text-center md:text-left ml-20"> <!-- Tambahkan ml-auto -->
        <!-- Logo dan Nama -->
        <div class="flex items-center justify-center md:justify-start space-x-3">
          <img src="@/assets/logogede.png" alt="Logo" class="h-12">
          <span class="text-4xl font-bold">Arcadia Flora Tech</span>
        </div>
        <!-- Informasi Alamat -->
        <div class="space-y-4">
          <p class="text-xl font-semibold">Our Office</p>
          <p class="text-lg">
            Telkom University, Bandung Techno Park<br>
            West Java, Indonesia.
          </p>
          <p class="text-lg leading-relaxed">
            Berkebun Cerdas,<br>
            Menebar Keindahan<br>
            <a href="mailto:arcadiafloratech@gmail.com" class="text-pink-500 hover:underline">arcadiafloratech@gmail.com</a>.
          </p>
        </div>
      </div>

      <!-- Section Kanan -->
      <div class="md:w-1/2 mt-8 md:mt-0 flex justify-center relative mr-15">
        <img src="@/assets/footerpunya.png" alt="Farmers Illustration" class="h-80 object-contain mx-auto md:mx-0 md:ml-12">
      </div>
    </div>

    <!-- Copyright -->
    <div class="text-center mt-12 text-lg text-gray-400">
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
/* .custom-left {
  margin-left: 100px;
} */
</style>
