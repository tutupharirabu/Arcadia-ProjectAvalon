<template>
<div>
    <div class="absolute inset-0 z-0">
      <img src="@/assets/Background.png" alt="Hero Background" class="w-full h-full object-cover opacity-70 hidden md:block">
    </div>
    <!-- Hero Section -->
    <div class="relative z-10 flex flex-col items-center justify-center text-center md:pt-40 min-h-screen " data-aos="fade-up">
      <h2 class="text-lg font-medium text-pink-600 mb-2" data-aos="fade-up" data-aos-delay="100">
        Arcadia Flora Tech
      </h2>

      <h1 class="text-4xl md:text-6xl font-extrabold text-pink-800 leading-tight" data-aos="fade-up" data-aos-delay="200">
        Berkebun Cerdas <span class="text-gradient">Menebar Keindahan</span>
      </h1>

      <div class="mt-4 px-6 py-4 bg-white bg-opacity-20 rounded-lg shadow-lg text-base md:text-lg text-gray-800" style="max-width: 70%;" data-aos="fade-up" data-aos-delay="300">
        Enhance Your Agricultural Yields with Our Smart Technology.
      </div>

      <div class="mt-8 mb-12 flex justify-center" data-aos="fade-up" data-aos-delay="400">
        <router-link to="/arcadia-partner" class="btn-register-now">
          Daftar Arcadia Partner
        </router-link>
      </div>
    </div>

    <!-- Pembatas -->
    <hr class="my-12 border-t-2 border-pink-600 mx-auto w-1/2">

    <!-- About Section -->
    <div class="container mx-auto py-16 px-4">
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">Tentang Kami</h1>
        <p class="text-lg text-gray-600">
          Kami adalah perusahaan teknologi pertanian yang berfokus pada inovasi cerdas untuk meningkatkan hasil panen.
        </p>
      </div>

      <!-- Content Layout -->
      <div class="relative grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
        <!-- Bagian Visi -->
        <div class="bg-white p-10 shadow-card rounded-lg relative z-10 mt-12">
    <h2 class="text-2xl font-semibold text-pink-600 mb-4">Visi</h2>
    <p class="text-gray-700 leading-relaxed">
        Menjadi pelopor dalam pengembangan teknologi IoT inovatif yang mendukung keberlanjutan pertanian modern, khususnya di bidang budidaya tanaman hias, dengan fokus pada efisiensi, kualitas, dan ketahanan lingkungan.
    </p>
</div>


        <!-- Gambar Visi -->
        <div class="flex justify-center hidden md:flex ">
          <img src="@/assets/visi.png" alt="Visi Image" class="custom-imageVisi">
        </div>

        <!-- Gambar Misi -->
        <div class="flex justify-center hidden md:flex mt-12">
          <img src="@/assets/misi.png" alt="Misi Image" class="custom-imageMisi">
        </div>

        <!-- Bagian Misi -->
        <div class="bg-white p-8 shadow-card rounded-lg relative z-10 mt-12">
          <h2 class="text-2xl font-semibold text-pink-600 mb-4">Misi</h2>
          <ul class="list-disc pl-6 text-gray-700">
            <li class="mb-2">Mengembangkan perangkat IoT yang mudah digunakan dan efektif untuk meningkatkan produktivitas pertanian, terutama dalam budidaya tanaman bunga.</li>
            <li class="mb-2">Memberikan solusi inovatif untuk mengatasi permasalahan hama tanaman melalui teknologi penyiraman pestisida otomatis yang aman dan efisien.</li>
            <li class="mb-2">Mendukung petani lokal dalam meningkatkan kualitas dan kuantitas hasil pertanian mereka dengan pendekatan berbasis teknologi.</li>
            <li class="mb-2">Melakukan penelitian dan pengembangan secara berkelanjutan untuk menciptakan alat pertanian modern yang ramah lingkungan.</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Batas -->
    <hr class="my-12 border-t-2 border-pink-600 mx-auto w-1/2">
    
    <!-- Memilih Section -->
    <div class="container mx-auto py-16 px-4 mb-12">
  <div class="text-center mb-12">
    <h2 class="text-3xl font-bold text-gray-800">Mengapa Memilih Kami?</h2>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-8">
    <!-- Inovasi Terdepan -->
    <div class="flex flex-col items-center text-center p-4 bg-white shadow-card rounded-lg transform hover:scale-105 transition duration-300">
      <img src="@/assets/InovasiTerdepan.png" alt="Inovasi Terdepan" class="feature-image mb-4">
      <h3 class="text-xl font-bold text-pink-600">Inovasi Terdepan</h3>
      <p class="text-gray-600">Produk kami menggunakan teknologi terbaru yang dirancang khusus untuk memenuhi kebutuhan petani bunga.</p>
    </div>

    <!-- Efisiensi dan Keefektifan -->
    <div class="flex flex-col items-center text-center p-4 bg-white shadow-card rounded-lg transform hover:scale-105 transition duration-300">
      <img src="@/assets/EfisiensiTinggi.png" alt="Efisiensi dan Keefektifan" class="feature-image mb-4">
      <h3 class="text-xl font-bold text-pink-600">Efisiensi dan Keefektifan</h3>
      <p class="text-gray-600">Dengan Aqua Flow, proses penyiraman menjadi lebih presisi, hemat sumber daya, dan mengurangi risiko kerusakan tanaman.</p>
    </div>

    <!-- Berbasis Bukti -->
    <div class="flex flex-col items-center text-center p-4 bg-white shadow-card rounded-lg transform hover:scale-105 transition duration-300">
      <img src="@/assets/BerbasisBukti.png" alt="Berbasis Bukti" class="feature-image mb-4">
      <h3 class="text-xl font-bold text-pink-600">Berbasis Bukti</h3>
      <p class="text-gray-600">Hasil pengujian di Innovillage 2023 membuktikan efektivitas produk kami dalam meningkatkan hasil pertanian.</p>
    </div>

    <!-- Komitmen terhadap Keberlanjutan -->
    <div class="flex flex-col items-center text-center p-4 bg-white shadow-card rounded-lg transform hover:scale-105 transition duration-300">
      <img src="@/assets/Komitmen.png" alt="Komitmen terhadap Keberlanjutan" class="feature-image mb-4">
      <h3 class="text-xl font-bold text-pink-600">Komitmen terhadap Keberlanjutan</h3>
      <p class="text-gray-600">Kami tidak hanya berfokus pada hasil panen tetapi juga pada keberlanjutan lingkungan dan kesejahteraan petani lokal.</p>
    </div>

    <!-- Dukungan Lokal -->
    <div class="flex flex-col items-center text-center p-4 bg-white shadow-card rounded-lg transform hover:scale-105 transition duration-300">
      <img src="@/assets/Dukungan.png" alt="Dukungan Lokal" class="feature-image mb-4">
      <h3 class="text-xl font-bold text-pink-600">Dukungan Lokal</h3>
      <p class="text-gray-600">Sebagai perusahaan yang lahir dari Desa Cihideung, kami memahami kebutuhan spesifik petani lokal dan berkomitmen untuk memberikan dampak yang nyata bagi komunitas kami.</p>
    </div>
  </div>
