import { useAuthStore } from '@/stores/Auth'
import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'Login',
      component: () => import('../views/LoginRegister/Login.vue'),
    },
    {
      path: '/register',
      name: 'Register',
      component: () => import('../views/LoginRegister/Register.vue'),
    },
    {
      path: '/verifikasiEmail',
      name: 'verifikasiEmail',
      component: () => import('../views/LoginRegister/VerifEmail.vue'),
      meta: { isAuth: true },
    }
  ]
})

export default router
