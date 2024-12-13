<template>
    <div :class="[
        'bg-accent text-base-content border-r border-neutral transition-all duration-300',
        isDrawerOpen ? 'w-64' : 'w-18'
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
            <button class="btn btn-primary w-full flex items-center justify-center gap-2" @click="handleLogout">
                <v-icon name="ri-logout-box-line" class="h-5 w-5" />
                <span v-if="isDrawerOpen">Logout</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useAuthStore } from "@/stores/Auth";
import DrawerList from "@/components/Drawer/DrawerListPetani.vue";

// OhVueIcons imports
import { addIcons } from "oh-vue-icons";
import { FaHome, BiList, RiLogoutBoxLine } from "oh-vue-icons/icons";

addIcons(FaHome, BiList, RiLogoutBoxLine);

// State untuk drawer terbuka/tertutup
const isDrawerOpen = ref(true);

const toggleDrawer = () => {
    isDrawerOpen.value = !isDrawerOpen.value;
};

// Data menu navigasi
const listURL = [
    { name: "Dashboard", url: "/monitoring-arcadia/dashboard", icon: "fa-home" },

];

const authStore = useAuthStore();
const { currentUser, logoutUser } = authStore;

const filterNavItems = computed(() => {
    return listURL.filter((item) => {
        //    if (item.name === "Admin") {
        //         return ["admin", "super-admin"].includes(currentUser?.role);
        //     }
        //     if (item.name === "Super-Admin") {
        //         return currentUser?.role === "super-admin";
        //     }
        return true;
    })
})

const handleLogout = () => {
    logoutUser();
}
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

/* Logout Button Styling */
button.btn-primary {
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
}

/* Adjust Positioning */
.menu {
    margin-top: 1rem;
}
</style>