</div>


      <!-- Batas -->
      <hr class="my-12 border-t-2 border-pink-600 mx-auto w-1/2">

      <!-- Meet Our Team -->
      <div class="container mx-auto py-2 px-4 mb-12">
      <div class="mt-16 mb-12 text-center">
        <h2 class="text-3xl font-bold text-gray-800 mb-12">Meet Our Team</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8" data-aos="fade-up">
          <div
            v-for="(member, index) in team"
            :key="member.name"
            class="bg-white shadow-lg rounded-lg overflow-hidden transform hover:scale-105 transition duration-300"
            :data-aos="'fade-up'"
            :data-aos-delay="index * 100 + 100"
          >
            <div class="w-40 h-40 mx-auto overflow-hidden rounded-full bg-gray-200 mb-4">
              <img :src="member.image" :alt="member.name" class="w-full h-full object-cover">
            </div>
            <div class="p-4 bg-pink-50">
              <h3 class="text-xl font-semibold text-gray-800">{{ member.name }}</h3>
              <p class="text-gray-600">{{ member.role }}</p>
            </div>
          </div>
        </div>
      </div>
      </div>

  </div>
</template>
<script>
  export default {
    name: "ContactUs",
    data() {
      return {
        name: "",
        email: "",
        message: ""
      };
    },
    data() {
    return {
      team: [
        { name: 'Salsa Arifah Zakkiyah', role: 'CEO', image: 'https://via.placeholder.com/200x200' },
        { name: 'Irfan Zharauri Nanda S.', role: 'CTO', image: 'https://via.placeholder.com/200x200' },
        { name: 'Abdurahman Dzakky', role: 'CDO', image: 'https://via.placeholder.com/200x200' },
        { name: 'Hafiz Yazid Muhammad', role: 'CIOT', image: 'https://via.placeholder.com/200x200' },
        // { name: 'Masna Abdul Baqi', role: 'Bussiness Analyst', image: 'https://via.placeholder.com/200x200' },
        // { name: 'Farhan Fath Fajar', role: 'IoT Developer', image: 'https://via.placeholder.com/200x200' },
        // { name: 'Ghaida Tsuraya Ali', role: 'Digital Marketing', image: 'https://via.placeholder.com/200x200' },
        // { name: 'Deazard Muhammad A.', role: 'Software Engineer Developer', image: 'https://via.placeholder.com/200x200' },
      ],
    };
  },
};

  </script>

<style scoped>
.text-gradient {
  background: linear-gradient(to right, #ff7e5f, #fc819e);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

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
}

.btn-register-now:hover {
  transform: scale(1.05);
  box-shadow: 0 6px 20px rgba(252, 129, 158, 0.6);
}

.custom-imageVisi {
  width: 100%; 
  max-width: 600px;
  height: auto;
  transition: transform 0.3s ease-in-out; /* Efek animasi hover */

}
.custom-imageMisi {
  width: 100%; 
  max-width: 600px;
  height: auto;
  transition: transform 0.3s ease-in-out; /* Efek animasi hover */

}
.custom-imageVisi:hover {
  transform: scale(1.1); /* Perbesar gambar saat hover */
}
.custom-imageMisi:hover {
  transform: scale(1.1); /* Perbesar gambar saat hover */
}

.feature-image {
  width: 200px; /* Ukuran gambar diperbesar */
  height: auto;
  object-fit: contain; /* Gambar tetap proporsional */
  transition: transform 0.3s ease-in-out; /* Efek animasi hover */
}

.feature-image:hover {
  transform: scale(1.1); /* Perbesar gambar saat hover */
}

.text-center h3 {
  margin-top: 1rem;
  font-size: 1.25rem;
}

.text-center p {
  font-size: 1rem;
  margin-top: 0.5rem;
  line-height: 1.6;
}

.min-h-screen {
  min-height: 80vh; /* Hero Section memenuhi layar penuh */
}

.mb-16 {
  margin-bottom: 4rem; /* Menambahkan jarak antara Hero dan About Section */
}
.shadow-card {
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
}

.shadow-card:hover {
  transform: scale(1.03);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
}


</style>
