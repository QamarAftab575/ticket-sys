<template>
  <div>
    <!-- Loading -->
    <div v-if="loading" class="px-4 py-10 flex justify-center">
      <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
      </svg>
    </div>

    <!-- Has results -->
    <template v-else-if="hasResults">
      <!-- Filter tabs -->
      <div class="flex gap-2 px-4 py-3 border-b border-gray-100 overflow-x-auto scrollbar-none">
        <button
          @click="$emit('tab-change', 'all')"
          :class="tabClass(activeTab === 'all')"
          class="cursor-pointer"
        >
          All
        </button>
        <button
          v-if="results.tasks.length > 0"
          @click="$emit('tab-change', 'tasks')"
          :class="tabClass(activeTab === 'tasks')"
          class="cursor-pointer"
        >
          <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
          </svg>
          Tasks
          <span class="ml-1 text-xs bg-gray-100 text-gray-600 rounded-full px-1.5 py-0.5 font-medium">{{ results.tasks.length }}</span>
        </button>
        <button
          v-if="results.projects.length > 0"
          @click="$emit('tab-change', 'projects')"
          :class="tabClass(activeTab === 'projects')"
          class="cursor-pointer"
        >
          <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
          </svg>
          Projects
          <span class="ml-1 text-xs bg-gray-100 text-gray-600 rounded-full px-1.5 py-0.5 font-medium">{{ results.projects.length }}</span>
        </button>
      </div>

      <!-- Section label -->
      <div class="px-4 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">
        {{ searchQuery ? 'Results' : 'Recent' }}
      </div>

      <!-- Tasks -->
      <div v-if="showTasks" class="divide-y divide-gray-50">
        <button
          v-for="task in results.tasks"
          :key="task.id"
          class="w-full px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors duration-150 flex items-start gap-3 group text-left"
          @click="$emit('select-task', task)"
        >
          <!-- Checkbox -->
          <div
            class="w-5 h-5 rounded border-2 flex items-center justify-center mt-0.5 flex-shrink-0 transition-colors duration-150"
            :class="task.is_completed ? 'bg-green-500 border-green-500' : 'border-gray-300 group-hover:border-green-400'"
          >
            <svg v-if="task.is_completed" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </div>
          <!-- Text -->
          <div class="flex-1 min-w-0">
            <p
              class="text-sm font-medium text-gray-900 group-hover:text-blue-600 truncate transition-colors duration-150"
              :class="{ 'line-through text-gray-400': task.is_completed }"
            >{{ task.name }}</p>
            <p v-if="task.project_name" class="text-xs text-gray-400 mt-0.5 truncate">{{ task.project_name }}</p>
          </div>
          <!-- Chevron -->
          <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-400 flex-shrink-0 mt-0.5 transition-colors duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>

        <!-- Load more -->
        <div v-if="results.has_more_tasks && (activeTab === 'all' || activeTab === 'tasks')" class="px-4 py-3 border-t border-gray-100 text-center">
          <button
            @click="$emit('load-more')"
            class="text-sm text-blue-600 hover:text-blue-700 font-medium transition-colors duration-150 cursor-pointer"
          >
            Load more tasks
          </button>
        </div>
      </div>

      <!-- Projects -->
      <div v-if="showProjects" class="divide-y divide-gray-50">
        <button
          v-for="project in results.projects"
          :key="project.id"
          class="w-full px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors duration-150 flex items-center gap-3 group text-left"
          @click="$emit('select-project', project)"
        >
          <!-- Color dot -->
          <div
            class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 text-white font-semibold text-xs"
            :style="{ backgroundColor: project.color || '#6B7280' }"
          >
            {{ project.name.charAt(0).toUpperCase() }}
          </div>
          <p class="flex-1 text-sm font-medium text-gray-900 group-hover:text-blue-600 truncate transition-colors duration-150">
            {{ project.name }}
          </p>
          <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-400 flex-shrink-0 transition-colors duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </template>

    <!-- Empty state -->
    <div v-else class="px-4 py-14 flex flex-col items-center gap-3 text-center">
      <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
      <p class="text-sm font-medium text-gray-700">
        {{ searchQuery ? 'No results found' : 'No recent items' }}
      </p>
      <p v-if="searchQuery" class="text-xs text-gray-400">
        Try searching with different keywords
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  results:     { type: Object,  required: true },
  loading:     { type: Boolean, default: false },
  activeTab:   { type: String,  default: 'all' },
  searchQuery: { type: String,  default: '' },
  mobile:      { type: Boolean, default: false },
})

defineEmits(['tab-change', 'select-task', 'select-project', 'load-more'])

const hasResults = computed(() =>
  props.results.tasks.length > 0 || props.results.projects.length > 0
)

const showTasks = computed(() =>
  (props.activeTab === 'all' || props.activeTab === 'tasks') && props.results.tasks.length > 0
)

const showProjects = computed(() =>
  (props.activeTab === 'all' || props.activeTab === 'projects') && props.results.projects.length > 0
)

function tabClass(active) {
  return [
    'flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-150 border whitespace-nowrap flex-shrink-0',
    active
      ? 'bg-blue-50 border-blue-200 text-blue-700'
      : 'bg-gray-50 border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-100',
  ]
}
</script>

<style scoped>
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
</style>
