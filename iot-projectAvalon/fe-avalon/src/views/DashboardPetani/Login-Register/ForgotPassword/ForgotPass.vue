<template>
    <div class="hero bg-base-content min-h-screen">
        <div class="hero-content flex-col text-base-100">
            <div class="text-center">
                <h1 class="text-5xl font-bold">Forgot Password</h1>
                <p class="py-6">
                    Masukkan email Anda untuk menerima kode OTP guna mengatur ulang password.
                    Jika Anda tidak menerima kode OTP, Anda dapat mengirim ulang.
                </p>
            </div>
            <div class="card bg-base-100 w-full max-w-sm shrink-0 shadow-2xl p-6">
                <form @submit.prevent="submitForgotPassword">
                    <div class="form-control">
                        <label for="email" class="label">
                            <span class="label-text">Email</span>
                        </label>
                        <input
                            id="email"
                            v-model="email"
                            type="email"
                            placeholder="Masukkan email Anda"
                            class="input input-bordered text-base-content"
                            required
                        />
                    </div>
                    <button type="submit" class="btn btn-primary w-full mt-4">
                        Kirim OTP
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import customFetch from '@/utils/customFetch';

const email = ref('');
const router = useRouter();

const submitForgotPassword = async () => {
    try {
        const response = await customFetch.post('/auth/forgot-password/send-email', { email: email.value });

        // Simpan email pengguna di localStorage
        localStorage.setItem('userEmail', email.value);

        alert(response.data.message);

        router.push('/forgot-password/verifikasiOTP');
    } catch (error) {
        console.error('Gagal mengirim OTP:', error);

        // Tampilkan pesan kesalahan
        alert(error.response?.data?.message || 'Terjadi kesalahan saat mengirim OTP.');
    }
};
</script>