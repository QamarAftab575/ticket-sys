import { ref, computed, onMounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { api } from '@/Services/api'

// Singleton cache
let workspaceDataCache = null
let cacheTimestamp = null
const CACHE_DURATION = 5 * 60 * 1000 // 5 minutes

// Global event emitter for cache invalidation
const cacheInvalidationCallbacks = []

export function onCacheInvalidation(callback) {
  cacheInvalidationCallbacks.push(callback)
}

export function invalidateCache(reason = 'unknown') {
  console.log(`Cache invalidated: ${reason}`)
  workspaceDataCache = null
  cacheTimestamp = null
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

  const isCacheValid = () => {
    if (!workspaceDataCache || !cacheTimestamp) return false
    return Date.now() - cacheTimestamp < CACHE_DURATION
  }

  const fetchWorkspaces = async (forceRefresh = false) => {
    // Return cached data if valid and not forcing refresh
    if (!forceRefresh && isCacheValid()) {
      userWorkspaces.value = workspaceDataCache
      return
    }

    isLoading.value = true
    error.value = null

    try {
      const response = await api.get('/workspaces')
      
      // Cache the data
      workspaceDataCache = response.data || []
      cacheTimestamp = Date.now()
      userWorkspaces.value = workspaceDataCache
    } catch (err) {
      error.value = err.message
      console.error('Failed to fetch workspaces:', err)
      
      // Fall back to page props if available
      if (page.props.userWorkspaces) {
        userWorkspaces.value = page.props.userWorkspaces
      }
    } finally {
      isLoading.value = false
    }
  }

  const refreshWorkspaces = () => {
    // Force refresh by invalidating cache
    return fetchWorkspaces(true)
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
      fetchWorkspaces(true)
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
