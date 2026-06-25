<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <!-- CTA Section - Final call to action -->
  <section class="relative py-24 px-6 bg-slate-900 dark:bg-white overflow-hidden">
    <!-- Subtle gradient overlay -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900/20 to-slate-900 dark:from-blue-50 dark:to-white pointer-events-none"></div>

    <!-- Grid pattern overlay -->
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2ZmZmZmZiIgc3Ryb2tlLW9wYWNpdHk9IjAuMDMiIHN0cm9rZS13aWR0aD0iMSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIvPjwvc3ZnPg==')] opacity-40 pointer-events-none"></div>

    <div class="relative z-10 max-w-4xl mx-auto text-center">
      <!-- Main headline -->
      <h2 class="text-4xl sm:text-5xl font-bold text-white dark:text-slate-900 mb-6 tracking-tight">
        {{ $t('ready_to_transform') }}
      </h2>
      
      <!-- Subheadline -->
      <p class="text-lg text-slate-300 dark:text-slate-600 mb-12 max-w-2xl mx-auto leading-relaxed">
        {{ $t('no_credit_card_required') }}
      </p>

      <!-- CTA Buttons -->
      <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-8">
        <Link 
          href="/register" 
          class="group px-6 py-3.5 bg-white hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-900 dark:text-white font-medium rounded-lg transition-colors duration-200 cursor-pointer inline-flex items-center gap-2"
        >
          <span>{{ $t('start_free_trial') }}</span>
          <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </Link>
        <Link 
          href="/login" 
          class="px-6 py-3.5 bg-slate-800 hover:bg-slate-700 dark:bg-slate-100 dark:hover:bg-slate-200 text-white dark:text-slate-900 font-medium rounded-lg border border-slate-700 dark:border-slate-300 transition-colors duration-200 cursor-pointer"
        >
          {{ $t('request_demo') }}
        </Link>
      </div>

      <!-- Trust indicators -->
      <p class="text-sm text-slate-400 dark:text-slate-600">
        {{ $t('trusted_by_developers') }}
      </p>
    </div>
  </section>
</template>
