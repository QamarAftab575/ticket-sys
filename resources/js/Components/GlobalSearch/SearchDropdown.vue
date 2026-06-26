<template>
  <!-- Desktop: Inline search bar -->
  <div v-if="!isMobile" class="relative w-full">
    <div class="relative">
      <input
        ref="desktopInput"
        v-model="searchQuery"
        type="text"
        :placeholder="$t ? $t('search') : 'Search'"
        class="w-full pl-10 pr-20 py-2 bg-[#1F2021] border border-gray-700 text-gray-200 placeholder-gray-500 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors duration-200"
        @focus="openDropdown"
        @input="handleSearch"
        @keydown.escape="closeAll"
      />
      <!-- Search icon left -->
      <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
      <!-- Keyboard shortcut badge -->
      <div class="absolute right-3 top-2 flex items-center gap-0.5 px-1.5 py-0.5 bg-[#2D2E2F] border border-gray-700 rounded text-xs text-gray-500 font-medium select-none">
        <span>⌘</span><span>K</span>
      </div>
    </div>

    <!-- Desktop dropdown -->
    <Transition name="dropdown">
      <div
        v-if="isOpen"
        class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-2xl z-50 overflow-hidden"
        style="max-height: 520px;"
      >
        <SearchResults
          :results="results"
          :loading="loading"
          :active-tab="activeTab"
          :search-query="searchQuery"
          @tab-change="activeTab = $event"
          @select-task="selectTask"
          @select-project="selectProject"
          @load-more="loadMore"
        />
      </div>
    </Transition>

    <!-- Desktop backdrop -->
    <div v-if="isOpen" class="fixed inset-0 z-40" @click="closeAll" />
  </div>

  <!-- Mobile: Full-screen overlay (teleported to body) -->
  <Teleport to="body">
    <Transition name="mobile-search">
      <div
        v-if="isMobile && showSearchMobile"
        class="fixed inset-0 z-[9999] flex flex-col bg-[#1A1A1A]"
        role="dialog"
        aria-modal="true"
        aria-label="Search"
      >
        <!-- Mobile search header -->
        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-700 bg-[#2C2C2C]">
          <button
            @click="closeMobileSearch"
            class="flex-shrink-0 p-2 rounded-full text-gray-400 hover:text-white hover:bg-gray-700 transition-colors duration-200 cursor-pointer"
            aria-label="Close search"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>

          <div class="relative flex-1">
            <input
              ref="mobileInput"
              v-model="searchQuery"
              type="search"
              :placeholder="$t ? $t('search') : 'Search tasks, projects...'"
              enterkeyhint="search"
              autocomplete="off"
              autocorrect="off"
              autocapitalize="off"
              spellcheck="false"
              class="w-full pl-10 pr-4 py-2.5 bg-[#1F2021] border border-gray-600 text-gray-100 placeholder-gray-500 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-base transition-colors duration-200"
              @input="handleSearch"
              @keydown.escape="closeMobileSearch"
            />
            <svg class="absolute left-3 top-3 w-4 h-4 text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <!-- Clear button -->
            <button
              v-if="searchQuery"
              @click="clearSearch"
              class="absolute right-3 top-2.5 p-0.5 rounded-full text-gray-400 hover:text-gray-200 transition-colors duration-200 cursor-pointer"
              aria-label="Clear search"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Mobile results (scrollable) -->
        <div class="flex-1 overflow-y-auto bg-white">
          <SearchResults
            :results="results"
            :loading="loading"
            :active-tab="activeTab"
            :search-query="searchQuery"
            :mobile="true"
            @tab-change="activeTab = $event"
            @select-task="selectTask"
            @select-project="selectProject"
            @load-more="loadMore"
          />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { api } from '@/Services/api'
import SearchResults from './SearchResults.vue'

// ─── Emits ───────────────────────────────────────────────────────────────────
const emit = defineEmits(['open-mobile-search'])

// ─── Refs ─────────────────────────────────────────────────────────────────────
const searchQuery    = ref('')
const desktopInput   = ref(null)
const mobileInput    = ref(null)
const isOpen         = ref(false)
const loading        = ref(false)
const activeTab      = ref('all')
const showSearchMobile = ref(false)
const isMobile       = ref(false)

const results = ref({ tasks: [], projects: [], has_more_tasks: false })
const currentPage = ref(1)
const allTasks    = ref([])

// ─── Expose open method so AppHeader can trigger it ──────────────────────────
defineExpose({ openMobileSearch })

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(() => {
  const checkMobile = () => { isMobile.value = window.innerWidth < 768 }
  checkMobile()
  window.addEventListener('resize', checkMobile)
  document.addEventListener('keydown', handleKeyboardShortcut)
})

onUnmounted(() => {
  window.removeEventListener('resize', () => {})
  document.removeEventListener('keydown', handleKeyboardShortcut)
})

// ─── Open / Close ─────────────────────────────────────────────────────────────
async function openMobileSearch() {
  showSearchMobile.value = true
  searchQuery.value = ''
  results.value = { tasks: [], projects: [], has_more_tasks: false }
  activeTab.value = 'all'
  await nextTick()
  // Small delay to ensure the DOM is rendered and keyboard opens
  setTimeout(() => {
    mobileInput.value?.focus()
  }, 80)
  // Pre-load recents
  await fetchResults()
}

function closeMobileSearch() {
  showSearchMobile.value = false
  searchQuery.value = ''
  results.value = { tasks: [], projects: [], has_more_tasks: false }
}

async function openDropdown() {
  isOpen.value = true
  if (results.value.tasks.length === 0 && results.value.projects.length === 0) {
    await fetchResults()
  }
}

function closeAll() {
  isOpen.value = false
  if (isMobile.value) closeMobileSearch()
}

function clearSearch() {
  searchQuery.value = ''
  currentPage.value = 1
  allTasks.value = []
  fetchResults()
  nextTick(() => mobileInput.value?.focus())
}

// ─── Search ───────────────────────────────────────────────────────────────────
let searchTimeout
function handleSearch() {
  clearTimeout(searchTimeout)
  currentPage.value = 1
  allTasks.value = []
  activeTab.value = 'all'
  // Open desktop dropdown on type
  if (!isMobile.value) isOpen.value = true
  searchTimeout = setTimeout(() => fetchResults(), 300)
}

async function fetchResults() {
  loading.value = true
  try {
    const response = await api.get('/search', {
      params: { q: searchQuery.value || null, page: currentPage.value, limit: 10 },
    })
    const apiData = response.data || response
    const normalized = {
      tasks: apiData?.tasks || [],
      projects: apiData?.projects || [],
      has_more_tasks: apiData?.has_more_tasks || false,
    }
    if (currentPage.value === 1) {
      results.value = normalized
      allTasks.value = normalized.tasks
    } else {
      results.value.tasks = [...allTasks.value, ...normalized.tasks]
      allTasks.value = results.value.tasks
      results.value.has_more_tasks = normalized.has_more_tasks
    }
  } catch (e) {
    console.error('Search error:', e)
    results.value = { tasks: [], projects: [], has_more_tasks: false }
  } finally {
    loading.value = false
  }
}

async function loadMore() {
  currentPage.value += 1
  await fetchResults()
}

// ─── Navigation ───────────────────────────────────────────────────────────────
function selectTask(task) {
  window.location.href = `/my-tasks?task=${task.id}`
  closeAll()
}

function selectProject(project) {
  window.location.href = `/projects/${project.id}`
  closeAll()
}

// ─── Keyboard shortcut ────────────────────────────────────────────────────────
function handleKeyboardShortcut(e) {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault()
    if (isMobile.value) {
      openMobileSearch()
    } else {
      desktopInput.value?.focus()
      openDropdown()
    }
  }
  if (e.key === 'Escape') closeAll()
}
</script>

<style scoped>
/* Desktop dropdown slide-in */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* Mobile full-screen slide-up */
.mobile-search-enter-active,
.mobile-search-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.mobile-search-enter-from,
.mobile-search-leave-to {
  opacity: 0;
  transform: translateY(12px);
}
</style>
