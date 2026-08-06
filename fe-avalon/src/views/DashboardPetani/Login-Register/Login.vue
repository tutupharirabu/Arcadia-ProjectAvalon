<template>
  <div class="flex min-h-screen items-center justify-center bg-neutral">
    <FormAuth :isRegister="false" />
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import FormAuth from '@/components/Login-Register/FormAuth.vue'
import { useAuthStore } from '@/stores/Auth'

const router = useRouter();
const authStore = useAuthStore();

onMounted(() => {
  // Jika pengguna sudah login, jangan hapus sesi aktif — langsung ke dashboard
  if (authStore.tokenUser) {
    router.replace('/monitoring-arcadia/dashboard');
    return;
  }

  // Hanya bersihkan artefak flow forgot-password (bukan token/user sesi aktif)
  localStorage.removeItem('accessForgotPassword');
  localStorage.removeItem('userEmail');
});
</script>
