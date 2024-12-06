<template>
  <div class="min-h-screen flex">
    <!-- Sisi Kiri: Form Register -->
    <div class="flex flex-col justify-center items-center w-full lg:w-1/2 bg-white p-8">
      <div class="w-full max-w-md">
        <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Create an account</h2>
        <p class="text-center text-sm text-gray-500 mb-6">
          Already have an account? 
          <a @click="navigateToLogin" class="text-pink-600 font-semibold cursor-pointer">Log in</a>
        </p>

        <form @submit.prevent="handleRegister" class="space-y-4">
          <div class="flex space-x-4">
            <div class="w-1/2">
              <label class="block text-sm font-medium text-gray-700" for="firstName">First Name</label>
              <input 
                id="firstName" 
                type="text" 
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-pink-300 focus:outline-none transition-transform transform focus:scale-105" 
                required 
              />
            </div>
            <div class="w-1/2">
              <label class="block text-sm font-medium text-gray-700" for="lastName">Last Name</label>
              <input 
                id="lastName" 
                type="text" 
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-pink-300 focus:outline-none transition-transform transform focus:scale-105" 
                required 
              />
            </div>
          </div>

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
            Create account
          </button>
        </form>
        <div class="mt-6 flex justify-between items-center text-gray-400 text-sm">
        <hr class="w-40 border-gray-600" />
        <span class="px-2">Or register with</span>
        <hr class="w-40 border-gray-600" />
      </div>

      <div class="mt-4 flex justify-center space-x-4">
        <button class="flex items-center px-4 py-2 bg-white border border-gray-600 rounded hover:bg-gray-700">
          <span class="">Google</span>
        </button>
        <button class="flex items-center px-4 py-2 bg-white border border-gray-600 rounded hover:bg-gray-700">
          <span class="">Apple</span>
        </button>
      </div>
      </div>
    </div>

    

    <!-- Sisi Kanan: Gambar -->
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
  </div>
</template>

<script>
export default {
  methods: {
    handleRegister() {
      alert('Account created!');
    },
    navigateToLogin() {
      this.$router.push('/login');
    },
  },
};
</script>

<style scoped>
body {
  margin: 0;
}
</style>
