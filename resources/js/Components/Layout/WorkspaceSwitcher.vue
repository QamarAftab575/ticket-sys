<template>
  <div class="relative">
    <!-- Workspace Switcher Trigger Button -->
    <button
      @click="isOpen = !isOpen"
      :class="[
        'group relative w-full flex items-center gap-2.5 rounded-lg transition-all duration-150',
        collapsed ? 'justify-center p-2.5' : 'px-3 py-2.5',
        isOpen
          ? 'bg-gray-700 text-gray-200'
          : 'text-gray-400 hover:bg-gray-700 hover:text-gray-200'
      ]"
    >
      <!-- Avatar -->
      <span class="shrink-0 w-6 h-6 rounded-md bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-xs font-bold uppercase shadow-sm">
        {{ activeWorkspace?.name?.charAt(0) || '?' }}
      </span>

      <!-- Workspace Name + Chevron -->
      <span v-if="!collapsed" class="flex-1 flex items-center gap-2 min-w-0">
        <span class="truncate text-sm font-semibold">{{ activeWorkspace?.name || 'Select Workspace' }}</span>
        <img
          src="/assets/images/up-and-down-arrows-svgrepo-com.svg"
          alt="Toggle workspace menu"
          class="w-4 h-4 shrink-0 transition-transform duration-200"
          :class="isOpen ? 'rotate-180' : ''"
        />
      </span>
      <!-- Tooltip for collapsed mode -->
      <span v-if="collapsed"
        class="pointer-events-none absolute left-full ml-3 px-2.5 py-1.5 rounded-lg bg-gray-900 text-white text-xs font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-150 z-50 shadow-lg"
      >
        {{ activeWorkspace?.name || 'Workspace' }}
        <span class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"/>
      </span>
    </button>

    <!-- Dropdown Menu -->
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        class="absolute bottom-full left-0 right-0 mb-2 bg-white rounded-lg shadow-xl border border-gray-200 z-50 overflow-hidden w-full"
      >
        <!-- Search Input -->
        <div class="p-3 border-b border-gray-100">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search workspaces..."
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
          />
        </div>

        <!-- Workspace List -->
        <div class="max-h-80 overflow-y-auto">
          <!-- Active Workspace Section -->
          <div v-if="filteredWorkspaces.length > 0" class="px-2 py-2">
            <p class="px-2 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">Active</p>
            
            <button
              v-for="ws in filteredWorkspaces"
              :key="ws.id"
              @click="switchWorkspace(ws)"
              :class="[
                'w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
                activeWorkspace?.id === ws.id
                  ? 'bg-indigo-100 text-indigo-700'
                  : 'text-gray-700 hover:bg-gray-50'
              ]"
            >
              <!-- Avatar -->
              <span class="shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-xs font-bold uppercase shadow-sm">
                {{ ws.name?.charAt(0) }}
              </span>

              <!-- Workspace Info -->
              <div class="flex-1 text-left min-w-0">
                <p class="font-medium truncate">{{ ws.name }}</p>
                <p v-if="ws.description" class="text-xs text-gray-500 truncate">{{ ws.description }}</p>
              </div>

              <!-- Active Indicator -->
              <svg
                v-if="activeWorkspace?.id === ws.id"
                class="w-5 h-5 text-indigo-600 shrink-0"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
            </button>
          </div>

          <!-- Empty State -->
          <div v-else class="px-4 py-8 text-center">
            <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-gray-500">No workspaces found</p>
          </div>
        </div>

        <!-- Footer: Create/Manage Workspaces -->
        <div class="border-t border-gray-100 p-2">
          <Link
            v-if="isOwnerOfAnyWorkspace"
            href="/workspace/create"
            @click="isOpen = false"
            class="flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Workspace
          </Link>
        </div>
      </div>
    </Transition>

    <!-- Click Outside Handler -->
    <div
      v-if="isOpen"
      class="fixed inset-0 z-40"
      @click="isOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useSidebarData } from '@/Composables/useSidebarData'
import { useActiveWorkspace } from '@/Composables/useActiveWorkspace'
import { onWorkspaceSwitched } from '@/Utils/cacheManager'

const props = defineProps({
  collapsed: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['workspace-changed', 'close'])

const isOpen = ref(false)
const searchQuery = ref('')

// Use composables for workspace data
const { userWorkspaces, isOwnerOfAnyWorkspace } = useSidebarData()
const { activeWorkspace, switchWorkspace: setActiveWorkspaceHelper, initializeActiveWorkspace } = useActiveWorkspace(userWorkspaces)

onMounted(() => {
  // Initialize active workspace on component mount
  if (userWorkspaces.value && userWorkspaces.value.length > 0) {
    initializeActiveWorkspace(userWorkspaces.value)
  }
})

// Filter workspaces by search query
const filteredWorkspaces = computed(() => {
  if (!searchQuery.value.trim()) {
    return userWorkspaces.value
  }
  
  const query = searchQuery.value.toLowerCase()
  return userWorkspaces.value.filter(ws =>
    ws.name.toLowerCase().includes(query) ||
    ws.description?.toLowerCase().includes(query)
  )
})

const switchWorkspace = async (workspace) => {
  if (workspace.id !== activeWorkspace.value?.id) {
    // Clear cache before switching
    onWorkspaceSwitched()
    
    // Update browser storage via helper
    setActiveWorkspaceHelper(workspace)
    
    // Navigate to workspace
    router.visit(`/workspace/${workspace.id}/dashboard`)
  }
  
  isOpen.value = false
  emit('workspace-changed', workspace)
  emit('close')
}
</script>

