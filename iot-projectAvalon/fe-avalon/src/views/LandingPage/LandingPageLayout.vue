<template>
    <Header />
    <main class="mt-18">
        <RouterView />
    </main>
    <Footer />
</template>

<script setup>
import { RouterView } from 'vue-router'
import Header from '@/components/Header/HeaderLanding.vue'
import Footer from '@/components/Footer/Footer.vue'
</script>