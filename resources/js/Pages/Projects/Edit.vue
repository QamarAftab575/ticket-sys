<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-bold">Edit Project</h2>
    </template>

    <div class="py-12">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <ProjectForm :project="project" :team-members="teamMembers" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProjectForm from '@/Components/Projects/ProjectForm.vue';

defineProps({
  project: Object,
  teamMembers: Array,
});
</script>
