<template>
    <div class="navbar bg-primary fixed top-0 z-10">
        <div class="navbar-start">
            <div class="dropdown">
                <ul tabindex="0"
                    class="menu menu-sm dropdown-content bg-base-100 rounded-box z-[1] mt-3 w-52 p-2 shadow">
                    <li v-for="item in filterNavItems" :key="item.name">
                        <RouterLink :to="item.url"> {{ item.name }}</RouterLink>
                    </li>
                </ul>
            </div>
            <a class="btn btn-ghost text-xl text-primary-content">Arcadia Flora Tech</a>
        </div>
        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal px-1 text-primary-content">
                <li v-for="item in filterNavItems" :key="item.name">
                    <RouterLink :to="item.url"> {{ item.name }}</RouterLink>
                </li>
            </ul>
        </div>
        <div class="navbar-end mr-4">
            <button v-if="currentUser" @click="handleLogout" class="btn btn-error">Logout</button>
            <router-link v-else to="/login" class="btn bg-primary-content text-primary">Login</router-link>
        </div>
    </div>
</template>

<script setup>
import { useAuthStore } from "@/stores/Auth";
import { computed } from "vue";
import listNav from "@/utils/listNav";

const AuthStore = useAuthStore();
const { currentUser, logoutUser } = AuthStore;

const filterNavItems = computed(() => {
    return listNav.filter((item) => {
        if (item.name === "Profile" && !currentUser) {
            return false
        } else {
            return true
        }
    })
})

const handleLogout = () => {
    logoutUser();
};;
</script>