import { ref, computed, watch, onMounted } from 'vue'

// Helper to read/write URL query params without vue-router
const getQueryParam = (key) => new URLSearchParams(window.location.search).get(key)

const setQueryParam = (key, value) => {
  const url = new URL(window.location.href)
  if (value === null || value === undefined) {
    url.searchParams.delete(key)
  } else {
    url.searchParams.set(key, value)
  }
  window.history.pushState({}, '', url.toString())
}

export function useViewState(projectId) {
  const activeView = ref(getQueryParam('view') || 'list')
  const preferences = ref({})
  const isLoading = ref(false)

  // Initialize from URL or localStorage
  const initializeView = () => {
    activeView.value = getQueryParam('view') || 'list'
    loadPreferences()
  }

  // Set active view and update URL
  const setActiveView = (viewType) => {
    activeView.value = viewType
    setQueryParam('view', viewType)
    localStorage.setItem(`project_${projectId}_view`, viewType)
  }

  // Load preferences from localStorage
  const loadPreferences = async () => {
    isLoading.value = true
    try {
      const stored = localStorage.getItem(`project_${projectId}_preferences`)
      if (stored) {
        preferences.value = JSON.parse(stored)
      }
      await fetchPreferencesFromBackend()
    } finally {
      isLoading.value = false
    }
  }

  // Fetch preferences from backend
  const fetchPreferencesFromBackend = async () => {
    try {
      const response = await fetch(
        `/projects/${projectId}/views/preferences/${activeView.value}`
      )
      if (response.ok) {
        const data = await response.json()
        preferences.value[activeView.value] = data
      }
    } catch (error) {
      console.error('Failed to fetch preferences:', error)
    }
  }

  // Save preferences to backend and localStorage
  const savePreferences = async (viewType, prefs) => {
    try {
      const response = await fetch(
        `/projects/${projectId}/views/preferences`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
          },
          body: JSON.stringify({
            view_type: viewType,
            ...prefs,
          }),
        }
      )

      if (response.ok) {
        preferences.value[viewType] = prefs
        localStorage.setItem(
          `project_${projectId}_preferences`,
          JSON.stringify(preferences.value)
        )
      }
    } catch (error) {
      console.error('Failed to save preferences:', error)
    }
  }

  // Get preferences for current view
  const getCurrentPreferences = computed(() => {
    return preferences.value[activeView.value] || {}
  })

  // Listen for browser back/forward navigation
  onMounted(() => {
    window.addEventListener('popstate', () => {
      const view = getQueryParam('view')
      if (view) activeView.value = view
    })
  })

  return {
    activeView,
    preferences,
    isLoading,
    initializeView,
    setActiveView,
    loadPreferences,
    savePreferences,
    getCurrentPreferences,
  }
}
