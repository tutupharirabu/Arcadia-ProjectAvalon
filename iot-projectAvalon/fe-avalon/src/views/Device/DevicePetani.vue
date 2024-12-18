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
                    <!-- Button Aktif/Nonaktif -->
                    <button :class="[
                        'btn px-4 py-2 rounded-md font-semibold text-white',
                        device.status === 'Active' ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600',
                        loadingStatus[device.deviceId] ? 'opacity-50 cursor-not-allowed' : ''
                    ]" :disabled="loadingStatus[device.deviceId] || isLoading"
                        @click="toggleDevice(device.deviceId, device.status === 'Active' ? 'Inactive' : 'Active')">
                        <span v-if="loadingStatus[device.deviceId]" class="loading loading-spinner"></span>
                        <span v-else>
                            {{ device.status === 'Active' ? 'Nonaktifkan' : 'Aktifkan' }}
                        </span>
                    </button>

                    <!-- Unlink Button -->
                    <button class="btn bg-red-500 text-primary-content py-2 rounded hover:bg-red-600 w-full md:w-auto"
                        :disabled="loadingUnlink[device.deviceId]" @click="unlinkDevice(device.deviceId)">
                        <span v-if="loadingUnlink[device.deviceId]" class="loading loading-spinner"></span>
                        <span v-else>Unlink</span>
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
            <span v-if="modalTitle === 'Proses Berhasil'"
                class="loading loading-spinner loading-lg text-primary mt-4"></span>
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

const AuthStore = useAuthStore();

// State untuk menyimpan daftar perangkat
const devices = ref([]);
const isLoading = ref(true); // Global loader
const loadingStatus = ref({}); // Loader untuk tombol aktif/nonaktif
const loadingUnlink = ref({}); // Loader untuk tombol unlink
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
            modalMessage.value = "Perangkat berhasil diputuskan~";
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

// Fungsi untuk mengubah status perangkat
const toggleDevice = async (deviceId, newStatus) => {
    loadingStatus.value[deviceId] = true; // Aktifkan loader per device
    try {
        // Kirim permintaan update status ke API
        await customFetch.post(
            `/device/${deviceId}?_method=PUT`,
            { status: newStatus },
            { headers: { Authorization: `Bearer ${AuthStore.tokenUser}` } }
        );

        // Tampilkan pesan berhasil
        modalTitle.value = "Berhasil";
        modalMessage.value = `Status perangkat berhasil diubah menjadi ${newStatus === "Active" ? "Aktif" : "Nonaktif"}.`;
        showModal.value = true;

        // Perbarui status di daftar perangkat
        const updatedDevice = devices.value.find((device) => device.deviceId === deviceId);
        if (updatedDevice) updatedDevice.status = newStatus;
    } catch (error) {
        console.error("Error updating device status:", error);
        modalTitle.value = "Error";
        modalMessage.value = "Gagal mengubah status perangkat.";
        showModal.value = true;
    } finally {
        loadingStatus.value[deviceId] = false; // Nonaktifkan loader
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