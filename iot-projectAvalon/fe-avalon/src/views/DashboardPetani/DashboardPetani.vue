<template>
    <div class="flex flex-wrap items-center justify-center space-x-4 p-4" style="max-width: 1920px;">

        <!-- Grid Container -->
        <div class="grid grid-cols-4 gap-4 w-full">

            <!-- Weather App Section (Full Row) -->
            <div class="col-span-4 p-4 border border-neutral rounded-lg shadow-md">
                <p>POKOKNYA DISINI ADA WEATHER APP</p>
            </div>

            <!-- Calendar Section (3/4 Lebar) -->
            <div class="col-span-3 rounded-lg shadow-md">
                <ScheduleXCalendar :calendar-app="calendarApp" />
            </div>

            <!-- Notification Section (1/4 Lebar) -->
            <div class="col-span-1 p-4 border border-neutral rounded-lg shadow-md">
                <p>POKOKNYA DISINI ADA FITUR NOTIFIKASI</p>
            </div>

            <!-- Loader atau Chart Section Dinamis -->
            <div class="col-span-4 mt-4 p-4 border border-neutral rounded-lg shadow-lg">
                <!-- Loader -->
                <div v-if="isLoading" class="w-full text-center">
                    <div class="loading loading-spinner loading-lg"></div>
                    <p class="mt-4 text-gray-600">Mendapatkan Data...</p>
                </div>

                <!-- Charts per Device -->
                <div v-else>
                    <!-- Jika chartsData kosong -->
                    <div v-if="Object.keys(chartsData).length === 0" class="text-center text-neutral">
                        <p class="font-semibold">Tidak ada perangkat aktif yang tersedia.</p>
                        <p>Silakan periksa status perangkat Anda atau tambahkan perangkat baru.</p>
                    </div>

                    <!-- Jika chartsData memiliki data -->
                    <div v-else>
                        <div v-for="(chart, deviceId) in chartsData" :key="deviceId" class="mb-8">
                            <h3 class="text-center font-bold text-lg mb-4">
                                Data Sensor untuk Alat {{ chart.name }}
                            </h3>
                            <Line :data="JSON.parse(JSON.stringify(chart))" :options="chartOptions" />
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import customFetch from '@/utils/customFetch';
import { ref, onMounted, onBeforeUnmount } from "vue";
import { useAuthStore } from '@/stores/Auth';

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
    Filler,
} from "chart.js";

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement, Filler);

import { ScheduleXCalendar } from "@schedule-x/vue";
import '@schedule-x/theme-shadcn/dist/index.css'
import {
    createCalendar,
    createViewMonthGrid,
} from "@schedule-x/calendar";

const calendarApp = createCalendar({
    selectedDate: new Date().toISOString().split("T")[0],
    views: [createViewMonthGrid()],
    events: [
        {
            id: 1,
            title: 'Event 1',
            start: '2024-12-19',
            end: '2024-12-19',
        },
        {
            id: 2,
            title: 'Event 2',
            start: '2024-12-20',
            end: '2024-12-20',
        },
    ],
    theme: 'shadcn',
});

const AuthStore = useAuthStore();
const isLoading = ref(true); // State untuk loader
const chartsData = ref({}); // Menyimpan data untuk setiap device
const devicesInfo = ref([]); // Menyimpan informasi nama perangkat

const chartOptions = {
    responsive: true,
    plugins: {
        legend: { position: "top" },
    },
};

// Fungsi inisialisasi data chart untuk setiap perangkat
const initializeChartForDevice = (deviceId, deviceName) => {
    if (!chartsData.value[deviceId]) {
        chartsData.value[deviceId] = {
            name: deviceName || `Device ${deviceId}`, // Gunakan nama perangkat atau default
            labels: [],
            datasets: [
                {
                    label: "Temperature (°C)",
                    data: [],
                    borderColor: "rgba(255, 99, 132, 1)",
                    backgroundColor: "rgba(255, 99, 132, 0.2)",
                    fill: true,
                },
                {
                    label: "Humidity (%)",
                    data: [],
                    borderColor: "rgba(54, 162, 235, 1)",
                    backgroundColor: "rgba(54, 162, 235, 0.2)",
                    fill: true,
                },
                {
                    label: "Soil Moisture (%)",
                    data: [],
                    borderColor: "rgba(75, 192, 192, 1)",
                    backgroundColor: "rgba(75, 192, 192, 0.2)",
                    fill: true,
                },
            ],
        };
    }
};

