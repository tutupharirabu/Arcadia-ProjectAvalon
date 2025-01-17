<template>
  <div class="navbar bg-primary fixed top-0 z-10">
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
          <li v-for="item in filterNavItems" :key="item.name" class="relative">
            <RouterLink :to="item.url" class="text-black"> {{ item.name }} </RouterLink>
            <!-- Submenu jika ada children -->
            <ul v-if="item.children" class="absolute left-full top-0 mt-1 bg-white rounded-md shadow-lg hidden group-hover:block">
              <li v-for="child in item.children" :key="child.name" class="px-4 py-2 hover:bg-gray-200">
                <RouterLink :to="child.url" class="text-black">{{ child.name }}</RouterLink>
              </li>
            </ul>
          </li>
        </ul>
      </div>

      <!-- Brand Title -->
      <a class="btn btn-ghost text-xl text-primary-content flex items-center space-x-2">
        <img src="@/assets/LogoPutihArcadia.png" alt="Arcadia Logo" class="h-8 w-8" />
        <span>Arcadia Flora Tech</span>
      </a>
    </div>

    <!-- Navbar Center (Desktop Menu) -->
    <div class="navbar-center hidden lg:flex">
      <ul class="menu menu-horizontal px-1 text-primary-content">
        <li v-for="item in filterNavItems" :key="item.name" class="relative group">
          <RouterLink :to="item.url" class="text-navbar"> {{ item.name }} </RouterLink>
          <!-- Submenu jika ada children -->
          <ul v-if="item.children" class="absolute left-0 top-full mt-1 bg-white rounded-md shadow-lg hidden group-hover:block">
            <li v-for="child in item.children" :key="child.name" class="px-4 py-2 hover:bg-gray-200">
              <RouterLink :to="child.url" class="text-black">{{ child.name }}</RouterLink>
            </li>
          </ul>
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

<style>
.navbar {
  z-index: 50;
  /* Pastikan navbar berada di atas elemen lain */
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  /* Bayangan untuk estetika */
}

.main-content {
  padding-top: 4rem;
  /* Tambahkan jarak agar konten tidak tertutup navbar */
}

.text-navbar {
  font-size: 1.0rem;
  font-weight: 300;
  color: #ffffff;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.6);
}

.text-navbar:hover {
  color: #FFF9E2;
  text-decoration: underline;
}

/* Submenu styling for desktop */
.menu li {
  position: relative;
}

.menu li > ul {
  display: none;
  position: absolute;
  top: 100%;
  left: 0;
  z-index: 1000;
  background-color: #ffffff;
  border-radius: 0.375rem; /* Rounded corners */
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); /* Smooth shadow */
  overflow: hidden; /* Ensure rounded corners apply */
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease-in-out; /* Smooth fade-in and slide */
  transform: translateY(-10px); /* Initial position for slide effect */
}

.menu li:hover > ul {
  display: block;
  opacity: 1;
  visibility: visible;
  transform: translateY(0); /* Slide to the visible position */
}

.menu li > ul > li {
  padding: 10px 20px; /* Better spacing */
  color: #333333;
  font-size: 0.95rem;
  transition: background-color 0.2s ease-in-out; /* Smooth hover effect */
}

.menu li > ul > li:hover {
  background-color: #f0f0f0; /* Highlight color on hover */
}

/* Submenu styling for mobile */
@media (max-width: 1024px) {
  .dropdown-content .menu-sm li > ul {
    display: none;
    position: static; /* Static positioning for nested items */
    background-color: #ffffff;
    box-shadow: none; /* No shadow for mobile */
  }

  .dropdown-content .menu-sm li:hover > ul {
    display: block;
  }

  .dropdown-content .menu-sm li > ul > li {
    padding: 12px 20px;
    font-size: 1rem;
  }

  .dropdown-content .menu-sm li > ul > li:hover {
    background-color: #e9e9e9;
  }
}
</style>