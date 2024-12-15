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
          name: 'HomeLandingPage',
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
          name: 'HomeDashboardPetani',
          component: () => import('../views/DashboardPetani/DashboardPetani.vue'),
        },
        {
          path: 'device',
          name: 'CekDevicePetani',
          component: () => import('../views/Device/DevicePetani.vue'),
        },
        {
          path: 'device/:id',
          name: 'DetailDevice',
          component: () => import('../views/Device/DetailDevice/DetailDevicePetani.vue'),
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
    },
    {
      path: '/forgot-password/verifikasiOTP',
      name: 'OTPForgotPassword',
      component: () => import('@/views/DashboardPetani/Login-Register/ForgotPassword/VerifOTPForgotPass.vue'),
      meta: { requiresParent: true }, // Tambahkan meta
    },
    {
      path: '/forgot-password/resetPassword',
      name: 'ResetPassword',
      component: () => import('@/views/DashboardPetani/Login-Register/ForgotPassword/SubmitPass.vue'),
      meta: { requiresParent: true }, // Tambahkan meta
    },
  ]
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  if (to.meta.isAuth && !authStore.tokenUser) {
    alert('Kamu tidak punya akses ke halaman ini!')
    return '/monitoring-arcadia/login'
  }

  if (to.meta.isPetani && !authStore.tokenUser && authStore?.currentUser?.role !== 'petani') {
    alert('Kamu tidak punya akses ke halaman dashboard petani ini!')
    return '/beranda'
  }

  if (to.meta.requiresParent) {
    const accessedForgotPassword = localStorage.getItem('accessForgotPassword');

    if (!accessedForgotPassword) {
      // Redirect ke '/forgot-password' jika flag tidak ditemukan
      next({ name: 'forgotPassword' });
    } else {
      next();
    }
  } else {
    next();
  }
})

export default router
