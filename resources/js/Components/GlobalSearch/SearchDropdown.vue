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
      class="absolute top-full left-0 right-0 mt-2 w-full bg-white border border-gray-200 rounded-lg shadow-lg z-50 max-h-96 overflow-y-auto"
    >
      <!-- Loading State -->
      <div v-if="loading" class="px-4 py-6 text-center text-gray-500">
        <div class="inline-block">
          <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
          </svg>
        </div>
      </div>

      <!-- Recent Tasks Section -->
      <div v-if="!loading && results.tasks.length > 0">
        <div class="px-4 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider bg-gray-50 border-b">
          Recent Tasks
        </div>
        <div class="divide-y">
          <div
            v-for="task in results.tasks"
            :key="task.id"
            class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition"
            @click="selectTask(task)"
          >
            <p class="text-sm font-medium text-gray-900">{{ task.name }}</p>
            <p v-if="task.project_name" class="text-xs text-gray-500">{{ task.project_name }}</p>
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
      <div v-if="!loading && results.projects.length > 0">
        <div class="px-4 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider bg-gray-50 border-t border-b">
          Projects
        </div>
        <div class="divide-y">
          <div
            v-for="project in results.projects"
            :key="project.id"
            class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition flex items-center gap-3"
            @click="selectProject(project)"
          >
            <!-- Project Icon/Color -->
            <div
              v-if="project.icon"
              class="w-5 h-5 flex items-center justify-center text-lg"
            >
              {{ project.icon }}
            </div>
            <div
              v-else
              class="w-5 h-5 rounded"
              :style="{ backgroundColor: project.color || '#999' }"
            />
            <p class="text-sm font-medium text-gray-900">{{ project.name }}</p>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-if="!loading && results.tasks.length === 0 && results.projects.length === 0"
        class="px-4 py-8 text-center text-gray-500"
      >
        <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <p class="text-sm">
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
const results = ref({
  tasks: [],
  projects: [],
  has_more_tasks: false,
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

    if (currentPage.value === 1) {
      results.value = response
      allTasks.value = response.tasks
    } else {
      // Append tasks for pagination
      results.value.tasks = [...allTasks.value, ...response.tasks]
      allTasks.value = results.value.tasks
      results.value.has_more_tasks = response.has_more_tasks
    }
  } catch (error) {
    console.error('Search error:', error)
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

