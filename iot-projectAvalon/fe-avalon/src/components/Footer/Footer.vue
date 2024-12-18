<template>
  <footer class="footer text-black px-10 py-8 bg-neutral-200">
    <!-- Container Utama -->
    <div class="container mx-auto flex flex-col justify-between space-y-8 md:flex-row md:space-y-0">
      <!-- Bagian Kiri -->
      <div class="md:w-1/3 space-y-6 text-center md:text-left">
        <!-- Logo dan Nama -->
        <div class="flex items-center justify-center md:justify-start space-x-3">
          <img src="/LogoArcadia.png" alt="Logo" class="h-12" />
          <span class="text-2xl md:text-4xl font-bold">Arcadia Flora Tech</span>
        </div>
        <!-- Informasi Alamat -->
        <div>
          <p class="text-lg font-semibold">Our Office</p>
          <p class="text-sm leading-relaxed">
            Telkom University, Bandung Techno Park<br />
            West Java, Indonesia.
          </p>
          <p class="text-sm leading-relaxed">
            Berkebun Cerdas, Menebar Keindahan<br />
            <a href="mailto:arcadiafloratech@gmail.com" class="text-pink-500 hover:underline">
              arcadiafloratech@gmail.com
            </a>
          </p>
        </div>
      </div>

      <!-- Bagian Menu -->
      <div class="w-full md:w-2/3 flex justify-center md:justify-end">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 text-center md:text-left">
          <!-- Kontak Kami -->
          <div>
            <p class="text-lg font-semibold mb-4">Kontak Kami</p>
            <ul class="space-y-2">
              <li>
                <router-link to="/kontak-kami" class="hover:text-pink-500">
                  Contact Via Email
                </router-link>
              </li>
              <li>
                <router-link to="#" class="hover:text-pink-500">
                  Contact Via WA
                </router-link>
              </li>
            </ul>
          </div>

          <!-- Produk -->
          <div>
            <p class="text-lg font-semibold mb-4">Produk</p>
            <ul class="space-y-2">
              <li>
                <router-link to="#" class="hover:text-pink-500">
                  Arcadia Skopia
                </router-link>
              </li>
              <li>
                <router-link to="#" class="hover:text-pink-500">
                  Arcadia FLOWer
                </router-link>
              </li>
            </ul>
          </div>

          <!-- Perusahaan -->
          <div>
            <p class="text-lg font-semibold mb-4">Perusahaan</p>
            <ul class="space-y-2">
              <li>
                <router-link to="/tentang-kami" class="hover:text-pink-500">
                  Tentang Kami
                </router-link>
              </li>
              <li>
                <router-link to="#" class="hover:text-pink-500">
                  FAQ
                </router-link>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </footer>
  <div class="grid grid-cols-1 place-items-center bg-neutral-200">
    <p class="text-sm font-semibold text-gray-600 mb-4">
      © 2024 Arcadia. All rights reserved.
    </p>
  </div>
</template>

<style scoped>
a:hover {
  color: #fbb6ce;
  /* Warna hover */
}
</style>
