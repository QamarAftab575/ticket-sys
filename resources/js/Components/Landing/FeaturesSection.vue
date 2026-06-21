<script setup>
defineProps({
  features: {
    type: Array,
    required: true
  }
});

// Determine if a card should be highlighted (cards 1, 3, 5)
const isHighlighted = (index) => {
  return (index + 1) % 2 === 1; // 0, 2, 4 (1st, 3rd, 5th cards)
};
</script>

<style scoped>
/* Google Fonts - Poppins/Open Sans for professional SaaS design */
@import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap');

/* Card hover elevation - no layout shift */
.feature-card {
  transform: translateZ(0);
  backface-visibility: hidden;
}

/* Enhanced glow effect for all cards */
.feature-card-glow {
  position: relative;
  overflow: hidden;
}

.feature-card-glow::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 120px;
  height: 120px;
  border-radius: 50% 0 100% 100%;
  opacity: 0;
  transition: opacity 0.4s ease;
  pointer-events: none;
}

/* Light mode glow - Blue for light cards, enhanced for dark cards */
.feature-card-glow.light-variant::before {
  background: radial-gradient(circle at center, rgba(59, 130, 246, 0.2), transparent 70%);
}

.feature-card-glow.dark-variant::before {
  background: radial-gradient(circle at center, rgba(139, 92, 246, 0.3), transparent 70%);
}

/* Dark mode glow */
:deep(.dark) .feature-card-glow.light-variant::before {
  background: radial-gradient(circle at center, rgba(96, 165, 250, 0.25), transparent 70%);
}

:deep(.dark) .feature-card-glow.dark-variant::before {
  background: radial-gradient(circle at center, rgba(168, 85, 247, 0.35), transparent 70%);
}

/* Hover glow activation */
.feature-card:hover .feature-card-glow::before {
  opacity: 1;
}

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {
  .feature-card,
  .feature-card-glow,
  * {
    transition: none !important;
  }
}
</style>

<template>
  <!-- Features Section - Flat Design with bold colors & dark variants -->
  <section id="features" class="py-24 px-6 bg-slate-50 dark:bg-slate-950">
    <div class="max-w-7xl mx-auto">
      <!-- Section header -->
      <div class="text-center mb-20">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 rounded-full text-sm font-semibold mb-6 border border-blue-100 dark:border-blue-900/50">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z" />
          </svg>
          <span>Features</span>
        </div>
        <h2 class="text-5xl sm:text-6xl font-bold text-slate-900 dark:text-white mb-6 tracking-tight" style="font-family: 'Poppins', sans-serif;">
          Everything you need
        </h2>
        <p class="text-xl text-slate-600 dark:text-slate-400 max-w-3xl mx-auto leading-relaxed" style="font-family: 'Open Sans', sans-serif;">
          Powerful features that adapt to your workflow. Built for speed and simplicity.
        </p>
      </div>

      <!-- Features grid - 3 columns with generous spacing -->
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
        <div 
          v-for="(feature, index) in features" 
          :key="feature.id" 
          class="feature-card group relative p-8 rounded-2xl border-2 transition-all duration-200 cursor-pointer"
          :class="[
            isHighlighted(index)
              ? 'bg-slate-900 dark:bg-slate-800 border-slate-800 dark:border-slate-700 hover:border-purple-500 dark:hover:border-purple-500 shadow-lg'
              : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-blue-500 dark:hover:border-blue-500'
          ]"
        >
          <!-- Glow effect wrapper -->
          <div class="feature-card-glow" :class="isHighlighted(index) ? 'dark-variant' : 'light-variant'"></div>

          <!-- Icon with solid background - Flat Design principle -->
          <div 
            class="relative w-14 h-14 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-200"
            :class="[
              isHighlighted(index)
                ? 'bg-purple-600 dark:bg-purple-700 group-hover:bg-purple-700 dark:group-hover:bg-purple-600'
                : 'bg-blue-500 dark:bg-blue-600 group-hover:bg-blue-600 dark:group-hover:bg-blue-500'
            ]"
          >
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" :d="feature.icon" />
            </svg>
          </div>

          <!-- Content with improved typography -->
          <h3 
            class="text-xl font-semibold mb-3 group-hover:opacity-90 transition-opacity duration-200"
            :class="[
              isHighlighted(index)
                ? 'text-white dark:text-slate-100'
                : 'text-slate-900 dark:text-white'
            ]"
            style="font-family: 'Poppins', sans-serif;"
          >
            {{ feature.title }}
          </h3>
          <p 
            class="text-base leading-relaxed"
            :class="[
              isHighlighted(index)
                ? 'text-slate-300 dark:text-slate-400'
                : 'text-slate-600 dark:text-slate-400'
            ]"
            style="font-family: 'Open Sans', sans-serif;"
          >
            {{ feature.description }}
          </p>

          <!-- Subtle accent line for highlighted cards -->
          <div 
            v-if="isHighlighted(index)"
            class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-600 via-blue-500 to-transparent rounded-b-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"
          ></div>
        </div>
      </div>
    </div>
  </section>
</template>