// Fungsi menambahkan data ke chart perangkat tertentu
const appendToDeviceChart = (deviceId, deviceData) => {
    const chart = chartsData.value[deviceId];
    const timestamp = new Date().toLocaleTimeString("id-ID", { timeZone: "Asia/Jakarta" });

    if (!chart.labels.includes(timestamp)) {
        chart.labels.push(timestamp);

        chart.datasets[0].data.push(parseFloat(deviceData.temperature));
        chart.datasets[1].data.push(parseFloat(deviceData.humidity));
        chart.datasets[2].data.push(parseFloat(deviceData.soilMoisture));
    }

    // Batasi jumlah data maksimum
    const maxDataPoints = 20;
    if (chart.labels.length > maxDataPoints) {
        chart.labels.shift();
        chart.datasets.forEach((dataset) => dataset.data.shift());
    }
};

// Fungsi untuk fetch data dari API
let isFetching = false;

const fetchData = async () => {
    if (isFetching) return;

    isFetching = true;
    try {
        console.log("Fetching devices...");

        if (!AuthStore.currentUser || !AuthStore.tokenUser) {
            console.error("User or token not found.");
            return;
        }

        const userId = AuthStore.currentUser.id;
        const response = await customFetch.get(`/device/check-by-user/${userId}`, {
            headers: { Authorization: `Bearer ${AuthStore.tokenUser}` },
        });

        const devices = response.data.data;

        // Filter hanya perangkat yang statusnya "Active"
        const activeDevices = devices.filter(device => device.status === "Active");

        // Simpan informasi perangkat aktif
        devicesInfo.value = activeDevices.map(device => ({
            id: device.devices_id,
            name: device.device_name || `Device ${device.devices_id}`,
        }));

        // Loop hanya perangkat yang berstatus "Active"
        for (const device of activeDevices) {
            try {
                const detailResponse = await customFetch.get(
                    `http://localhost:3000/api/dashboard/${device.devices_id}`,
                    { headers: { Authorization: `Bearer ${AuthStore.tokenUser}` } }
                );

                const detailData = detailResponse.data.data;

                // Inisialisasi chart untuk setiap device
                initializeChartForDevice(device.devices_id, device.device_name);

                const deviceData = {
                    temperature: 0,
                    humidity: 0,
                    soilMoisture: 0,
                };

                // Ambil data sensor secara dinamis
                for (const key in detailData) {
                    if (detailData.hasOwnProperty(key) && key.startsWith("proto-one-")) {
                        const [, parameter] = key.split("."); // Extract parameter seperti temperature
                        if (parameter) {
                            // Gunakan bracket notation untuk menangani karakter "-"
                            switch (parameter) {
                                case "temperature":
                                    deviceData.temperature = parseFloat(detailData[key]?.value || 0);
                                    break;
                                case "humidity":
                                    deviceData.humidity = parseFloat(detailData[key]?.value || 0);
                                    break;
                                case "soil-moisture":
                                    deviceData.soilMoisture = parseFloat(detailData[key]?.value || 0);
                                    break;
                            }
                        }
                    }
                }

                // Tambahkan data ke chart per device
                appendToDeviceChart(device.devices_id, deviceData);
            } catch (error) {
                console.error(`Error fetching device ${device.devices_id}:`, error);
            }
        }
    } catch (error) {
        console.error("Error fetching data:", error);
    } finally {
        isFetching = false;
        isLoading.value = false;
    }
};

// Interval fetch
let fetchInterval = null;

onMounted(() => {
    fetchData(); // Initial fetch
    fetchInterval = setInterval(fetchData, 10000); // Fetch every 10 seconds
});

onBeforeUnmount(() => {
    if (fetchInterval) {
        clearInterval(fetchInterval);
    }
});
</script>

<style scoped>
.sx-vue-calendar-wrapper {
    height: 600px;
    max-height: 90vh;
}
</style>