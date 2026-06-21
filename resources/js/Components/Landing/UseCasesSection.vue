<script setup>
defineProps({
  useCases: {
    type: Array,
    required: true
  }
});
</script>

<template>
  <!-- Use Cases Section -->
  <section id="use-cases" class="py-24 px-6 bg-white dark:bg-slate-950">
    <div class="max-w-6xl mx-auto">
      <!-- Section header -->
      <div class="text-center mb-16">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-full text-sm font-medium mb-6">
          <span>Use Cases</span>
        </div>
        <h2 class="text-4xl sm:text-5xl font-bold text-slate-900 dark:text-white mb-4 tracking-tight">
          Built for every team
        </h2>
        <p class="text-lg text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
          From engineering to operations, adapt to your unique workflow.
        </p>
      </div>

      <!-- Use cases grid -->
      <div class="grid sm:grid-cols-2 gap-8">
        <div 
          v-for="useCase in useCases" 
          :key="useCase.id" 
          class="group p-8 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 hover:border-blue-300 dark:hover:border-blue-700 hover:shadow-lg hover:shadow-blue-500/5 transition-all duration-200 cursor-pointer"
        >
          <!-- Icon -->
          <div class="w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-950/50 border border-blue-100 dark:border-blue-900/50 flex items-center justify-center mb-6 group-hover:bg-blue-100 dark:group-hover:bg-blue-900/70 transition-colors duration-200">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="useCase.icon" />
            </svg>
          </div>

          <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-3">
            {{ useCase.title }}
          </h3>
          <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
            {{ useCase.description }}
          </p>

          <!-- Benefits -->
          <ul class="space-y-3">
            <li 
              v-for="benefit in useCase.benefits" 
              :key="benefit" 
              class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-400"
            >
              <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span>{{ benefit }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>
</template>
