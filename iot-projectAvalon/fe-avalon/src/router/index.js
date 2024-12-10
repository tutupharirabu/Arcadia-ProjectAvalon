import { useAuthStore } from '@/stores/Auth'
import { createRouter, createWebHistory } from 'vue-router'

import DashboardLayout from '@/views/DashboardPetani/PetaniLayout.vue'
import LandingPageLayout from '@/views/LandingPage/LandingPageLayout.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      component: LandingPageLayout,
      children: [
        {
          path: '/beranda',
          name: 'Home',
          component: () => import('../views/LandingPage/HomeView.vue'),
        },
      ],
    },
    {
      path: '/monitoring-arcadia',
      component: DashboardLayout,
      meta: { isPetani: true },
      children: [
        {
          path: '/home-dashboard-petani',
          name: 'Home Dashboard Petani',
          component: () => import('../views/DashboardPetani/DashboardPetani.vue'),
        },
      ],
    },
    {
      path: '/login',
      name: 'Login',
      component: () => import('../views/DashboardPetani/Login-Register/Login.vue'),
    },
    {
      path: '/register',
      name: 'Register',
      component: () => import('../views/DashboardPetani/Login-Register/Register.vue'),
    },
    {
      path: '/verifikasiEmail',
      name: 'verifikasiEmail',
      component: () => import('../views/DashboardPetani/Login-Register/VerifEmail.vue'),
      meta: { isAuth: true },
    },
    {
      path: '/forgot-password',
      name: 'forgotPassword',
      component: () => import('../views/DashboardPetani/Login-Register/ForgotPassword/ForgotPass.vue'),
    },
    {
      path: '/forgot-password/verifikasiOTP',
      name: 'OTP - forgotPassword',
      component: () => import('../views/DashboardPetani/Login-Register/ForgotPassword/VerifOTPForgotPass.vue'),
    },
    {
      path: '/forgot-password/resetPassword',
      name: 'Submit - forgotPassword',
      component: () => import('../views/DashboardPetani/Login-Register/ForgotPassword/SubmitPass.vue'),
    }
  ]
})

router.beforeEach((to, from) => {
  const authStore = useAuthStore()
  if (to.meta.isAuth && !authStore.tokenUser) {
    alert('Kamu tidak punya akses ke halaman ini!')
    return '/login'
  }

  if (to.meta.isPetani && !authStore.tokenUser && authStore?.currentUser?.role !== 'petani') {
    alert('Kamu tidak punya akses ke halaman dashboard petani ini!')
    return '/beranda'
  }
})

export default router
