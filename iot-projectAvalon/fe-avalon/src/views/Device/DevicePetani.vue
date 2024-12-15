<template>
    <div class="p-4 mt-12" style="max-width: 1920px;">
        <!-- Tombol Add Device -->
        <div class="text-right mb-4">
            <button class="bg-primary text-primary-content px-4 py-2 rounded hover:bg-primary-light"
                @click="linkDevice">
                + Add Device
            </button>
        </div>

        <!-- Daftar perangkat -->
        <div v-if="devices.length > 0" class="grid grid-cols-1 gap-6">
            <div v-for="(device, index) in devices" :key="device.deviceId"
                class="bg-accent text-on-secondary shadow-md rounded-lg p-4 border border-outline">
                <!-- Informasi Perangkat -->
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-secondary-content">{{ `ArcadiaFlow ${index +
                        1}` }}</h3>
                    <p class="text-sm">Type: {{ device.deviceType }}</p>
                    <p class="text-sm">Status: {{ device.status }}</p>
                </div>

                <!-- Kontrol Perangkat -->
                <div class="flex space-x-2">
                    <button class="flex-1 bg-primary text-success-content py-2 rounded hover:bg-success-light"
                        @click="toggleDevice(device.deviceId, 'on')">
                        ON / OFF
                    </button>
                    <button class="flex-1 bg-warning text-warning-content py-2 rounded hover:bg-warning-light"
                        @click="unlinkDevice(device.deviceId)">
                        Unlink
                    </button>
                    <button class="flex-1 bg-neutral text-base-100 py-2 rounded hover:bg-warning-light"
                        @click="goToDetail(device.deviceId)">
                        Detail
                    </button>
                </div>
            </div>
        </div>

        <!-- Pesan jika tidak ada perangkat -->
        <div v-else class="text-center text-neutral mt-6">
            <p>No devices found. Add a device to get started.</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/Auth";
import customFetch from "@/utils/customFetch";

// State untuk menyimpan daftar perangkat
const devices = ref([]);
const router = useRouter(); // Inisialisasi router

// Fetch daftar perangkat
const fetchDevices = async () => {
    try {
        const AuthStore = useAuthStore();
        const userId = AuthStore.currentUser.id;

        const response = await customFetch.get(`/device/check-by-user/${userId}`, {
            headers: {
                Authorization: `Bearer ${AuthStore.tokenUser}`,
            },
        });

        devices.value = response.data.data.map((device) => ({
            deviceId: device.devices_id,
            deviceName: device.device_name || "Unnamed Device",
            deviceType: device.device_type || "Unknown Type",
            status: device.status || "Unknown Status",
        }));

        console.log("Devices:", devices.value);
    } catch (error) {
        console.error("Error fetching devices:", error);
    }
};

// Fungsi navigasi ke detail perangkat
const goToDetail = (deviceId) => {
    console.log("Navigating to device detail with ID:", deviceId);
    router.push({ name: "DetailDevice", params: { id: deviceId } });
};

// Fungsi untuk menautkan perangkat baru
const linkDevice = async () => {
    try {
        const AuthStore = useAuthStore();
        const userId = AuthStore.currentUser.id;
        const deviceId = prompt("Enter the Device ID to link:");

        if (!deviceId) {
            alert("Device ID is required!");
            return;
        }

        const response = await customFetch.post(
            `/device/link/${deviceId}`,
            { user_id: userId },
            {
                headers: {
                    Authorization: `Bearer ${AuthStore.tokenUser}`,
                },
            }
        );

        if (response.data.status === "success") {
            alert("Device linked successfully!");
            fetchDevices();
        } else {
            alert(response.data.message || "Failed to link device.");
        }
    } catch (error) {
        console.error("Error linking device:", error);
        alert("Failed to link device. Please try again.");
    }
};

// Fungsi untuk mengontrol perangkat
// const toggleDevice = async (deviceId, action) => {
//     try {
//         const response = await customFetch.post(`/device/${deviceId}/${action}`, {}, {
//             headers: {
//                 Authorization: `Bearer ${useAuthStore().tokenUser}`,
//             },
//         });

//         if (response.data.status === "success") {
//             alert(`Device turned ${action.toUpperCase()} successfully!`);
//             fetchDevices();
//         } else {
//             alert(response.data.message || "Failed to control device.");
//         }
//     } catch (error) {
//         console.error(`Error turning device ${action}:`, error);
//         alert(`Failed to turn device ${action}. Please try again.`);
//     }
// };

// Fungsi untuk memutuskan perangkat
const unlinkDevice = async (deviceId) => {
    try {
        const response = await customFetch.delete(`/device/unlink/${deviceId}`, {
            headers: {
                Authorization: `Bearer ${useAuthStore().tokenUser}`,
            },
        });

        if (response.data.status === "success") {
            alert("Device unlinked successfully!");
            fetchDevices();
        } else {
            alert(response.data.message || "Failed to unlink device.");
        }
    } catch (error) {
        console.error("Error unlinking device:", error);
        alert("Failed to unlink device. Please try again.");
    }
};

// Fetch perangkat saat komponen dimuat
onMounted(fetchDevices);
</script>