<template>
    <form class="card-body" @submit.prevent="handleSubmit">
        <div v-if="authStore.isError">
            <div role="alert" class="alert alert-error">
                <ul>
                    <li v-for="(error, index) in authStore.errMsg.split(', ')" :key="index" class="text-black">
                        {{ error }}
                    </li>
                </ul>
            </div>
        </div>
        <div class="form-control" v-if="props.isRegister">
            <label class="label">
                <span class="label-text">Nama</span>
            </label>
            <input type="text" placeholder="name" class="input input-bordered" v-model="inputForm.name" />
        </div>
        <div class="form-control">
            <label class="label">
                <span class="label-text">Email</span>
            </label>
            <input type="email" placeholder="email" class="input input-bordered" v-model="inputForm.email" />
        </div>
        <div class="form-control mt-4">
            <label class="label">
                <span class="label-text">Password</span>
            </label>
            <input type="password" placeholder="password" class="input input-bordered" v-model="inputForm.password" />
        </div>
        <div class="form-control mt-4" v-if="props.isRegister">
            <label class="label">
                <span class="label-text">Confirm Password</span>
            </label>
            <input type="password" placeholder="confirm password" class="input input-bordered"
                v-model="inputForm.password_confirmation" />
        </div>
        <div class="form-control mt-6">
            <button class="btn btn-primary">{{ props.isRegister ? "Register" : "Login" }}</button>
            <div v-if="props.isRegister">
                <br>
                Sudah mendaftar? Silahkan <router-link to="/login" class="text-primary">Login</router-link>
            </div>
            <div v-else>
                <br>
                Belum terdaftar? Silahkan <router-link to="/register" class="text-primary">Register</router-link>
            </div>
        </div>
    </form>
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
    password_confirmation: '',
});

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