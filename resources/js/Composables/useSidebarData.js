import { ref, computed, onMounted, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { api } from '@/Services/api'

// Singleton cache with expiration (5-10 minutes)
let workspaceDataCache = null
let cacheTimestamp = null
const CACHE_DURATION = 5 * 60 * 1000 // 5 minutes in milliseconds

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

function isCacheValid() {
  if (!workspaceDataCache || !cacheTimestamp) return false
  const now = Date.now()
  return (now - cacheTimestamp) < CACHE_DURATION
}

export function useSidebarData() {
  const page = usePage()
  // Initialize from cache if available and valid
  const userWorkspaces = ref(isCacheValid() ? workspaceDataCache : [])
  const isLoading = ref(false)
  const error = ref(null)
  
  // Log cache usage
  if (isCacheValid()) {
    console.log('Initializing from cache:', workspaceDataCache.length, 'workspaces')
  }

  const currentWorkspaceId = computed(() => {
    // First try to get from URL
    const match = page.url.match(/\/workspace\/([^\/]+)/)
    if (match) return match[1]
    
    // Fall back to auth user's active workspace
    return page.props.auth?.user?.active_workspace_id || null
  })

  const currentWorkspace = computed(() => {
    if (!userWorkspaces.value?.length) return null
    return userWorkspaces.value.find(ws => ws.id === currentWorkspaceId.value) || userWorkspaces.value[0]
  })

  const isOwnerOfAnyWorkspace = computed(() => {
    return userWorkspaces.value.some(ws => {
      const membership = ws.organization_memberships?.[0]
      return membership?.role === 'owner'
    })
  })

  const fetchWorkspaces = async () => {
    // Use cached data if available and valid
    if (isCacheValid()) {
      console.log('✓ Using cached workspace data (valid for', Math.round((CACHE_DURATION - (Date.now() - cacheTimestamp)) / 1000), 'more seconds)')
      userWorkspaces.value = workspaceDataCache
      return
    }

    // Prevent duplicate concurrent fetches
    if (isLoading.value) {
      console.log('⏳ Fetch already in progress, skipping...')
      return
    }

    isLoading.value = true
    error.value = null

    try {
      // First try to get workspaces from Inertia page props (passed by server)
      let workspaces = page.props.userWorkspaces || page.props.workspaces || []
      
      console.log('Fetching workspaces - from props:', workspaces.length)
      
      // If not in props, fetch from web route
      if (workspaces.length === 0) {
        console.log('📡 No workspaces in props, fetching from API...')
        const response = await api.get('/get-user-workspaces')
        console.log('API response:', response)
        // API returns {data: [...]} so we need response.data.data
        workspaces = response.data?.data || response.data || []
        console.log('Extracted workspaces:', workspaces)
      }
      
      if (workspaces.length > 0) {
        // Cache the data with timestamp
        workspaceDataCache = workspaces
        cacheTimestamp = Date.now()
        userWorkspaces.value = workspaces
        console.log('✓ Workspaces loaded and cached for 5 minutes:', workspaces.length)
      } else {
        // If no workspaces in props or API, show empty state
        userWorkspaces.value = []
        console.log('⚠️ No workspaces found')
      }
    } catch (err) {
      error.value = err.message
      console.error('❌ Failed to fetch workspaces:', err)
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

  // IMPORTANT: Watch runs BEFORE onMounted with immediate:true
  // This ensures data from page props is loaded before component renders
  watch(() => page.props.userWorkspaces, (newVal) => {
    if (newVal && newVal.length > 0) {
      console.log('Page props watcher - updating workspaces:', newVal.length, newVal)
      userWorkspaces.value = newVal
      workspaceDataCache = newVal
      cacheTimestamp = Date.now()
    }
  }, { immediate: true, deep: true })

  onMounted(() => {
    // Check if cache is valid first
    if (isCacheValid() && userWorkspaces.value.length > 0) {
      console.log('Mounted - using cached workspaces:', userWorkspaces.value.length)
      return
    }
    
    // Fetch workspaces if not already loaded from props or cache
    if (userWorkspaces.value.length === 0) {
      console.log('Mounted - no workspaces loaded, fetching...')
      fetchWorkspaces()
    } else {
      console.log('Mounted - workspaces already loaded from props:', userWorkspaces.value.length)
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
