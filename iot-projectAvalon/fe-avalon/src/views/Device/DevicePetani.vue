<template>
    <div class="p-4" style="max-width: 1920px;">
        <!-- Loader -->
        <div v-if="isLoading" class="flex justify-center items-center h-40">
            <span class="loading loading-spinner loading-lg text-primary"></span>
            <p class="ml-2 text-neutral">Menyiapkan Semua Alat...</p>
        </div>

        <!-- Daftar perangkat -->
        <div v-else-if="devices.length > 0" class="grid grid-cols-1 gap-6">
            <div v-for="(device, index) in devices" :key="device.deviceId"
                class="bg-accent text-on-secondary shadow-lg rounded-lg p-4 border border-neutral relative">
                <!-- Informasi Perangkat -->
                <div class="mb-4">
                    <h3 class="text-xl font-semibold text-secondary-content">{{ device.deviceName }}</h3>
                    <p class="text-md"><strong>Type:</strong> {{ device.deviceType }}</p>
                    <p class="text-md">
                        <strong>Status:</strong>
                        {{ device.status === "Active" ? "Aktif" : "Nonaktif" }}
                    </p>
                </div>

                <!-- Kontrol Perangkat -->
                <div class="flex flex-wrap items-center justify-end space-x-2 space-y-2 md:space-y-0">
                    <!-- Toggle Switch -->
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" :checked="device.status === 'Active'"
                            @change="toggleDevice(device.deviceId, device.status === 'Active' ? 'Inactive' : 'Active')"
                            class="sr-only peer" />
                        <div
                            class="w-16 h-8 bg-gray-300 rounded-full peer-focus:outline-none peer-checked:bg-green-500 relative">
                            <div
                                class="absolute top-0.5 left-1 h-7 w-7 bg-white rounded-full transition-transform peer-checked:translate-x-full">
                            </div>
                        </div>
                        <span class="ml-3 text-md font-medium">
                            {{ device.status === "Active" ? "Aktif" : "Nonaktif" }}
                        </span>
                    </label>

                    <!-- Unlink Button -->
                    <button
                        class="btn bg-warning text-warning-content py-2 rounded hover:bg-warning-focus w-full md:w-auto">
                        <span v-if="loadingUnlink[device.deviceId]" class="loading loading-spinner"></span>
                        <span v-else @click="unlinkDevice(device.deviceId)">Unlink</span>
                    </button>

                    <!-- Detail Button -->
                    <button class="btn bg-neutral text-base-100 py-2 rounded hover:bg-primary w-full md:w-auto"
                        @click="goToDetail(device.deviceId)">
                        Detail
                    </button>
                </div>
            </div>
        </div>

        <!-- Pesan jika tidak ada perangkat -->
        <div v-else class="text-center text-neutral mt-6">
            <p>Tidak ada data alat yang ditemukan. Silahkan tambahkan alat untuk memulai.</p>
        </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <!-- Modal Content -->
        <div class="bg-white p-6 rounded-lg shadow-lg text-center max-w-sm">
            <h3 class="text-lg font-semibold mb-4">{{ modalTitle }}</h3>
            <p>{{ modalMessage }}</p>
            <span v-if="modalTitle === 'Proses Berhasil'" class="loading loading-spinner loading-lg text-primary mt-4"></span>
            <!-- Tombol hanya ditampilkan jika ada error -->
            <button v-if="modalTitle === 'Proses Gagal'" @click="showModal = false" class="btn btn-primary mt-4">
                OK
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/Auth";
import customFetch from "@/utils/customFetch";

// State untuk menyimpan daftar perangkat
const devices = ref([]);
const isLoading = ref(true); // Loader state
const loadingUnlink = ref({}); // State loader untuk setiap unlink
const router = useRouter(); // Inisialisasi router

// Modal state
const showModal = ref(false);
const modalTitle = ref("");
const modalMessage = ref("");

// Polling interval state
let pollingInterval = null;

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

        // Tutup modal secara otomatis saat polling berjalan
        if (showModal.value) {
            showModal.value = false; // Modal ditutup otomatis
        }
    } catch (error) {
        showModal.value = true;
        modalTitle.value = "Error";
        modalMessage.value = "Gagal mengambil daftar perangkat.";
    } finally {
        isLoading.value = false; // Matikan loader
    }
};

// Fungsi navigasi ke detail perangkat
const goToDetail = (deviceId) => {
    router.push({ name: "DetailDevice", params: { id: deviceId } });
};

// Fungsi untuk memutuskan perangkat
const unlinkDevice = async (deviceId) => {
    loadingUnlink.value[deviceId] = true; // Aktifkan loader
    try {
        const response = await customFetch.delete(`/device/unlink/${deviceId}`, {
            headers: {
                Authorization: `Bearer ${useAuthStore().tokenUser}`,
            },
        });

        if (response.data.status === "success") {
            modalTitle.value = "Proses Berhasil";
            modalMessage.value = "Perangkat berhasil diputuskan!";
            showModal.value = true; // Tampilkan modal sementara
        } else {
            throw new Error(response.data.message || "Gagal memutuskan perangkat.");
        }
    } catch (error) {
        modalTitle.value = "Proses Gagal";
        modalMessage.value = "Gagal memutuskan perangkat. Silakan coba lagi.";
        showModal.value = true; // Tampilkan modal error
    } finally {
        loadingUnlink.value[deviceId] = false; // Matikan loader
    }
};

// Fungsi polling untuk memperbarui data setiap beberapa detik
const startPolling = () => {
    pollingInterval = setInterval(fetchDevices, 5000); // Refresh setiap 5 detik
};

// Cleanup polling saat komponen di-unmount
onUnmounted(() => {
    if (pollingInterval) clearInterval(pollingInterval);
});

// Fetch perangkat saat komponen dimuat dan mulai polling
onMounted(async () => {
    await fetchDevices();
    startPolling();
});
</script>