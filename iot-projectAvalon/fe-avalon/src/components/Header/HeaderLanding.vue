<template>
  <div class="navbar bg-primary fixed top-0 z-30">
    <!-- Navbar Start -->
    <div class="navbar-start">
      <div class="dropdown">
        <!-- Tombol Menu Mobile -->
        <label tabindex="0" class="btn btn-ghost lg:hidden">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            class="inline-block w-6 h-6 stroke-current">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </label>

        <!-- Dropdown Menu Mobile -->
        <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] w-52 bg-base-100 rounded-box shadow">
          <li v-for="item in filterNavItems" :key="item.name">
            <RouterLink :to="item.url"> {{ item.name }} </RouterLink>
          </li>
        </ul>
      </div>

      <!-- Brand Title -->
      <a class="btn btn-ghost text-xl text-primary-content">Arcadia Flora Tech</a>
    </div>

    <!-- Navbar Center (Desktop Menu) -->
    <div class="navbar-center hidden lg:flex">
      <ul class="menu menu-horizontal px-1 text-primary-content">
        <li v-for="item in filterNavItems" :key="item.name">
          <RouterLink :to="item.url"> {{ item.name }} </RouterLink>
        </li>
      </ul>
    </div>

    <!-- Navbar End (Login/Logout) -->
    <div class="navbar-end mr-4">
      <button v-if="currentUser" @click="handleLogout" class="btn btn-error">Logout</button>
      <RouterLink v-else to="/monitoring-arcadia/login" class="btn bg-primary-content text-primary">
        Login
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from "@/stores/Auth";
import { computed } from "vue";
import listNav from "@/utils/listNav";

const authStore = useAuthStore();
const { currentUser, logoutUser } = authStore;

const filterNavItems = computed(() => {
  return listNav.filter((item) => {
    // Menyembunyikan Profile jika user belum login
    if (item.name === "Profile" && !currentUser) return false;
    return true;
  });
});

const handleLogout = () => {
  logoutUser();
};
</script>