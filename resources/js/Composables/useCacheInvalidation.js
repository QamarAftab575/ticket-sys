import { router } from '@inertiajs/vue3'
import { invalidateSidebarCache, onWorkspaceModified, onProjectModified, onWorkspaceSwitched, onWorkspaceMemberModified } from '@/Utils/cacheManager'

/**
 * Auto-clear cache on successful Inertia responses
 * Call this in your main.js or App.vue to enable global cache invalidation
 */
export function setupCacheInvalidation() {
  // Clear cache on successful workspace operations
  router.on('success', (event) => {
    const path = event.detail.page.url
    const method = event.detail.page.component
    
    // Workspace operations
    if (path.includes('/workspace/create') || path.includes('/workspace/') && path.includes('/edit')) {
      onWorkspaceModified('modified')
    }
    
    if (path.includes('/workspace/') && path.includes('/delete')) {
      onWorkspaceModified('deleted')
    }

    // Project operations
    if (path.includes('/projects/create')) {
      onProjectModified('created')
    }
    
    if (path.includes('/projects/') && path.includes('/edit')) {
      onProjectModified('modified')
    }
    
    if (path.includes('/projects/') && path.includes('/delete')) {
      onProjectModified('deleted')
    }

    // Member operations
    if (path.includes('/members') || path.includes('/invitation')) {
      onWorkspaceMemberModified('changed')
    }
  })

  // Optionally listen for specific action confirmations
  window.addEventListener('workspace:created', () => onWorkspaceModified('created'))
  window.addEventListener('workspace:updated', () => onWorkspaceModified('updated'))
  window.addEventListener('workspace:deleted', () => onWorkspaceModified('deleted'))
  window.addEventListener('workspace:switched', () => onWorkspaceSwitched())
  window.addEventListener('project:created', () => onProjectModified('created'))
  window.addEventListener('project:updated', () => onProjectModified('updated'))
  window.addEventListener('project:deleted', () => onProjectModified('deleted'))
  window.addEventListener('member:added', () => onWorkspaceMemberModified('added'))
  window.addEventListener('member:removed', () => onWorkspaceMemberModified('removed'))
}

/**
 * Dispatch custom events for cache invalidation
 * Use these in your components when performing actions
 */
export function dispatchWorkspaceEvent(action) {
  window.dispatchEvent(new CustomEvent(`workspace:${action}`))
}

export function dispatchProjectEvent(action) {
  window.dispatchEvent(new CustomEvent(`project:${action}`))
}

export function dispatchMemberEvent(action) {
  window.dispatchEvent(new CustomEvent(`member:${action}`))
}
