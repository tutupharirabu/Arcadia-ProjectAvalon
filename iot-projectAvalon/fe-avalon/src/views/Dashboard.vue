<template>
  <div class="min-h-screen flex flex-col bg-gradient-to-br from-gray-50 to-pink-50">
    <!-- Navbar -->
    <header class="bg-gradient-to-r from-pink-500 to-pink-700 text-white py-4 shadow-lg">
      <div class="container mx-auto flex justify-between items-center px-6">
        <h1 class="text-2xl font-bold flex items-center space-x-2">
          <img src="@/assets/logogede.png" alt="Logo" class="h-10">
          <span>Arcadia Dashboard</span>
        </h1>
        <nav class="flex space-x-4">
          <button
            @click="logout"
            class="bg-white text-pink-600 px-4 py-2 rounded-lg hover:bg-pink-100 transition-transform transform hover:scale-105 shadow"
          >
            Logout
          </button>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 container mx-auto px-6 py-10">
      <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">
        Selamat Datang, <span class="text-pink-600">Petani Pintar!</span>
      </h2>

      <!-- IoT Monitoring Section -->
      <section class="grid lg:grid-cols-2 gap-8 mb-12">
        <!-- IoT Card -->
        <div class="p-6 bg-white shadow-lg rounded-lg hover:shadow-xl transition transform hover:scale-105 border-t-4 border-pink-500">
          <h3 class="text-xl font-semibold text-pink-700 flex items-center mb-4">
            <span class="ml-2">Monitoring IoT</span>
          </h3>
          <div class="flex items-center">
            <img src="@/assets/kaca.png" alt="IoT Icon" class="w-14 h-14 mr-4 animate-bounce" />
            <div>
              <p class="text-gray-600">
                Status sensor tanah: <span class="font-bold text-green-600">Optimal</span>
              </p>
              <p class="text-gray-600">
                Kelembaban udara: <span class="font-bold">{{ humidity }}%</span>
              </p>
              <p class="text-gray-600">
                Suhu lingkungan: <span class="font-bold">{{ temperature }}°C</span>
              </p>
            </div>
          </div>
        </div>

        <!-- Automatic Watering Section -->
        <div class="p-6 bg-white shadow-lg rounded-lg hover:shadow-xl transition transform hover:scale-105 border-t-4 border-pink-500">
          <h3 class="text-xl font-semibold text-pink-700 flex items-center mb-4">
            <span class="ml-2">Penyiraman Otomatis</span>
          </h3>
          <div class="flex items-center">
            <img src="@/assets/air.png" alt="Watering Icon" class="w-14 h-14 mr-4 animate-bounce" />
            <div>
              <p class="text-gray-600">
                Status: <span :class="wateringStatusClass">{{ wateringStatus }}</span>
              </p>
              <p class="text-gray-600">
                Penyiraman terakhir: <span class="font-bold">{{ lastWatering }}</span>
              </p>
            </div>
          </div>
          <div class="mt-4 flex space-x-4">
            <button
              @click="startWatering"
              class="px-4 py-2 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition-transform transform hover:scale-105"
            >
              Mulai Penyiraman
            </button>
            <button
              @click="stopWatering"
              class="px-4 py-2 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition-transform transform hover:scale-105"
            >
              Hentikan Penyiraman
            </button>
          </div>
        </div>
      </section>

      <!-- Weather Section -->
      <section class="p-6 bg-white shadow-lg rounded-lg hover:shadow-xl transition transform hover:scale-105 border-t-4 border-pink-500 mb-10">
        <h3 class="text-xl font-semibold text-pink-700 flex items-center mb-4">
          <span class="ml-2">Prediksi dan Pemantauan Cuaca</span>
        </h3>
        <div class="flex items-center">
          <img
            src="@/assets/suhu.png"
            alt="Weather Icon"
            class="w-14 h-14 mr-4 animate-bounce"
          />
          <div>
            <p class="text-gray-600">
              Kondisi Cuaca: <span class="font-bold">{{ weather.condition }}</span>
            </p>
            <p class="text-gray-600">
              Suhu: <span class="font-bold">{{ weather.temperature }}°C</span>
            </p>
            <p class="text-gray-600">
              Kelembapan: <span class="font-bold">{{ weather.humidity }}%</span>
            </p>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<script>
export default {
  data() {
    return {
      temperature: 28,
      humidity: 65,
      wateringStatus: "Aktif",
      lastWatering: "5 menit yang lalu",
      weather: {
        condition: "Cerah",
        temperature: 28,
        humidity: 70,
      },
    };
  },
  computed: {
    wateringStatusClass() {
      return this.wateringStatus === "Aktif"
        ? "font-bold text-blue-600"
        : "font-bold text-red-600";
    },
  },
  methods: {
    logout() {
      this.$router.push("/login");
    },
    startWatering() {
      this.wateringStatus = "Aktif";
      this.lastWatering = "Baru saja";
      alert("Penyiraman dimulai!");
    },
    stopWatering() {
      this.wateringStatus = "Tidak Aktif";
      alert("Penyiraman dihentikan!");
    },
  },
};
</script>

<style scoped>
.container {
  max-width: 1200px;
}

footer {
  font-size: 0.875rem;
  letter-spacing: 0.05em;
}
</style>
