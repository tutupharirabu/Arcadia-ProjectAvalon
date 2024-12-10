<template>
    <div class="hero bg-base-content min-h-screen">
        <div class="hero-content flex-col text-base-100">
            <div class="text-center">
                <h1 class="text-5xl font-bold">Verifikasi Kode OTP</h1>
                <p class="py-6">
                    Masukkan kode OTP yang telah dikirim ke email Anda untuk mengatur ulang password.
                    Jika Anda belum menerima kode OTP, klik tombol untuk mengirim ulang.
                </p>
            </div>
            <div class="card bg-base-100 w-full max-w-sm shrink-0 shadow-2xl p-6">
                <form @submit.prevent="submitOtpVerification">
                    <div class="form-control">
                        <label for="otp" class="label">
                            <span class="label-text">Kode OTP</span>
                        </label>
                        <input id="otp" v-model="otp" type="text" placeholder="Masukkan kode OTP"
                            class="input input-bordered text-base-content" required />
                    </div>
                    <button type="submit" class="btn btn-primary w-full mt-4">
                        Verifikasi
                    </button>
                </form>
                <p class="text-base-content text-center mt-4">
                    Belum menerima kode? Klik tombol di bawah untuk mengirim ulang kode OTP.
                </p>
                <!-- Tombol Kirim Ulang Kode OTP -->
                <button @click="resendOtp" :disabled="isResendDisabled" class="btn btn-outline w-full mt-2"
                    :class="{ 'opacity-50 cursor-not-allowed': isResendDisabled }">
                    Kirim Ulang Kode OTP
                </button>
                <!-- Timer untuk countdown -->
                <p v-if="isResendDisabled" class="text-center text-gray-400 mt-2">
                    Anda dapat mengirim ulang kode dalam {{ countdown }} detik.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import customFetch from '@/utils/customFetch';

const otp = ref('');
const router = useRouter();

const isResendDisabled = ref(false);
const countdown = ref(0);

const submitOtpVerification = async () => {
    try {
        // Ambil email dari localStorage
        const email = localStorage.getItem('userEmail');
        if (!email) {
            return alert('Email tidak ditemukan. Silakan masukkan email terlebih dahulu.');
        }

        // Panggil API untuk verifikasi OTP
        const response = await customFetch.post('/auth/forgot-password/verify-otp-code', {
            email, // Sertakan email
            otp_code: otp.value, // Sertakan OTP dari pengguna
        });

        // Ambil reset token dari respons dan simpan di localStorage
        const resetToken = response.data.reset_token;
        localStorage.setItem('resetToken', resetToken);

        alert(response.data.message);
        router.push('/forgot-password/resetPassword');
    } catch (error) {
        console.error('Gagal memverifikasi kode OTP:', error);

        // Tampilkan pesan kesalahan jika terjadi error
        alert(error.response?.data?.message || 'Terjadi kesalahan saat memverifikasi.');
    }
};

const resendOtp = async () => {
    if (isResendDisabled.value) return;

    try {
        // Ambil email dari localStorage
        const email = localStorage.getItem('userEmail');
        if (!email) {
            return alert('Email tidak ditemukan. Silakan masukkan email terlebih dahulu.');
        }

        const response = await customFetch.post('/auth/forgot-password/send-email', { email });
        alert(response.data.message);

        isResendDisabled.value = true;
        startCountdown(60); // Set timer 300 detik
    } catch (error) {
        console.error('Gagal mengirim ulang kode OTP:', error);
        alert(error.response?.data?.message || 'Terjadi kesalahan saat mengirim ulang OTP.');
    }
};

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