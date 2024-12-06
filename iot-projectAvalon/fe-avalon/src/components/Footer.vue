<template>
  <footer id="footer" class="bg-black text-white py-12">
    <div class="container mx-auto flex flex-col md:flex-row items-center justify-between px-6">
      <!-- Section Kiri -->
      <div class="md:w-1\2 space-y-6 text-center md:text-left mb-8 md:mb-0">
        <!-- Logo dan Nama -->
        <div class="flex items-center justify-center md:justify-start space-x-3">
          <img src="@/assets/logogede.png" alt="Logo" class="h-12">
          <span class="text-2xl md:text-4xl font-bold">Arcadia Flora Tech</span>
        </div>
        <!-- Informasi Alamat -->
        <div class="space-y-4">
          <p class="text-lg font-semibold">Our Office</p>
          <p class="text-sm md:text-lg">
            Telkom University, Bandung Techno Park<br>
            West Java, Indonesia.
          </p>
          <p class="text-sm md:text-lg leading-relaxed">
            Berkebun Cerdas,<br>
            Menebar Keindahan<br>
            <a href="mailto:arcadiafloratech@gmail.com" class="text-pink-500 hover:underline">arcadiafloratech@gmail.com</a>
          </p>
        </div>
      </div>

      <!-- Section Kanan -->
      <div class="md:w-1\2 flex justify-center">
        <img
          src="@/assets/footerpunya.png"
          alt="Farmers Illustration"
          class="h-64 md:h-80 object-contain"
        />
      </div>
    </div>

    <!-- Copyright -->
    <div class="text-center mt-8 text-sm md:text-lg text-gray-400">
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

img {
  object-fit: contain; /* Menjaga proporsi gambar */
}

@media (max-width: 768px) {
  footer {
    padding: 2rem 1rem;
  }

  .container {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .md:w-1\2 {
    width: 100%; 
  }

  img {
    max-width: 100%;
  }
}
</style>
