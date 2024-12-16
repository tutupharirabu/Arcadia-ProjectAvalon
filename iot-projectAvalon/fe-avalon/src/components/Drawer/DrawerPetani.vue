<template>
    <div :class="[
        'bg-accent text-base-content border-r border-neutral transition-all duration-300',
        isDrawerOpen ? 'w-48' : 'w-18'
    ]" class="flex flex-col h-screen">
        <!-- Drawer Header -->
        <div class="flex items-center justify-between p-3 text-primary">
            <span v-if="isDrawerOpen" class="text-lg font-semibold">Arcadia Flora Tech™</span>
            <button class="btn btn-circle btn-ghost" @click="toggleDrawer" aria-label="toggle drawer">
                <!-- Jika drawer terbuka, tampilkan ikon bi-list -->
                <v-icon v-if="isDrawerOpen" name="bi-list" class="h-6 w-6" />
                <!-- Jika drawer tertutup, tampilkan gambar logo -->
                <img v-else src="@/assets/LogoArcadia.png" alt="Arcadia Flora Tech™" class="h-6 w-auto" />
            </button>
        </div>

        <!-- Drawer Items -->
        <ul class="menu flex-grow text-primary">
            <DrawerList v-for="item in filterNavItems" :key="item.name" :data="item" :isDrawerOpen="isDrawerOpen" />
        </ul>

        <!-- Logout Button -->
        <div class="p-2 mt-auto">
            <button class="btn btn-primary w-full flex items-center justify-center gap-2" @click="handleLogout"
                :disabled="isLoading">
                <v-icon v-if="!isLoading" name="ri-logout-box-line" class="h-5 w-5" />
                <span v-if="!isLoading && isDrawerOpen">Logout</span>
                <span v-else-if="isLoading" class="loading loading-spinner loading-sm text-primary"></span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, defineEmits } from "vue";
import { useAuthStore } from "@/stores/Auth";
import DrawerList from "@/components/Drawer/DrawerListPetani.vue";

// OhVueIcons imports
import { addIcons } from "oh-vue-icons";
import { FaHome, BiList, RiLogoutBoxLine, MdDevicehubOutlined } from "oh-vue-icons/icons";

addIcons(FaHome, BiList, RiLogoutBoxLine, MdDevicehubOutlined);

// State untuk drawer terbuka/tertutup
const isDrawerOpen = ref(true);
const emit = defineEmits(["toggle"]);

const isLoading = ref(false); // State untuk loader

const toggleDrawer = () => {
    isDrawerOpen.value = !isDrawerOpen.value;
    emit("toggle", isDrawerOpen.value); // Kirim state ke parent
};

// Data menu navigasi
const listURL = [
    { name: "Beranda", url: "/monitoring-arcadia/dashboard", icon: "fa-home" },
    { name: "Alat-Alat", url: "/monitoring-arcadia/device", icon: "md-devicehub-outlined" },
];

const authStore = useAuthStore();
const { logoutUser } = authStore;

const filterNavItems = computed(() => listURL);

const handleLogout = async () => {
    isLoading.value = true; // Aktifkan loader
    try {
        await logoutUser(); // Proses logout
    } catch (error) {
        console.error("Error during logout:", error);
    } finally {
        isLoading.value = false; // Matikan loader
    }
};
</script>

<style scoped>
.menu li {
    display: flex;
    align-items: center;
    padding: 0.5rem 1rem;
    gap: 0.75rem;
    transition: background-color 0.3s ease;
    border-radius: 0.5rem;
}

.menu li:hover {
    background-color: var(--base-300);
}

/* Adjust Positioning */
.menu {
    margin-top: 1rem;
}
</style>