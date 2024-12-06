<template>
  <div>
    <!-- Navbar hanya ditampilkan jika hideNavbar tidak ada di meta rute -->
    <Navbar v-if="!$route.meta.hideNavbar" />

    <!-- Tempatkan router-view untuk menampilkan halaman sesuai rute -->
    <router-view />
  </div>
</template>

<script>
import Navbar from "@/components/Navbar.vue"; // import komponen navbar

export default {
  components: {
    Navbar,
  },
};
</script>


<style>
/* Tambahkan styling global jika diperlukan */
body {
  margin: 0;
  font-family: 'Arial', sans-serif;
}
</style>
