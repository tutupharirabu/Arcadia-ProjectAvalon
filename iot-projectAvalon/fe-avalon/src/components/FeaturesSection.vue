<template>
  <section id="product">
    <!-- Background Image -->
    <div class="relative bg-product-section py-16 bg-gray-100">
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
  </section>
</template>

<script>
export default {
  name: "ProductSection",
};
</script>

<style scoped>
.bg-product-section {
  background-image: url('@/assets/product.png'); /* Gambar sama untuk kedua section */
  background-size: cover;
  background-position: center; /* Pastikan posisi sama */
  background-attachment: fixed; /* Untuk efek seamless */
}

.about-title {
  position: relative;
  display: inline-block;
  text-align: center;
}

.about-title::before,
.about-title::after {
  content: '';
  position: absolute;
  top: 50%;
  width: 40px; /* Panjang garis */
  height: 2px; /* Ketebalan garis */
  background-color: #fc819e; /* Warna garis */
}

.about-title::before {
  left: -50px; /* Jarak dari teks ke garis kiri */
}

.about-title::after {
  right: -50px; /* Jarak dari teks ke garis kanan */
}

</style>

