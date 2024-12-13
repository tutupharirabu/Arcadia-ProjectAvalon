<template>
    <div class="navbar bg-base-200 fixed top-0 z-10 border-b border-neutral">
        <!-- Navbar -->
        <div class="navbar-start">
            <a class="btn btn-ghost text-xl text-primary">
                Halo {{ currentUser?.name || "Pengguna" }}, Selamat Datang di Dashboard Petani!
            </a>
        </div>
    </div>
</template>

<script setup>
import { useAuthStore } from "@/stores/Auth";
import { computed } from "vue";

const authStore = useAuthStore();

// Menggunakan computed untuk akses reaktif ke currentUser
const currentUser = computed(() => authStore.currentUser);
</script>