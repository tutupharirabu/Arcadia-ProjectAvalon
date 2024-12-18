<template>
  <div class="container min-h-screen mx-auto py-16 px-4">
    <!-- Hero Section -->
    <div class="relative z-10 flex flex-col items-center justify-center text-center md:pt-40" data-aos="fade-up">
      <!-- Subheading -->
      <h2 class="text-lg font-medium text-pink-600 mb-2" data-aos="fade-up" data-aos-delay="100">
        Arcadia Flora Tech
      </h2>

      <!-- Main Heading -->
      <h1 class="text-4xl md:text-6xl font-bold text-pink-800 leading-tight" data-aos="fade-up" data-aos-delay="200">
        Berkebun Cerdas <span class="text-pink-600">Menebar Keindahan</span>
      </h1>

      <!-- Description Box -->
      <div class="mt-4 px-6 py-4 bg-white bg-opacity-20 rounded-lg shadow-md text-base md:text-lg text-gray-800"
        style="max-width: 90%;" data-aos="fade-up" data-aos-delay="300">
        Enhance Your Agricultural Yields with Our Smart Technology.
      </div>
    </div>

    <!-- Daftar Sekarang Button -->
    <div class="mt-8 mb-12 flex justify-center" data-aos="fade-up" data-aos-delay="400">
      <router-link to="/arcadia-partner" class="btn-register-now">
        Daftar Arcadia Partner
      </router-link>
    </div>
  </div>
</template>

<style scoped>
/* Hero Section Button */
.btn-register-now {
  display: inline-block;
  padding: 0.75rem 2rem;
  font-size: 1.25rem;
  font-weight: bold;
  color: #fff;
  background: linear-gradient(90deg, #ff7e5f, #fc819e);
  border-radius: 9999px;
  box-shadow: 0 4px 15px rgba(252, 129, 158, 0.4);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  text-align: center;
  text-decoration: none;
}

.btn-register-now:hover {
  transform: scale(1.05);
  box-shadow: 0 6px 20px rgba(252, 129, 158, 0.6);
}

.btn-register-now:active {
  transform: scale(0.95);
}
</style>
