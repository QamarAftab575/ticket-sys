<template>
  <AppLayout :user-workspaces="userWorkspaces" :current-workspace="workspace" :user-role="userRole">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Workspaces Section -->
      <div class="mb-12">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold">Your Workspaces</h3>
          <Link
            href="/organizations"
            class="text-blue-600 hover:underline"
          >
            View All
          </Link>
        </div>

        <div v-if="workspaces.length === 0" class="text-gray-500 text-center py-8">
          No workspaces yet. Complete onboarding to create one.
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-12">
          <Link
            v-for="workspace in workspaces"
            :key="workspace.id"
            :href="`/workspace/${workspace.id}/dashboard`"
            class="block border rounded-lg p-6 hover:shadow-lg transition bg-white"
          >
            <div class="flex items-center gap-4">
              <div
                :style="{ backgroundColor: workspace.avatar_color }"
                class="w-12 h-12 rounded-lg flex items-center justify-center text-white text-xl font-bold flex-shrink-0"
              >
                {{ workspace.name.charAt(0).toUpperCase() }}
              </div>
              <div class="flex-1">
                <h4 class="font-semibold text-gray-900">{{ workspace.name }}</h4>
                <p class="text-sm text-gray-600">{{ workspace.members_count || 0 }} members</p>
              </div>
            </div>
            <p v-if="workspace.description" class="text-sm text-gray-600 mt-3 line-clamp-2">
              {{ workspace.description }}
            </p>
          </Link>
        </div>
      </div>

      <!-- Projects Section -->
      <div class="mb-12">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold">Your Projects</h3>
          <Link
            href="/projects"
            class="text-blue-600 hover:underline"
          >
            View All
          </Link>
        </div>

        <div v-if="projects.length === 0" class="text-gray-500 text-center py-8">
          No projects yet. <Link href="/projects/create" class="text-blue-600 hover:underline">Create one</Link>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <Link
            v-for="project in projects"
            :key="project.id"
            :href="`/projects/${project.id}`"
            class="block border rounded-lg p-4 hover:shadow-lg transition"
          >
            <h4 class="font-semibold text-blue-600 hover:underline">{{ project.name }}</h4>
            <p class="text-sm text-gray-600 mt-1">{{ project.description }}</p>
            <div class="flex gap-4 mt-4 text-xs text-gray-500">
              <span>Status: <strong>{{ formatStatus(project.status) }}</strong></span>
              <span>Members: <strong>{{ project.members.length }}</strong></span>
            </div>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
  projects: Array,
  workspaces: Array,
  userRole: String,
  workspace: Object,
  userWorkspaces: Array,
})

const formatStatus = (status) => {
  const statusMap = {
    on_track: 'On Track',
    at_risk: 'At Risk',
    off_track: 'Off Track',
    archived: 'Archived',
  };
  return statusMap[status] || status;
};
</script>
