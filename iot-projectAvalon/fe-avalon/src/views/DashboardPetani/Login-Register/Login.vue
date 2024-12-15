<template>
    <div class="flex min-h-screen items-center justify-center bg-neutral">
        <FormAuth :isRegister="false" />
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import FormAuth from '@/components/Login-Register/FormAuth.vue'

onMounted(() => {
  localStorage.removeItem('accessForgotPassword');
  localStorage.removeItem('token'); 
  localStorage.removeItem('user'); 
});
</script>