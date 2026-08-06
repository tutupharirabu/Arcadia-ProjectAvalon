<template>
    <div class="hero bg-base-content min-h-screen flex items-center justify-center">
        <div class="hero-content flex-col text-base-100">
            <!-- Header -->
            <div class="text-center">
                <h1 class="text-5xl font-bold">Verifikasi Email</h1>
                <p class="py-6 text-gray-200">
                    Masukkan kode OTP yang telah dikirim ke email Anda untuk verifikasi.
                    Jika Anda belum menerima kode OTP, klik tombol untuk mengirim ulang.
                </p>
            </div>

            <!-- Form Card -->
            <div class="card bg-base-100 w-full max-w-sm shadow-2xl p-6">
                <form @submit.prevent="submitVerification">
                    <!-- Input OTP -->
                    <div class="form-control">
                        <label for="otp" class="label">
                            <span class="label-text text-gray-600">Kode OTP</span>
                        </label>
                        <input id="otp" v-model="otp" type="text" placeholder="Masukkan kode OTP"
                            class="input input-bordered w-full text-neutral" required />
                    </div>
                    <!-- Button Verifikasi -->
                    <button type="submit" class="btn btn-primary w-full mt-4 flex items-center justify-center"
                        :disabled="isVerifying">
                        <span v-if="isVerifying" class="loading loading-spinner"></span>
                        <span v-else>Verifikasi</span>
                    </button>
                </form>

                <!-- Kirim Ulang OTP -->
                <p class="text-center mt-4 text-gray-500">
                    Belum menerima kode? Klik tombol di bawah untuk mengirim ulang kode OTP.
                </p>
                <button @click="resendOtp" :disabled="isResendDisabled || isResending"
                    class="btn btn-outline w-full mt-2 flex items-center justify-center"
                    :class="{ 'opacity-50 cursor-not-allowed': isResendDisabled }">
                    <span v-if="isResending" class="loading loading-spinner"></span>
                    <span v-else>Kirim Ulang Kode OTP</span>
                </button>

                <!-- Countdown Timer -->
                <p v-if="isResendDisabled" class="text-center text-gray-400 mt-2">
                    Anda dapat mengirim ulang kode dalam {{ countdown }} detik.
                </p>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" role="dialog" aria-modal="true" aria-label="Hasil Verifikasi Email"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" @click.self="closeModal">
        <div class="bg-white rounded-lg p-6 shadow-lg text-center w-80">
            <h3 class="text-lg font-bold mb-4 text-base-content">{{ modalTitle }}</h3>
            <p class="text-base-content mb-6">{{ modalMessage }}</p>
            <button @click="closeModal" class="btn btn-primary w-full">OK</button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '@/stores/Auth';
import { useRouter } from 'vue-router';
import customFetch from '@/utils/customFetch';

const AuthStore = useAuthStore();
const router = useRouter();

// State
const otp = ref('');
const isVerifying = ref(false); // Loader untuk tombol Verifikasi
const isResending = ref(false); // Loader untuk tombol Kirim Ulang
const isResendDisabled = ref(false);
const countdown = ref(0);

// Modal State
const showModal = ref(false);
const modalTitle = ref('');
const modalMessage = ref('');
let isRedirect = false; // Flag untuk redirect
let lastFocusedElement = null; // Untuk restore fokus setelah modal ditutup

// Buka modal sambil menyimpan elemen yang fokus sebelumnya
const openResultModal = (title, message) => {
    lastFocusedElement = document.activeElement;
    modalTitle.value = title;
    modalMessage.value = message;
    showModal.value = true;
};

// Submit Verifikasi OTP
const submitVerification = async () => {
    isVerifying.value = true; // Aktifkan loader
    try {
        const response = await customFetch.post(
            '/auth/verification-email',
            { otp_code: otp.value }
        );

        openResultModal('Verifikasi Berhasil', response.data.message);
        isRedirect = true; // Aktifkan redirect
    } catch (error) {
        console.error('Gagal memverifikasi kode OTP:', error);
        openResultModal('Kesalahan', error.response?.data?.message || 'Terjadi kesalahan saat memverifikasi.');
        isRedirect = false;
    } finally {
        isVerifying.value = false; // Matikan loader
    }
};

// Kirim Ulang OTP
const resendOtp = async () => {
    if (isResendDisabled.value || isResending.value) return;

    isResending.value = true; // Aktifkan loader
    try {
        const email = AuthStore.currentUser.email;
        if (!email) {
            openResultModal('Kesalahan', 'Email tidak ditemukan. Silakan ulangi proses login.');
            return;
        }

        const response = await customFetch.post('/auth/generate-otp-code', { email });
        openResultModal('Kode OTP Dikirim Ulang', response.data.message);

        isResendDisabled.value = true;
        startCountdown(60); // Mulai countdown
    } catch (error) {
        console.error('Gagal mengirim ulang kode OTP:', error);
        openResultModal('Kesalahan', error.response?.data?.message || 'Terjadi kesalahan saat mengirim ulang OTP.');
    } finally {
        isResending.value = false; // Matikan loader
    }
};

// Countdown Timer
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

// Tutup Modal
const closeModal = () => {
    showModal.value = false;
    lastFocusedElement?.focus?.();
    if (isRedirect) {
        router.push('/monitoring-arcadia/login');
    }
};

// Tutup modal saat ESC ditekan
const handleModalKeydown = (event) => {
    if (event.key === 'Escape' && showModal.value) {
        closeModal();
    }
};

onMounted(() => window.addEventListener('keydown', handleModalKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleModalKeydown));
</script>