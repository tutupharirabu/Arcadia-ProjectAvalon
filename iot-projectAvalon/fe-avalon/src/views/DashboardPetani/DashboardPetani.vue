<template>
    <div class="flex flex-wrap items-center justify-center space-x-4 p-4 mt-12" style="max-width: 1920px;">
        <!-- Add Device Section -->
        <div class=" mt-8 p-4 border border-neutral rounded-lg">
            <p>POKOKNYA DISINI ADA WEATHER APP</p>
        </div>

        <!-- Calendar Section -->
        <div class="w-full mt-8 p-4 flex-grow max-h-128">
            <ScheduleXCalendar :calendar-app="calendarApp" @event-click="onEventClick" />
        </div>

        <!-- Charts Section -->
        <div v-if="tempHumData && HumidityData && soilMoistureData"
            class="w-full mt-8 p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Temperature Chart -->
            <div class="p-4 border border-neutral rounded-lg">
                <h3 class="text-center font-bold text-lg mb-4">Temperature</h3>
                <Line :data="JSON.parse(JSON.stringify(tempHumData))" :options="chartOptions" />
            </div>

            <!-- Humidity Chart -->
            <div class="p-4 border border-neutral rounded-lg">
                <h3 class="text-center font-bold text-lg mb-4">Humidity</h3>
                <Line :data="JSON.parse(JSON.stringify(HumidityData))" :options="chartOptions" />
            </div>

            <!-- Soil Moisture Chart -->
            <div class="p-4 border border-neutral rounded-lg">
                <h3 class="text-center font-bold text-lg mb-4">Soil Moisture</h3>
                <Line :data="JSON.parse(JSON.stringify(soilMoistureData))" :options="chartOptions" />
            </div>
        </div>
        <div v-else class="w-full mt-8 text-center border border-neutral rounded-lg shadow-md p-4">
            <p>Loading charts...</p>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
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

// Registrasi ChartJS
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

const tempHumData = ref({
    labels: [],
    datasets: [
        {
            label: "Temperature (°C)",
            data: [],
            backgroundColor: "rgba(255, 99, 132, 0.2)",
            fill: true,
        },
    ],
});

const HumidityData = ref({
    labels: [],
    datasets: [
        {
            label: "Humidity (%)",
            data: [],
            backgroundColor: "rgba(54, 162, 235, 0.2)",
            fill: true,
        },
    ],
});

const soilMoistureData = ref({
    labels: [],
    datasets: [
        {
            label: "Soil Moisture (%)",
            data: [],
            backgroundColor: "rgba(75, 192, 192, 0.2)",
            fill: true,
        },
    ],
});

const chartOptions = {
    responsive: true,
    plugins: {
        legend: {
            position: "top",
        },
        title: {
            display: true,
            text: "Sensor Data",
        },
    },
};

// Fungsi untuk menambahkan data baru ke grafik
const appendToCharts = (deviceData) => {
    const timestamp = new Date().toLocaleTimeString('id-ID', { timeZone: 'Asia/Jakarta' });

    // Hindari duplikasi data di setiap grafik
    if (!tempHumData.value.labels.includes(timestamp)) {
        // Update Temperature Data
        tempHumData.value.labels.push(timestamp);
        tempHumData.value.datasets[0].data.push(parseFloat(deviceData.temperature?.value || 0));

        // Update Humidity Data
        HumidityData.value.labels.push(timestamp);
        HumidityData.value.datasets[0].data.push(parseFloat(deviceData.humidity?.value || 0));

        // Update Soil Moisture Data
        soilMoistureData.value.labels.push(timestamp);
        soilMoistureData.value.datasets[0].data.push(parseFloat(deviceData.soilMoisture?.value || 0));
    } else {
        console.warn(`Data for timestamp ${timestamp} already exists.`);
    }

    // Batasi jumlah data maksimal di setiap grafik
    const maxDataPoints = 20; // Sesuaikan sesuai kebutuhan

    if (tempHumData.value.labels.length > maxDataPoints) {
        tempHumData.value.labels.shift();
        tempHumData.value.datasets[0].data.shift();
    }

    if (HumidityData.value.labels.length > maxDataPoints) {
        HumidityData.value.labels.shift();
        HumidityData.value.datasets[0].data.shift();
    }

    if (soilMoistureData.value.labels.length > maxDataPoints) {
        soilMoistureData.value.labels.shift();
        soilMoistureData.value.datasets[0].data.shift();
    }
};

// Fungsi untuk fetch data dari API
let isFetching = false;
const fetchData = async () => {
    if (isFetching) {
        console.warn("Fetch already in progress, skipping...");
        return;
    }

    isFetching = true;
    try {
        console.log("Fetching devices...");

        if (!AuthStore.currentUser || !AuthStore.tokenUser) {
            console.error("User or token not found.");
            return;
        }

        const userId = AuthStore.currentUser.id;
        const response = await customFetch.get(`/device/check-by-user/${userId}`, {
            headers: {
                Authorization: `Bearer ${AuthStore.tokenUser}`,
            },
        });

        const devices = response.data.data;
        console.log("Devices fetched:", devices);

        for (const device of devices) {
            try {
                const detailResponse = await axios.get(
                    `http://localhost:3000/api/dashboard/${device.devices_id}`,
                    {
                        headers: {
                            Authorization: `Bearer ${AuthStore.tokenUser}`,
                        },
                    }
                );

                const detailData = detailResponse.data.data;

                const deviceData = {
                    deviceId: device.devices_id,
                    soilMoisture: detailData.soil_moisture,
                    humidity: detailData.humidity,
                    temperature: detailData.temperature,
                };

                // Tambahkan data ke grafik
                appendToCharts(deviceData);
            } catch (error) {
                console.error(`Error fetching device ${device.devices_id}:`, error);
            }
        }
    } catch (error) {
        console.error("Error fetching data:", error);
    } finally {
        isFetching = false;
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