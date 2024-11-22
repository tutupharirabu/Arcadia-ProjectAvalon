<template>
  <section id="home" class="relative bg-[#fc819e] min-h-screen">
    Background Image
    <div class="absolute inset-0">
      <img
        src="@/assets/background.png"
        alt="Hero Background"
        class="w-full h-full object-cover"
      />
    </div>

    <!-- Navbar -->
    <Navbar />

    <!-- Hero Content -->
    <div
      class="relative z-10 flex flex-col items-center justify-center text-center pt-50 md:pt-60"
      data-aos="fade-up"
    >
      <!-- Subheading -->
      <h2
        class="text-lg font-medium text-pink-600 mb-2"
        data-aos="fade-up"
        data-aos-delay="100"
      >
        Arcadia Flora Tech
      </h2>

      <!-- Main Heading -->
      <h1
        class="text-4xl md:text-6xl font-bold text-pink-800 leading-tight"
        data-aos="fade-up"
        data-aos-delay="200"
      >
        Berkebun Cerdas <span class="text-pink-600">Menebar Keindahan</span>
      </h1>

      <!-- Description Box -->
      <div
        class="mt-4 px-6 py-4 bg-white bg-opacity-20 rounded-lg shadow-md text-base md:text-lg text-gray-800"
        style="max-width: 90%;"
        data-aos="fade-up"
        data-aos-delay="300"
      >
        Maintain harvest & reduce harvest failure risk with us.
      </div>
    </div>

    <!-- Logo Pojok Kiri dan Kanan -->
    <img
      src="@/assets/bungagede.png"
      alt="Logo"
      class="logo-left"
    />
    <img
      src="@/assets/bungagede.png"
      alt="Logo"
      class="logo-right"
    />
  </section>
</template>

<script>
import Navbar from "@/components/Navbar.vue";
import AOS from 'aos';
import 'aos/dist/aos.css'; // Import AOS styles

export default {
  components: {
    Navbar,
  },
  mounted() {
    AOS.init(); // Initialize AOS when the component is mounted
  },
};
</script>

<style scoped>
/* Rotasi Logo */
@keyframes rotate-logo {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* Penempatan dan animasi logo kiri */
.logo-left {
  position: absolute;
  bottom: 0;
  left: 0;
  transform-origin: center;
  animation: rotate-logo 10s linear infinite;
  width: 150px; /* Atur ukuran logo */
  margin-left: 20px;
}

/* Penempatan dan animasi logo kanan */
.logo-right {
  position: absolute;
  bottom: 0;
  right: 0;
  transform-origin: center;
  animation: rotate-logo 10s linear infinite;
  width: 150px; /* Atur ukuran logo */
  margin-right: 20px;
}

h1 {
  line-height: 1.3;
}
</style>
