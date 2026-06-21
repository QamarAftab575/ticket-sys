<template>
  <div class="md:col-span-1">
    <div class="bg-white rounded-lg shadow p-4 space-y-2">
      <Link
        href="/settings"
        :class="[
          'block px-4 py-2 rounded-lg font-medium transition-colors',
          isActive('/settings') 
            ? 'bg-blue-50 text-blue-600' 
            : 'text-gray-700 hover:bg-gray-50'
        ]"
      >
        My Workspaces
      </Link>

      <!-- Billing Section - Only show if owner of active workspace -->
      <div v-if="isOwnerOfActiveWorkspace" class="mt-4 pt-4 border-t">
        <h3 class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Billing</h3>
        <Link
          href="/settings/subscriptions"
          :class="[
            'block px-4 py-2 rounded-lg font-medium transition-colors ml-2',
            isActive('/settings/subscriptions') 
              ? 'bg-blue-50 text-blue-600' 
              : 'text-gray-700 hover:bg-gray-50'
          ]"
        >
          Plans & Subscriptions
        </Link>
      </div>

      <!-- Integrations Section - Only show if owner of active workspace -->
      <div v-if="isOwnerOfActiveWorkspace" class="mt-4 pt-4 border-t">
        <h3 class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Integrations</h3>
        <Link
          href="/settings/integrations/tokens"
          :class="[
            'block px-4 py-2 rounded-lg font-medium transition-colors ml-2',
            isActive('/settings/integrations/tokens') 
              ? 'bg-blue-50 text-blue-600' 
              : 'text-gray-700 hover:bg-gray-50'
          ]"
        >
          Access Tokens
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { useWorkspaceOwnership } from '@/Composables/useWorkspaceOwnership'

const page = usePage()

const props = defineProps({
  userWorkspaces: {
    type: Array,
    default: () => []
  }
})

// Use the workspace ownership composable with a computed ref to track prop changes
const userWorkspacesComputed = computed(() => props.userWorkspaces)
const { isOwnerOfActiveWorkspace } = useWorkspaceOwnership(userWorkspacesComputed)

const isActive = (route) => {
  return page.url === route || page.url.startsWith(route + '/')
}
</script>

