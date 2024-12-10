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
                <router-link to="/beranda" class="btn bg-primary-content text-primary">Back to website →</router-link>
            </div>
            <!-- Content -->
            <div class="text-center max-w-md mt-28">
                <img src="@/assets/Full.png" alt="Illustration" class="mb-4 rounded-lg shadow-md max-w-full h-auto" />
                <h1 class="text-3xl lg:text-2xl font-bold mb-2">Capturing Moments, Creating Memories</h1>
                <p class="text-sm lg:text-base">
                    {{ props.isRegister ? 'Explore the features by registering an account today!' : 'Log in to access your account and start exploring today!' }}
                </p>
            </div>
        </div>

        <!-- Right Section (Form) -->
        <form class="bg-base-100 w-full lg:w-1/2 p-8 shadow-xl max-w-lg flex flex-col justify-center h-full"
            @submit.prevent="handleSubmit">
            <h1 class="text-3xl font-bold mb-6">{{ props.isRegister ? 'Create an account' : 'Log in' }}</h1>
            <p class="mb-4">
                {{ props.isRegister ? 'Already have an account?' : "Don't have an account?" }}
                <router-link :to="props.isRegister ? '/login' : '/register'" class="text-primary underline">
                    {{ props.isRegister ? 'Log in' : 'Register' }}
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
                <label class="label"><span class="label-text">Full Name</span></label>
                <input type="text" placeholder="Full Name" class="input input-bordered" v-model="inputForm.name" />
            </div>

            <!-- Email Input -->
            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Email</span></label>
                <input type="email" placeholder="Email" class="input input-bordered" v-model="inputForm.email" />
            </div>

            <!-- Password Input -->
            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Password</span></label>
                <input type="password" placeholder="Password" class="input input-bordered"
                    v-model="inputForm.password" />
            </div>

            <!-- Confirm Password Input (Register Only) -->
            <div v-if="props.isRegister" class="form-control mb-4">
                <label class="label"><span class="label-text">Confirm Password</span></label>
                <input type="password" placeholder="Confirm your password" class="input input-bordered"
                    v-model="inputForm.password_confirmation" />
            </div>

            <!-- Forgot Password (Login Only) -->
            <p v-if="!props.isRegister" class="text-left mb-4">
                <router-link to="/forgot-password" class="text-primary underline">
                    Forgot Password?
                </router-link>
            </p>

            <!-- Submit Button -->
            <div class="form-control">
                <button class="btn btn-primary w-full">
                    {{ props.isRegister ? 'Create account' : 'Log in' }}
                </button>
            </div>



        </form>
    </div>
</template>

<script setup>
import { reactive } from 'vue'
import { useAuthStore } from '@/stores/Auth'

const props = defineProps({
    isRegister: Boolean
})

const authStore = useAuthStore()

const { loginUser, registerUser } = authStore

const inputForm = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})

const handleSubmit = () => {
    if (props.isRegister) {
        registerUser(inputForm)
        alert('Registrasi berhasil! Silakan cek email Anda untuk verifikasi.');
    } else {
        loginUser(inputForm)
        alert('Login berhasil!');
    }
}
</script>

<style scoped>
.flex {
    height: 85vh;
    /* Full viewport height */
}
</style>