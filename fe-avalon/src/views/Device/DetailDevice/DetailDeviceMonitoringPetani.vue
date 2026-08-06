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
            <div class="bg-accent text-accent-content border border-neutral shadow-md rounded-lg p-6">
                <h2 class="text-2xl font-semibold mb-4 text-accent-content">Informasi Alat</h2>
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
                            <td class="border px-4 py-2 tabular-nums">{{ data.timestamp }}</td>
                            <td class="border px-4 py-2 tabular-nums">{{ data.temperature }} °C</td>
                            <td class="border px-4 py-2 tabular-nums">{{ data.humidity }} %</td>
                            <td class="border px-4 py-2 tabular-nums">{{ data.soilMoisture }} %</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination Controls -->
                <div class="flex justify-center items-center mt-4 space-x-2">
                    <button class="btn btn-sm btn-neutral"
                        :class="{ 'btn-disabled': totalPages === 0 || currentPage === 1 }"
                        :disabled="totalPages === 0 || currentPage === 1" aria-label="Halaman sebelumnya"
                        @click="changePage(currentPage - 1)">
                        Prev
                    </button>

                    <!-- Tombol Pagination -->
                    <button v-for="page in visiblePages" :key="page"
                        class="btn btn-sm px-3 py-2 text-primary border border-gray-300 rounded"
                        :class="{ 'bg-primary text-white': page === currentPage }"
                        :aria-current="page === currentPage ? 'page' : null" @click="changePage(page)">
                        {{ page }}
                    </button>

                    <button class="btn btn-sm btn-neutral"
                        :class="{ 'btn-disabled': totalPages === 0 || currentPage === totalPages }"
                        :disabled="totalPages === 0 || currentPage === totalPages" aria-label="Halaman berikutnya"
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
const isLoading = ref(true);

const deviceDetail = ref({});
const historyData = ref([]);

// Objek chart dibuat sekali; data dimutasi in-place agar identitas dataset stabil
// (vue-chartjs v5 watch deep pada prop data, sehingga push/splice memicu update tanpa re-create chart)
const createChartData = (label, borderColor, backgroundColor) => ({
    labels: [],
    datasets: [
        {
            label,
            data: [],
            borderColor,
            backgroundColor,
            fill: true,
        },
    ],
});

const temperatureChartData = ref(createChartData("Temperature (°C)", "red", "rgba(255, 99, 132, 0.2)"));
const humidityChartData = ref(createChartData("Humidity (%)", "blue", "rgba(54, 162, 235, 0.2)"));
const soilMoistureChartData = ref(createChartData("Soil Moisture (%)", "green", "rgba(75, 192, 192, 0.2)"));

// Mutasi data chart in-place tanpa mengganti identitas objek
const setChartData = (chartData, labels, values) => {
    chartData.labels.splice(0, chartData.labels.length, ...labels);
    chartData.datasets[0].data.splice(0, chartData.datasets[0].data.length, ...values);
};

// Pagination states
const currentPage = ref(1);
const pageSize = 10;
const totalPages = computed(() => Math.ceil(historyData.value.length / pageSize));

// Fungsi untuk menghitung halaman yang terlihat
const visiblePages = computed(() => {
    const maxVisible = 10; // Jumlah maksimal tombol pagination yang terlihat
    const total = totalPages.value;

    if (total <= maxVisible) {
        return Array.from({ length: total }, (_, i) => i + 1);
    }

    const start = Math.max(currentPage.value - Math.floor(maxVisible / 2), 1);
    const end = Math.min(start + maxVisible - 1, total);

    const adjustedStart = Math.max(end - maxVisible + 1, 1); // Penyesuaian jika halaman terakhir melebihi total
    return Array.from({ length: end - adjustedStart + 1 }, (_, i) => adjustedStart + i);
});

// Data yang dipaginasi
const paginatedHistoryData = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return historyData.value.slice(start, start + pageSize);
});

// Fungsi untuk mengubah halaman
const changePage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

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
        const response = await customFetch.get(`/device/check-private/${deviceId}`);
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
            return false;
        }

        const response = await customFetch.get(`/historical-data/${deviceId}`);
        const data = response.data.data;

        // Cek jika data kosong
        if (!data || data.length === 0) {
            console.warn("Tidak ada data historis untuk perangkat ini.");
            return false; // Keluar dari fungsi
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

        // Data untuk chart (urutan kronologis)
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

        // Update chart in-place (identitas objek dataset dipertahankan)
        setChartData(temperatureChartData.value, timestamps, temperature);
        setChartData(humidityChartData.value, timestamps, humidity);
        setChartData(soilMoistureChartData.value, timestamps, soilMoisture);

        return true;
    } catch (error) {
        // Menangani error spesifik berdasarkan status code
        if (error.response) {
            if (error.response.status === 404) {
                console.warn("Perangkat tidak ditemukan.");
            } else if (error.response.status === 403) {
                return false;
            } else {
                console.error("Terjadi kesalahan lain:", error.response.data.pesan);
            }
        } else {
            console.error("Network Error:", error.message);
        }
        return false;
    } finally {
        isLoading.value = false; // Loader selesai
    }
};

const navigateToUpdateForm = () => {
    router.push({ name: "updateDevice", params: { id: route.params.id } });
};

// ---- Polling dengan visibility pause + exponential backoff ----
// History lengkap di-fetch tiap 30 detik (dikurangi dari 10s) untuk mengurangi beban,
// chart & tabel tetap diperbarui dari data yang sama.
const BASE_INTERVAL = 30000;
const MAX_INTERVAL = 120000;
let pollTimer = null;
let currentInterval = BASE_INTERVAL;
let consecutiveErrors = 0;

const scheduleNextPoll = () => {
    if (pollTimer) clearTimeout(pollTimer);
    // Jangan jadwalkan saat tab tersembunyi (visibilitychange akan menjadwalkan ulang)
    if (document.visibilityState !== "visible") return;
    pollTimer = setTimeout(runPoll, currentInterval);
};

const runPoll = async () => {
    if (document.visibilityState !== "visible") return;

    const success = await fetchHistoricalData();

    // Exponential backoff: interval naik 2x saat error, reset saat sukses
    if (success) {
        consecutiveErrors = 0;
        currentInterval = BASE_INTERVAL;
    } else {
        consecutiveErrors += 1;
        currentInterval = Math.min(BASE_INTERVAL * 2 ** consecutiveErrors, MAX_INTERVAL);
    }

    scheduleNextPoll();
};

const handleVisibilityChange = () => {
    if (document.visibilityState === "visible") {
        // Kembali ke tab: reset backoff dan poll segera
        consecutiveErrors = 0;
        currentInterval = BASE_INTERVAL;
        scheduleNextPoll();
    } else if (pollTimer) {
        clearTimeout(pollTimer);
        pollTimer = null;
    }
};

onMounted(async () => {
    document.addEventListener("visibilitychange", handleVisibilityChange);
    await fetchDeviceDetail();
    await fetchHistoricalData();
    scheduleNextPoll();
});

onUnmounted(() => {
    if (pollTimer) clearTimeout(pollTimer);
    document.removeEventListener("visibilitychange", handleVisibilityChange);
});
</script>
