<template>
  <div>
    <Head title="All Tasks" />
    <TaskListLayout :project-id="null" />
  </div>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TaskListLayout from '@/Components/Tasks/Layout/TaskListLayout.vue';
</script>
