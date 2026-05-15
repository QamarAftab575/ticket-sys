import { ref, computed } from 'vue'

export function useDashboardState(projectId) {
  const DEFAULT_WIDGETS = [
    'project-status',
    'task-completion',
    'tasks-by-assignee',
    'tasks-by-priority',
    'upcoming-milestones',
    'overdue-tasks',
    'recent-activity',
  ]

  const visibleWidgets = ref([...DEFAULT_WIDGETS])
  const widgetOrder = ref([...DEFAULT_WIDGETS])

  // Load from localStorage
  const loadState = () => {
    const key = `dashboardState_${projectId}`
    const stored = localStorage.getItem(key)
    if (stored) {
      try {
        const state = JSON.parse(stored)
        visibleWidgets.value = state.visibleWidgets || [...DEFAULT_WIDGETS]
        widgetOrder.value = state.widgetOrder || [...DEFAULT_WIDGETS]
      } catch (e) {
        console.error('Failed to load dashboard state:', e)
      }
    }
  }

  // Save to localStorage
  const saveState = () => {
    const key = `dashboardState_${projectId}`
    localStorage.setItem(
      key,
      JSON.stringify({
        visibleWidgets: visibleWidgets.value,
        widgetOrder: widgetOrder.value,
      })
    )
  }

  // Remove a widget
  const removeWidget = (widgetId) => {
    visibleWidgets.value = visibleWidgets.value.filter((id) => id !== widgetId)
    widgetOrder.value = widgetOrder.value.filter((id) => id !== widgetId)
    saveState()
  }

  // Add a widget
  const addWidget = (widgetId) => {
    if (!visibleWidgets.value.includes(widgetId)) {
      visibleWidgets.value.push(widgetId)
      widgetOrder.value.push(widgetId)
      saveState()
    }
  }

  // Reorder widgets
  const reorderWidgets = (newOrder) => {
    widgetOrder.value = newOrder
    saveState()
  }

  // Get ordered visible widgets
  const orderedWidgets = computed(() => {
    return widgetOrder.value.filter((id) => visibleWidgets.value.includes(id))
  })

  // Check if widget is visible
  const isWidgetVisible = (widgetId) => {
    return visibleWidgets.value.includes(widgetId)
  }

  return {
    visibleWidgets,
    widgetOrder,
    orderedWidgets,
    loadState,
    saveState,
    removeWidget,
    addWidget,
    reorderWidgets,
    isWidgetVisible,
  }
}
