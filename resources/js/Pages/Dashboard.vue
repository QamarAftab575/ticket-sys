<template>
  <AppLayout :user-workspaces="userWorkspaces" :current-workspace="workspace" :user-role="userRole">
    <Head title="Dashboard" />
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Greeting Header -->
      <DashboardGreeting />

      <!-- My Tasks Section -->
      <div class="mb-12">
        <DashboardTasks :user-initials="userInitials" @select-task="handleTaskSelect" />
      </div>

      <!-- Projects & Notes Section -->
      <div class="mb-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Left Column: Projects (2/3 width) -->
          <div class="lg:col-span-1">
            <ProjectsSection :projects="projects" />
          </div>

          <!-- Right Column: Private Notes (1/3 width) -->
          <div class="lg:col-span-1">
            <PrivateNotesSection />
          </div>
        </div>
      </div>
    </div>

    <!-- Task Detail Panel -->
    <TaskDetailPanel
      v-if="selectedTask"
      :task="selectedTask"
      :project="selectedTask?.project || null"
      :current-user="currentUser"
      @close="handleCloseTaskPanel"
      @update="handleTaskUpdate"
      @open-task="handleTaskSelect"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Link, usePage, Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import DashboardGreeting from '@/Components/Dashboard/DashboardGreeting.vue'
import DashboardTasks from '@/Components/Dashboard/DashboardTasks.vue'
import ProjectsSection from '@/Components/Dashboard/ProjectsSection.vue'
import PrivateNotesSection from '@/Components/Dashboard/PrivateNotesSection.vue'
import TaskDetailPanel from '@/Components/Projects/TaskDetailPanel.vue'

const page = usePage()

const props = defineProps({
  projects: Array,
  workspaces: Array,
  userRole: String,
  workspace: Object,
  userWorkspaces: Array,
})

const userInitials = computed(() => {
  const name = page.props.auth?.user?.name || 'User'
  return name.split(' ').map(n => n.charAt(0)).join('').toUpperCase().slice(0, 2)
})

const currentUser = computed(() => page.props.auth?.user || null)

// URL query parameter helpers
const getQueryParam = (key) => new URLSearchParams(window.location.search).get(key)
const setQueryParams = (params) => {
  const url = new URL(window.location.href)
  Object.entries(params).forEach(([key, value]) => {
    if (value === undefined || value === null) {
      url.searchParams.delete(key)
    } else {
      url.searchParams.set(key, value)
    }
  })
  window.history.pushState({}, '', url.toString())
}

const formatStatus = (status) => {
  const statusMap = {
    on_track: 'On Track',
    at_risk: 'At Risk',
    off_track: 'Off Track',
    archived: 'Archived',
  };
  return statusMap[status] || status;
};

// Task Detail Panel State
const selectedTask = ref(null)

const handleTaskSelect = (task) => {
  selectedTask.value = task
  // Add task ID to URL
  setQueryParams({ task: task.id })
}

const handleCloseTaskPanel = () => {
  selectedTask.value = null
  // Remove task ID from URL
  setQueryParams({ task: undefined })
}

const handleTaskUpdate = (taskId, updates) => {
  // Task is already updated in DashboardTasks component
  // We just need to update the selected task if it's still open
  if (selectedTask.value && selectedTask.value.id === taskId) {
    Object.assign(selectedTask.value, updates)
  }
}

// Handle browser back/forward buttons
const handlePopState = () => {
  const taskId = getQueryParam('task')
  if (taskId) {
    // Try to fetch the task from API
    fetch(`/api/tasks/${taskId}`)
      .then(response => response.ok ? response.json() : null)
      .then(data => {
        if (data) {
          selectedTask.value = data.data || data
        }
      })
      .catch(() => {
        selectedTask.value = null
      })
  } else {
    selectedTask.value = null
  }
}

// Check for task in URL on mount
onMounted(() => {
  const taskId = getQueryParam('task')
  if (taskId) {
    // Fetch the task from API
    fetch(`/api/tasks/${taskId}`)
      .then(response => response.ok ? response.json() : null)
      .then(data => {
        if (data) {
          selectedTask.value = data.data || data
        }
      })
      .catch(err => {
        console.error('Failed to load task from URL:', err)
      })
  }

  // Add popstate listener for browser navigation
  window.addEventListener('popstate', handlePopState)
})

// Cleanup on unmount
onBeforeUnmount(() => {
  window.removeEventListener('popstate', handlePopState)
})
</script>
