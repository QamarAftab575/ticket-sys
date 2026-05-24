<template>
  <FilesView
    :project="null"
    :tasks="tasks"
    :is-loading="isLoading"
    @select-task="$emit('select-task', $event)"
  />
</template>

<script setup lang="ts">
import FilesView from '@/Components/Projects/Views/FilesView.vue';
import type { Task } from '@/Types/tasks';

interface Props {
  tasks: Task[];
  isLoading: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
  'select-task': [taskId: string];
}>();
</script>
