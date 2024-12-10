<template>
    <div class="hero bg-base-content min-h-screen">
        <div class="hero-content flex-col text-base-100">
            <div class="text-center">
                <h1 class="text-5xl font-bold">Reset Password</h1>
                <p class="py-6">
                    Masukkan password baru Anda untuk mengatur ulang password akun Anda.
                </p>
            </div>
            <div class="card bg-base-100 w-full max-w-sm shrink-0 shadow-2xl p-6">
                <form @submit.prevent="submitResetPassword">
                    <div class="form-control">
                        <label for="password" class="label">
                            <span class="label-text">Password Baru</span>
                        </label>
                        <input
                            id="password"
                            v-model="password"
                            type="password"
                            placeholder="Masukkan password baru"
                            class="input input-bordered text-base-content"
                            required
                        />
                    </div>
                    <div class="form-control mt-4">
                        <label for="password_confirmation" class="label">
                            <span class="label-text">Konfirmasi Password</span>
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="passwordConfirmation"
                            type="password"
                            placeholder="Konfirmasi password baru"
                            class="input input-bordered text-base-content"
                            required
                        />
                    </div>
                    <button type="submit" class="btn btn-primary w-full mt-4">
                        Reset Password
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

const password = ref('');
const passwordConfirmation = ref('');
const router = useRouter();

// Ambil data dari localStorage
const email = localStorage.getItem('userEmail');
const resetToken = localStorage.getItem('resetToken');

const submitResetPassword = async () => {
    try {
        const email = localStorage.getItem('userEmail');
        const resetToken = localStorage.getItem('resetToken');

        if (!email || !resetToken) {
            return alert('Email atau token tidak ditemukan. Harap ulangi proses reset password.');
        }

        // Panggil API untuk reset password
        const response = await customFetch.post('/auth/forgot-password/reset-password', {
            email, // Sertakan email
            reset_token: resetToken, // Sertakan reset token
            password: password.value, // Password baru
            password_confirmation: passwordConfirmation.value, // Konfirmasi password baru
        });
        
        alert(response.data.message);

        // Hapus email dan resetToken dari localStorage setelah berhasil
        localStorage.removeItem('userEmail');
        localStorage.removeItem('resetToken');

        // Redirect ke halaman login
        router.push('/login');
    } catch (error) {
        console.error('Gagal mereset password:', error);

        // Tampilkan pesan kesalahan
        alert(error.response?.data?.message || 'Terjadi kesalahan saat mereset password.');
    }
};
</script>