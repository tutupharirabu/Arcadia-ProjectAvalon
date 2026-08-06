<template>
    <li>
        <RouterLink :to="data.url" class="flex items-center gap-4 p-0 rounded-lg hover:bg-base-200">
            <v-icon :name="data.icon" class="h-6 w-6" />
            <span v-if="isDrawerOpen">{{ data.name }}</span>
        </RouterLink>
    </li>
</template>

<script setup>
import { defineProps } from "vue";

const props = defineProps({
    data: Object,
    isDrawerOpen: Boolean,
});
</script>

<style scoped>
li {
    margin-bottom: 0.5rem;
}
</style>