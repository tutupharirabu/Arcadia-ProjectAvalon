<template>
    <div class="flex lg:flex-row flex-col items-center justify-between bg-neutral min-h-screen">
        <!-- Left Section (Image and Text) -->
        <div class="bg-primary text-primary-content w-full lg:w-1/2 flex flex-col justify-center p-6 lg:p-8 relative">
            <!-- Logo -->
            <div class="absolute top-4 left-4">
                <img src="@/assets/LogoPutihArcadia.png" alt="Logo AMU" class="h-12" />
            </div>
            <!-- Back To Home -->
            <div class="absolute top-4 right-4">
                <router-link to="/beranda" class="btn bg-primary-content text-neutral hover:bg-neutral hover:text-primary-content">Kembali ke Beranda →</router-link>
            </div>
            <!-- Content -->
            <div class="text-center max-w-md lg:mt-2 mt-20">
                <img src="@/assets/Full-2.png" alt="Illustration" class="mb-4 rounded-lg shadow-md max-w-full h-auto" />
                <h1 class="text-3xl lg:text-2xl font-bold mb-2">Solusi Berkebun Cerdas!</h1>
                <p class="text-sm lg:text-base">
                    {{ props.isRegister ? 'Jelajahi fitur lengkap untuk monitoring bunga dengan mendaftar akun hari ini!' : 'Masuk untuk memantau perkembangan bunga Anda dan mulai eksplorasi hari ini!' }}
                </p>
            </div>
        </div>

        <!-- Right Section (Form) -->
        <form class="bg-primary-content w-full lg:w-1/2 p-8 shadow-xl max-w-lg flex flex-col justify-center h-full"
            @submit.prevent="handleSubmit">
            <h1 class="text-3xl font-bold mb-6">{{ props.isRegister ? 'Buat Akun Baru' : 'Log in' }}</h1>
            <p class="mb-4">
                {{ props.isRegister ? 'Sudah punya akun?' : "Belum punya akun?" }}
                <router-link :to="props.isRegister ? '/monitoring-arcadia/login' : '/monitoring-arcadia/register'" class="text-primary underline">
                    {{ props.isRegister ? 'Log in' : 'Buat Akun Baru' }}
                </router-link>
            </p>

            <!-- Show errors -->
            <div v-if="authStore.isError" role="alert" class="alert alert-error mb-4">
                <ul>
                    <li v-for="(error, index) in authStore.errMsg.split(', ')" :key="index" class="text-black">
                        {{ error }}
                    </li>
                </ul>
            </div>

            <!-- Name Input (Register Only) -->
            <div v-if="props.isRegister" class="form-control mb-4">
                <label class="label"><span class="label-text">Nama Lengkap</span></label>
                <input type="text" placeholder="Isi Nama Lengkap" class="input bg-primary-content input-bordered" v-model="inputForm.name" required />
            </div>

            <!-- Email Input -->
            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Email</span></label>
                <input type="email" placeholder="Isi Email" class="input bg-primary-content input-bordered" v-model="inputForm.email" required />
            </div>

            <!-- Password Input -->
            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Password</span></label>
                <input type="password" placeholder="Isi Password" class="input input-bordered bg-primary-content"
                    v-model="inputForm.password" required />
            </div>

            <!-- Confirm Password Input (Register Only) -->
            <div v-if="props.isRegister" class="form-control mb-4">
                <label class="label"><span class="label-text">Konfirmasi Password</span></label>
                <input type="password" placeholder="Konfirmasi Password Anda!" class="input input-bordered bg-primary-content"
                    v-model="inputForm.password_confirmation" required />
            </div>

            <!-- Forgot Password (Login Only) -->
            <p v-if="!props.isRegister" class="text-left mb-4">
                <router-link to="/forgot-password" class="text-primary underline">
                    Lupa Password?
                </router-link>
            </p>

            <!-- Submit Button with Loader -->
            <div class="form-control">
                <button class="btn btn-primary w-full items-center justify-center" :disabled="isLoading">
                    <span v-if="isLoading" class="loading loading-spinner"></span>
                    <span v-else>{{ props.isRegister ? 'Buat Akun' : 'Log in' }}</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useAuthStore } from '@/stores/Auth'

const props = defineProps({
    isRegister: Boolean
})

const isLoading = ref(false) // Loader State

const authStore = useAuthStore()

const { loginUser, registerUser } = authStore

const inputForm = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})

const handleSubmit = async () => {
    isLoading.value = true
    try {
        if (props.isRegister) {
            await registerUser(inputForm) // Call register API
        } else {
            await loginUser(inputForm) // Call login API
        }
    } catch (error) {
        console.error('Error:', error)
    } finally {
        isLoading.value = false // Turn off loader
    }
}
</script>

<style scoped>
.flex {
    height: 85vh;
    /* Full viewport height */
}
</style>