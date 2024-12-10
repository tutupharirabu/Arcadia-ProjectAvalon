import { ref } from 'vue'
import { defineStore } from 'pinia'
import { useRouter } from 'vue-router'
import customFetch from '@/utils/customFetch'

export const useAuthStore = defineStore('auth', () => {
    const router = useRouter()
    const tokenUser = ref(null)
    const currentUser = ref(null)
    const isError = ref(false)
    const errMsg = ref('')

    const loginUser = async (formData) => {
        try {
            isError.value = false;
            errMsg.value = '';
            const { email, password } = formData;

            const { data } = await customFetch.post('/auth/login', {
                email,
                password,
            });

            const { token, user } = data;

            tokenUser.value = token;
            currentUser.value = user;

            // Simpan data ke localStorage
            localStorage.setItem('token', JSON.stringify(token));
            localStorage.setItem('user', JSON.stringify(user));

            // Redirect berdasarkan role
            if (user.role === 'Petani') {
                router.push('/home-dashboard-petani');
            } 
        } catch (error) {
            isError.value = true;
            const errors = error.response?.data || { message: 'An error occurred' };

            if (typeof errors === 'object') {
                errMsg.value = Object.values(errors)
                    .flat()
                    .join(', ');
            } else {
                errMsg.value = errors;
            }
        }
    };

    const registerUser = async (formData) => {
        try {
            isError.value = false
            errMsg.value = ''
            const { name, email, password, password_confirmation } = formData

            const { data } = await customFetch.post('/auth/register', {
                name,
                email,
                password,
                password_confirmation,
            })

            const { token, data: user } = data

            tokenUser.value = token
            currentUser.value = user

            localStorage.setItem('userEmail', email);
            localStorage.setItem('token', JSON.stringify(token))
            localStorage.setItem('user', JSON.stringify(user))

            router.push('/verifikasiEmail')
        } catch (error) {
            isError.value = true;
            const errors = error.response?.data || { message: 'An error occurred' };

            if (typeof errors === 'object') {
                errMsg.value = Object.values(errors)
                    .flat()
                    .join(', ');
            } else {
                errMsg.value = errors;
            }
        }
    }

    const logoutUser = async () => {
        try {
            const { data } = await customFetch.post('/auth/logout', null, {
                headers: {
                    Authorization: `Bearer ${tokenUser.value}`,
                }
            })

            // PINIA: Reset all state values
            tokenUser.value = null
            currentUser.value = null

            // Remove token and user from localStorage
            localStorage.removeItem('token')
            localStorage.removeItem('user')

            router.push('/login')

        } catch (error) {
            console.error(error)
        }
    }

    const getUser = async () => {
        try {
            const { data } = await customFetch.get('/auth/me', {
                headers: {
                    Authorization: `Bearer ${tokenUser.value}`,
                }
            })

            const { data: user } = data

            currentUser.value = user
            localStorage.setItem('user', JSON.stringify(user))

        } catch (error) {
            console.error(error)
        }
    }

    return {
        tokenUser,
        currentUser,
        isError,
        errMsg,
        loginUser,
        registerUser,
        logoutUser,
        getUser,
    }
})