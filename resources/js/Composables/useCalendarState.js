import { ref, computed } from 'vue'

export function useCalendarState(projectId) {
  const currentDate = ref(new Date())
  const viewMode = ref('month')
  const expandedDays = ref(new Set())

  // Load state from localStorage
  const loadState = () => {
    const stored = localStorage.getItem(`calendar_${projectId}`)
    if (stored) {
      try {
        const state = JSON.parse(stored)
        if (state.currentDate) {
          currentDate.value = new Date(state.currentDate)
        }
        if (state.viewMode) {
          viewMode.value = state.viewMode
        }
        if (state.expandedDays) {
          expandedDays.value = new Set(state.expandedDays)
        }
      } catch (e) {
        console.error('Failed to load calendar state:', e)
      }
    }
  }

  // Save state to localStorage
  const saveState = () => {
    const state = {
      currentDate: currentDate.value.toISOString(),
      viewMode: viewMode.value,
      expandedDays: Array.from(expandedDays.value),
    }
    localStorage.setItem(`calendar_${projectId}`, JSON.stringify(state))
  }

  const nextMonth = () => {
    currentDate.value.setMonth(currentDate.value.getMonth() + 1)
    currentDate.value = new Date(currentDate.value)
    saveState()
  }

  const previousMonth = () => {
    currentDate.value.setMonth(currentDate.value.getMonth() - 1)
    currentDate.value = new Date(currentDate.value)
    saveState()
  }

  const nextWeek = () => {
    currentDate.value.setDate(currentDate.value.getDate() + 7)
    currentDate.value = new Date(currentDate.value)
    saveState()
  }

  const previousWeek = () => {
    currentDate.value.setDate(currentDate.value.getDate() - 7)
    currentDate.value = new Date(currentDate.value)
    saveState()
  }

  const goToToday = () => {
    currentDate.value = new Date()
    saveState()
  }

  const toggleViewMode = () => {
    viewMode.value = viewMode.value === 'month' ? 'week' : 'month'
    saveState()
  }

  const toggleExpandDay = (date) => {
    const dateStr = date.toISOString().split('T')[0]
    if (expandedDays.value.has(dateStr)) {
      expandedDays.value.delete(dateStr)
    } else {
      expandedDays.value.add(dateStr)
    }
    saveState()
  }

  const clearExpandedDays = () => {
    expandedDays.value.clear()
    saveState()
  }

  return {
    currentDate,
    viewMode,
    expandedDays,
    loadState,
    saveState,
    nextMonth,
    previousMonth,
    nextWeek,
    previousWeek,
    goToToday,
    toggleViewMode,
    toggleExpandDay,
    clearExpandedDays,
  }
}
