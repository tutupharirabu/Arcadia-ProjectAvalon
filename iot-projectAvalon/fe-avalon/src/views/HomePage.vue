<template>
  <div>
    <!-- Hero Section -->
    <HeroSection />

    <!-- Other Sections -->
    <AboutSection />
    <!-- <FeaturesSection /> -->
    <TeamSection />
    <!-- <ContactSection /> -->
    <Footer />

    <!-- Logout Button -->
    <!-- <div class="flex justify-end p-6">
      <button
        @click="logout"
        class="px-4 py-2 bg-red-500 text-white font-semibold rounded-lg hover:bg-red-600 transition"
      >
        Logout
      </button>
    </div> -->
  </div>
</template>

<script>
import HeroSection from '@/components/HeroSection.vue';
import AboutSection from '@/components/AboutSection.vue';
import FeaturesSection from '@/components/FeaturesSection.vue';
import TeamSection from '@/components/TeamSection.vue';
import ContactSection from '@/components/ContactSection.vue';
import Footer from '@/components/Footer.vue';

export default {
  components: {
    HeroSection,
    AboutSection,
    // FeaturesSection,
    TeamSection,
    // ContactSection,
    Footer,
  },
  methods: {
    logout() {
      // Hapus status login dari localStorage
      localStorage.removeItem('isLoggedIn');

      // Arahkan ke halaman login
      this.$router.push('/login');
    },
  },
};
</script>
