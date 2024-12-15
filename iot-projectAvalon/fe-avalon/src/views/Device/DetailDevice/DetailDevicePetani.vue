<template>
    <div class="p-6 space-y-12 mt-12" style="max-width: 1920px;">
        <!-- Informasi Perangkat -->
        <div class="bg-accent text-on-secondary shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4 text-secondary-content">Device Information</h2>
            <div class="space-y-2">
                <!-- <p class="text-sm"><strong>Device ID:</strong> {{ deviceDetail.deviceId }}</p> -->
                <p class="text-sm"><strong>Device Name:</strong> {{ deviceDetail.deviceName }}</p>
                <p class="text-sm"><strong>Device Type:</strong> {{ deviceDetail.deviceType }}</p>
                <p class="text-sm"><strong>Status:</strong> {{ deviceDetail.status }}</p>
                <p class="text-sm"><strong>Location:</strong> {{ deviceDetail.location }}</p>
                <p class="text-sm"><strong>Description:</strong> {{ deviceDetail.description }}</p>
                <!-- <p class="text-sm"><strong>User ID:</strong> {{ deviceDetail.userId }}</p> -->
            </div>
        </div>

        <!-- Bagian Chart -->
        <div class="grid grid-cols-3 gap-6">
            <!-- Temperature Chart -->
            <div class="bg-base-100 text-on-surface shadow-md rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Temperature</h3>
                <Line :data="temperatureChartData" :options="chartOptions" />
            </div>

            <!-- Humidity Chart -->
            <div class="bg-base-100 text-on-surface shadow-md rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Humidity</h3>
                <Line :data="humidityChartData" :options="chartOptions" />
            </div>

            <!-- Soil Moisture Chart -->
            <div class="bg-base-100 text-on-surface shadow-md rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Soil Moisture</h3>
                <Line :data="soilMoistureChartData" :options="chartOptions" />
            </div>
        </div>

        <!-- Bagian Tabel Parameter History -->
        <div class="bg-base-100 text-on-surface shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">History Data</h3>
            <table class="w-full border-collapse border border-gray-300 text-sm">
                <thead class="bg-neutral text-neutral-content">
                    <tr>
                        <th class="border border-gray-300 px-4 py-2">Timestamp</th>
                        <th class="border border-gray-300 px-4 py-2">Temperature</th>
                        <th class="border border-gray-300 px-4 py-2">Humidity</th>
                        <th class="border border-gray-300 px-4 py-2">Soil Moisture</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(data, index) in historyData" :key="index">
                        <td class="border border-gray-300 px-4 py-2">{{ data.timestamp }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ data.temperature }} °C</td>
                        <td class="border border-gray-300 px-4 py-2">{{ data.humidity }} %</td>
                        <td class="border border-gray-300 px-4 py-2">{{ data.soilMoisture }} %</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
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

const temperatureChartData = ref({
    labels: [],
    datasets: [
        {
            label: "Temperature (°C)",
            data: [],
            backgroundColor: "rgba(255, 99, 132, 0.2)",
            borderColor: "rgba(255, 99, 132, 1)",
            fill: true,
        },
    ],
});
const humidityChartData = ref({
    labels: [],
    datasets: [
        {
            label: "Humidity (%)",
            data: [],
            backgroundColor: "rgba(54, 162, 235, 0.2)",
            borderColor: "rgba(54, 162, 235, 1)",
            fill: true,
        },
    ],
});
const soilMoistureChartData = ref({
    labels: [],
    datasets: [
        {
            label: "Soil Moisture (%)",
            data: [],
            backgroundColor: "rgba(75, 192, 192, 0.2)",
            borderColor: "rgba(75, 192, 192, 1)",
            fill: true,
        },
    ],
});
const historyData = ref([]);

// Chart Options
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

// State for device detail
const deviceDetail = ref({});
const route = useRoute(); // Get route params

// Fetch device detail on mounted
const fetchDeviceDetail = async () => {
    try {
        const deviceId = route.params.id; // Get ID from route params
        const AuthStore = useAuthStore();
        console.log(`Fetching details for device ID: ${deviceId}`);

        const response = await customFetch.get(`/device/check-private/${deviceId}`, {
            headers: {
                Authorization: `Bearer ${AuthStore.tokenUser}`,
            },
        });

        deviceDetail.value = {
            deviceId: response.data.data.devices_id,
            deviceName: response.data.data.device_name || "Unnamed Device",
            deviceType: response.data.data.device_type || "Unknown Type",
            status: response.data.data.status || "Unknown Status",
            location: response.data.data.location || "No Location Set",
            description: response.data.data.description || "No Description",
            userId: response.data.data.users_id || "Unknown User",
        };

        console.log("Device Detail:", deviceDetail.value);
    } catch (error) {
        console.error("Error fetching device detail:", error);
    }
};

// Load Data on Mount
onMounted(fetchDeviceDetail);
</script>