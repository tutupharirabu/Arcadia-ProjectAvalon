<template>
    <!-- Header -->
    <HeaderLanding />
  
    <!-- Main Content -->
    <main class="mt-20">
      <RouterView />
    </main>
  
    <!-- Footer -->
    <Footer />
  </template>
  
  <script setup>
  import { RouterView } from "vue-router";
  import HeaderLanding from "@/components/Header/HeaderLanding.vue";
  import Footer from "@/components/Footer/Footer.vue";
  </script>
  