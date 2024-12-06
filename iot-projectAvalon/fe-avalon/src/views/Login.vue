<template>
  <div class="min-h-screen flex">
    <!-- Sisi Kiri: Gambar -->
    <div class="hidden lg:flex flex-col justify-center items-center w-1/2 bg-gradient-to-r from-pink-500 via-pink-600 to-pink-700 text-white p-10">
      <div class="absolute top-6 left-6">
        <h1 class="text-3xl font-bold">Arcadia</h1>
      </div>
      <h2 class="text-4xl font-semibold mb-4">Berkebun Cerdas Menebar Keindahan</h2>
      <p class="text-center">Enhance Your Agricultural Yields with Our Smart Technology.</p>
      <button 
        @click="$router.push('/')" 
        class="absolute top-6 right-6 bg-white text-pink-800 px-4 py-2 rounded-md hover:bg-gray-200 transition"
      >
        Back to website →
      </button>
    </div>

    <!-- Sisi Kanan: Form Login -->
    <div class="flex flex-col justify-center items-center w-full lg:w-1/2 bg-white p-8">
      <div class="w-full max-w-md">
        <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Log in to your account</h2>
        <p class="text-center text-sm text-gray-500 mb-6">
          Don't have an account? 
          <a @click="navigateToRegister" class="text-pink-600 font-semibold cursor-pointer">Create an account</a>
        </p>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700" for="email">Email</label>
            <input 
              id="email" 
              type="email" 
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-pink-300 focus:outline-none transition-transform transform focus:scale-105" 
              required 
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700" for="password">Password</label>
            <input 
              id="password" 
              type="password" 
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-pink-300 focus:outline-none transition-transform transform focus:scale-105" 
              required 
            />
          </div>

          <button 
            type="submit" 
            class="w-full py-3 bg-pink-600 text-white font-bold rounded-lg hover:bg-pink-700 transition-transform transform active:scale-95"
          >
            Log in
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  methods: {
    handleLogin() {
      this.$router.push("/dashboard");
    },
    navigateToRegister() {
      this.$router.push('/register');
    },
  },
};
</script>

<style scoped>
body {
  margin: 0;
}
</style>
