import { ref, computed } from 'vue'

const STORAGE_KEY = 'asira_active_workspace'

/**
 * Single source of truth for active workspace management
 * Handles getting and setting the active workspace in browser storage
 * No database persistence
 */

const activeWorkspaceId = ref(null)
const isInitialized = ref(false)

/**
 * Initialize and get the active workspace
 * First checks browser storage, if not found, uses first available workspace
 * @param {Array} availableWorkspaces - List of workspaces user has access to
 * @returns {Object|null} - Active workspace object or null
 */
export function initializeActiveWorkspace(availableWorkspaces = []) {
  if (isInitialized.value && activeWorkspaceId.value) {
    const workspace = availableWorkspaces.find(w => w.id === activeWorkspaceId.value)
    if (workspace) return workspace
  }

  // Try to get from localStorage
  const stored = localStorage.getItem(STORAGE_KEY)
  
  if (stored && availableWorkspaces.length > 0) {
    const workspace = availableWorkspaces.find(w => w.id === stored)
    if (workspace) {
      activeWorkspaceId.value = workspace.id
      isInitialized.value = true
      return workspace
    }
  }

  // Default to first available workspace
  if (availableWorkspaces.length > 0) {
    const firstWorkspace = availableWorkspaces[0]
    setActiveWorkspace(firstWorkspace)
    return firstWorkspace
  }

  return null
}

/**
 * Get the current active workspace ID
 * @returns {String|null}
 */
export function getActiveWorkspaceId() {
  if (!activeWorkspaceId.value) {
    const stored = localStorage.getItem(STORAGE_KEY)
    if (stored) {
      activeWorkspaceId.value = stored
    }
  }
  return activeWorkspaceId.value
}

/**
 * Set the active workspace and save to localStorage
 * @param {Object} workspace - Workspace object with at least 'id' property
 */
export function setActiveWorkspace(workspace) {
  if (!workspace || !workspace.id) {
    console.error('Invalid workspace object')
    return
  }

  activeWorkspaceId.value = workspace.id
  localStorage.setItem(STORAGE_KEY, workspace.id)
}

/**
 * Get the active workspace object
 * @param {Array} availableWorkspaces - List of workspaces
 * @returns {Object|null}
 */
export function getActiveWorkspace(availableWorkspaces = []) {
  const id = getActiveWorkspaceId()
  if (id && availableWorkspaces.length > 0) {
    return availableWorkspaces.find(w => w.id === id) || null
  }
  return null
}

/**
 * Clear the active workspace from storage
 * Used when user logs out or when storage needs to be reset
 */
export function clearActiveWorkspace() {
  activeWorkspaceId.value = null
  localStorage.removeItem(STORAGE_KEY)
  isInitialized.value = false
}

/**
 * Vue composable for use in components
 */
export function useActiveWorkspace(workspaces = ref([])) {
  const activeWorkspace = computed(() => {
    if (!workspaces.value || workspaces.value.length === 0) {
      return null
    }
    return getActiveWorkspace(workspaces.value)
  })

  const switchWorkspace = (workspace) => {
    setActiveWorkspace(workspace)
  }

  return {
    activeWorkspace,
    switchWorkspace,
    getActiveWorkspaceId,
    initializeActiveWorkspace,
  }
}
