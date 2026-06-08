import { ref, computed, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

// Singleton cache
let workspaceDataCache = null

// Global event emitter for cache invalidation
const cacheInvalidationCallbacks = []

export function onCacheInvalidation(callback) {
  cacheInvalidationCallbacks.push(callback)
}

export function invalidateCache(reason = 'unknown') {
  console.log(`Cache invalidated: ${reason}`)
  workspaceDataCache = null
  cacheInvalidationCallbacks.forEach(cb => cb(reason))
}

export function useSidebarData() {
  const page = usePage()
  const userWorkspaces = ref([])
  const isLoading = ref(false)
  const error = ref(null)

  const currentWorkspaceId = computed(() => {
    // First try to get from URL
    const match = page.url.match(/\/workspace\/([^\/]+)/)
    if (match) return match[1]
    
    // Fall back to auth user's active workspace
    return page.props.auth?.user?.active_workspace_id || null
  })

  const currentWorkspace = computed(() => {
    if (!userWorkspaces.value.length) return null
    return userWorkspaces.value.find(ws => ws.id === currentWorkspaceId.value) || userWorkspaces.value[0]
  })

  const isOwnerOfAnyWorkspace = computed(() => {
    return userWorkspaces.value.some(ws => {
      const membership = ws.organization_memberships?.[0]
      return membership?.role === 'owner'
    })
  })

  const fetchWorkspaces = async () => {
    // Use cached data if available
    if (workspaceDataCache) {
      userWorkspaces.value = workspaceDataCache
      return
    }

    isLoading.value = true
    error.value = null

    try {
      // Get workspaces from Inertia page props (passed by server)
      const workspaces = page.props.userWorkspaces || page.props.workspaces || []
      
      if (workspaces.length > 0) {
        // Cache the data
        workspaceDataCache = workspaces
        userWorkspaces.value = workspaces
      } else {
        // If no workspaces in props, show empty state
        userWorkspaces.value = []
      }
    } catch (err) {
      error.value = err.message
      console.error('Failed to fetch workspaces:', err)
      userWorkspaces.value = []
    } finally {
      isLoading.value = false
    }
  }

  const refreshWorkspaces = () => {
    // Force refresh by invalidating cache
    invalidateCache('manual refresh')
    return fetchWorkspaces()
  }

  // Listen for cache invalidation events
  const handleCacheInvalidation = (callback) => {
    onCacheInvalidation(callback)
  }

  onMounted(() => {
    // Fetch workspaces if not already loaded
    if (userWorkspaces.value.length === 0) {
      fetchWorkspaces()
    }

    // Listen for cache invalidation from other operations
    handleCacheInvalidation(() => {
      fetchWorkspaces()
    })
  })

  return {
    userWorkspaces,
    currentWorkspaceId,
    currentWorkspace,
    isOwnerOfAnyWorkspace,
    isLoading,
    error,
    fetchWorkspaces,
    refreshWorkspaces
  }
}
