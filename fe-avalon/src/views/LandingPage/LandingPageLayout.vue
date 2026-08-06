<template>
  <!-- Header -->
  <HeaderLanding />

  <!-- Main Content -->
  <main class="mt-24">
    <RouterView />
  </main>

  <!-- Footer -->
  <Footer v-if="!route.meta.hideFooter" />
</template>

<script setup>
import { RouterView } from "vue-router";
import HeaderLanding from "@/components/Header/HeaderLanding.vue";
import Footer from "@/components/Footer/Footer.vue";
import { useRoute } from "vue-router";

const route = useRoute(); // Akses metadata rute
</script>
