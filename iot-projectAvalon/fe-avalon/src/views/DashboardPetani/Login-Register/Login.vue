<template>
    <div class="flex min-h-screen items-center justify-center bg-neutral">
        <FormAuth :isRegister="false" />
    </div>
</template>

<script setup>
import FormAuth from '@/components/Login-Register/FormAuth.vue'
</script>