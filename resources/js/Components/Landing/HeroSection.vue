<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  stats: {
    type: Array,
    required: true
  }
});
</script>

<template>
  <!-- Hero Section - Full width Linear/Notion inspired design -->
  <section class="relative min-h-screen flex items-center justify-center px-8 py-20 bg-white dark:bg-slate-950 overflow-hidden">
    <!-- Subtle gradient background -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-50/30 via-white to-white dark:from-blue-950/10 dark:via-slate-950 dark:to-slate-950 pointer-events-none"></div>
    
    <!-- Grid pattern overlay -->
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzAwMDAwMCIgc3Ryb2tlLW9wYWNpdHk9IjAuMDMiIHN0cm9rZS13aWR0aD0iMSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIvPjwvc3ZnPg==')] opacity-40 dark:opacity-20 pointer-events-none"></div>

    <div class="relative z-10 w-full max-w-7xl mx-auto">
      <!-- Content Section -->
      <div class="text-center mb-16">
        <!-- Small badge -->
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 rounded-full text-sm font-medium mb-8 border border-blue-100 dark:border-blue-900/50">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
          </svg>
          <span>{{ $t('trusted_by_teams') }}</span>
        </div>

        <!-- Main headline -->
        <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold text-slate-900 dark:text-white leading-tight tracking-tight mb-6 max-w-5xl mx-auto">
          {{ $t('build_projects') }}<br class="hidden sm:block" />
          <span class="text-blue-600 dark:text-blue-400">{{ $t('like_linear') }}</span>
        </h1>

        <!-- Subheadline -->
        <p class="text-lg sm:text-xl text-slate-600 dark:text-slate-400 max-w-3xl mx-auto leading-relaxed mb-10">
          {{ $t('project_management_tool') }}
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-10">
          <Link 
            href="/register" 
            class="group px-6 py-3.5 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-medium rounded-lg transition-colors duration-200 cursor-pointer inline-flex items-center gap-2"
          >
            <span>{{ $t('start_building_free') }}</span>
            <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </Link>
          <Link 
            href="/login" 
            class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white font-medium rounded-lg transition-colors duration-200 cursor-pointer"
          >
            {{ $t('view_demo') }}
          </Link>
        </div>

        <!-- Trust indicators -->
        <div class="flex flex-wrap items-center justify-center gap-8 text-sm text-slate-500 dark:text-slate-400">
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>{{ $t('no_credit_card') }}</span>
          </div>
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>{{ $t('free_trial') }}</span>
          </div>
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>{{ $t('cancel_anytime') }}</span>
          </div>
        </div>
      </div>

      <!-- Stats grid -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-8 mb-16 max-w-4xl mx-auto">
        <div v-for="stat in stats" :key="stat.label" class="text-center">
          <div class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white mb-1">{{ stat.value }}</div>
          <div class="text-sm text-slate-500 dark:text-slate-400">{{ stat.label }}</div>
        </div>
      </div>

      <!-- Product preview - Full width mockup -->
      <div class="w-full">
        <div class="relative w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-2xl shadow-blue-500/10 overflow-hidden">
          <!-- Browser chrome -->
          <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900">
            <div class="flex gap-1.5">
              <div class="w-3 h-3 rounded-full bg-slate-300 dark:bg-slate-700"></div>
              <div class="w-3 h-3 rounded-full bg-slate-300 dark:bg-slate-700"></div>
              <div class="w-3 h-3 rounded-full bg-slate-300 dark:bg-slate-700"></div>
            </div>
          </div>
          
          <!-- Full width kanban mockup -->
          <div class="p-8 bg-slate-50 dark:bg-slate-950 min-h-[450px]">
            <div class="grid grid-cols-3 gap-6">
              <!-- Column 1 - To Do -->
              <div class="space-y-4">
                <div class="flex items-center justify-between mb-4">
                  <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-slate-400 dark:bg-slate-600"></div>
                    To Do
                    <span class="text-xs text-slate-500 dark:text-slate-400 bg-slate-200 dark:bg-slate-800 px-2 py-0.5 rounded-full">3</span>
                  </div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-slate-200 dark:border-slate-700 shadow-sm cursor-pointer hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 transition-all duration-200">
                  <div class="flex items-start justify-between mb-3">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">TASK-101</span>
                    <div class="w-2 h-2 rounded-full bg-orange-500"></div>
                  </div>
                  <h4 class="text-sm font-medium text-slate-900 dark:text-white mb-2">Redesign dashboard</h4>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Update the analytics view</p>
                  <div class="flex items-center justify-between">
                    <div class="flex -space-x-2">
                      <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 border-2 border-white dark:border-slate-800"></div>
                      <div class="w-6 h-6 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 border-2 border-white dark:border-slate-800"></div>
                    </div>
                    <span class="text-xs text-slate-400 dark:text-slate-500">Due in 3d</span>
                  </div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-slate-200 dark:border-slate-700 shadow-sm cursor-pointer hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 transition-all duration-200">
                  <div class="flex items-start justify-between mb-3">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">TASK-102</span>
                    <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                  </div>
                  <h4 class="text-sm font-medium text-slate-900 dark:text-white mb-2">API integration</h4>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Connect third-party services</p>
                  <div class="flex items-center justify-between">
                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-green-400 to-green-600 border-2 border-white dark:border-slate-800"></div>
                    <span class="text-xs text-slate-400 dark:text-slate-500">Due in 5d</span>
                  </div>
                </div>
              </div>

              <!-- Column 2 - In Progress -->
              <div class="space-y-4">
                <div class="flex items-center justify-between mb-4">
                  <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                    In Progress
                    <span class="text-xs text-slate-500 dark:text-slate-400 bg-slate-200 dark:bg-slate-800 px-2 py-0.5 rounded-full">2</span>
                  </div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-blue-200 dark:border-blue-800 shadow-sm cursor-pointer hover:shadow-md transition-all duration-200">
                  <div class="flex items-start justify-between mb-3">
                    <span class="text-xs font-medium text-blue-600 dark:text-blue-400">TASK-98</span>
                    <div class="w-2 h-2 rounded-full bg-red-500"></div>
                  </div>
                  <h4 class="text-sm font-medium text-slate-900 dark:text-white mb-2">Bug fix: Login flow</h4>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Fix OAuth redirect issue</p>
                  <div class="flex items-center justify-between">
                    <div class="flex -space-x-2">
                      <div class="w-6 h-6 rounded-full bg-gradient-to-br from-pink-400 to-pink-600 border-2 border-white dark:border-slate-800"></div>
                    </div>
                    <span class="text-xs text-blue-500 dark:text-blue-400 font-medium">In progress</span>
                  </div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-blue-200 dark:border-blue-800 shadow-sm cursor-pointer hover:shadow-md transition-all duration-200">
                  <div class="flex items-start justify-between mb-3">
                    <span class="text-xs font-medium text-blue-600 dark:text-blue-400">TASK-99</span>
                    <div class="w-2 h-2 rounded-full bg-orange-500"></div>
                  </div>
                  <h4 class="text-sm font-medium text-slate-900 dark:text-white mb-2">Mobile optimization</h4>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Improve responsive design</p>
                  <div class="flex items-center justify-between">
                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 border-2 border-white dark:border-slate-800"></div>
                    <span class="text-xs text-blue-500 dark:text-blue-400 font-medium">In progress</span>
                  </div>
                </div>
              </div>

              <!-- Column 3 - Done -->
              <div class="space-y-4">
                <div class="flex items-center justify-between mb-4">
                  <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                    Done
                    <span class="text-xs text-slate-500 dark:text-slate-400 bg-slate-200 dark:bg-slate-800 px-2 py-0.5 rounded-full">4</span>
                  </div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-slate-200 dark:border-slate-700 shadow-sm opacity-75">
                  <div class="flex items-start justify-between mb-3">
                    <span class="text-xs font-medium text-green-600 dark:text-green-400">TASK-95</span>
                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <h4 class="text-sm font-medium text-slate-900 dark:text-white mb-2 line-through">Email notifications</h4>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Setup transactional emails</p>
                  <div class="flex items-center justify-between">
                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-teal-400 to-teal-600 border-2 border-white dark:border-slate-800"></div>
                    <span class="text-xs text-green-500 dark:text-green-400 font-medium">Completed</span>
                  </div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-slate-200 dark:border-slate-700 shadow-sm opacity-75">
                  <div class="flex items-start justify-between mb-3">
                    <span class="text-xs font-medium text-green-600 dark:text-green-400">TASK-96</span>
                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <h4 class="text-sm font-medium text-slate-900 dark:text-white mb-2 line-through">User onboarding</h4>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Create welcome tutorial</p>
                  <div class="flex items-center justify-between">
                    <div class="flex -space-x-2">
                      <div class="w-6 h-6 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 border-2 border-white dark:border-slate-800"></div>
                      <div class="w-6 h-6 rounded-full bg-gradient-to-br from-cyan-400 to-cyan-600 border-2 border-white dark:border-slate-800"></div>
                    </div>
                    <span class="text-xs text-green-500 dark:text-green-400 font-medium">Completed</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Smooth color transitions */
* {
  transition: background-color 200ms ease-out, border-color 200ms ease-out, color 200ms ease-out;
}
</style>
