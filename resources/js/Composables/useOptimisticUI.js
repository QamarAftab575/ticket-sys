import { ref, computed } from 'vue'

export function useOptimisticUI(initialItems = []) {
  const items = ref([...initialItems])
  const pendingChanges = ref(new Map())

  // Add item optimistically
  const addItem = (item) => {
    const tempId = `temp_${Date.now()}`
    const tempItem = { ...item, id: tempId }
    items.value.push(tempItem)
    pendingChanges.value.set(tempId, { action: 'create', item })
    return tempId
  }

  // Update item optimistically
  const updateItem = (id, changes) => {
    const index = items.value.findIndex((item) => item.id === id)
    if (index >= 0) {
      items.value[index] = { ...items.value[index], ...changes }
      pendingChanges.value.set(id, { action: 'update', changes })
    }
  }

  // Delete item optimistically
  const deleteItem = (id) => {
    const index = items.value.findIndex((item) => item.id === id)
    if (index >= 0) {
      items.value.splice(index, 1)
      pendingChanges.value.set(id, { action: 'delete' })
    }
  }

  // Confirm change (remove from pending)
  const confirmChange = (id) => {
    pendingChanges.value.delete(id)
  }

  // Revert change (undo optimistic update)
  const revertChange = (id, originalItem) => {
    const index = items.value.findIndex((item) => item.id === id)
    if (index >= 0) {
      if (originalItem) {
        items.value[index] = originalItem
      } else {
        items.value.splice(index, 1)
      }
    }
    pendingChanges.value.delete(id)
  }

  // Check if item has pending changes
  const hasPendingChanges = (id) => {
    return pendingChanges.value.has(id)
  }

  // Get all pending changes
  const getPendingChanges = computed(() => {
    return Array.from(pendingChanges.value.entries())
  })

  return {
    items,
    pendingChanges,
    addItem,
    updateItem,
    deleteItem,
    confirmChange,
    revertChange,
    hasPendingChanges,
    getPendingChanges,
  }
}
