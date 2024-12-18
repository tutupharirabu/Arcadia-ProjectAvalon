<template>
    <!-- Navbar -->
    <div class="navbar bg-base-200 top-0 z-30 border-b border-neutral px-4 w-full flex justify-between items-center">
        <div class="navbar-start">
            <p class="font-semibold text-xl text-primary truncate">
                Halo {{ currentUser?.name || "Pengguna" }}, Selamat Datang di Dashboard Petani!
            </p>
        </div>
        <div class="navbar-end">
            <button class="btn btn-primary text-primary-content px-4 py-2 rounded-lg" @click="showInputModal = true">
                Tambah Alat +
            </button>
        </div>
    </div>

    <!-- Modal Input Device ID -->
    <div v-if="showInputModal" class="fixed inset-0 flex items-center justify-center z-40 bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm text-center">
            <h3 class="text-lg font-semibold mb-4">Masukkan Device ID</h3>

            <!-- Input Device ID -->
            <div v-if="!isLoading && (!isCameraActive || deviceIdInput)">
                <input v-model="deviceIdInput" :readonly="isFrozen" type="text" placeholder="Device ID"
                    class="input input-bordered w-full mb-4" />
            </div>

            <!-- QR Code Scanner -->
            <StreamQrcodeBarcodeReader v-if="!isLoading" ref="refCamera" capture="shoot" show-on-stream="false"
                @onloading="onLoading" @result="onResult" class="rounded-2xl mb-4">

                <!-- Action Buttons -->
                <template #actions="{ onCanPlay, isReset }">
                    <div class="flex justify-center space-x-2 mb-4">
                        <!-- Tombol Mulai Kamera -->
                        <button v-if="!isCameraActive" class="btn btn-secondary px-4 py-1 rounded-md"
                            @click="startCamera(onCanPlay)">
                            Mulai Kamera
                        </button>

                        <!-- Tombol Reset -->
                        <button v-if="isReset" class="btn btn-neutral px-4 py-1 rounded-md" @click="onReset">
                            Reset
                        </button>

                        <!-- Tombol Kirim -->
                        <button v-if="isCameraActive && deviceIdInput" class="btn btn-primary px-4 py-1 rounded-md"
                            @click="linkDevice">
                            Kirim
                        </button>
                    </div>
                </template>

                <template #action-facemode="{ onChangeFacemode }">
                    <button class="btn btn-primary px-4 py-1 rounded-md" @click="onChangeFacemode"> Change </button>
                </template>

                <!-- Action Stop -->
                <template #action-stop="{ onCanStop }">
                    <button class="btn btn-error px-4 py-1 mr-5 mb-10 rounded-md" @click="handleStop(onCanStop);">
                        Stop
                    </button>
                </template>
            </StreamQrcodeBarcodeReader>

            <!-- Loader -->
            <div v-if="isLoading" class="flex justify-center items-center mb-4">
                <span class="loading loading-spinner loading-lg text-primary"></span>
            </div>

            <!-- Tombol Aksi: Batal dan Kirim -->
            <div v-if="!isLoading && !isCameraActive" class="flex justify-between items-center space-x-2">
                <button @click="closeModal" class="btn btn-neutral flex-1">Batal</button>
                <button @click="linkDevice" class="btn btn-primary flex-1">Kirim</button>
            </div>
        </div>
    </div>

    <!-- Modal Result -->
    <div v-if="showModal" class="fixed inset-0 flex items-center justify-center z-40 bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm text-center">
            <h3 class="text-lg font-semibold mb-4">{{ modalTitle }}</h3>
            <p>{{ modalMessage }}</p>
            <button @click="showModal = false" class="btn btn-primary mt-4">Tutup</button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { StreamQrcodeBarcodeReader } from "vue3-barcode-qrcode-reader";
import { useAuthStore } from "@/stores/Auth";
import customFetch from "@/utils/customFetch";

const authStore = useAuthStore();

const currentUser = computed(() => authStore.currentUser);

const deviceIdInput = ref("");

const showInputModal = ref(false);
const isLoading = ref(false);
const showModal = ref(false);
const modalTitle = ref("");
const modalMessage = ref("");

const isFrozen = ref(false); // Variabel untuk membekukan input
const refCamera = ref(null); // Ref untuk StreamQrcodeBarcodeReader
const isCameraActive = ref(false); // Menandakan apakah kamera sedang aktif atau tidak

// Fungsi untuk menutup modal dan mereset input
function closeModal() {
    showInputModal.value = false; // Tutup modal
    deviceIdInput.value = ""; // Reset input field
}

// Fungsi saat QR Code memberikan hasil
function onResult(data) {
    if (data) {
        console.log("QR Code Result:", data.text);
        deviceIdInput.value = data.text; // Isi input dengan hasil scan
        isFrozen.value = true; // Membekukan input setelah hasil diperoleh
    }
}

// Fungsi saat Mulai Kamera diklik
function startCamera(onCanPlay) {
    isCameraActive.value = true; // Tandai kamera aktif
    onCanPlay(); // Jalankan kamera
}

// Fungsi saat Reset diklik
function onReset() {
    refCamera.value?.onReset();
    isCameraActive.value = false; // Kamera tidak aktif, tampilkan tombol kembali
    isFrozen.value = false; // Hapus kondisi beku
    deviceIdInput.value = ""; // Reset input field
}

// Fungsi saat tombol Stop diklik
function handleStop(onCanStop) {
    // Jalankan fungsi default untuk menghentikan kamera
    onCanStop();

    // Reset state modal
    refCamera.value?.onReset();  // Reset kamera
    isCameraActive.value = false; // Kamera tidak aktif
    deviceIdInput.value = ""; // Kosongkan input field
}

// Fungsi menautkan perangkat
const linkDevice = async () => {
    isLoading.value = true;

    try {
        const userId = authStore.currentUser.id;

        // Validasi input kosong
        if (!deviceIdInput.value) {
            modalTitle.value = "Peringatan";
            modalMessage.value = "Device ID diperlukan!";
            showModal.value = true;
            return;
        }

        // Permintaan ke backend
        const response = await customFetch.post(
            `/device/link/${deviceIdInput.value}`,
            { user_id: userId },
            {
                headers: {
                    Authorization: `Bearer ${authStore.tokenUser}`,
                },
            }
        );

        // Penanganan respons
        if (response.data.status === "success") {
            modalTitle.value = "Berhasil";
            modalMessage.value = "Perangkat berhasil ditautkan!";
        } else {
            modalTitle.value = "Gagal";
            modalMessage.value = response.data.message || "Gagal menautkan perangkat.";
        }
    } catch (error) {
        console.error("Error linking device:", error);
        modalTitle.value = "Error";
        modalMessage.value = "Gagal menautkan perangkat. Silakan coba lagi.";
    } finally {

        // Reset semua state ke keadaan semula
        deviceIdInput.value = ""; // Kosongkan input field
        isLoading.value = false;
        showModal.value = true;
        showInputModal.value = false;

        // Reset inputan dan status kamera
        isFrozen.value = false; // Hapus kondisi beku
        isCameraActive.value = false;  // Nonaktifkan status kamera
        refCamera.value?.onReset();    // Reset kamera jika aktif
    }
};
</script>