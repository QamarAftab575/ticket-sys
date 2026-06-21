import { computed, ref, onMounted } from 'vue'
import { useActiveWorkspace } from '@/Composables/useActiveWorkspace'

/**
 * Composable to check if user is owner of the active workspace
 * Uses the centralized active workspace helper as single source of truth
 */
export function useWorkspaceOwnership(userWorkspaces = ref([])) {
  const { activeWorkspace, initializeActiveWorkspace } = useActiveWorkspace(userWorkspaces)

  // Initialize active workspace on first use
  onMounted(() => {
    if (userWorkspaces.value && userWorkspaces.value.length > 0) {
      initializeActiveWorkspace(userWorkspaces.value)
    }
  })

  /**
   * Check if user is owner of the currently selected workspace
   * @returns {Boolean}
   */
  const isOwnerOfActiveWorkspace = computed(() => {
    if (!activeWorkspace.value) {
      return false
    }

    return activeWorkspace.value.is_owner === true
  })

  /**
   * Check if user is admin or owner of the active workspace
   * @returns {Boolean}
   */
  const isAdminOrOwnerOfActiveWorkspace = computed(() => {
    if (!activeWorkspace.value) {
      return false
    }

    return activeWorkspace.value.is_owner === true || activeWorkspace.value.is_admin === true
  })

  return {
    isOwnerOfActiveWorkspace,
    isAdminOrOwnerOfActiveWorkspace,
    activeWorkspace,
  }
}
