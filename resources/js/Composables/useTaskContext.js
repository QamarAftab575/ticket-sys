import { ref, computed, provide, inject } from 'vue'

/**
 * Task Context - replaces prop drilling
 * Provides task data and methods to deeply nested components without prop cascades
 */
const TASK_CONTEXT_KEY = Symbol('taskContext')

export function useProvideTaskContext(initialTask, project, currentUser, onTaskUpdate) {
  const task = ref(initialTask)
  const loading = ref(false)
  const error = ref(null)

  const context = {
    task,
    loading,
    error,
    project,
    currentUser,
    
    // Common actions
    updateTask(field, value) {
      task.value[field] = value
      onTaskUpdate?.({...task.value})
    },
    
    setLoading(state) {
      loading.value = state
    },
    
    setError(err) {
      error.value = err
    }
  }

  provide(TASK_CONTEXT_KEY, context)
  return context
}

/**
 * Use task context in any nested component - no prop drilling needed
 */
export function useTaskContext() {
  const context = inject(TASK_CONTEXT_KEY)
  
  if (!context) {
    throw new Error('useTaskContext must be used within a component providing task context')
  }
  
  return context
}
