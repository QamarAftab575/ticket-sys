<script setup>
defineProps({
  testimonials: {
    type: Array,
    required: true
  }
});
</script>

<template>
  <!-- Testimonials Section -->
  <section id="testimonials" class="py-24 px-6 bg-slate-50 dark:bg-slate-900">
    <div class="max-w-6xl mx-auto">
      <!-- Section header -->
      <div class="text-center mb-16">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-full text-sm font-medium mb-6 border border-slate-200 dark:border-slate-700">
          <span>Testimonials</span>
        </div>
        <h2 class="text-4xl sm:text-5xl font-bold text-slate-900 dark:text-white mb-4 tracking-tight">
          Loved by teams
        </h2>
        <p class="text-lg text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
          See what teams are saying about their workflow transformation.
        </p>
      </div>

      <!-- Testimonials grid -->
      <div class="grid md:grid-cols-3 gap-8">
        <div 
          v-for="testimonial in testimonials" 
          :key="testimonial.id" 
          class="p-8 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-lg transition-all duration-200"
        >
          <!-- Rating stars -->
          <div class="flex gap-1 mb-6">
            <svg 
              v-for="i in testimonial.rating" 
              :key="i" 
              class="w-4 h-4 text-yellow-500" 
              fill="currentColor" 
              viewBox="0 0 20 20"
            >
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
          </div>

          <!-- Testimonial content -->
          <p class="text-slate-600 dark:text-slate-400 mb-6 leading-relaxed">
            "{{ testimonial.content }}"
          </p>

          <!-- Author info -->
          <div class="flex items-center gap-4 pt-6 border-t border-slate-200 dark:border-slate-800">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold text-sm">
              {{ testimonial.avatar }}
            </div>
            <div>
              <div class="font-semibold text-slate-900 dark:text-white">
                {{ testimonial.name }}
              </div>
              <div class="text-sm text-slate-500 dark:text-slate-400">
                {{ testimonial.role }}, {{ testimonial.company }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
