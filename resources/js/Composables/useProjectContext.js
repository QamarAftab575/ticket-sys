import { ref, provide, inject } from 'vue'

/**
 * Project Context - replaces prop drilling for project data
 * Used in Projects/Show.vue and cascaded to all child components
 */
const PROJECT_CONTEXT_KEY = Symbol('projectContext')

export function useProvideProjectContext(initialProject, permissions = {}) {
  const project = ref(initialProject)
  const members = ref(initialProject?.members || [])
  const loading = ref(false)
  const error = ref(null)

  const context = {
    project,
    members,
    loading,
    error,
    permissions,
    
    // Common actions
    updateProject(field, value) {
      project.value[field] = value
    },
    
    setLoading(state) {
      loading.value = state
    },
    
    setError(err) {
      error.value = err
    },
    
    hasPermission(perm) {
      return permissions[perm] === true
    }
  }

  provide(PROJECT_CONTEXT_KEY, context)
  return context
}

/**
 * Inject project context - no props needed
 */
export function useProjectContext() {
  const context = inject(PROJECT_CONTEXT_KEY)
  
  if (!context) {
    throw new Error('useProjectContext must be used within a component providing project context')
  }
  
  return context
}
