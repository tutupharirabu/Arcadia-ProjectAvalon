<template>
    <nav class="w-full py-4 px-6 bg-white z-50">
      <div class="container mx-auto flex items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center">
          <img src="@/assets/frame 4.png" alt="Logo" class="h-10">
          <span class="ml-2 text-xl font-bold text-pink-800">Arcadia</span>
        </div>
  
        <!-- Navigation Links -->
        <ul class="hidden md:flex space-x-8 text-gray-600 font-medium">
          <li><a href="#home" class="hover:text-pink-600">Home</a></li>
          <li><a href="#about" class="hover:text-pink-600">About Us</a></li>
          <li><a href="#product" class="hover:text-pink-600">Products</a></li>
          <li><a href="#team" class="hover:text-pink-600">Teams</a></li>
          <li><a href="#contact" class="hover:text-pink-600">Contact</a></li>
        </ul>
  
        <!-- Button -->
        <button class="hidden md:block px-4 py-2 bg-pink-600 text-white rounded-full shadow hover:bg-pink-700 transition duration-300">
          Register
        </button>
      </div>
    </nav>
  </template>
  
  <style scoped>
  /* Tambahkan warna latar belakang */
  nav {
    background: white; 
    position: relative; 
    z-index: 50; 
  }
  
  body {
    margin-top: 4rem;
  }
  </style>
  