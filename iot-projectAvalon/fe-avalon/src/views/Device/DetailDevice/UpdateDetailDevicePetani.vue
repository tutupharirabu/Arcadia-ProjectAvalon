<template>
    <div class="max-w-2xl mx-auto p-6 bg-white border border-neutral shadow-lg rounded-lg">
        <!-- Loader -->
        <div v-if="isLoading" class="flex justify-center">
            <span class="loading loading-spinner loading-lg text-primary"></span>
        </div>

        <!-- Form Update -->
        <div v-else>
            <h2 class="text-2xl font-semibold mb-6">Update Informasi Alat</h2>
            <form @submit.prevent="submitForm">
                <!-- Nama Alat -->
                <div class="mb-4">
                    <label for="deviceName" class="block font-medium mb-2">Nama Alat</label>
                    <input v-model="formData.deviceName" type="text" id="deviceName" class="input input-bordered w-full"
                        placeholder="Masukkan nama alat" required />
                </div>

                <!-- Status Toggle -->
                <div class="mb-4">
                    <label for="deviceStatus" class="block font-medium mb-2">Status</label>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="formData.deviceStatus" class="sr-only peer" />
                            <div
                                class="w-16 h-8 bg-gray-300 rounded-full peer-focus:outline-none peer-checked:bg-green-500 peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-1 after:bg-white after:rounded-full after:h-7 after:w-7 after:transition-all">
                            </div>
                            <span class="ml-3 text-md font-medium">{{ formData.deviceStatus ? "Aktif" : "Nonaktif"
                                }}</span>
                        </label>
                    </div>
                </div>

                <!-- Lokasi -->
                <div class="mb-4">
                    <label for="location" class="block font-medium mb-2">Lokasi</label>
                    <div class="flex space-x-2">
                        <input v-model="formData.location" type="text" id="location" class="input input-bordered w-full"
                            placeholder="Lokasi alat" readonly />
                        <button type="button" @click="fetchCurrentLocation"
                            class="btn btn-neutral px-4 py-2 rounded-lg">
                            Ambil Lokasi Sekarang
                        </button>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div class="mb-6">
                    <label for="description" class="block font-medium mb-2">Deskripsi</label>
                    <textarea v-model="formData.description" id="description" rows="4"
                        class="textarea textarea-bordered w-full" placeholder="Masukkan deskripsi alat"></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary px-6 py-2" :disabled="isSubmitting">
                        <span v-if="isSubmitting" class="loading loading-spinner"></span>
                        <span v-else>Update</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm text-center">
            <h3 class="text-lg font-semibold mb-4">{{ modalTitle }}</h3>
            <p>{{ modalMessage }}</p>
            <button @click="showModal = false" class="btn btn-primary mt-4">OK</button>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/Auth";
import customFetch from "@/utils/customFetch";

const route = useRoute();
const router = useRouter();
const isLoading = ref(true);
const isSubmitting = ref(false);
const showModal = ref(false);
const modalTitle = ref("");
const modalMessage = ref("");
const AuthStore = useAuthStore();

// Data Form
const formData = ref({
    deviceName: "",
    deviceStatus: false, // Boolean for toggle ON/OFF
    location: "",
    description: "",
});

// Ambil Detail Alat
const fetchDeviceDetail = async () => {
    const deviceId = route.params.id;
    try {
        const response = await customFetch.get(`/device/check-private/${deviceId}`, {
            headers: { Authorization: `Bearer ${AuthStore.tokenUser}` },
        });
        const data = response.data.data;

        formData.value = {
            deviceName: data.device_name || "",
            deviceStatus: data.status === "Active" || data.status === "Aktif",
            location: data.location || "",
            description: data.description || "",
        };
    } catch (error) {
        console.error("Error fetching device data:", error);
        modalTitle.value = "Error";
        modalMessage.value = "Gagal mengambil data alat!";
        showModal.value = true;
    } finally {
        isLoading.value = false;
    }
};

// Submit Update
const submitForm = async () => {
    isSubmitting.value = true;
    const deviceId = route.params.id;

    // Konversi nilai boolean menjadi 'Aktif' atau 'Nonaktif'
    const statusValue = formData.value.deviceStatus ? "Active" : "Inactive";

    try {
        await customFetch.post(
            `/device/${deviceId}?_method=PUT`,
            {
                device_name: formData.value.deviceName,
                status: statusValue,
                location: formData.value.location,
                description: formData.value.description,
            },
            {
                headers: { Authorization: `Bearer ${AuthStore.tokenUser}` },
            }
        );

        modalTitle.value = "Berhasil";
        modalMessage.value = "Informasi alat berhasil diperbarui!";
        showModal.value = true;

        setTimeout(() => {
            router.push({ name: "DetailDevice", params: { id: route.params.id } });
        }, 1500);
    } catch (error) {
        console.error("Error updating device data:", error);
        modalTitle.value = "Error";
        modalMessage.value = "Gagal memperbarui informasi alat!";
        showModal.value = true;
    } finally {
        isSubmitting.value = false;
    }
};

// Ambil Lokasi Sekarang
const fetchCurrentLocation = () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                formData.value.location = `${position.coords.latitude}, ${position.coords.longitude}`;
            },
            () => {
                modalTitle.value = "Error";
                modalMessage.value = "Gagal mengambil lokasi!";
                showModal.value = true;
            }
        );
    } else {
        modalTitle.value = "Error";
        modalMessage.value = "Geolocation tidak didukung di browser ini.";
        showModal.value = true;
    }
};

fetchDeviceDetail();
</script>

<style scoped>
.loading {
    display: inline-block;
}
</style>