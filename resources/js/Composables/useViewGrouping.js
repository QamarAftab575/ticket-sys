import { ref, computed } from 'vue'

export function useViewGrouping() {
  const groupingField = ref(null)

  // Set grouping field
  const setGrouping = (field) => {
    groupingField.value = field
  }

  // Clear grouping
  const clearGrouping = () => {
    groupingField.value = null
  }

  // Check if grouping is active
  const hasGrouping = computed(() => {
    return !!groupingField.value
  })

  // Apply grouping to tasks (client-side)
  const applyGrouping = (tasks) => {
    if (!hasGrouping.value) {
      return tasks
    }

    const grouped = {}

    tasks.forEach((task) => {
      const key = task[groupingField.value] || 'Ungrouped'
      if (!grouped[key]) {
        grouped[key] = []
      }
      grouped[key].push(task)
    })

    return grouped
  }

  // Get grouped tasks as array of groups
  const getGroupedArray = (tasks) => {
    const grouped = applyGrouping(tasks)
    return Object.entries(grouped).map(([name, items]) => ({
      name,
      items,
      count: items.length,
    }))
  }

  return {
    groupingField,
    setGrouping,
    clearGrouping,
    hasGrouping,
    applyGrouping,
    getGroupedArray,
  }
}
