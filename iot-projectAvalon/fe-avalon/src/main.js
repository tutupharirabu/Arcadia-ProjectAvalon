import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { OhVueIcon } from 'oh-vue-icons'

import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/Auth'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.component('v-icon', OhVueIcon)

// Validasi token tersimpan saat aplikasi dimuat (tidak memblokir render).
// Jika token expired → interceptor 401 di customFetch menangani logout otomatis.
const authStore = useAuthStore()
if (authStore.tokenUser) {
  authStore.getUser()
}

app.mount('#app')
