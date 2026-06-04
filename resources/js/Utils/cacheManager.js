import { invalidateCache } from '@/Composables/useSidebarData'

/**
 * Clear sidebar cache when workspace data changes
 */
export function clearSidebarCache(reason = 'data-change') {
  invalidateCache(`workspace-${reason}`)
}

/**
 * Call this after workspace create/update/delete operations
 */
export function onWorkspaceModified(action) {
  clearSidebarCache(`workspace-${action}`)
}

/**
 * Call this after workspace switch
 */
export function onWorkspaceSwitched() {
  clearSidebarCache('workspace-switched')
}

/**
 * Call this after project create/update/delete operations
 * Projects affect workspace display
 */
export function onProjectModified(action) {
  clearSidebarCache(`project-${action}`)
}

/**
 * Call this after any workspace member change
 */
export function onWorkspaceMemberModified(action) {
  clearSidebarCache(`member-${action}`)
}

/**
 * Generic cache invalidation for any reason
 */
export function invalidateSidebarCache(reason) {
  clearSidebarCache(reason)
}
