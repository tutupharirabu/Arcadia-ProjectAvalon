<template>
      <div class="absolute inset-0 z-0">
      <img src="@/assets/Background.png" alt="Hero Background" class="w-full h-full object-cover opacity-70 hidden md:block">
    </div>
    <div class="container mx-auto py-16 px-4">
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold mb-4 text-gray-800">Formulir Pendaftaran Arcadia Partner</h1>
        <p class="text-lg text-gray-600">Bergabunglah dengan kami untuk mendukung kemajuan pertanian modern di Indonesia.</p>
      </div>
  
      <form @submit.prevent="submitForm" class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow-md">
        <div class="mb-6">
          <label for="name" class="block text-gray-800 font-semibold mb-2">Nama Petani *</label>
          <input v-model="form.name" type="text" id="name" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-pink-200 focus:border-pink-300" placeholder="Nama" required>
        </div>
  
        <div class="mb-6">
          <label for="phone" class="block text-gray-800 font-semibold mb-2">No Telepon/Whatsapp *</label>
          <input v-model="form.phone" type="tel" id="phone" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-pink-200 focus:border-pink-300" placeholder="No telepon" required>
        </div>
  
        <div class="mb-6">
          <label for="age" class="block text-gray-800 font-semibold mb-2">Usia *</label>
          <input v-model="form.age" type="number" id="age" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-pink-200 focus:border-pink-300" placeholder="Usia" required>
        </div>
  
        <div class="mb-6">
          <label for="gender" class="block text-gray-800 font-semibold mb-2">Jenis Kelamin *</label>
          <select v-model="form.gender" id="gender" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-pink-200 focus:border-pink-300" required>
            <option>Laki-laki</option>
            <option>Perempuan</option>
          </select>
        </div>
  
        <div class="mb-6">
          <label for="location" class="block text-gray-800 font-semibold mb-2">Lokasi Lahan *</label>
          <input v-model="form.location" type="text" id="location" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-pink-200 focus:border-pink-300" placeholder="Kota Bandung" required>
        </div>
  
        <div class="mb-6">
          <label for="home_address" class="block text-gray-800 font-semibold mb-2">Alamat Rumah *</label>
          <textarea v-model="form.home_address" id="home_address" rows="3" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-pink-200 focus:border-pink-300" placeholder="Masukan Alamat Rumah" required></textarea>
        </div>
  
        <div class="mb-6">
          <label for="land_address" class="block text-gray-800 font-semibold mb-2">Alamat Lahan *</label>
          <textarea v-model="form.land_address" id="land_address" rows="3" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-pink-200 focus:border-pink-300" placeholder="Masukan Alamat Lahan" required></textarea>
        </div>
  
        <button type="submit" class="btn-register-now w-full py-3 mt-4 text-white font-bold rounded-md hover:shadow-lg focus:outline-none focus:ring focus:ring-pink-300 focus:ring-opacity-80 transition duration-300">
          Daftar Sekarang
        </button>
      </form>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        form: {
          name: '',
          phone: '',
          age: '',
          gender: 'Laki-laki',
          location: '',
          home_address: '',
          land_address: ''
        }
      };
    },
    methods: {
      submitForm() {
        // Implement form submission logic here (e.g., send data to backend)
        console.log('Form submitted:', this.form);
      }
    }
  };
  </script>
  
  <style scoped>
  /* Button */
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
  
  /* Gaya formulir input */
  input, textarea, select {
    border-radius: 0.375rem;
    border: 1px solid #d1d5db;
    padding: 0.75rem;
    width: 100%;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease-in-out;
  }
  
  input:focus, textarea:focus, select:focus {
    border-color: #f472b6;
    outline: none;
    box-shadow: 0 0 0 2px rgba(247, 118, 182, 0.2);
  }
  </style>
  