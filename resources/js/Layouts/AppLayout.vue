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
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppSidebar from '@/Components/Layout/AppSidebar.vue'
import AppHeader from '@/Components/Layout/AppHeader.vue'
import InviteModal from '@/Components/Workspace/InviteModal.vue'
import { useSidebarData } from '@/Composables/useSidebarData'

const page = usePage()
const showInviteModal = ref(false)
const mobileOpen = ref(false)

// Optional props for backward compatibility
defineProps({
  userWorkspaces: Array,
  currentWorkspace: Object,
  userRole: String,
})

// Use composable for independent data loading
const { userWorkspaces, currentWorkspaceId, currentWorkspace } = useSidebarData()

const currentRoute = computed(() => {
  const url = page.url
  if (url.includes('/dashboard')) return 'dashboard'
  if (url.includes('/inbox')) return 'inbox'
  if (url.includes('/projects')) return 'projects'
  if (url.includes('/profile')) return 'profile'
  if (url.includes('/settings')) return 'settings'
  return 'dashboard'
})

const handleInviteSent = () => {
  showInviteModal.value = false
  window.location.reload()
}
</script>
