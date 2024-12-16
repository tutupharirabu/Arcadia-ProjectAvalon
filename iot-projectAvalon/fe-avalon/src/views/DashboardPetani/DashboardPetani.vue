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
                <ScheduleXCalendar :calendar-app="calendarApp" @event-click="onEventClick" />
            </div>

            <!-- Notification Section (1/4 Lebar) -->
            <div class="col-span-1 p-4 border border-neutral rounded-lg shadow-md">
                <p>POKOKNYA DISINI ADA FITUR NOTIFIKASI</p>
            </div>

            <!-- Loader atau Combined Chart Section (Full Row) -->
            <div class="col-span-4 mt-4 p-4 border border-neutral rounded-lg shadow-lg">
                <div v-if="isLoading" class="w-full text-center">
                    <div class="loading loading-spinner loading-lg"></div>
                    <p class="mt-4 text-gray-600">Mendapatkan Data...</p>
                </div>

                <div v-else>
                    <h3 class="text-center font-bold text-lg mb-4">
                        Data Sensor
                    </h3>
                    <Line :data="JSON.parse(JSON.stringify(combinedChartData))" :options="chartOptions" />
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
const combinedChartData = ref({
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
});

const chartOptions = {
    responsive: true,
    plugins: {
        legend: { position: "top" },
    },
};

// Fungsi menambahkan data ke chart
const appendToCharts = (deviceData) => {
    const timestamp = new Date().toLocaleTimeString("id-ID", { timeZone: "Asia/Jakarta" });

    if (!combinedChartData.value.labels.includes(timestamp)) {
        combinedChartData.value.labels.push(timestamp);

        // Tambahkan data ke setiap dataset
        combinedChartData.value.datasets[0].data.push(parseFloat(deviceData.temperature?.value || 0));
        combinedChartData.value.datasets[1].data.push(parseFloat(deviceData.humidity?.value || 0));
        combinedChartData.value.datasets[2].data.push(parseFloat(deviceData.soilMoisture?.value || 0));
    }

    // Batasi jumlah data maksimum
    const maxDataPoints = 20;
    if (combinedChartData.value.labels.length > maxDataPoints) {
        combinedChartData.value.labels.shift();
        combinedChartData.value.datasets.forEach((dataset) => dataset.data.shift());
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

        for (const device of devices) {
            try {
                const detailResponse = await customFetch.get(
                    `http://localhost:3000/api/dashboard/${device.devices_id}`,
                    { headers: { Authorization: `Bearer ${AuthStore.tokenUser}` } }
                );

                const detailData = detailResponse.data.data;

                const deviceData = {
                    deviceId: device.devices_id,
                    soilMoisture: detailData.soil_moisture,
                    humidity: detailData.humidity,
                    temperature: detailData.temperature,
                };

                // Tambahkan data ke chart
                appendToCharts(deviceData);
            } catch (error) {
                console.error(`Error fetching device ${device.devices_id}:`, error);
            }
        }
    } catch (error) {
        console.error("Error fetching data:", error);
    } finally {
        isFetching = false;
        isLoading.value = false; // Matikan loader setelah data selesai dimuat
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

const onEventClick = (event) => {
    alert(`Event clicked: ${event.title}`);
};
</script>

<style scoped>
.sx-vue-calendar-wrapper {
    height: 600px;
    max-height: 90vh;
}
</style>