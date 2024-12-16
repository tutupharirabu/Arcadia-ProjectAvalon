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

            <!-- Bagian Chart -->
            <div class="grid grid-cols-3 gap-6">
                <!-- Temperature Chart -->
                <div class="bg-base-100 border border-neutral shadow-md rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Suhu</h3>
                    <Line :data="temperatureChartData" :options="chartOptions" />
                </div>

                <!-- Humidity Chart -->
                <div class="bg-base-100 border border-neutral shadow-md rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Kelembapan Udara</h3>
                    <Line :data="humidityChartData" :options="chartOptions" />
                </div>

                <!-- Soil Moisture Chart -->
                <div class="bg-base-100 border border-neutral shadow-md rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Kelembapan Tanah</h3>
                    <Line :data="soilMoistureChartData" :options="chartOptions" />
                </div>
            </div>

            <!-- Bagian Tabel Parameter History -->
            <div class="bg-base-100 border border-neutral shadow-md rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">History Data</h3>
                <table class="w-full border-collapse border border-gray-300 text-sm">
                    <thead class="bg-neutral text-neutral-content">
                        <tr>
                            <th class="border px-4 py-2">Timestamp</th>
                            <th class="border px-4 py-2">Suhu</th>
                            <th class="border px-4 py-2">Kelembapan Udara</th>
                            <th class="border px-4 py-2">Kelembapan Tanah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(data, index) in paginatedHistoryData" :key="index">
                            <td class="border px-4 py-2">{{ data.timestamp }}</td>
                            <td class="border px-4 py-2">{{ data.temperature }} °C</td>
                            <td class="border px-4 py-2">{{ data.humidity }} %</td>
                            <td class="border px-4 py-2">{{ data.soilMoisture }} %</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination Controls -->
                <div class="flex justify-center items-center mt-4 space-x-2">
                    <button class="btn btn-sm btn-neutral" :class="{ 'btn-disabled': currentPage === 1 }"
                        @click="changePage(currentPage - 1)">
                        Prev
                    </button>
                    <button v-for="page in totalPages" :key="page" class="btn btn-sm btn-base-200 text-primary"
                        :class="{ 'btn-primary text-primary-content': page === currentPage }" @click="changePage(page)">
                        {{ page }}
                    </button>
                    <button class="btn btn-sm btn-neutral" :class="{ 'btn-disabled': currentPage === totalPages }"
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
import { Line } from "vue-chartjs";
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    CategoryScale,
    LinearScale,
    PointElement,
} from "chart.js";
import customFetch from "@/utils/customFetch";

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement);

const route = useRoute();
const router = useRouter();
const AuthStore = useAuthStore();
const isLoading = ref(true);
let pollingInterval = null;

const deviceDetail = ref({});
const historyData = ref([]);
const temperatureChartData = ref({ labels: [], datasets: [] });
const humidityChartData = ref({ labels: [], datasets: [] });
const soilMoistureChartData = ref({ labels: [], datasets: [] });

// Pagination states
const currentPage = ref(1);
const pageSize = 10;
const totalPages = computed(() => Math.ceil(historyData.value.length / pageSize));
const paginatedHistoryData = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return historyData.value.slice(start, start + pageSize);
});

// Chart Options
const chartOptions = {
    responsive: true,
    plugins: {
        legend: { position: "top" },
        title: { display: true, text: "Sensor Data" },
    },
};

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

// Fetch Historical Data
const fetchHistoricalData = async () => {
    try {
        const deviceId = route.params.id;

        // Cek deviceId
        if (deviceId === undefined || deviceId === null) {
            console.warn("Device ID tidak valid.");
            return;
        }

        const response = await customFetch.get(`/historical-data/${deviceId}`, {
            headers: { Authorization: `Bearer ${AuthStore.tokenUser}` },
        });
        const data = response.data.data;

        // Cek jika data kosong
        if (!data || data.length === 0) {
            console.warn("Tidak ada data historis untuk perangkat ini.");
            return; // Keluar dari fungsi
        }

        // Data untuk tabel (data terbaru di atas)
        historyData.value = data.map(item => ({
            timestamp: new Date(item.created_at).toLocaleString("id-ID", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit"
            }),
            temperature: item.parameters.temperature,
            humidity: item.parameters.humidity,
            soilMoisture: item.parameters.soil_moisture,
        }));

        // Data untuk chart
        const reversedDataForChart = [...data].reverse();

        const timestamps = reversedDataForChart.map(item =>
            new Date(item.created_at).toLocaleString("id-ID", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit"
            })
        );

        const temperature = reversedDataForChart.map(item => item.parameters.temperature);
        const humidity = reversedDataForChart.map(item => item.parameters.humidity);
        const soilMoisture = reversedDataForChart.map(item => item.parameters.soil_moisture);

        // Set chart data
        temperatureChartData.value = {
            labels: timestamps,
            datasets: [
                {
                    label: "Temperature (°C)",
                    data: temperature,
                    borderColor: "red",
                    backgroundColor: "rgba(255, 99, 132, 0.2)",
                    fill: true,
                },
            ],
        };
        humidityChartData.value = {
            labels: timestamps,
            datasets: [
                {
                    label: "Humidity (%)",
                    data: humidity,
                    borderColor: "blue",
                    backgroundColor: "rgba(54, 162, 235, 0.2)",
                    fill: true,
                },
            ],
        };
        soilMoistureChartData.value = {
            labels: timestamps,
            datasets: [
                {
                    label: "Soil Moisture (%)",
                    data: soilMoisture,
                    borderColor: "green",
                    backgroundColor: "rgba(75, 192, 192, 0.2)",
                    fill: true,
                },
            ],
        };
    } catch (error) {
        // Menangani error spesifik berdasarkan status code
        if (error.response) {
            if (error.response.status === 404) {
                console.warn("Perangkat tidak ditemukan.");
            } else if (error.response.status === 403) {
                return;
            } else {
                console.error("Terjadi kesalahan lain:", error.response.data.pesan);
            }
        } else {
            console.error("Network Error:", error.message);
        }
    } finally {
        isLoading.value = false; // Loader selesai
    }
};

const navigateToUpdateForm = () => {
    router.push({ name: "updateDevice", params: { id: route.params.id } });
};

// Change Page
const changePage = page => {
    if (page >= 1 && page <= totalPages.value) currentPage.value = page;
};

onMounted(async () => {
    await fetchDeviceDetail();
    await fetchHistoricalData();
    pollingInterval = setInterval(fetchHistoricalData, 10000);
});

onUnmounted(() => clearInterval(pollingInterval));
</script>