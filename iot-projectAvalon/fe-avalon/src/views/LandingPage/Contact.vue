<template>
  
    <div class="container mx-auto py-16 px-4">
      <div class="absolute inset-0 z-0">
      <img src="@/assets/Background.png" alt="Hero Background" class="w-full h-full object-cover opacity-70 hidden md:block">
    </div>
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold mb-4 text-gray-800">Kontak Kami</h1>
        <p class="text-lg text-gray-600">
          Jika ada pertanyaan, silakan hubungi kami melalui email:
          <a href="mailto:arcadiafloratech@gmail.com" class="text-pink-500 hover:underline">
            arcadiafloratech@gmail.com
          </a>
        </p>
      </div>
  
      <div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow-md">
        <form>
          <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nama</label>
            <input type="text" id="name" v-model="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Masukkan nama Anda" required>
          </div>
  
          <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input type="email" id="email" v-model="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Masukkan email Anda" required>
          </div>
  
          <div class="mb-4">
            <label for="message" class="block text-gray-700 text-sm font-bold mb-2">Pesan</label>
            <textarea id="message" v-model="message" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Tulis pesan Anda" required></textarea>
          </div>
  
          <button type="submit" class="w-full bg-pink-500 hover:bg-pink-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Kirim</button>
        </form>
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
};

  </script>
  
  <style scoped>
  .container {
    max-width: 768px;
  }
  
  button {
    transition: background-color 0.3s ease;
  }
  
  button:hover {
    background-color: #e64a9e;
  }
  </style>
  