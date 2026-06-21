import { ref } from 'vue'
import axios from 'axios'

export function useDashboardTasks() {
  const tasks = ref({
    upcoming: [],
    overdue: [],
    completed: [],
  })

  const loading = ref(false)
  const error = ref(null)

  // Fetch tasks for a specific tab
  const fetchTasks = async (tab = 'upcoming') => {
    loading.value = true
    error.value = null

    try {
      const today = new Date()
      today.setHours(0, 0, 0, 0)
      const todayStr = today.toISOString().split('T')[0]

      let filters = []

      if (tab === 'upcoming') {
        // Due date >= today AND status != 'complete'
        filters = [
          { field: 'due_date', operator: 'greater_than', value: todayStr },
          { field: 'status', operator: 'not_equals', value: 'complete' },
        ]
      } else if (tab === 'overdue') {
        // Due date < today AND status != 'complete'
        filters = [
          { field: 'due_date', operator: 'less_than', value: todayStr },
          { field: 'status', operator: 'not_equals', value: 'complete' },
        ]
      } else if (tab === 'completed') {
        filters = [
          { field: 'status', operator: 'equals', value: 'complete' },
        ]
      }

      const response = await axios.get('/my-tasks/api/tasks', {
        params: {
          filters: JSON.stringify(filters),
          page: 1,
          per_page: 100,
        },
      })

      if (response.data && Array.isArray(response.data)) {
        tasks.value[tab] = response.data
      } else if (response.data && response.data.data && Array.isArray(response.data.data)) {
        tasks.value[tab] = response.data.data
      }
    } catch (err) {
      console.error(`Failed to fetch ${tab} tasks:`, err)
      error.value = err.message
      tasks.value[tab] = []
    } finally {
      loading.value = false
    }
  }

  // Toggle task completion
  const toggleTaskComplete = async (taskId) => {
    try {
      await axios.post(`/api/tasks/${taskId}/toggle-complete`, {}, {
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        },
      })
      return true
    } catch (err) {
      console.error('Failed to toggle task:', err)
      return false
    }
  }

  return {
    tasks,
    loading,
    error,
    fetchTasks,
    toggleTaskComplete,
  }
}
