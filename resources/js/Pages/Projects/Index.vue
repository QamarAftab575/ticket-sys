<template>
  <AppLayout :user-workspaces="userWorkspaces" :current-workspace="currentWorkspace" :user-role="userRole">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Projects</h2>
        <Link
          href="/projects/create"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Create Project
        </Link>
      </div>

      <ProjectList :projects="projects.data" :pagination="projects" :filters="filters" />
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProjectList from '@/Components/Projects/ProjectList.vue';

defineProps({
  projects: Object,
  filters: Object,
  userWorkspaces: Array,
  currentWorkspace: Object,
  userRole: String,
});
</script>
