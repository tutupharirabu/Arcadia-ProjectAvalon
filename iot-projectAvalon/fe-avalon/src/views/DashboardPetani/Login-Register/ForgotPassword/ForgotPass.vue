<template>
    <div class="hero bg-base-content min-h-screen">
        <div class="hero-content flex-col text-primary-content">
            <div class="text-center">
                <h1 class="text-5xl font-bold">Forgot Password</h1>
                <p class="py-6">
                    Masukkan email Anda untuk menerima kode OTP guna mengatur ulang password.
                    Jika Anda tidak menerima kode OTP, Anda dapat mengirim ulang.
                </p>
            </div>
            <div class="card bg-primary-content w-full max-w-sm shrink-0 shadow-2xl p-6">
                <form @submit.prevent="submitForgotPassword">
                    <div class="form-control">
                        <label for="email" class="label">
                            <span class="label-text">Email</span>
                        </label>
                        <input id="email" v-model="email" type="email" placeholder="Masukkan email Anda"
                            class="input input-bordered text-neutral bg-primary-content" required />
                    </div>
                    <button type="submit" class="btn btn-primary w-full mt-4 flex items-center justify-center"
                        :disabled="isLoading">
                        <span v-if="isLoading" class="loading loading-ellipsis"></span>
                        <span v-else>Kirim OTP</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal untuk menampilkan pesan -->
    <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white rounded-lg p-6 w-80 shadow-lg">
            <h3 class="text-lg font-bold text-base-content">{{ modalTitle }}</h3>
            <p class="mt-4 text-base-content">{{ modalMessage }}</p>
            <button @click="closeModal"
                class="btn mt-6 w-full py-2 px-4 btn-primary font-medium rounded-md transition duration-300">
                OK
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import customFetch from '@/utils/customFetch';

// State
const email = ref('');
const router = useRouter();

// Modal State
const showModal = ref(false);
const modalTitle = ref('');
const modalMessage = ref('');
const isLoading = ref(false); // State untuk loader
let redirectAfterModal = false; // Flag redirect
let timeoutId = null; // ID untuk timeout

// Fungsi untuk menampilkan modal
const openModal = (title, message, redirect = false) => {
    modalTitle.value = title;
    modalMessage.value = message;
    showModal.value = true;
    redirectAfterModal = redirect;

    // Timeout untuk redirect otomatis jika pengguna tidak klik OK
    if (redirectAfterModal) {
        timeoutId = setTimeout(() => {
            router.push({ name: 'OTPForgotPassword' });
            closeModal();
        }, 5000); // Timeout 5 detik
    }
};

// Fungsi untuk menutup modal dan melakukan navigasi jika diperlukan
const closeModal = () => {
    showModal.value = false;
    if (timeoutId) clearTimeout(timeoutId); // Hentikan timeout jika tombol OK diklik
    if (redirectAfterModal) {
        router.push({ name: 'OTPForgotPassword' });
    }
};

const submitForgotPassword = async () => {
    isLoading.value = true; // Aktifkan loader
    try {
        const response = await customFetch.post('/auth/forgot-password/send-email', { email: email.value });

        // Simpan email pengguna di localStorage
        localStorage.setItem('userEmail', email.value);
        localStorage.setItem('accessForgotPassword', 'true');

        // Tampilkan modal sukses dengan timeout otomatis
        openModal('Berhasil', response.data.message, true);
    } catch (error) {
        console.error('Gagal mengirim OTP:', error);

        // Tampilkan modal error tanpa redirect
        openModal('Kesalahan', error.response?.data?.message || 'Terjadi kesalahan saat mengirim OTP.', false);
    } finally {
        isLoading.value = false; // Matikan loader
    }
};
</script>