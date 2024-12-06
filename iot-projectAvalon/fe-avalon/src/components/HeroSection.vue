<template>
  <section id="home" class="relative bg-[#fc819e] min-h-screen">
    <!-- Background Image -->
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
      class="relative z-10 flex flex-col items-center justify-center text-center pt-24 md:pt-60"
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
      Enhance Your Agricultural Yields with Our Smart Technology.
      </div>
    <div class="mt-8 flex justify-center" data-aos="fade-up" data-aos-delay="400">
  <a
    href="/register"
    class="btn-register-now"
  >
    Daftar Sekarang
  </a>
</div>

    </div>

    <!-- Logo Pojok Kiri dan Kanan -->
    <img
      src="@/assets/bungagede.png"
      alt="Logo"
      class="logo-left md:w-36 sm:w-24"
    />
    <img
      src="@/assets/bungagede.png"
      alt="Logo"
      class="logo-right md:w-36 sm:w-24"
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
  margin-left: 100px;
}

/* Penempatan dan animasi logo kanan */
.logo-right {
  position: absolute;
  bottom: 0;
  right: 0;
  transform-origin: center;
  animation: rotate-logo 10s linear infinite;
  width: 150px; /* Atur ukuran logo */
  margin-right: 100px;
}

h1 {
  line-height: 1.3;
}

/* Responsif - Logo di perangkat mobile */
@media (max-width: 640px) {
  .logo-left {
    width: 100px;
    margin-left: 20px;
  }

  .logo-right {
    width: 100px;
    margin-right: 20px;
  }

  .text-4xl {
    font-size: 2rem;
  }

  .text-lg {
    font-size: 1rem;
  }

  .pt-50 {
    padding-top: 50px; 
  }

  .pt-60 {
    padding-top: 60px; 
  }
}
/* Tombol "Daftar Sekarang" */
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

/* Responsif - Penyesuaian untuk mobile */
@media (max-width: 640px) {
  .btn-register-now {
    font-size: 1rem;
    padding: 0.5rem 1.5rem;
  }
}

</style>
