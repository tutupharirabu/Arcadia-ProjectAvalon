import { useAuthStore } from '@/stores/Auth'
import { createRouter, createWebHistory } from 'vue-router'

import DashboardLayout from '@/views/DashboardPetani/PetaniLayout.vue'
import LandingPageLayout from '@/views/LandingPage/LandingPageLayout.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // Landing Page Routes
    {
      path: '/',
      component: LandingPageLayout,
      children: [
        {
          path: 'beranda',
          name: 'Home Landing Page',
          component: () => import('@/views/LandingPage/HomeView.vue'),
        },
      ],
    },

    // Dashboard Petani Routes
    {
      path: '/monitoring-arcadia',
      component: DashboardLayout,
      meta: { isPetani: true },
      children: [
        {
          path: 'dashboard',
          name: 'Home Dashboard Petani',
          component: () => import('../views/DashboardPetani/DashboardPetani.vue'),
        },
      ],
    },

    // Authentication Routes
    {
      path: '/monitoring-arcadia/login',
      name: 'Login',
      component: () => import('@/views/DashboardPetani/Login-Register/Login.vue'),
    },
    {
      path: '/monitoring-arcadia/register',
      name: 'Register',
      component: () => import('@/views/DashboardPetani/Login-Register/Register.vue'),
    },

    // Email Verification
    {
      path: '/verifikasiEmail',
      name: 'verifikasiEmail',
      component: () => import('@/views/DashboardPetani/Login-Register/VerifEmail.vue'),
      meta: { isAuth: true },
    },

    // Forgot Password Routes
    {
      path: '/forgot-password',
      name: 'forgotPassword',
      component: () => import('@/views/DashboardPetani/Login-Register/ForgotPassword/ForgotPass.vue'),
      children: [
        {
          path: 'verifikasiOTP',
          name: 'OTP Forgot Password',
          component: () => import('@/views/DashboardPetani/Login-Register/ForgotPassword/VerifOTPForgotPass.vue'),
        },
        {
          path: 'resetPassword',
          name: 'Reset Password',
          component: () => import('@/views/DashboardPetani/Login-Register/ForgotPassword/SubmitPass.vue'),
        },
      ],
    },
  ]
})

router.beforeEach((to, from) => {
  const authStore = useAuthStore()
  if (to.meta.isAuth && !authStore.tokenUser) {
    alert('Kamu tidak punya akses ke halaman ini!')
    return '//monitoring-arcadia/login'
  }

  if (to.meta.isPetani && !authStore.tokenUser && authStore?.currentUser?.role !== 'petani') {
    alert('Kamu tidak punya akses ke halaman dashboard petani ini!')
    return '/beranda'
  }
})

export default router
