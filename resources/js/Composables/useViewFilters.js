import { ref, computed } from 'vue'

export function useViewFilters() {
  const filters = ref([])

  // Add a filter rule
  const addFilter = (field, operator = 'equals', value = '') => {
    filters.value.push({
      field,
      operator,
      value,
    })
  }

  // Remove a filter rule by index
  const removeFilter = (index) => {
    filters.value.splice(index, 1)
  }

  // Update a filter rule
  const updateFilter = (index, field, operator, value) => {
    if (filters.value[index]) {
      filters.value[index] = { field, operator, value }
    }
  }

  // Clear all filters
  const clearFilters = () => {
    filters.value = []
  }

  // Check if any filters are active
  const hasFilters = computed(() => {
    return filters.value.length > 0
  })

  // Get filter count
  const filterCount = computed(() => {
    return filters.value.length
  })

  // Apply filters to tasks (client-side)
  const applyFilters = (tasks) => {
    if (!hasFilters.value) {
      return tasks
    }

    return tasks.filter((task) => {
      return filters.value.every((filter) => {
        const value = task[filter.field]

        switch (filter.operator) {
          case 'equals':
            return value === filter.value
          case 'not_equals':
            return value !== filter.value
          case 'contains':
            return String(value).includes(filter.value)
          case 'not_contains':
            return !String(value).includes(filter.value)
          case 'greater_than':
            return value > filter.value
          case 'less_than':
            return value < filter.value
          case 'is_empty':
            return !value
          case 'is_not_empty':
            return !!value
          default:
            return true
        }
      })
    })
  }

  return {
    filters,
    addFilter,
    removeFilter,
    updateFilter,
    clearFilters,
    hasFilters,
    filterCount,
    applyFilters,
  }
}
