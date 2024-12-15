<template>
    <div class="hero bg-base-content min-h-screen">
        <div class="hero-content flex-col text-primary-content">
            <div class="text-center">
                <h1 class="text-5xl font-bold">Verifikasi Kode OTP</h1>
                <p class="py-6">
                    Masukkan kode OTP yang telah dikirim ke email Anda untuk mengatur ulang password.
                    Jika Anda belum menerima kode OTP, klik tombol untuk mengirim ulang.
                </p>
            </div>
            <div class="card bg-primary-content w-full max-w-sm shrink-0 shadow-2xl p-6">
                <!-- Form untuk Verifikasi OTP -->
                <form @submit.prevent="submitOtpVerification">
                    <div class="form-control">
                        <label for="otp" class="label">
                            <span class="label-text">Kode OTP</span>
                        </label>
                        <input id="otp" v-model="otp" type="text" placeholder="Masukkan kode OTP"
                            class="input input-bordered text-neutral bg-primary-content" required />
                    </div>
                    <button type="submit" class="btn btn-primary w-full mt-4 flex items-center justify-center"
                        :disabled="isVerifying">
                        <span v-if="isVerifying" class="loading loading-ellipsis"></span>
                        <span v-else>Verifikasi</span>
                    </button>
                </form>

                <!-- Pesan Belum Menerima OTP -->
                <p class="text-base-content text-center mt-4">
                    Belum menerima kode? Klik tombol di bawah untuk mengirim ulang kode OTP.
                </p>

                <!-- Tombol Kirim Ulang Kode OTP -->
                <button @click="resendOtp" :disabled="isResendDisabled || isResendLoading"
                    class="btn btn-outline hover:text-primary-content w-full mt-2 flex items-center justify-center"
                    :class="{ 'opacity-50 cursor-not-allowed': isResendDisabled || isResendLoading }">
                    <span v-if="isResendLoading" class="loading loading-ellipsis"></span>
                    <span v-else>Kirim Ulang Kode OTP</span>
                </button>

                <!-- Timer untuk Countdown -->
                <p v-if="isResendDisabled" class="text-center text-gray-400 mt-2">
                    Anda dapat mengirim ulang kode dalam {{ countdown }} detik.
                </p>
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

const otp = ref('');
const router = useRouter();

// State untuk loader
const isVerifying = ref(false); // Loader untuk Verifikasi OTP
const isResendLoading = ref(false); // Loader untuk Kirim Ulang OTP
const isResendDisabled = ref(false);
const countdown = ref(0);

// Modal State
const showModal = ref(false);
const modalTitle = ref('');
const modalMessage = ref('');
let redirectAfterModal = false; // Flag redirect
let timeoutId = null; // ID untuk timeout otomatis

// Fungsi untuk membuka modal
const openModal = (title, message, redirect = false) => {
    modalTitle.value = title;
    modalMessage.value = message;
    showModal.value = true;
    redirectAfterModal = redirect;

    // Jika redirect diperlukan (untuk fungsi verifikasi OTP)
    if (redirect) {
        timeoutId = setTimeout(() => {
            router.push('/forgot-password/resetPassword'); // Navigasi otomatis
            closeModal(); // Tutup modal
        }, 5000); // Timeout 5 detik
    }
};

// Fungsi untuk menutup modal dan navigasi jika diperlukan
const closeModal = () => {
    showModal.value = false;
    if (timeoutId) clearTimeout(timeoutId); // Hentikan timeout jika tombol OK ditekan
    if (redirectAfterModal) {
        router.push('/forgot-password/resetPassword'); // Navigasi manual
    }
};

// Fungsi Verifikasi OTP
const submitOtpVerification = async () => {
    isVerifying.value = true; // Aktifkan loader di tombol Verifikasi
    try {
        const email = localStorage.getItem('userEmail');
        if (!email) {
            openModal('Kesalahan', 'Email tidak ditemukan. Silakan masukkan email terlebih dahulu.');
            return;
        }

        const response = await customFetch.post('/auth/forgot-password/verify-otp-code', {
            email,
            otp_code: otp.value,
        });

        const resetToken = response.data.reset_token;
        localStorage.setItem('resetToken', resetToken);

        // Tampilkan modal sukses dengan timeout otomatis
        openModal('Berhasil', response.data.message, true);
    } catch (error) {
        openModal('Kesalahan', error.response?.data?.message || 'Terjadi kesalahan saat memverifikasi.');
    } finally {
        isVerifying.value = false; // Matikan loader di tombol Verifikasi
    }
};

// Fungsi Kirim Ulang Kode OTP
const resendOtp = async () => {
    if (isResendDisabled.value) return;

    isResendLoading.value = true; // Aktifkan loader di tombol Kirim Ulang
    try {
        const email = localStorage.getItem('userEmail');
        if (!email) {
            openModal('Kesalahan', 'Email tidak ditemukan. Silakan masukkan email terlebih dahulu.');
            return;
        }

        const response = await customFetch.post('/auth/forgot-password/send-email', { email });
        openModal('Berhasil', response.data.message, false); // Tidak ada navigasi otomatis
        isResendDisabled.value = true;
        startCountdown(60); // Countdown 60 detik
    } catch (error) {
        openModal('Kesalahan', error.response?.data?.message || 'Terjadi kesalahan saat mengirim ulang OTP.', false);
    } finally {
        isResendLoading.value = false; // Matikan loader di tombol Kirim Ulang
    }
};

// Fungsi untuk Countdown Timer
const startCountdown = (seconds) => {
    countdown.value = seconds;

    const timer = setInterval(() => {
        countdown.value -= 1;

        if (countdown.value <= 0) {
            clearInterval(timer);
            isResendDisabled.value = false;
        }
    }, 1000);
};
</script>