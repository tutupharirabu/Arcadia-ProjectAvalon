<template>
  <section id="about">
    <div class="relative bg-product-section py-16">
      <div class="container mx-auto px-4 text-center">
        <!-- Judul About Us -->
        <h2
          class="about-title text-3xl font-bold text-pink-600"
          data-aos="fade-up"
          data-aos-duration="1000"
        >
          About Us
        </h2>

        <!-- Tombol Navigasi -->
        <div class="mt-8 flex justify-center space-x-8">
          <button
            @click="switchTab('whatWeDo')"
            :class="{ 'border-pink-600': selectedTab === 'whatWeDo' }"
            class="tab-btn border-2 border-transparent hover:border-pink-600 py-2 px-6 text-pink-600 font-medium rounded-full focus:outline-none"
          >
          Product Overview
          </button>
          <button
            @click="switchTab('visionMission')"
            :class="{ 'border-pink-600': selectedTab === 'visionMission' }"
            class="tab-btn border-2 border-transparent hover:border-pink-600 py-2 px-6 text-pink-600 font-medium rounded-full focus:outline-none"
          >
            Vision & Mission
          </button>
          <button
            @click="switchTab('ourPillar')"
            :class="{ 'border-pink-600': selectedTab === 'ourPillar' }"
            class="tab-btn border-2 border-transparent hover:border-pink-600 py-2 px-6 text-pink-600 font-medium rounded-full focus:outline-none"
          >
            Our Pillar
          </button>
        </div>

        <!-- Konten Berdasarkan Tab yang Dipilih -->
        <div
          class="mt-8 text-gray-600"
          data-aos="fade-in"
          data-aos-duration="1000"
          :key="selectedTab"
        >
        <!-- Pilih Our Product -->
        <div v-if="selectedTab === 'whatWeDo'">
          <div class="container mx-auto px-4 mt-12 text-center">
            <h2 class="about-title text-3xl font-bold text-pink-600 mb-8">Product Overview</h2>
            <div
              class="grid grid-cols-1 md:grid-cols-2 gap-8 justify-center items-center mx-auto"
              style="max-width: 800px;"
            >
              <!-- Card 1 -->
              <div
                class="bg-pink-100 p-6 rounded-lg shadow-md hover:shadow-lg transform transition duration-300 hover:scale-105 mx-auto"
                style="max-width: 300px;"
                data-aos="fade-up"
                data-aos-delay="0"
              >
                <div
                  class="overflow-hidden rounded-lg mx-auto mb-4"
                  style="height: 150px; width: 100%; max-width: 250px;"
                >
                  <img
                    src="@/assets/aquaflow.png"
                    alt="Pemberdayaan"
                    class="w-full h-full object-cover transition-transform duration-300 hover:scale-110"
                  />
                </div>
                <h3 class="text-xl font-semibold text-pink-700">Aquaflow</h3>
                <p class="mt-2 text-pink-600">
                  Aquaflow adalah solusi pertanian berbasis teknologi yang mengintegrasikan sensor IoT untuk memantau kualitas air dan kondisi lingkungan secara real-time.                 </p>
              </div>

              <!-- Card 2 -->
              <div
                class="bg-pink-100 p-6 rounded-lg shadow-md hover:shadow-lg transform transition duration-300 hover:scale-105 mx-auto"
                style="max-width: 300px;"
                data-aos="fade-up"
                data-aos-delay="200"
              >
                <div
                  class="overflow-hidden rounded-lg mx-auto mb-4"
                  style="height: 150px; width: 100%; max-width: 250px;"
                >
                  <img
                    src="@/assets/website.png"
                    alt="Teknologi Tepat Guna"
                    class="w-full h-full object-cover transition-transform duration-300 hover:scale-110"
                  />
                </div>
                <h3 class="text-xl font-semibold text-pink-700">Website</h3>
                <p class="mt-2 text-pink-600">
                  Website ini menawarkan dashboard yang memungkinkan pengguna untuk memantau dan mengelola  pertanian mereka dengan mudah.</p>
              </div>
            </div>
          </div>
        </div>

          <!-- Plih Visi Dan Misi -->
          <div v-if="selectedTab === 'visionMission'" class="mt-12 text-center">
              <!-- Heading -->
              <h2 class="about-title text-3xl font-bold text-pink-600 mb-15" data-aos="fade-right">
                    Visi dan Misi 
                  </h2>
              <!-- Vision -->
              <div class="mb-16">
                <h3 class="text-2xl font-semibold text-gray-800">Visi</h3>
                <hr class="my-4 border-t-2 border-gray-300 w-20 mx-auto">
                <p class="text-lg text-gray-600 leading-relaxed max-w-4xl mx-auto">
                  Menjadi pelopor dalam pengembangan teknologi IoT inovatif yang mendukung keberlanjutan pertanian modern, khususnya di bidang budidaya tanaman hias, dengan fokus pada efisiensi, kualitas, dan ketahanan lingkungan.               
                 </p>
              </div>

              <!-- Mission -->
              <div>
                <h3 class="text-2xl font-semibold text-gray-800">Misi</h3>
                <hr class="my-4 border-t-2 border-gray-300 w-20 mx-auto">
                <div class="text-lg text-gray-600 leading-relaxed max-w-4xl mx-auto">
                  <p class="mb-4" style="text-align: center;">
                    1. Mengembangkan perangkat IoT yang mudah digunakan dan efektif untuk meningkatkan produktivitas pertanian, terutama dalam budidaya tanaman bunga.
                  </p>
                  <p class="mb-4" style="text-align: center;">
                    2. Memberikan solusi inovatif untuk mengatasi permasalahan hama tanaman melalui teknologi penyiraman pestisida otomatis yang aman dan efisien.
                  </p>
                  <p class="mb-4" style="text-align: center;">
                    3. Mendukung petani lokal dalam meningkatkan kualitas dan kuantitas hasil pertanian mereka dengan pendekatan berbasis teknologi.
                  </p>
                  <p class="mb-4" style="text-align: center;">
                    4. Melakukan penelitian dan pengembangan secara berkelanjutan untuk menciptakan alat pertanian modern yang ramah lingkungan.
                  </p>
                  <p class="mb-4" style="text-align: center;">
                    5. Berkontribusi pada pembangunan ekonomi masyarakat desa melalui penerapan teknologi tepat guna dalam sektor pertanian.
                  </p>
              </div>
              </div>
            </div>

          <!-- Pilih Pillar -->
          <div v-if="selectedTab === 'ourPillar'">
  <div class="container mx-auto px-4 mt-12 text-center">
    <h2 class="about-title text-3xl font-bold text-pink-600 mb-8">Our Pillar</h2>
    <div
      class="grid grid-cols-1 md:grid-cols-2 gap-8 justify-center items-center mx-auto"
      style="max-width: 800px;"
    >
      <!-- Card 1 -->
      <div
        class="bg-pink-100 p-6 rounded-lg shadow-md hover:shadow-lg transform transition duration-300 hover:scale-105 mx-auto"
        style="max-width: 300px;"
        data-aos="fade-up"
        data-aos-delay="0"
      >
        <div
          class="overflow-hidden rounded-lg mx-auto mb-4"
          style="height: 150px; width: 100%; max-width: 250px;"
        >
          <img
            src="@/assets/ourpillar1.png"
            alt="Pemberdayaan"
            class="w-full h-full object-cover transition-transform duration-300 hover:scale-110"
          />
        </div>
        <h3 class="text-xl font-semibold text-pink-700">Pemberdayaan</h3>
        <p class="mt-2 text-pink-600">
          Mendukung petani dan urban gardeners melalui pelatihan dan pendampingan, meningkatkan kemandirian dalam mengelola pertanian dengan solusi yang tepat guna.
        </p>
      </div>

      <!-- Card 2 -->
      <div
        class="bg-pink-100 p-6 rounded-lg shadow-md hover:shadow-lg transform transition duration-300 hover:scale-105 mx-auto"
        style="max-width: 300px;"
        data-aos="fade-up"
        data-aos-delay="200"
      >
        <div
          class="overflow-hidden rounded-lg mx-auto mb-4"
          style="height: 150px; width: 100%; max-width: 250px;"
        >
          <img
            src="@/assets/ourpillar2.png"
            alt="Teknologi Tepat Guna"
            class="w-full h-full object-cover transition-transform duration-300 hover:scale-110"
          />
        </div>
        <h3 class="text-xl font-semibold text-pink-700">Teknologi Tepat Guna</h3>
        <p class="mt-2 text-pink-600">
          Menyediakan starter kit dan perangkat IoT untuk monitoring dan penyiraman otomatis, mendukung pertanian yang efisien, hemat sumber daya, dan ramah lingkungan.
        </p>
      </div>
    </div>
  </div>
