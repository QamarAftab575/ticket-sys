<template>
  <div class="relative w-full">
    <!-- Search Input -->
    <div class="relative">
      <input
        ref="searchInput"
        v-model="searchQuery"
        type="text"
        placeholder="Search"
        class="w-full pl-10 pr-20 py-2 bg-[#1F2021] border border-gray-700 text-gray-200 placeholder-gray-500 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
        @focus="openDropdown"
        @keyup="handleSearch"
      />

      <!-- Search Icon -->
      <svg
        class="absolute left-3 top-2.5 w-4 h-4 text-gray-500 pointer-events-none"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
        />
      </svg>

      <!-- Keyboard Shortcut Badge -->
      <div class="absolute right-3 top-2 flex items-center gap-0.5 px-1.5 py-0.5 bg-[#2D2E2F] border border-gray-700 rounded text-xs text-gray-500 font-medium">
        <span>⌘</span>
        <span>K</span>
      </div>
    </div>

    <!-- Dropdown Content -->
    <div
      v-if="isOpen"
      class="absolute top-full left-0 right-0 mt-2 w-full bg-white border border-gray-200 rounded-lg shadow-2xl z-50 max-h-[600px] overflow-y-auto"
    >
      <!-- Loading State -->
      <div v-if="loading" class="px-4 py-8 text-center text-gray-500">
        <div class="inline-block">
          <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
          </svg>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div v-if="!loading && (results.tasks.length > 0 || results.projects.length > 0)" class="flex gap-2 px-4 py-3 border-b border-gray-100 flex-wrap">
        <button
          @click="activeTab = 'all'"
          :class="[
            'px-4 py-1.5 rounded-full text-sm font-medium transition-all border',
            activeTab === 'all'
              ? 'bg-blue-50 border-blue-200 text-blue-700'
              : 'bg-gray-50 border-gray-200 text-gray-600 hover:border-gray-300'
          ]"
        >
          <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          All
        </button>

        <button
          v-if="results.tasks.length > 0"
          @click="activeTab = 'tasks'"
          :class="[
            'px-4 py-1.5 rounded-full text-sm font-medium transition-all border',
            activeTab === 'tasks'
              ? 'bg-blue-50 border-blue-200 text-blue-700'
              : 'bg-gray-50 border-gray-200 text-gray-600 hover:border-gray-300'
          ]"
        >
          <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
          </svg>
          Tasks
        </button>

        <button
          v-if="results.projects.length > 0"
          @click="activeTab = 'projects'"
          :class="[
            'px-4 py-1.5 rounded-full text-sm font-medium transition-all border',
            activeTab === 'projects'
              ? 'bg-blue-50 border-blue-200 text-blue-700'
              : 'bg-gray-50 border-gray-200 text-gray-600 hover:border-gray-300'
          ]"
        >
          <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
          </svg>
          Projects
        </button>
      </div>

      <!-- Recents Label -->
      <div v-if="!loading && (results.tasks.length > 0 || results.projects.length > 0)" class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
        Recents
      </div>

      <!-- Tasks Section -->
      <div v-if="!loading && (activeTab === 'all' || activeTab === 'tasks') && results.tasks.length > 0">
        <div class="divide-y">
          <div
            v-for="task in results.tasks"
            :key="task.id"
            class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition flex items-start gap-3 group"
            @click="selectTask(task)"
          >
            <!-- Checkbox (completed state) -->
            <div class="w-5 h-5 rounded border-2 flex items-center justify-center mt-0.5 flex-shrink-0 transition-all"
              :class="task.is_completed 
                ? 'bg-green-600 border-green-600' 
                : 'border-gray-300 group-hover:border-green-600'">
              <svg v-if="task.is_completed" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </div>

            <!-- Task Content -->
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 group-hover:text-blue-600"
                :class="task.is_completed ? 'line-through text-gray-500' : ''">
                {{ task.name }}
              </p>
              <p v-if="task.project_name" class="text-xs text-gray-500 mt-0.5">{{ task.project_name }}</p>
            </div>
          </div>
        </div>

        <!-- Load More Button -->
        <div
          v-if="results.has_more_tasks"
          class="px-4 py-3 text-center border-t border-gray-200"
        >
          <button
            @click="loadMore"
            class="text-sm text-blue-600 hover:text-blue-700 font-medium transition"
          >
            Load More
          </button>
        </div>
      </div>

      <!-- Projects Section -->
      <div v-if="!loading && (activeTab === 'all' || activeTab === 'projects') && results.projects.length > 0">
        <div class="divide-y">
          <div
            v-for="project in results.projects"
            :key="project.id"
            class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition flex items-center gap-3 group"
            @click="selectProject(project)"
          >
            <!-- Project Icon/Color -->
            <div
              class="w-5 h-5 rounded flex items-center justify-center flex-shrink-0 text-white font-semibold text-xs"
              :style="{ backgroundColor: project.color || '#9CA3AF' }"
            >
              {{ project.name.charAt(0).toUpperCase() }}
            </div>
            <p class="text-sm font-medium text-gray-900 group-hover:text-blue-600">{{ project.name }}</p>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-if="!loading && results.tasks.length === 0 && results.projects.length === 0"
        class="px-4 py-12 text-center"
      >
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <p class="text-sm text-gray-600 font-medium">
          {{ searchQuery ? 'No tasks or projects found' : 'No recent tasks' }}
        </p>
      </div>
    </div>

    <!-- Overlay to close dropdown -->
    <div
      v-if="isOpen"
      class="fixed inset-0 z-40"
      @click="closeDropdown"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { api } from '@/Services/api'

