import { ref, computed } from 'vue'

export function useViewSort() {
  const sortRules = ref([])

  // Add a sort rule
  const addSortRule = (field, direction = 'asc') => {
    sortRules.value.push({
      field,
      direction,
    })
  }

  // Remove a sort rule by index
  const removeSortRule = (index) => {
    sortRules.value.splice(index, 1)
  }

  // Update a sort rule
  const updateSortRule = (index, field, direction) => {
    if (sortRules.value[index]) {
      sortRules.value[index] = { field, direction }
    }
  }

  // Reorder sort rules
  const reorderSortRules = (fromIndex, toIndex) => {
    const rule = sortRules.value.splice(fromIndex, 1)[0]
    sortRules.value.splice(toIndex, 0, rule)
  }

  // Clear all sort rules
  const clearSort = () => {
    sortRules.value = []
  }

  // Check if any sort rules are active
  const hasSort = computed(() => {
    return sortRules.value.length > 0
  })

  // Get sort count
  const sortCount = computed(() => {
    return sortRules.value.length
  })

  // Apply sort rules to tasks (client-side)
  const applySortRules = (tasks) => {
    if (!hasSort.value) {
      return tasks
    }

    const sorted = [...tasks]
    sorted.sort((a, b) => {
      for (const rule of sortRules.value) {
        const aValue = a[rule.field]
        const bValue = b[rule.field]

        if (aValue === bValue) {
          continue
        }

        const comparison = aValue < bValue ? -1 : 1
        return rule.direction === 'asc' ? comparison : -comparison
      }
      return 0
    })

    return sorted
  }

  return {
    sortRules,
    addSortRule,
    removeSortRule,
    updateSortRule,
    reorderSortRules,
    clearSort,
    hasSort,
    sortCount,
    applySortRules,
  }
}
