<template>
  <div class="bg-white rounded-lg shadow">
    <!-- Header -->
    <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-sm font-semibold">
          {{ userInitials }}
        </div>
        <div>
          <h2 class="text-lg font-semibold text-gray-900">My tasks</h2>
          <p class="text-xs text-gray-500 mt-0.5">{{ taskStats }}</p>
        </div>
      </div>
      <button class="text-gray-400 hover:text-gray-600 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 12a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
        </svg>
      </button>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 px-6">
      <div class="flex gap-8">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="selectTab(tab.id)"
          :class="[
            'py-3 px-1 border-b-2 font-medium text-sm transition-colors',
            activeTab === tab.id
              ? 'border-gray-900 text-gray-900'
              : 'border-transparent text-gray-600 hover:text-gray-900'
          ]"
        >
          {{ tab.label }}
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="px-6 py-4">
      <!-- Tasks List -->
      <div v-if="loading" class="space-y-3">
        <div v-for="i in 3" :key="i" class="h-10 bg-gray-100 rounded animate-pulse"/>
      </div>

      <div v-else-if="displayedTasks.length === 0" class="text-center py-12">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-gray-500 text-sm">No {{ activeTab }} tasks</p>
      </div>

      <div v-else class="space-y-0">
        <DashboardTaskRow
          v-for="task in displayedTasks"
          :key="task.id"
          :task="task"
          @toggle-complete="handleToggleComplete"
          @click="handleTaskClick"
          @update-dates="handleUpdateDates"
        />
      </div>

      <!-- Show More Button -->
      <div v-if="hasMore" class="mt-6 text-center">
        <button
          @click="loadMore"
          :disabled="loadingMore"
          class="text-gray-600 hover:text-gray-900 text-sm font-medium transition-colors disabled:opacity-50"
        >
          {{ loadingMore ? 'Loading...' : 'Show more' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import DashboardTaskRow from './DashboardTaskRow.vue'

const props = defineProps({
  userInitials: { type: String, default: 'CF' },
})

const activeTab = ref('upcoming')
const showCreateTask = ref(false)
const loadingMore = ref(false)
const loading = ref(false)

const allTasks = ref({
  upcoming: [],
  overdue: [],
  completed: [],
})

const displayCounts = ref({
  upcoming: 10,
  overdue: 10,
  completed: 10,
})

const tabs = [
  { id: 'upcoming', label: 'Upcoming' },
  { id: 'overdue', label: 'Overdue' },
  { id: 'completed', label: 'Completed' },
]

const displayedTasks = computed(() => {
  const taskList = allTasks.value[activeTab.value] || []
  return taskList.slice(0, displayCounts.value[activeTab.value])
})

const hasMore = computed(() => {
  const taskList = allTasks.value[activeTab.value] || []
  return taskList.length > displayCounts.value[activeTab.value]
})

const taskStats = computed(() => {
  const totalCompleted = allTasks.value.completed?.length || 0
  const totalUpcoming = allTasks.value.upcoming?.length || 0
  const totalOverdue = allTasks.value.overdue?.length || 0
  const totalActive = totalUpcoming + totalOverdue
  
  // Show count only if we have fetched data
  if (loading.value) {
    return 'Loading...'
  }
  
  return `${totalCompleted} tasks completed, ${totalActive} active`
})

// Fetch all tabs on mount
const initializeTabs = async () => {
  loading.value = true
  try {
    // Fetch all three tabs in parallel
    await Promise.all([
      fetchTasks('upcoming'),
      fetchTasks('overdue'),
      fetchTasks('completed'),
    ])
  } finally {
    loading.value = false
  }
}

// Fetch tasks using same method as MyTasks store
const fetchTasks = async (tab = activeTab.value) => {
  loading.value = true

  try {
    const params = new URLSearchParams({
      page: '1',
      per_page: '100',
    })

    // No filters initially - just get all tasks
    // Then filter them client-side
    const response = await fetch(`/my-tasks/api/tasks?${params.toString()}`, {
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()
    
    
    
    // Handle both flat array and grouped response
    let allTasksData = []
    if (Array.isArray(data)) {
      allTasksData = data
    } else if (Array.isArray(data.tasks)) {
      allTasksData = data.tasks
    } else if (data.tasks && typeof data.tasks === 'object') {
      // Grouped response - flatten it
      allTasksData = Object.values(data.tasks).flat()
    } else {
      allTasksData = []
    }

    

    // Filter tasks by tab
    const today = new Date()
    today.setHours(0, 0, 0, 0)

    let filteredTasks = []

    if (tab === 'upcoming') {
      filteredTasks = allTasksData.filter(task => {
        if (task.status === 'complete') return false
        if (!task.due_date) return false
        const dueDate = new Date(task.due_date)
        dueDate.setHours(0, 0, 0, 0)
        return dueDate >= today
      })
    } else if (tab === 'overdue') {
      filteredTasks = allTasksData.filter(task => {
        if (task.status === 'complete') return false
        if (!task.due_date) return false
        const dueDate = new Date(task.due_date)
        dueDate.setHours(0, 0, 0, 0)
        return dueDate < today
      })
    } else if (tab === 'completed') {
      filteredTasks = allTasksData.filter(task => task.status === 'complete')
    }

    

    allTasks.value[tab] = filteredTasks
    displayCounts.value[tab] = 10
  } catch (err) {
    console.error(`Failed to fetch ${tab} tasks:`, err)
    allTasks.value[tab] = []
  } finally {
    loading.value = false
  }
}

// Toggle task completion with optimistic UI
const toggleTaskComplete = async (taskId) => {
  try {
    // Find the task in all tabs
    let taskIndex = -1
    let taskTab = null
    let currentTask = null

    for (const tab of Object.keys(allTasks.value)) {
      taskIndex = allTasks.value[tab].findIndex(t => t.id === taskId)
      if (taskIndex !== -1) {
        taskTab = tab
        currentTask = allTasks.value[tab][taskIndex]
        break
      }
    }

    if (!currentTask || taskIndex === -1 || !taskTab) return false

    // Get new status (toggle between complete and to_do)
    const newStatus = currentTask.status === 'complete' ? 'to_do' : 'complete'

    // Store old task data for potential revert
    const oldTask = { ...currentTask }
    const oldTab = taskTab

    // **OPTIMISTIC UPDATE** - Instantly update the UI
    currentTask.status = newStatus
    if (newStatus === 'complete') {
      currentTask.completed_at = new Date().toISOString()
    } else {
      currentTask.completed_at = null
    }

    // **MOVE TASK TO CORRECT TAB**
    // Remove from current tab
    allTasks.value[taskTab].splice(taskIndex, 1)

    // Determine which tab it should go to
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    let newTab = 'upcoming'

    if (newStatus === 'complete') {
      newTab = 'completed'
    } else {
      // Task is now to_do - figure out if upcoming or overdue
      if (currentTask.due_date) {
        const dueDate = new Date(currentTask.due_date)
        dueDate.setHours(0, 0, 0, 0)
        if (dueDate < today) {
          newTab = 'overdue'
        } else {
          newTab = 'upcoming'
        }
      } else {
        // No due date - put in upcoming
        newTab = 'upcoming'
      }
    }

    // Add to new tab
    allTasks.value[newTab].unshift(currentTask)

    // **SEND REQUEST** - Now send to backend
    const response = await fetch(`/api/tasks/${taskId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        status: newStatus,
      }),
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    // **SUCCESS** - Update with server response
    const data = await response.json()
    if (data.data) {
      // Find task in new tab and update it
      const newIndex = allTasks.value[newTab].findIndex(t => t.id === taskId)
      if (newIndex !== -1) {
        Object.assign(allTasks.value[newTab][newIndex], data.data)
      }
    }

    return true
  } catch (err) {
    console.error('Failed to toggle task:', err)
    // **ERROR** - Revert to old state by refreshing
    await fetchTasks(activeTab.value)
    return false
  }
}

// Load more tasks (client-side pagination)
const loadMore = async () => {
  loadingMore.value = true
  await new Promise(resolve => setTimeout(resolve, 300))
  displayCounts.value[activeTab.value] += 10
  loadingMore.value = false
}

// Select tab and fetch if needed
const selectTab = (tab) => {
  activeTab.value = tab
  if (!allTasks.value[tab] || allTasks.value[tab].length === 0) {
    fetchTasks(tab)
  }
}

// Handle task toggle
const handleToggleComplete = async (taskId) => {
  await toggleTaskComplete(taskId)
}

// Handle task click
const handleTaskClick = (taskId) => {
  window.location.href = `/tasks/${taskId}`
}

// Handle date update
const handleUpdateDates = async ({ taskId, startDate, endDate }) => {
  try {
    // Find the task
    let currentTask = null
    let taskTab = null
    for (const tab of Object.keys(allTasks.value)) {
      currentTask = allTasks.value[tab].find(t => t.id === taskId)
      if (currentTask) {
        taskTab = tab
        break
      }
    }

    if (!currentTask) return

    // Update task dates
    const response = await fetch(`/api/tasks/${taskId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        start_date: startDate,
        due_date: endDate,
      }),
    })

    if (response.ok) {
      const data = await response.json()
      if (data.data) {
        Object.assign(currentTask, data.data)
      }
    }
  } catch (err) {
    console.error('Failed to update task dates:', err)
  }
}

// Initialize - fetch all tabs on mount
onMounted(() => {
  initializeTabs()
})

// Watch tab changes
watch(() => activeTab.value, (newTab) => {
  selectTab(newTab)
})
</script>
