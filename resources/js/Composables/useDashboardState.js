import { ref } from 'vue'

export function useDashboardState(projectId) {
  // Default ordered layout: each column is an ordered array of widget ids
  const DEFAULT_LEFT = [
    'project-status',
    'task-completion',
    'tasks-by-assignee',
    'upcoming-milestones',
  ]

  const DEFAULT_RIGHT = [
    'tasks-by-priority',
    'overdue-tasks',
    'recent-activity',
  ]

  const ALL_DEFAULT = [...DEFAULT_LEFT, ...DEFAULT_RIGHT]

  const leftColumn = ref([...DEFAULT_LEFT])
  const rightColumn = ref([...DEFAULT_RIGHT])
  const hiddenWidgets = ref([])

  // Load from localStorage
  const loadState = () => {
    const key = `dashboardState_${projectId}`
    const stored = localStorage.getItem(key)
    if (stored) {
      try {
        const state = JSON.parse(stored)
        if (state.leftColumn && Array.isArray(state.leftColumn)) {
          leftColumn.value = state.leftColumn
        }
        if (state.rightColumn && Array.isArray(state.rightColumn)) {
          rightColumn.value = state.rightColumn
        }
        if (state.hiddenWidgets && Array.isArray(state.hiddenWidgets)) {
          hiddenWidgets.value = state.hiddenWidgets
        }
      } catch (e) {
        console.error('Failed to load dashboard state:', e)
        _resetToDefaults()
      }
    } else {
      _resetToDefaults()
    }
  }

  const _resetToDefaults = () => {
    leftColumn.value = [...DEFAULT_LEFT]
    rightColumn.value = [...DEFAULT_RIGHT]
    hiddenWidgets.value = []
  }

  // Save to localStorage
  const saveState = () => {
    const key = `dashboardState_${projectId}`
    localStorage.setItem(
      key,
      JSON.stringify({
        leftColumn: leftColumn.value,
        rightColumn: rightColumn.value,
        hiddenWidgets: hiddenWidgets.value,
      })
    )
  }

  // Check if widget is visible
  const isWidgetVisible = (widgetId) => {
    return !hiddenWidgets.value.includes(widgetId)
  }

  // Hide a widget (removes from columns, adds to hidden list)
  const hideWidget = (widgetId) => {
    leftColumn.value = leftColumn.value.filter((id) => id !== widgetId)
    rightColumn.value = rightColumn.value.filter((id) => id !== widgetId)
    if (!hiddenWidgets.value.includes(widgetId)) {
      hiddenWidgets.value.push(widgetId)
    }
    saveState()
  }

  // Show a hidden widget (appends to shorter column)
  const addWidget = (widgetId) => {
    if (hiddenWidgets.value.includes(widgetId)) {
      hiddenWidgets.value = hiddenWidgets.value.filter((id) => id !== widgetId)
      // Add to the shorter column
      if (leftColumn.value.length <= rightColumn.value.length) {
        leftColumn.value.push(widgetId)
      } else {
        rightColumn.value.push(widgetId)
      }
      saveState()
    }
  }

  /**
   * Reorder a widget within or across columns.
   * @param {string} draggedId  - widget being dragged
   * @param {string} targetId   - widget being dropped onto (null = end of column)
   * @param {'left'|'right'} targetColumn - column being dropped into
   */
  const reorderWidget = (draggedId, targetId, targetColumn) => {
    // Remove from wherever it currently lives
    leftColumn.value = leftColumn.value.filter((id) => id !== draggedId)
    rightColumn.value = rightColumn.value.filter((id) => id !== draggedId)

    const col = targetColumn === 'left' ? leftColumn.value : rightColumn.value

    if (!targetId || targetId === draggedId) {
      // Drop at end of column
      col.push(draggedId)
    } else {
      const idx = col.indexOf(targetId)
      if (idx === -1) {
        col.push(draggedId)
      } else {
        col.splice(idx, 0, draggedId)
      }
    }

    saveState()
  }

  return {
    leftColumn,
    rightColumn,
    hiddenWidgets,
    loadState,
    saveState,
    hideWidget,
    addWidget,
    isWidgetVisible,
    reorderWidget,
  }
}
