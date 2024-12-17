<template>
  <footer id="footer" class="bg-gray-200 text-black py-8">
    <div class="container mx-auto flex flex-col md:flex-row justify-between px-6">
      <!-- Bagian Kiri -->
      <div class="md:w-1/3 space-y-6 text-center md:text-left mb-8 md:mb-0">
        <!-- Logo dan Nama -->
        <div class="flex items-center justify-center md:justify-start space-x-3">
          <img src="/LogoArcadia.png" alt="Logo" class="h-12">
          <span class="text-2xl md:text-4xl font-bold">Arcadia Flora Tech</span>
        </div>
        <!-- Informasi Alamat -->
        <div>
          <p class="text-lg font-semibold">Our Office</p>
          <p class="text-sm leading-relaxed">
            Telkom University, Bandung Techno Park<br>
            West Java, Indonesia.
          </p>
          <p class="text-sm leading-relaxed">
            Berkebun Cerdas, Menebar Keindahan<br>
            <a href="mailto:arcadiafloratech@gmail.com" class="text-pink-500 hover:underline">
              arcadiafloratech@gmail.com
            </a>
          </p>
        </div>
      </div>

      <!-- Bagian Menu -->
      <div class="w-full md:w-2/3 flex justify-center md:justify-end">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 text-center md:text-left">
          <div>
            <p class="text-lg font-semibold mb-4">Kontak Kami</p>
            <ul class="space-y-2">
              <li><a href="/kontak-kami" class="hover:text-pink-500">Contact Via Email</a></li>
              <li><a   class="hover:text-pink-500">Contact Via WA</a></li>
            </ul>
          </div>

          <div>
            <p class="text-lg font-semibold mb-4">Produk</p>
            <ul class="space-y-2">
              <li><a   class="hover:text-pink-500">Arcadia Skopia</a></li>
              <li><a   class="hover:text-pink-500">Arcadia FLOWer</a></li>
            </ul>
          </div>

          <div>
            <p class="text-lg font-semibold mb-4">Perusahaan</p>
            <ul class="space-y-2">
              <li><a href="/tentang-kami" class="hover:text-pink-500">Tentang Kami</a></li>
              <li><a   class="hover:text-pink-500">FAQ</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Copyright -->
    <div class="text-center mt-8 text-sm text-gray-600">
      © 2024 Arcadia. All rights reserved.
    </div>
  </footer>
</template>

<style scoped>
footer {
  background-color: rgb(227, 223, 223);
}

a:hover {
  color: #fbb6ce; /* Warna hover */
}
</style>
