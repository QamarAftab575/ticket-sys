<script setup>
defineProps({
  features: {
    type: Array,
    required: true
  }
});
</script>

<template>
  <!-- Features Section - Clean minimal design -->
  <section id="features" class="py-24 px-6 bg-white dark:bg-slate-950">
    <div class="max-w-6xl mx-auto">
      <!-- Section header -->
      <div class="text-center mb-20">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-full text-sm font-medium mb-6">
          <span>Features</span>
        </div>
        <h2 class="text-4xl sm:text-5xl font-bold text-slate-900 dark:text-white mb-4 tracking-tight">
          Everything you need
        </h2>
        <p class="text-lg text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
          Powerful features that adapt to your workflow. Built for speed and simplicity.
        </p>
      </div>

      <!-- Features grid -->
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <div 
          v-for="feature in features" 
          :key="feature.id" 
          class="group relative p-8 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 hover:border-blue-300 dark:hover:border-blue-700 hover:shadow-lg hover:shadow-blue-500/5 transition-all duration-200 cursor-pointer"
        >
          <!-- Icon -->
          <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-950/50 border border-blue-100 dark:border-blue-900/50 flex items-center justify-center mb-5 group-hover:bg-blue-100 dark:group-hover:bg-blue-900/70 group-hover:border-blue-200 dark:group-hover:border-blue-800 transition-colors duration-200">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.icon" />
            </svg>
          </div>

          <!-- Content -->
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
            {{ feature.title }}
          </h3>
          <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
            {{ feature.description }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
