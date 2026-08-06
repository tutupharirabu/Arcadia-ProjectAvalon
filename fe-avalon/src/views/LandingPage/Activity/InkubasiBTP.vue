<template>
  <div class="no-footer">
    <div class="absolute inset-0 z-0">
      <!-- Background Image Section -->
      <div class="relative">
        <img src="@/assets/FJIP.png" alt="Background" class="w-full h-full object-cover">
        <div class="absolute inset-0 flex items-center justify-center">
          <h1 class="text-white text-5xl font-bold">Inkubasi Bandung Techno Park</h1>
        </div>
      </div>

      <!-- About Section -->
      <div class="container mx-auto px-4 py-8"> <!-- Mengurangi padding di sini -->
        <h2 class="text-3xl font-bold mb-2">Sudah Terlaksana</h2>
        <ul class="list-disc list-inside text-lg text-gray-700 mb-4 pl-8"> <!-- Menambahkan padding kiri -->
          <li>Mengikuti Faculty Join Incubation Program dari Bandung Techno Park (BTP)</li>
          <li>Berhasil Mengimplementasikan Alat Arcadia FLOWer ke Petani Cihideung</li>
          <li>Saat ini sedang progres membuat hak cipta dan Minimum Valuable Product untuk Arcadia FLOWer</li>
        </ul>

        <h2 class="text-3xl font-bold mb-2">Akan Terlaksana</h2>
        <ul class="list-disc list-inside text-lg text-gray-700 mb-4 pl-8"> <!-- Menambahkan padding kiri -->
          <li>Membuat Workshop terkait Arcadia FLOWer ke beberapa petani di Cihideung</li>
          <li>Membuat Komunitas Petani di Cihideung</li>
          <li>Membuat Perancangan Alat Selanjutnya Arcadia Skopia</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<style scoped>
h1 {
  text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
}
.no-footer footer {
  display: none;
}
</style>
