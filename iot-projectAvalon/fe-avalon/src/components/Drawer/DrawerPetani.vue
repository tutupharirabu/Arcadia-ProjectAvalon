<template>
    <div :class="[
        'bg-base-200 text-base-content border-r border-base-300 transition-all duration-300',
        isDrawerOpen ? 'w-64' : 'w-18'
    ]" class="flex flex-col h-screen">
        <!-- Drawer Header -->
        <div class="flex items-center justify-between p-3">
            <span v-if="isDrawerOpen" class="text-lg font-semibold">Arcadia Flora Tech™</span>
            <button class="btn btn-circle btn-ghost" @click="toggleDrawer" aria-label="toggle drawer">
                <v-icon name="bi-list" class="h-6 w-6" />
            </button>
        </div>

        <!-- Drawer Items -->
        <ul class="menu flex-grow">
            <DrawerList v-for="item in filterNavItems" :key="item.name" :data="item" :isDrawerOpen="isDrawerOpen" />
        </ul>

        <!-- Logout Button -->
        <div class="p-2 mt-auto">
            <button class="btn btn-error w-full flex items-center justify-center gap-2" @click="handleLogout">
                <v-icon name="co-account-logout" class="h-5 w-5" />
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
import { BiList, FaHome, FaBriefcase, CoAccountLogout, BiPeopleFill, BiEnvelopeExclamationFill } from "oh-vue-icons/icons";

addIcons(BiList, FaHome, FaBriefcase, CoAccountLogout, BiPeopleFill, BiEnvelopeExclamationFill);

// State untuk drawer terbuka/tertutup
const isDrawerOpen = ref(true);

const toggleDrawer = () => {
    isDrawerOpen.value = !isDrawerOpen.value;
};

// Data menu navigasi
const listURL = [
    { name: "Dashboard", url: "//dashboard-petani", icon: "fa-home" },
    { name: "Devices", url: "#", icon: "fa-briefcase" },
    { name: "Metrik Historical", url: "#", icon: "bi-envelope-exclamation-fill" },
    { name: "Admin", url: "#", icon: "bi-envelope-exclamation-fill" },
    { name: "Super-Admin", url: "#", icon: "bi-envelope-exclamation-fill" },
];

const authStore = useAuthStore();
const { currentUser, logoutUser } = authStore;

const filterNavItems = computed(() => {
    return listURL.filter((item) => {
       if (item.name === "Admin") {
            return ["admin", "super-admin"].includes(currentUser?.role);
        }
        if (item.name === "Super-Admin") {
            return currentUser?.role === "super-admin";
        }
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
button.btn-error {
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
}

/* Adjust Positioning */
.menu {
    margin-top: 1rem;
}
</style>