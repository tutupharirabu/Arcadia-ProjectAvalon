<template>
    <div class="hero bg-base-content min-h-screen">
        <div class="hero-content flex-col text-primary-content">
            <div class="text-center">
                <h1 class="text-5xl font-bold">Reset Password</h1>
                <p class="py-6">
                    Masukkan password baru Anda untuk mengatur ulang password akun Anda.
                </p>
            </div>
            <div class="card bg-primary-content w-full max-w-sm shrink-0 shadow-2xl p-6">
                <form @submit.prevent="submitResetPassword">
                    <div class="form-control">
                        <label for="password" class="label">
                            <span class="label-text">Password Baru</span>
                        </label>
                        <input id="password" v-model="password" type="password" placeholder="Masukkan password baru"
                            class="input input-bordered text-neutral bg-primary-content" required />
                    </div>
                    <div class="form-control mt-4">
                        <label for="password_confirmation" class="label">
                            <span class="label-text">Konfirmasi Password</span>
                        </label>
                        <input id="password_confirmation" v-model="passwordConfirmation" type="password"
                            placeholder="Konfirmasi password baru"
                            class="input input-bordered text-neutral bg-primary-content" required />
                    </div>
                    <button type="submit" class="btn btn-primary w-full mt-4 flex items-center justify-center"
                        :disabled="isLoading">
                        <span v-if="isLoading" class="loading loading-ellipsis"></span>
                        <span v-else>Reset Password</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal untuk Menampilkan Pesan -->
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

const password = ref('');
const passwordConfirmation = ref('');
const isLoading = ref(false); // Loader State
const router = useRouter();

// Modal State
const showModal = ref(false);
const modalTitle = ref('');
const modalMessage = ref('');
let redirectAfterModal = false; // Flag untuk menentukan navigasi setelah modal
let timeoutId = null; // ID untuk timeout otomatis

// Fungsi untuk membuka modal
const openModal = (title, message, redirect = false) => {
    modalTitle.value = title;
    modalMessage.value = message;
    showModal.value = true;
    redirectAfterModal = redirect;

    // Jika redirect diperlukan, tambahkan timeout otomatis
    if (redirect) {
        timeoutId = setTimeout(() => {
            router.push('/monitoring-arcadia/login'); // Navigasi otomatis
            closeModal(); // Tutup modal
        }, 5000); // Timeout 5 detik
    }
};

// Fungsi untuk menutup modal dan melakukan navigasi manual jika diperlukan
const closeModal = () => {
    showModal.value = false;
    if (timeoutId) clearTimeout(timeoutId); // Hentikan timeout jika tombol OK ditekan
    if (redirectAfterModal) {
        router.push('/monitoring-arcadia/login'); // Navigasi manual
    }
};

const submitResetPassword = async () => {
    isLoading.value = true; // Aktifkan loader
    try {
        const email = localStorage.getItem('userEmail');
        const resetToken = localStorage.getItem('resetToken');

        if (!email || !resetToken) {
            openModal('Kesalahan', 'Email atau token tidak ditemukan. Harap ulangi proses reset password.', false);
            return;
        }

        // Panggil API untuk reset password
        const response = await customFetch.post('/auth/forgot-password/reset-password', {
            email, // Sertakan email
            reset_token: resetToken, // Sertakan reset token
            password: password.value, // Password baru
            password_confirmation: passwordConfirmation.value, // Konfirmasi password baru
        });

        // Tampilkan modal berhasil dengan timeout otomatis
        openModal('Berhasil', response.data.message, true);

        // Hapus email dan resetToken dari localStorage setelah berhasil
        localStorage.removeItem('userEmail');
        localStorage.removeItem('resetToken');
        localStorage.removeItem('accessForgotPassword');
    } catch (error) {
        console.error('Gagal mereset password:', error);

        // Tampilkan modal error tanpa redirect
        openModal('Kesalahan', error.response?.data?.message || 'Terjadi kesalahan saat mereset password.', false);
    } finally {
        isLoading.value = false; // Matikan loader
    }
};
</script>