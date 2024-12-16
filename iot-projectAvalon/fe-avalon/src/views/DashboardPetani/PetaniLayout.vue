<template>
  <div class="flex h-screen bg-base-100">
    <!-- Drawer -->
    <Drawer @toggle="handleDrawerToggle" />

    <!-- Main Content -->
    <div class="flex-grow flex flex-col transition-all duration-300">
      <!-- Header -->
      <Header />

      <!-- Main Content -->
      <main class="flex-grow overflow-auto p-4">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { RouterView } from "vue-router";
import Header from "@/components/Header/HeaderPetani.vue";
import Drawer from "@/components/Drawer/DrawerPetani.vue";

const isDrawerOpen = ref(true);

const handleDrawerToggle = (state) => {
  isDrawerOpen.value = state;
};
</script>