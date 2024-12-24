<template>
    <div class="p-6 space-y-12" style="max-width: 1920px;">
        <!-- Loader -->
        <div v-if="isLoading" class="text-center">
            <span class="loading loading-spinner loading-lg text-primary"></span>
            <p class="mt-4 text-gray-500">Menyiapkan Detail Data Alat...</p>
        </div>

        <!-- Konten Jika Selesai Loading -->
        <template v-else>
            <!-- Informasi Perangkat -->
            <div class="bg-accent text-on-secondary border border-neutral shadow-md rounded-lg p-6">
                <h2 class="text-2xl font-semibold mb-4 text-secondary-content">Informasi Alat</h2>
                <div class="space-y-2">
                    <p class="text-md"><strong>Nama Alat:</strong> {{ deviceDetail.deviceName }}</p>
                    <p class="text-md"><strong>Tipe Alat:</strong> {{ deviceDetail.deviceType }}</p>
                    <p class="text-md">
                        <strong>Status:</strong>
                        {{ deviceDetail.status === "Active" ? "Aktif" : "Nonaktif" }}
                    </p>
                    <p class="text-md"><strong>Lokasi:</strong> {{ deviceDetail.location }}</p>
                    <p class="text-md"><strong>Deskripsi:</strong> {{ deviceDetail.description }}</p>
                </div>

                <!-- Tombol Update -->
                <div class="mt-4 flex justify-end">
                    <button @click="navigateToUpdateForm"
                        class="btn btn-primary px-4 py-2 rounded-lg text-primary-content hover:bg-primary-focus">
                        Update Informasi Alat
                    </button>
                </div>
            </div>

            <!-- Bagian Tabel Water Pump Log -->
            <div class="bg-base-100 border border-neutral shadow-md rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Data Waktu Nyala Pompa Air</h3>
                <table class="w-full border-collapse border border-gray-300 text-sm">
                    <thead class="bg-neutral text-neutral-content">
                        <tr>
                            <th class="border px-4 py-2">Waktu Mulai</th>
                            <th class="border px-4 py-2">Waktu Selesai</th>
                            <th class="border px-4 py-2">Durasi (Dalam Detik)</th>
                        </tr>
                    </thead>
                    <tbody v-if="paginatedPumpLogData.length > 0">
                        <tr v-for="(log, index) in paginatedPumpLogData" :key="index">
                            <td class="border px-4 py-2">{{ formatTimestamp(log.start_time) }}</td>
                            <td class="border px-4 py-2">{{ formatTimestamp(log.end_time) }}</td>
                            <td class="border px-4 py-2">{{ log.duration }}</td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr>
                            <td colspan="4" class="text-center border px-4 py-6 text-neutral">
                                Tidak ada log pompa air untuk perangkat ini.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination Controls -->
                <div class="flex justify-center items-center mt-4 space-x-2">
                    <button class="btn btn-sm btn-neutral" :class="{ 'btn-disabled': currentPage === 1 }"
                        @click="changePage(currentPage - 1)">
                        Prev
                    </button>
                    <button v-for="page in totalPagesPumpLog" :key="page" class="btn btn-sm btn-base-200 text-primary"
                        :class="{ 'btn-primary text-primary-content': page === currentPage }" @click="changePage(page)">
                        {{ page }}
                    </button>
                    <button class="btn btn-sm btn-neutral"
                        :class="{ 'btn-disabled': currentPage === totalPagesPumpLog }"
                        @click="changePage(currentPage + 1)">
                        Next
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/Auth";
import customFetch from "@/utils/customFetch";

const route = useRoute();
const router = useRouter();
const AuthStore = useAuthStore();
const isLoading = ref(true);
let pollingInterval = null;

const deviceDetail = ref({});
const pumpLogData = ref([]);

// Pagination states
const currentPage = ref(1);
const pageSize = 10;

const totalPagesPumpLog = computed(() => Math.ceil(pumpLogData.value.length / pageSize));
const paginatedPumpLogData = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return pumpLogData.value.slice(start, start + pageSize);
});

// Fetch Device Detail
const fetchDeviceDetail = async () => {
    try {
        const deviceId = route.params.id;
        const response = await customFetch.get(`/device/check-private/${deviceId}`, {
            headers: { Authorization: `Bearer ${AuthStore.tokenUser}` },
        });
        deviceDetail.value = {
            deviceName: response.data.data.device_name,
            deviceType: response.data.data.device_type || "Unknown Type",
            status: response.data.data.status || "Unknown Status",
            location: response.data.data.location || "No Location Set",
            description: response.data.data.description || "No Description",
        };
    } catch (error) {
        console.error("Error fetching device detail:", error);
    }
};

// Fetch Water Pump Log
const fetchPumpLogData = async () => {
    try {
        const deviceId = route.params.id;
        const response = await customFetch.get(`/water-pump/log/${deviceId}`, {
            headers: { Authorization: `Bearer ${AuthStore.tokenUser}` },
        });
        pumpLogData.value = response.data.data;
    } catch (error) {
        pumpLogData.value = []; // Fallback ke array kosong
        console.error("Error fetching water pump log data:", error);
    } finally {
        isLoading.value = false; // Loader selesai
    }
};

const navigateToUpdateForm = () => {
    router.push({ name: "updateDevice", params: { id: route.params.id } });
};

// Change Page
const changePage = (page) => {
    if (page >= 1 && page <= totalPagesPumpLog.value) {
        currentPage.value = page;
    }
};

const formatTimestamp = (timestamp) => {
    if (!timestamp) return "-";
    const date = new Date(timestamp);
    return date.toLocaleString();
};

onMounted(async () => {
    isLoading.value = true; // Set loader aktif saat halaman dimuat
    await fetchDeviceDetail(); // Hanya panggil sekali saat halaman dimuat
    await fetchPumpLogData(); // Jalankan polling hanya untuk log pompa air
    pollingInterval = setInterval(fetchPumpLogData, 10000); // Fetch data setiap 10 detik
});

onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval); // Hentikan polling
        pollingInterval = null; // Pastikan nilai pollingInterval direset
    }
});
</script>