<template>
  <div>
    <HeroSection />
    <AboutSection />
    <FeaturesSection />
    <TeamSection />
    <Footer />
    <ContactSection />
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
    Footer
    // ContactSection,
  },
};
</script>
