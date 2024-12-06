<template>
    <section id="team" class="py-16">
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
      <div class="container mx-auto text-center" data-aos="fade-up" data-aos-delay="300">
        <h2 class="text-3xl font-bold mb-8 text-pink-600">Meet Our Team</h2>
        <p class="text-gray-600 mb-12">
          Kami adalah tim yang berdedikasi untuk menghadirkan inovasi di bidang pertanian modern.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
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
    </section>
  </template>
  
  <script>
  import AOS from 'aos';
  import 'aos/dist/aos.css';
  import deazardImage from '@/assets/Deazard Muhammad Arrayyan_FotoNoBG.png';
  import salsa from '@/assets/salsa.png';
  import irfan from '@/assets/irfan.png';
  
  export default {
    data() {
      return {
        team: [
          { name: 'Salsa Arifah Zakkiyah', role: 'CEO', image: salsa },
          { name: 'Irfan Zharauri Nanda Sudiyanto', role: 'CTO', image: irfan },
          { name: 'Abdurahman Dzakky', role: 'CDO', image: 'https://via.placeholder.com/200x200' },
          { name: 'Hafiz Yazid Muhammad', role: 'CIOT', image: 'https://via.placeholder.com/200x200' },
          { name: 'Masna Abdul Baqi', role: 'Bussiness Analyst', image: 'https://via.placeholder.com/200x200' },
          { name: 'Farhan Fath Fajar', role: 'IoT Developer', image: 'https://via.placeholder.com/200x200' },
          { name: 'Ghaida Tsuraya Ali', role: 'Digital Marketing', image: 'https://via.placeholder.com/200x200' },
          { name: 'Deazard Muhammad Arrayyan', role: 'Software Engineer Developer', image: deazardImage },
        ],
      };
    },
    mounted() {
      // Inisialisasi AOS setelah komponen dimuat
      AOS.init();
    },
  };
  </script>
  
  <style scoped>
  /* Animasi untuk kemunculan anggota tim */
  section#team div {
    animation: fadeInUp 1s ease-out;
  }
  
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(50px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

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
  bottom: 1;
  left: 0;
  transform-origin: center;
  animation: rotate-logo 10s linear infinite;
  width: 120px; /* Atur ukuran logo */
  margin-left: 350px;
}

/* Penempatan dan animasi logo kanan */
.logo-right {
  position: absolute;
  bottom: 1;
  right: 0;
  transform-origin: center;
  animation: rotate-logo 10s linear infinite;
  width: 120px; /* Atur ukuran logo */
  margin-right: 350px;
}
  </style>
  