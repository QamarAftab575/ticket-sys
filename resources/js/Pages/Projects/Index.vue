<template>
  <AppLayout :user-workspaces="userWorkspaces" :current-workspace="currentWorkspace" :user-role="userRole">
    <Head title="Projects" />
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <div class="flex justify-between items-start gap-6">
            <div class="flex-1">
              <h1 class="text-3xl font-bold text-gray-900 mb-2">Projects</h1>
              <p class="text-gray-600">Manage and track all your projects in one place</p>
            </div>
            <Link
              href="/projects/create"
              class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shrink-0 cursor-pointer"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Create Project
            </Link>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <ProjectList :projects="projects.data" :pagination="projects" :filters="filters" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, Head } from '@inertiajs/vue3';
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

