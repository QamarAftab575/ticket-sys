<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  pricingPlans: {
    type: Array,
    required: true
  }
});
</script>

<template>
  <!-- Pricing Section -->
  <section id="pricing" class="py-24 px-6 bg-white dark:bg-slate-950">
    <div class="max-w-6xl mx-auto">
      <!-- Section header -->
      <div class="text-center mb-16">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-full text-sm font-medium mb-6">
          <span>{{ $t('pricing') }}</span>
        </div>
        <h2 class="text-4xl sm:text-5xl font-bold text-slate-900 dark:text-white mb-4 tracking-tight">
          {{ $t('simple_pricing') }}
        </h2>
        <p class="text-lg text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
          {{ $t('start_free_scale') }}
        </p>
      </div>

      <!-- Pricing cards -->
      <div class="grid md:grid-cols-3 gap-8">
        <div 
          v-for="plan in pricingPlans" 
          :key="plan.id" 
          :class="[
            'rounded-lg p-8 transition-all duration-200',
            plan.highlighted 
              ? 'bg-slate-900 dark:bg-white border-2 border-slate-900 dark:border-white shadow-xl scale-105' 
              : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-lg'
          ]"
        >
          <!-- Plan name -->
          <div class="mb-8">
            <h3 :class="[
              'text-xl font-semibold mb-2',
              plan.highlighted ? 'text-white dark:text-slate-900' : 'text-slate-900 dark:text-white'
            ]">
              {{ plan.name }}
            </h3>
            <p :class="[
              'text-sm',
              plan.highlighted ? 'text-slate-300 dark:text-slate-600' : 'text-slate-600 dark:text-slate-400'
            ]">
              {{ plan.description }}
            </p>
          </div>

          <!-- Price -->
          <div class="mb-8">
            <div class="flex items-baseline gap-2">
              <span :class="[
                'text-5xl font-bold',
                plan.highlighted ? 'text-white dark:text-slate-900' : 'text-slate-900 dark:text-white'
              ]">
                {{ plan.price }}
              </span>
              <span :class="[
                'text-sm',
                plan.highlighted ? 'text-slate-300 dark:text-slate-600' : 'text-slate-600 dark:text-slate-400'
              ]">
                {{ plan.period }}
              </span>
            </div>
          </div>

          <!-- CTA Button -->
          <Link 
            :href="plan.id === 'enterprise' ? '#' : '/register'" 
            :class="[
              'block w-full py-3 px-6 rounded-lg font-medium text-center transition-colors duration-200 cursor-pointer mb-8',
              plan.highlighted 
                ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-800' 
                : 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 hover:bg-slate-800 dark:hover:bg-slate-100'
            ]"
          >
            {{ plan.cta }}
          </Link>

          <!-- Features list -->
          <ul class="space-y-4">
            <li 
              v-for="feature in plan.features" 
              :key="feature" 
              class="flex items-start gap-3"
            >
              <svg 
                :class="[
                  'w-5 h-5 mt-0.5 flex-shrink-0',
                  plan.highlighted ? 'text-blue-400 dark:text-blue-600' : 'text-blue-600 dark:text-blue-400'
                ]" 
                fill="currentColor" 
                viewBox="0 0 20 20"
              >
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span :class="[
                'text-sm',
                plan.highlighted ? 'text-slate-200 dark:text-slate-700' : 'text-slate-600 dark:text-slate-400'
              ]">
                {{ feature }}
              </span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>
</template>
