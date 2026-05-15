<template>
  <div class="w-64 bg-white border-r border-gray-200 flex flex-col">
    <!-- Logo -->
    <div class="px-6 py-4 border-b border-gray-200">
      <Link href="/" class="text-xl font-bold text-blue-600">Asira</Link>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
      <!-- Main Navigation -->
      <SidebarLink href="/" label="Home" />
      <SidebarLink href="/my-tasks" label="My Tasks" />
      <SidebarLink href="/inbox" label="Inbox" />

      <!-- Insights Section -->
      <div class="pt-4">
        <p class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase">Insights</p>
        <SidebarLink href="/reporting" label="Reporting" />
        <SidebarLink href="/portfolios" label="Portfolios" />
        <SidebarLink href="/goals" label="Goals" />
      </div>

      <!-- Projects Section -->
      <div class="pt-4">
        <p class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase">Projects</p>
        <div class="space-y-1">
          <SidebarLink
            v-for="project in projects"
            :key="project.id"
            :href="`/projects/${project.id}`"
            :label="project.name"
          />
        </div>
      </div>

      <!-- Team Section -->
      <div class="pt-4">
        <p class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase">Team</p>
        
        <!-- Workspace Switcher -->
        <div v-if="userWorkspaces.length > 1" class="px-3 py-2 mb-3">
          <select
            @change="switchWorkspace"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
          >
            <option v-for="ws in userWorkspaces" :key="ws.id" :value="ws.id">
              {{ ws.name }}
            </option>
          </select>
        </div>

        <!-- Current Workspace -->
        <div class="px-3 py-2 text-sm font-medium text-gray-900">
          {{ workspace.name }}
        </div>
      </div>
    </nav>

    <!-- Bottom Actions -->
    <div class="border-t border-gray-200 p-4 space-y-2">
      <button
        @click="$emit('invite')"
        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition"
      >
        + Invite Teammates
      </button>
      <Link
        v-if="canEdit"
        href="/settings"
        class="block w-full px-4 py-2 text-center text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium transition"
      >
        Settings
      </Link>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import SidebarLink from '@/Components/Workspace/SidebarLink.vue'

const props = defineProps({
  workspace: Object,
  userWorkspaces: Array,
  userRole: String,
})

const emit = defineEmits(['invite'])

const projects = ref(props.workspace.projects || [])
const canEdit = ref(['owner', 'admin'].includes(props.userRole))

const switchWorkspace = (e) => {
  const workspaceId = e.target.value
  router.post(`/workspace/${workspaceId}/switch`)
}
</script>
