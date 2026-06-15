<template>
  <div class="flex min-h-screen bg-gray-50">
    <!-- Mobile overlay -->
    <div
      v-if="mobileOpen"
      class="fixed inset-0 z-20 bg-black/50 lg:hidden"
      @click="mobileOpen = false"
    />

    <!-- Sidebar -->
    <AppSidebar
      :current-route="currentRoute"
      :user-workspaces="userWorkspaces"
      :current-workspace-id="currentWorkspaceId"
      :user-role="userRole"
      :mobile-open="mobileOpen"
      @close="mobileOpen = false"
      @invite="showInviteModal = true"
    />

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">
      <AppHeader @toggle-sidebar="mobileOpen = !mobileOpen" />
      <div class="flex-1 overflow-auto">
        <slot />
      </div>
    </div>

    <!-- Invite Modal -->
    <InviteModal
      v-if="showInviteModal"
      :workspace="currentWorkspace"
      @close="showInviteModal = false"
      @invited="handleInviteSent"
    />

    <!-- Upgrade Modal -->
    <UpgradeModal
      v-if="showUpgradeModal"
      @dismiss="dismissUpgradeModal"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppSidebar from '@/Components/Layout/AppSidebar.vue'
import AppHeader from '@/Components/Layout/AppHeader.vue'
import InviteModal from '@/Components/Workspace/InviteModal.vue'
import UpgradeModal from '@/Components/UpgradeModal.vue'
import { useSidebarData } from '@/Composables/useSidebarData'
import { useRealtimeListeners } from '@/Composables/useRealtimeListeners'

const page = usePage()
const showInviteModal = ref(false)
const showUpgradeModalState = ref(false)
const mobileOpen = ref(false)

// Props passed from server (for backward compatibility)
const props = defineProps({
  userWorkspaces: Array,
  currentWorkspace: Object,
  userRole: String,
})

// Use composable for independent data loading
const { userWorkspaces, currentWorkspaceId, currentWorkspace } = useSidebarData()

// If props are passed, use them to pre-populate and override composable
watch(() => props.userWorkspaces, (newVal) => {
  if (newVal && newVal.length > 0 && userWorkspaces.value.length === 0) {
    userWorkspaces.value = newVal
  }
}, { immediate: true })

const currentRoute = computed(() => {
  const url = page.url
  if (url.includes('/dashboard')) return 'dashboard'
  if (url.includes('/inbox')) return 'inbox'
  if (url.includes('/projects')) return 'projects'
  if (url.includes('/profile')) return 'profile'
  if (url.includes('/settings')) return 'settings'
  if (url.includes('/reports')) return 'reports'
  if (url.includes('/my-tasks')) return 'my-tasks'
  return 'dashboard'
})

const showUpgradeModal = computed(() => {
  return page.props.auth?.user?.needs_upgrade === true && !showUpgradeModalState.value === false
})

const dismissUpgradeModal = () => {
  showUpgradeModalState.value = true
}

const handleInviteSent = () => {
  showInviteModal.value = false
  window.location.reload()
}

// Subscribe to the authenticated user's private channel for notifications
// and personal task updates. Runs once; channel is cleaned up on unmount.
const { listenToUser } = useRealtimeListeners()

onMounted(() => {
  const userId = page.props.auth?.user?.id
  if (userId) {
    listenToUser(userId)
  }
})
</script>
