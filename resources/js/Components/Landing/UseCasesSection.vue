<script setup>
defineProps({
  useCases: {
    type: Array,
    required: true
  }
});
</script>

<style scoped>
/* Google Fonts - SF Pro style with Inter */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

/* Bento Box Grid - Apple-style modular cards */
.bento-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
  grid-auto-rows: auto;
}

@media (min-width: 768px) {
  .bento-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
  }
}

/* Card hover with subtle scale - Apple style */
.bento-card {
  transform: scale(1);
  backface-visibility: hidden;
  will-change: transform;
}

.bento-card:hover {
  transform: scale(1.02);
}

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {
  .bento-card,
  .bento-card:hover,
  * {
    transform: none !important;
    transition: none !important;
  }
}
</style>

<template>
  <!-- Use Cases Section - Bento Box Grid (Apple-style) -->
  <section id="use-cases" class="py-24 px-6 bg-slate-50 dark:bg-slate-900">
    <div class="max-w-7xl mx-auto">
      <!-- Section header - Minimal Apple style -->
      <div class="text-center mb-16">
        <h2 class="text-5xl sm:text-6xl font-bold text-slate-900 dark:text-white mb-4 tracking-tight leading-tight">
          Built for every team
        </h2>
        <p class="text-xl text-slate-600 dark:text-slate-400 max-w-2xl mx-auto font-medium">
          From engineering to operations, adapt to your unique workflow.
        </p>
      </div>

      <!-- Bento Box Grid -->
      <div class="bento-grid">
        <div 
          v-for="useCase in useCases" 
          :key="useCase.id" 
          class="bento-card group relative p-8 sm:p-10 bg-white dark:bg-slate-950 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 cursor-pointer overflow-hidden"
        >
          <!-- Background gradient on hover -->
          <div class="absolute inset-0 bg-gradient-to-br from-blue-50/50 via-transparent to-purple-50/50 dark:from-blue-950/20 dark:to-purple-950/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          
          <!-- Content -->
          <div class="relative z-10">
            <!-- Icon - Minimal circle design -->
            <div class="w-14 h-14 rounded-2xl bg-slate-900 dark:bg-white flex items-center justify-center mb-6">
              <svg class="w-7 h-7 text-white dark:text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" :d="useCase.icon" />
              </svg>
            </div>

            <!-- Title & Description -->
            <h3 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white mb-3 tracking-tight">
              {{ useCase.title }}
            </h3>
            <p class="text-base text-slate-600 dark:text-slate-400 mb-8 leading-relaxed">
              {{ useCase.description }}
            </p>

            <!-- Benefits - Clean list -->
            <ul class="space-y-3">
              <li 
                v-for="benefit in useCase.benefits" 
                :key="benefit" 
                class="flex items-center gap-3 text-sm text-slate-700 dark:text-slate-300"
              >
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="font-medium">{{ benefit }}</span>
              </li>
            </ul>
          </div>

          <!-- Arrow indicator on hover -->
          <div class="absolute bottom-6 right-6 w-8 h-8 rounded-full bg-slate-900 dark:bg-white flex items-center justify-center opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0 transition-all duration-300">
            <svg class="w-4 h-4 text-white dark:text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