</div>


        </div>
      </div>
    </div>
  </section>
</template>

<script>
import AOS from "aos";
import "aos/dist/aos.css";

export default {
  data() {
    return {
      selectedTab: "whatWeDo", // Default tab yang dipilih
    };
  },
  mounted() {
    // Inisialisasi AOS
    AOS.init({
      once: true, // Animasi berjalan sekali saat scroll
    });
  },
  methods: {
    switchTab(tab) {
      // Animasi keluar
      const content = document.querySelector(".text-gray-600");
      content.style.opacity = 0;

      // Ganti tab setelah animasi selesai
      setTimeout(() => {
        this.selectedTab = tab;
        content.style.opacity = 1; // Animasi masuk
      }, 300); // Durasi animasi keluar (300ms)
    },
  },
};
</script>

<style scoped>

/* Tambahan untuk garis heading */
hr {
  transition: width 0.3s ease;
}

hr:hover {
  width: 30%;
}

/* Gaya latar belakang untuk section */
.bg-product-section {
  /* background-image: url("@/assets/product.png"); 
  background-size: cover;
  background-position: center;
  background-attachment: fixed; */
}

/* Gaya untuk Judul */
.about-title {
  position: relative;
  display: inline-block;
  text-align: center;
  margin-bottom: 1rem;
}

.about-title::before,
.about-title::after {
  content: "";
  position: absolute;
  top: 50%;
  width: 60px; /* Panjang garis */
  height: 2px; /* Ketebalan garis */
  background-color: #4caf50; /* Warna garis */
}

.about-title::before {
  left: -80px; /* Jarak garis kiri */
}

.about-title::after {
  right: -80px; /* Jarak garis kanan */
}

/* Gaya tombol */
.tab-btn {
  transition: all 0.3s ease;
}

.tab-btn:hover {
  background-color: rgba(255, 182, 193, 0.2); /* Warna latar belakang saat hover */
}

/* Transisi untuk konten */
.text-gray-600 {
  transition: opacity 0.3s ease-in-out;
}
</style>
