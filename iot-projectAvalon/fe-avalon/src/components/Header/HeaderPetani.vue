<template>
    <div class="navbar bg-base-200 top-0 z-50 border-b border-neutral px-4 w-full flex justify-between items-center">
        <!-- Navbar Start -->
        <div class="navbar-start">
            <p class="font-semibold text-xl text-primary truncate">
                Halo {{ currentUser?.name || "Pengguna" }}, Selamat Datang di Dashboard Petani!
            </p>
        </div>

        <!-- Navbar End -->
        <div class="navbar-end">
            <button class="btn btn-primary text-primary-content px-4 py-2 rounded-lg" @click="showInputModal = true">
                Tambah Alat +
            </button>
        </div>
    </div>

    <!-- Modal Input Device ID -->
    <div v-if="showInputModal" class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm text-center">
            <h3 class="text-lg font-semibold mb-4">Masukkan Device ID</h3>

            <!-- Loader -->
            <div v-if="isLoading" class="flex justify-center items-center">
                <span class="loading loading-spinner loading-lg text-primary"></span>
            </div>

            <!-- Input dan Tombol -->
            <div v-else>
                <input v-model="deviceIdInput" type="text" placeholder="Device ID"
                    class="input input-bordered w-full mb-4" />
                <div class="flex justify-end space-x-2">
                    <button @click="showInputModal = false" class="btn btn-neutral">Batal</button>
                    <button @click="linkDevice" class="btn btn-primary">Kirim</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Result -->
    <div v-if="showModal" class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm text-center">
            <h3 class="text-lg font-semibold mb-4">{{ modalTitle }}</h3>
            <p>{{ modalMessage }}</p>
            <button @click="showModal = false" class="btn btn-primary mt-4">Tutup</button>
        </div>
    </div>
</template>

<script setup>
import { useAuthStore } from "@/stores/Auth";
import { computed, ref } from "vue";
import customFetch from "@/utils/customFetch";

const authStore = useAuthStore();

// State untuk modal input Device ID
const showInputModal = ref(false);
const deviceIdInput = ref("");

// State untuk loader
const isLoading = ref(false);

// State modal hasil
const showModal = ref(false);
const modalTitle = ref("");
const modalMessage = ref("");

// Menggunakan computed untuk akses reaktif ke currentUser
const currentUser = computed(() => authStore.currentUser);

// Fungsi untuk menautkan perangkat
const linkDevice = async () => {
    isLoading.value = true; // Aktifkan loader
    try {
        const userId = authStore.currentUser.id;

        if (!deviceIdInput.value) {
            modalTitle.value = "Peringatan";
            modalMessage.value = "Device ID diperlukan!";
            showModal.value = true;
            return;
        }

        const response = await customFetch.post(
            `/device/link/${deviceIdInput.value}`,
            { user_id: userId },
            {
                headers: {
                    Authorization: `Bearer ${authStore.tokenUser}`,
                },
            }
        );

        if (response.data.status === "success") {
            modalTitle.value = "Berhasil";
            modalMessage.value = "Perangkat berhasil ditautkan!";
        } else {
            modalTitle.value = "Gagal";
            modalMessage.value = response.data.message || "Gagal menautkan perangkat.";
        }
    } catch (error) {
        console.error("Error linking device:", error);
        modalTitle.value = "Error";
        modalMessage.value = "Gagal menautkan perangkat. Silakan coba lagi.";
    } finally {
        isLoading.value = false; // Matikan loader
        showModal.value = true;
        showInputModal.value = false; // Tutup modal input
        deviceIdInput.value = ""; // Reset input
    }
};
</script>