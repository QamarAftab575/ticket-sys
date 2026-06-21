<template>
  <div>
    <Head :title="`${project.name} - Tasks`" />
    <TaskListLayout :project-id="project.id" :project="project" />
  </div>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TaskListLayout from '@/Components/Tasks/Layout/TaskListLayout.vue';

interface Props {
  project: {
    id: string;
    name: string;
  };
}

defineProps<Props>();
</script>

