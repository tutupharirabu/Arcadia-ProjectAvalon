<template>
    <div class="container min-h-screen mx-auto py-16 px-4">
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold mb-4 text-gray-800">Tentang Kami</h1>
        <p class="text-lg text-gray-600">
          Kami adalah perusahaan teknologi pertanian yang berfokus pada inovasi cerdas
          untuk meningkatkan hasil panen.
        </p>
      </div>
  
      <div class="relative bg-product-section py-6">
        <div class="container mx-auto px-4 text-center">
          <div
            class="mt-8 text-gray-600"
            data-aos="fade-in"
            data-aos-duration="1000"
          >
            <h2 class="about-title text-3xl font-bold text-pink-600 mb-10" data-aos="fade-right">
              Visi dan Misi
            </h2>
            <div class="flex flex-col md:flex-row justify-between gap-8 mt-12">
              <!-- Bagian Visi -->
              <div class="w-full md:w-1/2 text-center md:text-left">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4">Visi</h3>
                <hr class="my-4 border-t-2 border-gray-300 w-20 mx-auto md:mx-0">
                <p class="text-lg text-gray-600 leading-relaxed md:max-w-lg mx-auto">
                  Menjadi pelopor dalam pengembangan teknologi IoT inovatif yang mendukung keberlanjutan pertanian modern, khususnya di bidang budidaya tanaman hias, dengan fokus pada efisiensi, kualitas, dan ketahanan lingkungan.
                </p>
              </div>
  
              <!-- Bagian Misi -->
              <div class="w-full md:w-1/2 text-center md:text-left">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4">Misi</h3>
                <hr class="my-4 border-t-2 border-gray-300 w-20 mx-auto md:mx-0">
                <div class="text-lg text-gray-600 leading-relaxed md:max-w-lg mx-auto">
                  <ul class="list-disc pl-6">
                    <li class="mb-4">Mengembangkan perangkat IoT yang mudah digunakan dan efektif untuk meningkatkan produktivitas pertanian, terutama dalam budidaya tanaman bunga.</li>
                    <li class="mb-4">Memberikan solusi inovatif untuk mengatasi permasalahan hama tanaman melalui teknologi penyiraman pestisida otomatis yang aman dan efisien.</li>
                    <li class="mb-4">Mendukung petani lokal dalam meningkatkan kualitas dan kuantitas hasil pertanian mereka dengan pendekatan berbasis teknologi.</li>
                    <li class="mb-4">Melakukan penelitian dan pengembangan secara berkelanjutan untuk menciptakan alat pertanian modern yang ramah lingkungan.</li>
                    <li class="mb-4">Berkontribusi pada pembangunan ekonomi masyarakat desa melalui penerapan teknologi tepat guna dalam sektor pertanian.</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
  
        <div class="container mx-auto text-center" data-aos="fade-up" data-aos-delay="300">
            <h2 class="about-title text-3xl font-bold text-pink-600 mb-10 mt-10" data-aos="fade-right">
              Meet Our Team
            </h2>          <p class="text-gray-600 mb-12">
            Kami adalah tim yang berdedikasi untuk menghadirkan inovasi di bidang pertanian modern.
          </p>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            <div 
              v-for="(member, index) in team" 
              :key="member.name" 
              class="relative bg-white shadow-lg rounded-lg overflow-hidden transform hover:scale-105 transition duration-300"
              :data-aos="'fade-up'"
              :data-aos-delay="index * 100 + 100"
            >
              <!-- Bagian gambar -->
              <div class="w-40 h-40 mx-auto overflow-hidden rounded-full bg-gray-200 mb-4">
                <img :src="member.image" :alt="member.name" class="w-full h-full object-cover">
              </div>
              
              <!-- Bagian keterangan dengan warna latar belakang berbeda -->
              <div class="p-4 bg-pink-100 rounded-b-lg">
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
    data() {
      return {
        team: [
          { name: 'Salsa Arifah Zakkiyah', role: 'CEO', image: 'https://via.placeholder.com/200x200' },
          { name: 'Irfan Zharauri Nanda S.', role: 'CTO', image: 'https://via.placeholder.com/200x200' },
          { name: 'Abdurahman Dzakky', role: 'CDO', image: 'https://via.placeholder.com/200x200' },
          { name: 'Hafiz Yazid Muhammad', role: 'CIOT', image: 'https://via.placeholder.com/200x200' },
          { name: 'Masna Abdul Baqi', role: 'Bussiness Analyst', image: 'https://via.placeholder.com/200x200' },
          { name: 'Farhan Fath Fajar', role: 'IoT Developer', image: 'https://via.placeholder.com/200x200' },
          { name: 'Ghaida Tsuraya Ali', role: 'Digital Marketing', image: 'https://via.placeholder.com/200x200' },
          { name: 'Deazard Muhammad A.', role: 'Software Engineer Developer', image: 'https://via.placeholder.com/200x200' },
        ],
      };
    },
  };
  </script>
  
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
  
  /* Responsif - Penyesuaian untuk mobile */
  @media (max-width: 640px) {
    .btn-register-now {
      font-size: 1rem;
      padding: 0.5rem 1.5rem;
    }
  }
  
  /* Tambahan untuk garis heading */
  hr {
    transition: width 0.3s ease;
  }
  
  hr:hover {
    width: 30%;
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
  