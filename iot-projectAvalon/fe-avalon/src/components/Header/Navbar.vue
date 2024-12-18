<template>
    <nav class="flex justify-around p-4 bg-gray-100 shadow-md">
      <ul class="flex space-x-6">
        <li v-for="(link, index) in listNav" :key="index">
          <router-link
            :to="link.url"
            class="text-gray-700 hover:text-pink-500 font-semibold"
          >
            {{ link.name }}
          </router-link>
        </li>
      </ul>
    </nav>
  </template>
  
  <script>
  import listNav from "@/listNav";
  
  export default {
    data() {
      return {
        listNav,
      };
    },
  };
  </script>
  
  <style scoped>
  /* Tambahkan style jika diperlukan */
  </style>
  