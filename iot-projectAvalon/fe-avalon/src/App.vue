<template>
  <div>
    <HeroSection />
    <AboutSection />
    <FeaturesSection />
    <TeamSection />
    <ContactSection />
  </div>
</template>

<script>
import HeroSection from '@/components/HeroSection.vue';
import AboutSection from '@/components/AboutSection.vue';
import FeaturesSection from '@/components/FeaturesSection.vue';
import TeamSection from '@/components/TeamSection.vue';
import ContactSection from '@/components/ContactSection.vue';

export default {
  components: {
    HeroSection,
    AboutSection,
    FeaturesSection,
    TeamSection,
    // ContactSection,
  },
};
</script>
