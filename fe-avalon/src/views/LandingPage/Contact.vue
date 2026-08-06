<template>
  <div class="container mx-auto py-16 px-4">
    <div class="text-center mb-8">
      <h1 class="text-4xl font-bold mb-4 text-gray-800">Kontak Kami</h1>
      <p class="text-lg text-gray-600">
        Jika ada pertanyaan, silakan hubungi kami melalui email:
        <a href="mailto:arcadiafloratech@gmail.com" class="text-pink-500 hover:underline">
          arcadiafloratech@gmail.com
        </a>
        atau kirim pesan lewat form di bawah.
      </p>
    </div>

    <div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow-md">
      <form @submit.prevent="submitForm">
        <div class="mb-4">
          <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nama</label>
          <input type="text" id="name" v-model="name" autocomplete="name"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            placeholder="Masukkan nama Anda" required>
        </div>

        <div class="mb-4">
          <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
          <input type="email" id="email" v-model="email" autocomplete="email"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            placeholder="Masukkan email Anda" required>
        </div>

        <div class="mb-4">
          <label for="message" class="block text-gray-700 text-sm font-bold mb-2">Pesan</label>
          <textarea id="message" v-model="message" rows="4"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            placeholder="Tulis pesan Anda" required></textarea>
        </div>

        <button type="submit" :disabled="isSubmitting"
          class="w-full bg-pink-500 hover:bg-pink-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline disabled:opacity-50 disabled:cursor-not-allowed">
          <span v-if="isSubmitting">Menyiapkan pesan...</span>
          <span v-else>Kirim</span>
        </button>
      </form>

      <!-- Status sukses -->
      <div v-if="status === 'success'" role="status" aria-live="polite"
        class="mt-4 p-3 bg-green-50 border border-green-300 text-green-800 rounded text-sm">
        Pesan berhasil disiapkan di WhatsApp. Jika jendela tidak terbuka, klik
        <a :href="waUrl" target="_blank" rel="noopener noreferrer" class="underline font-semibold">di sini</a>.
      </div>

      <!-- Status gagal -->
      <div v-if="status === 'error'" role="alert" aria-live="assertive"
        class="mt-4 p-3 bg-red-50 border border-red-300 text-red-800 rounded text-sm">
        Gagal membuka WhatsApp. Silakan coba lagi atau hubungi kami melalui email
        <a href="mailto:arcadiafloratech@gmail.com" class="underline font-semibold">arcadiafloratech@gmail.com</a>.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

// Nomor WhatsApp tim Arcadia Flora Tech (digunakan juga di Footer.vue)
const WA_NUMBER = '6285121072770';

const name = ref('');
const email = ref('');
const message = ref('');
const isSubmitting = ref(false);
const status = ref(null); // null | 'success' | 'error'

const waUrl = computed(() =>
  `https://wa.me/${WA_NUMBER}?text=${encodeURIComponent(
    `Halo Tim Arcadia Flora Tech,\n\nNama: ${name.value}\nEmail: ${email.value}\n\n${message.value}`
  )}`
);

const submitForm = () => {
  // Validasi minimal (atribut required + pola sudah menangani di level input)
  if (!name.value.trim() || !email.value.trim() || !message.value.trim()) {
    status.value = 'error';
    return;
  }

  isSubmitting.value = true;
  status.value = null;

  try {
    // Buka WhatsApp dengan pesan prefilled (tanpa memindahkan halaman)
    const anchor = document.createElement('a');
    anchor.href = waUrl.value;
    anchor.target = '_blank';
    anchor.rel = 'noopener noreferrer';
    anchor.click();

    status.value = 'success';

    // Reset form setelah berhasil
    name.value = '';
    email.value = '';
    message.value = '';
  } catch (error) {
    console.error('Gagal membuka WhatsApp:', error);
    status.value = 'error';
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
.container {
  max-width: 768px;
}

button {
  transition: background-color 0.3s ease;
}

button:hover:not(:disabled) {
  background-color: #e64a9e;
}
</style>
