<template>
    <div class="flex h-screen bg-base-100">
      <!-- Drawer -->
      <Drawer />
  
      <!-- Main Content -->
      <div class="flex-grow flex flex-col">
        <Header />
        <main class="flex-grow overflow-auto p-4">
          <RouterView />
        </main>
      </div>
    </div>
  </template>
  
  <script setup>
  import { RouterView } from "vue-router";
  import Header from "@/components/Header/HeaderPetani.vue";
  import Drawer from "@/components/Drawer/DrawerPetani.vue";
  </script>