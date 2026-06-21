<script setup>
import { onMounted } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import HeroSection from '@/Components/Landing/HeroSection.vue';
import FeaturesSection from '@/Components/Landing/FeaturesSection.vue';
import ViewsSection from '@/Components/Landing/ViewsSection.vue';
import UseCasesSection from '@/Components/Landing/UseCasesSection.vue';
import TestimonialsSection from '@/Components/Landing/TestimonialsSection.vue';
import PricingSection from '@/Components/Landing/PricingSection.vue';
import CTASection from '@/Components/Landing/CTASection.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

// Get data from props (passed from controller)
const views = page.props.views || [];
const features = page.props.features || [];
const useCases = page.props.useCases || [];
const testimonials = page.props.testimonials || [];
const pricingPlans = page.props.pricingPlans || [];
const stats = page.props.stats || [];

// Scroll animation observer
onMounted(() => {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-in');
        }
      });
    },
    {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    }
  );

  document.querySelectorAll('.scroll-animate').forEach((el) => {
    observer.observe(el);
  });
});
</script>

<style scoped>
/* Google Fonts Import - Inter (Professional SaaS design system) */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

/* Typography */
* {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Scroll animations - explicitly named to avoid over-animation */
.scroll-animate {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.5s ease-out, transform 0.5s ease-out;
}

.scroll-animate.animate-in {
  opacity: 1;
  transform: translateY(0);
}

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {
  html {
    scroll-behavior: auto;
  }
  
  .scroll-animate {
    opacity: 1;
    transform: none;
    transition: none;
  }
  
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>

<template>
  <GuestLayout>
    <HeroSection :stats="stats" />
    <FeaturesSection :features="features" class="scroll-animate" />
    <ViewsSection :views="views" class="scroll-animate" />
    <UseCasesSection :useCases="useCases" class="scroll-animate" />
    <TestimonialsSection :testimonials="testimonials" class="scroll-animate" />
    <PricingSection :pricingPlans="pricingPlans" class="scroll-animate" />
    <CTASection class="scroll-animate" />
  </GuestLayout>
</template>

