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
      redirect: '/beranda',
      children: [
        {
          path: 'beranda',
          name: 'HomeLandingPage',
          component: () => import('@/views/LandingPage/HomeView.vue'),
        },
        {
          path: 'aktivitas',
          redirect: '/aktivitas/innovillage-2023',  // Mengarahkan ke halaman Innovillage2023
          children: [
            {
              path: 'innovillage-2023',
              name: 'Innovillage2023',
              component: () => import('@/views/LandingPage/Activity/Innovillage2023.vue'),
            },
            {
              path: 'inkubasi-btp',
              name: 'InkubasiBTP',
              component: () => import('@/views/LandingPage/Activity/InkubasiBTP.vue'),
            },
          ],
          meta: { hideFooter: true },
        },
        
        
        {
          path: 'kontak-kami',
          name: 'ContactUs',
          component: () => import('@/views/LandingPage/Contact.vue'),
        },
        // router/index.js
        {
          path: 'arcadia-partner',
          name: 'ArcadiaPartner',
          component: () => import('@/views/LandingPage/ArcadiaPartner.vue'),
        }
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
          path: 'device/monitoring/:id',
          name: 'DetailDeviceMonitoring',
          component: () => import('../views/Device/DetailDevice/DetailDeviceMonitoringPetani.vue'),
        },
        {
          path: 'device/watering/:id',
          name: 'DetailDeviceWatering',
          component: () => import('../views/Device/DetailDevice/DetailDeviceWateringPetani.vue'),
        },
        {
          path: "device/update/:id",
          name: "updateDevice",
          component: () => import('../views/Device/DetailDevice/UpdateDetailDevicePetani.vue'),
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
      meta: { forgotPass: true },
    },
    {
      path: '/forgot-password/resetPassword',
      name: 'ResetPassword',
      component: () => import('@/views/DashboardPetani/Login-Register/ForgotPassword/SubmitPass.vue'),
      meta: { forgotPass: true },
    },
  ]
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  // Cek halaman yang membutuhkan autentikasi
  if (to.meta.isAuth && !authStore.tokenUser) {
    alert('Kamu tidak punya akses ke halaman ini!');
    next('/monitoring-arcadia/login'); // Gunakan next() dengan path tujuan
    return; // Pastikan keluar dari fungsi
  }

  // Cek halaman dashboard petani
  if (to.meta.isPetani && (!authStore.tokenUser || authStore?.currentUser?.role !== 'Petani')) {
    alert('Kamu tidak punya akses ke halaman dashboard petani ini!');
    next('/beranda');
    return;
  }

  // Cek halaman forgot-password dengan flag di localStorage
  if (to.meta.forgotPass) {
    const accessedForgotPassword = localStorage.getItem('accessForgotPassword');

    if (!accessedForgotPassword) {
      // Redirect ke '/forgot-password' jika flag tidak ditemukan
      next({ name: 'forgotPassword' });
      return;
    }
  }

  next(); // Lanjutkan ke halaman tujuan jika semua kondisi lolos
});

export default router
