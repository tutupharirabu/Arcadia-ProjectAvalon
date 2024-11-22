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
            Our Products
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
          <div v-if="selectedTab === 'whatWeDo'">
            <div class="relative bg-product-section">
                <div class="container mx-auto px-4 text-center">
                  <h2 class="about-title text-3xl font-bold text-pink-600" data-aos="fade-right">
                    Our Products 
                  </h2>
                  <p class="text-gray-600 mb-10">
                    End-to-end precise control of your farm
                  </p>
                  <div class="container mx-auto px-4 mt-12">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                      <!-- Card 1 -->
                      <div
                        class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transform transition duration-300 hover:scale-105"
                        data-aos="fade-up"
                        data-aos-delay="0"
                      >
                        <div
                          class="overflow-hidden rounded-lg"
                          style="height: 200px;"
                        >
                          <img
                            src="@/assets/aquaflow.png"
                            alt="Integrated IoT Sensors"
                            class="w-full h-full object-cover transition-transform duration-300 hover:scale-110"
                          />
                        </div>
                        <h3 class="mt-4 text-xl font-semibold text-gray-800">
                          Aquaflow
                        </h3>
                        <p class="mt-2 text-gray-600">
                          testtesttestetsttesttetstetettsttstsetsttstes
                        </p>
                      </div>

                      <!-- Card 2 -->
                      <div
                        class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transform transition duration-300 hover:scale-105"
                        data-aos="fade-up"
                        data-aos-delay="200"
                      >
                        <div
                          class="overflow-hidden rounded-lg"
                          style="height: 200px;"
                        >
                          <img
                            src="@/assets/backgroundwebsite.png"
                            alt="Smart Dashboard"
                            class="w-full h-full object-cover transition-transform duration-300 hover:scale-110"
                          />
                        </div>
                        <h3 class="mt-4 text-xl font-semibold text-gray-800">
                          Produk 2
                        </h3>
                        <p class="mt-2 text-gray-600">
                          testtesttestetsttesttetstetettsttstsetsttstes
                          testtesttestetsttesttetstetettsttstsetsttstes
                        </p>
                      </div>

                      <!-- Card 3 -->
                      <div
                        class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transform transition duration-300 hover:scale-105"
                        data-aos="fade-up"
                        data-aos-delay="400"
                      >
                        <div
                          class="overflow-hidden rounded-lg"
                          style="height: 200px;"
                        >
                          <img
                            src="@/assets/backgroundwebsite.png"
                            alt="Integrated Controller"
                            class="w-full h-full object-cover transition-transform duration-300 hover:scale-110"
                          />
                        </div>
                        <h3 class="mt-4 text-xl font-semibold text-gray-800">
                          Produk 3
                        </h3>
                        <p class="mt-2 text-gray-600">
                          testtesttestetsttesttetstetettsttstsetsttstes
                          testtesttestetsttesttetstetettsttstsetsttstes
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
          </div>
          <div v-if="selectedTab === 'visionMission'">
            <p>
              testtesttestetsttesttetstetettsttstsetsttstes
            </p>
          </div>
            <div v-if="selectedTab === 'ourPillar'">

              <div class="container mx-auto px-4 mt-12">
                <div>
                <h2 class="about-title text-3xl font-bold text-pink-600"
                > Our Pillar</h2>
              </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                  <!-- Card 1 -->
                  <div
                    class="bg-pink-100 p-6 rounded-lg shadow-md hover:shadow-lg transform transition duration-300 hover:scale-105 text-center"
                    data-aos="fade-up"
                    data-aos-delay="0"
                  >
                    <div
                      class="overflow-hidden rounded-lg mx-auto mb-4"
                      style="height: 150px; width: 100%; max-width: 250px;"
                    >
                      <img
                        src="@/assets/Frame 4.png"
                        alt="Edukasi"
                        class="w-full h-full object-cover transition-transform duration-300 hover:scale-110"
                      />
                    </div>
                    <h3 class="text-xl font-semibold text-pink-700">Edukasi</h3>
                    <p class="mt-2 text-pink-600">
                      Memberikan pengetahuan praktis tentang cara bercocok tanam dengan teknologi modern.
                    </p>
                  </div>

                  <!-- Card 2 -->
                  <div
                    class="bg-pink-100 p-6 rounded-lg shadow-md hover:shadow-lg transform transition duration-300 hover:scale-105 text-center"
                    data-aos="fade-up"
                    data-aos-delay="200"
                  >
                    <div
                      class="overflow-hidden rounded-lg mx-auto mb-4"
                      style="height: 150px; width: 100%; max-width: 250px;"
                    >
                      <img
                        src="@/assets/Frame 4.png"
                        alt="Pemberdayaan"
                        class="w-full h-full object-cover transition-transform duration-300 hover:scale-110"
                      />
                    </div>
                    <h3 class="text-xl font-semibold text-pink-700">Pemberdayaan</h3>
                    <p class="mt-2 text-pink-600">
                      Mendukung petani melalui pelatihan dan solusi untuk kemandirian.
                    </p>
                  </div>

                  <!-- Card 3 -->
                  <div
                    class="bg-pink-100 p-6 rounded-lg shadow-md hover:shadow-lg transform transition duration-300 hover:scale-105 text-center"
                    data-aos="fade-up"
                    data-aos-delay="400"
                  >
                    <div
                      class="overflow-hidden rounded-lg mx-auto mb-4"
                      style="height: 150px; width: 100%; max-width: 250px;"
                    >
                      <img
                        src="@/assets/Frame 4.png"
                        alt="Teknologi Tepat Guna"
                        class="w-full h-full object-cover transition-transform duration-300 hover:scale-110"
                      />
                    </div>
                    <h3 class="text-xl font-semibold text-pink-700">Teknologi Tepat Guna</h3>
                    <p class="mt-2 text-pink-600">
                      Menyediakan perangkat IoT untuk mendukung pertanian efisien.
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