const searchQuery = ref('')
const searchInput = ref(null)
const isOpen = ref(false)
const loading = ref(false)
const activeTab = ref('all')
const results = ref({
  tasks: [],
  projects: [],
  has_more_tasks: false,
  length: undefined,
})
const currentPage = ref(1)
const allTasks = ref([])

/**
 * Open the dropdown and fetch initial results
 */
const openDropdown = async () => {
  isOpen.value = true
  if (results.value.tasks.length === 0 && results.value.projects.length === 0) {
    await fetchResults()
  }
}

/**
 * Close the dropdown
 */
const closeDropdown = () => {
  isOpen.value = false
}

/**
 * Handle search with debounce
 */
let searchTimeout
const handleSearch = async () => {
  clearTimeout(searchTimeout)
  currentPage.value = 1
  allTasks.value = []
  activeTab.value = 'all'

  searchTimeout = setTimeout(async () => {
    await fetchResults()
  }, 300)
}

/**
 * Fetch search results from API
 */
const fetchResults = async () => {
  loading.value = true
  try {
    const response = await api.get('/search', {
      params: {
        q: searchQuery.value || null,
        page: currentPage.value,
        limit: 10,
      },
    })

    // Axios returns data in response.data
    const apiData = response.data || response

    // Ensure response has the correct structure
    const normalizedResponse = {
      tasks: apiData?.tasks || [],
      projects: apiData?.projects || [],
      has_more_tasks: apiData?.has_more_tasks || false,
    }

    if (currentPage.value === 1) {
      results.value = normalizedResponse
      allTasks.value = normalizedResponse.tasks
    } else {
      // Append tasks for pagination
      results.value.tasks = [...allTasks.value, ...normalizedResponse.tasks]
      allTasks.value = results.value.tasks
      results.value.has_more_tasks = normalizedResponse.has_more_tasks
    }
  } catch (error) {
    console.error('Search error:', error)
    // Set empty results on error to prevent undefined access
    results.value = {
      tasks: [],
      projects: [],
      has_more_tasks: false,
    }
  } finally {
    loading.value = false
  }
}

/**
 * Load more tasks
 */
const loadMore = async () => {
  currentPage.value += 1
  await fetchResults()
}

/**
 * Navigate to task detail
 */
const selectTask = (task) => {
  if (task.project_name) {
    // Extract project ID from the task object if available
    // For now, we'll emit the event and let the parent handle navigation
    window.location.href = `/tasks/${task.id}`
  }
  closeDropdown()
}

/**
 * Navigate to project
 */
const selectProject = (project) => {
  window.location.href = `/projects/${project.id}`
  closeDropdown()
}

/**
 * Handle Cmd+K / Ctrl+K keyboard shortcut
 */
const handleKeyboardShortcut = (e) => {
  // Check for Cmd+K (Mac) or Ctrl+K (Windows/Linux)
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault()
    if (searchInput.value) {
      searchInput.value.focus()
    }
  }
  
  // ESC to close dropdown
  if (e.key === 'Escape' && isOpen.value) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleKeyboardShortcut)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeyboardShortcut)
})
</script>

