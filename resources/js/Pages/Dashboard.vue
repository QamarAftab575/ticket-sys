<template>
  <AppLayout :user-workspaces="userWorkspaces" :current-workspace="workspace" :user-role="userRole">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Greeting Header -->
      <DashboardGreeting />

      <!-- My Tasks Section -->
      <div class="mb-12">
        <DashboardTasks :user-initials="userInitials" />
      </div>

      <!-- Projects & Notes Section -->
      <div class="mb-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Left Column: Projects (2/3 width) -->
          <div class="lg:col-span-1">
            <ProjectsSection :projects="projects" />
          </div>

          <!-- Right Column: Private Notes (1/3 width) -->
          <div class="lg:col-span-1">
            <PrivateNotesSection />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import DashboardGreeting from '@/Components/Dashboard/DashboardGreeting.vue'
import DashboardTasks from '@/Components/Dashboard/DashboardTasks.vue'
import ProjectsSection from '@/Components/Dashboard/ProjectsSection.vue'
import PrivateNotesSection from '@/Components/Dashboard/PrivateNotesSection.vue'

const page = usePage()

const props = defineProps({
  projects: Array,
  workspaces: Array,
  userRole: String,
  workspace: Object,
  userWorkspaces: Array,
})

const userInitials = computed(() => {
  const name = page.props.auth?.user?.name || 'User'
  return name.split(' ').map(n => n.charAt(0)).join('').toUpperCase().slice(0, 2)
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

