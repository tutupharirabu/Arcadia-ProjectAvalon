import { createRouter, createWebHistory } from 'vue-router';
import Homepage from '@/views/HomePage.vue';
import Login from '@/views/Login.vue';
import Register from "@/views/Register.vue";
import Dashboard from '@/views/Dashboard.vue';

const routes = [
  {
    path: '/',
    name: 'home',
    component: Homepage,
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { hideNavbar: true } // Menyembunyikan Navbar di halaman login
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: { hideNavbar: true } // Menyembunyikan Navbar di halaman login
  },
  {
    path: "/dashboard",
    name: "Dashboard",
    component: Dashboard,
    meta: { hideNavbar: true }, // Menyembunyikan Navbar di halaman login
    component: () => import("@/views/Dashboard.vue"),
  },
  
  
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
