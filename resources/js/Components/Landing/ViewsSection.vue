<script setup>
import { ref } from 'vue';

defineProps({
  views: {
    type: Array,
    required: true
  }
});

const activeView = ref('kanban');
</script>

<template>
  <!-- Views Section - Beautiful interactive view switcher -->
  <section id="views" class="py-24 px-8 bg-white dark:bg-slate-950">
    <div class="max-w-7xl mx-auto">
      <!-- Section header -->
      <div class="text-center mb-16">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-full text-sm font-medium mb-6">
          <span>Views</span>
        </div>
        <h2 class="text-4xl sm:text-5xl font-bold text-slate-900 dark:text-white mb-4 tracking-tight">
          See work your way
        </h2>
        <p class="text-lg text-slate-600 dark:text-slate-400 max-w-3xl mx-auto">
          Multiple views for the same data. Switch instantly between perspectives.
        </p>
      </div>

      <!-- View tabs -->
      <div class="flex flex-wrap justify-center gap-2 mb-12">
        <button 
          v-for="view in views" 
          :key="view.id"
          @click="activeView = view.id"
          :class="[
            'px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 cursor-pointer',
            activeView === view.id 
              ? 'bg-blue-600 dark:bg-blue-500 text-white shadow-lg shadow-blue-500/30' 
              : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'
          ]"
        >
          {{ view.title }}
        </button>
      </div>

      <!-- View content card -->
      <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xl shadow-slate-900/5 dark:shadow-black/20 overflow-hidden">
        <!-- View header -->
        <div class="p-8 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50">
          <div class="flex items-start justify-between">
            <div>
              <h3 class="text-2xl font-semibold text-slate-900 dark:text-white mb-2">
                {{ views.find(v => v.id === activeView).title }}
              </h3>
              <p class="text-sm text-slate-600 dark:text-slate-400 max-w-2xl">
                {{ views.find(v => v.id === activeView).description }}
              </p>
            </div>
            <div class="hidden sm:flex items-center gap-2">
              <button class="p-2 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
              </button>
              <button class="p-2 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
              </button>
            </div>
          </div>
        </div>
        
        <!-- View mockup area -->
        <div class="p-8 bg-slate-50 dark:bg-slate-950 min-h-[500px]">
          <!-- Kanban view -->
          <div v-if="activeView === 'kanban'" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div v-for="(col, index) in [
              { name: 'To Do', color: 'slate', count: 5 },
              { name: 'In Progress', color: 'blue', count: 3 },
              { name: 'Done', color: 'green', count: 8 }
            ]" :key="col.name" class="space-y-3">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                  <div :class="[
                    'w-2 h-2 rounded-full',
                    col.color === 'slate' ? 'bg-slate-400' : col.color === 'blue' ? 'bg-blue-500' : 'bg-green-500'
                  ]"></div>
                  <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ col.name }}</span>
                  <span class="text-xs text-slate-500 dark:text-slate-400 bg-slate-200 dark:bg-slate-800 px-2 py-0.5 rounded-full">{{ col.count }}</span>
                </div>
                <button class="p-1 hover:bg-slate-200 dark:hover:bg-slate-800 rounded cursor-pointer transition-colors">
                  <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
              
              <!-- Cards -->
              <div v-for="cardIdx in (index === 2 ? 2 : 3)" :key="cardIdx" 
                :class="[
                  'bg-white dark:bg-slate-800 rounded-lg p-4 border shadow-sm cursor-pointer hover:shadow-md transition-all duration-200',
                  col.color === 'blue' ? 'border-blue-200 dark:border-blue-800' : 'border-slate-200 dark:border-slate-700',
                  col.name === 'Done' ? 'opacity-60' : 'hover:border-blue-300 dark:hover:border-blue-700'
                ]"
              >
                <div class="flex items-start justify-between mb-3">
                  <span class="text-xs font-medium text-slate-500 dark:text-slate-400">TASK-{{ 100 + index * 10 + cardIdx }}</span>
                  <div :class="[
                    'w-2 h-2 rounded-full',
                    cardIdx === 1 ? 'bg-red-500' : cardIdx === 2 ? 'bg-orange-500' : 'bg-yellow-500'
                  ]"></div>
                </div>
                <h4 :class="[
                  'text-sm font-medium mb-2',
                  col.name === 'Done' ? 'text-slate-500 dark:text-slate-400 line-through' : 'text-slate-900 dark:text-white'
                ]">
                  {{ ['Update design system', 'Fix navigation bug', 'Add new feature'][cardIdx - 1] }}
                </h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                  {{ ['Refactor components', 'Mobile view issue', 'User dashboard'][cardIdx - 1] }}
                </p>
                <div class="flex items-center justify-between">
                  <div class="flex -space-x-2">
                    <div v-for="avatar in (cardIdx === 3 ? 2 : 1)" :key="avatar" 
                      :class="[
                        'w-6 h-6 rounded-full border-2 border-white dark:border-slate-800',
                        avatar === 1 ? 'bg-gradient-to-br from-blue-400 to-blue-600' : 'bg-gradient-to-br from-purple-400 to-purple-600'
                      ]"
                    ></div>
                  </div>
                  <span class="text-xs text-slate-400 dark:text-slate-500">
                    {{ col.name === 'Done' ? 'Completed' : `Due ${cardIdx + 1}d` }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- List view -->
          <div v-else-if="activeView === 'list'" class="space-y-2">
            <!-- Header -->
            <div class="grid grid-cols-12 gap-4 px-4 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
              <div class="col-span-1">
                <input type="checkbox" class="rounded border-slate-300 dark:border-slate-700 cursor-pointer" />
              </div>
              <div class="col-span-4">Task</div>
              <div class="col-span-2">Assignee</div>
              <div class="col-span-2">Status</div>
              <div class="col-span-2">Priority</div>
              <div class="col-span-1">Due</div>
            </div>

            <!-- Rows -->
            <div v-for="(item, idx) in 6" :key="idx" 
              class="grid grid-cols-12 gap-4 px-4 py-3 items-center bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer transition-colors duration-200"
            >
              <div class="col-span-1">
                <input type="checkbox" class="rounded border-slate-300 dark:border-slate-700 cursor-pointer" />
              </div>
              <div class="col-span-4">
                <div class="flex items-center gap-2">
                  <span class="text-xs text-slate-500 dark:text-slate-400">TASK-{{ 200 + idx }}</span>
                  <span class="text-sm font-medium text-slate-900 dark:text-white">
                    {{ ['Implement authentication', 'Design landing page', 'Setup CI/CD', 'Write documentation', 'Performance optimization', 'Add unit tests'][idx] }}
                  </span>
                </div>
              </div>
              <div class="col-span-2">
                <div class="flex -space-x-2">
                  <div :class="[
                    'w-7 h-7 rounded-full border-2 border-white dark:border-slate-800',
                    idx % 3 === 0 ? 'bg-gradient-to-br from-blue-400 to-blue-600' : 
                    idx % 3 === 1 ? 'bg-gradient-to-br from-green-400 to-green-600' : 
                    'bg-gradient-to-br from-purple-400 to-purple-600'
                  ]"></div>
                  <div v-if="idx % 2 === 0" class="w-7 h-7 rounded-full bg-gradient-to-br from-pink-400 to-pink-600 border-2 border-white dark:border-slate-800"></div>
                </div>
              </div>
              <div class="col-span-2">
                <span :class="[
                  'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium',
                  idx % 3 === 0 ? 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300' :
                  idx % 3 === 1 ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300' :
                  'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'
                ]">
                  <div :class="[
                    'w-1.5 h-1.5 rounded-full',
                    idx % 3 === 0 ? 'bg-blue-500' : idx % 3 === 1 ? 'bg-green-500' : 'bg-slate-400'
                  ]"></div>
                  {{ idx % 3 === 0 ? 'In Progress' : idx % 3 === 1 ? 'Done' : 'To Do' }}
                </span>
              </div>
              <div class="col-span-2">
                <span :class="[
                  'inline-flex px-2.5 py-1 rounded text-xs font-medium',
                  idx % 4 === 0 ? 'bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300' :
                  idx % 4 === 1 ? 'bg-orange-100 dark:bg-orange-950 text-orange-700 dark:text-orange-300' :
                  idx % 4 === 2 ? 'bg-yellow-100 dark:bg-yellow-950 text-yellow-700 dark:text-yellow-300' :
                  'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'
                ]">
                  {{ idx % 4 === 0 ? 'Urgent' : idx % 4 === 1 ? 'High' : idx % 4 === 2 ? 'Medium' : 'Low' }}
                </span>
              </div>
              <div class="col-span-1">
                <span class="text-xs text-slate-500 dark:text-slate-400">
                  {{ idx + 2 }}d
                </span>
              </div>
            </div>
          </div>
          
          <!-- Timeline view -->
          <div v-else-if="activeView === 'timeline'" class="space-y-1">
            <!-- Timeline header -->
            <div class="flex items-center gap-4 mb-6 px-4">
              <div class="w-32 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Task</div>
              <div class="flex-1 grid grid-cols-7 gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400 text-center">
                <div v-for="day in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']" :key="day">{{ day }}</div>
              </div>
            </div>

            <!-- Timeline rows -->
            <div v-for="(task, idx) in 6" :key="idx" class="flex items-center gap-4 p-4 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 hover:border-blue-300 dark:hover:border-blue-700 cursor-pointer transition-all duration-200">
              <div class="w-32">
                <div class="flex items-center gap-2">
                  <div :class="[
                    'w-2 h-2 rounded-full',
                    idx % 3 === 0 ? 'bg-blue-500' : idx % 3 === 1 ? 'bg-green-500' : 'bg-orange-500'
                  ]"></div>
                  <span class="text-sm font-medium text-slate-900 dark:text-white truncate">
                    Task {{ idx + 1 }}
                  </span>
                </div>
              </div>
              <div class="flex-1 relative h-10">
                <div class="absolute inset-0 grid grid-cols-7 gap-2">
                  <div v-for="day in 7" :key="day" class="bg-slate-100 dark:bg-slate-900 rounded"></div>
                </div>
                <div 
                  class="absolute h-8 rounded-lg shadow-md flex items-center justify-between px-3 cursor-pointer hover:shadow-lg transition-all duration-200"
                  :class="[
                    idx % 3 === 0 ? 'bg-gradient-to-r from-blue-500 to-blue-600' :
                    idx % 3 === 1 ? 'bg-gradient-to-r from-green-500 to-green-600' :
                    'bg-gradient-to-r from-orange-500 to-orange-600'
                  ]"
                  :style="`left: ${(idx % 4) * 14}%; width: ${30 + (idx % 3) * 10}%; top: 4px;`"
                >
                  <span class="text-xs font-medium text-white">{{ 2 + idx }} days</span>
                  <div class="flex -space-x-1">
                    <div class="w-5 h-5 rounded-full bg-white/30 border border-white/50"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Calendar view -->
          <div v-else class="space-y-4">
            <!-- Calendar header -->
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">June 2026</h3>
              <div class="flex items-center gap-2">
                <button class="p-2 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer">
                  <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                  </svg>
                </button>
                <button class="p-2 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer">
                  <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Calendar grid -->
            <div class="grid grid-cols-7 gap-2">
              <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day" 
                class="text-center text-xs font-semibold text-slate-500 dark:text-slate-400 py-2"
              >
                {{ day }}
              </div>
              <div v-for="date in 35" :key="date" 
                class="aspect-square bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-2 hover:border-blue-300 dark:hover:border-blue-700 cursor-pointer transition-all duration-200"
              >
                <div class="text-sm font-medium text-slate-900 dark:text-white mb-1">
                  {{ date <= 30 ? date : date - 30 }}
                </div>
                <div v-if="date % 5 === 0" class="space-y-1">
                  <div class="w-full h-1 bg-blue-500 rounded"></div>
                  <div v-if="date % 7 === 0" class="w-full h-1 bg-green-500 rounded"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
