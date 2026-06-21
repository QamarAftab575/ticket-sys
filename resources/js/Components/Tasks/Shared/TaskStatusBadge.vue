<template>
  <span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
    :class="statusClasses"
  >
    {{ statusLabel }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { TaskStatus } from '@/Types/tasks';

interface Props {
  status: TaskStatus;
}

const props = defineProps<Props>();

const statusLabel = computed(() => {
  const labels: Record<TaskStatus, string> = {
    todo: 'To Do',
    in_progress: 'In Progress',
    done: 'Done',
  };
  return labels[props.status] || 'To Do';
});

const statusClasses = computed(() => {
  const classes: Record<TaskStatus, string> = {
    todo: 'bg-gray-100 text-gray-800',
    in_progress: 'bg-blue-100 text-blue-800',
    done: 'bg-green-100 text-green-800',
  };
  return classes[props.status] || 'bg-gray-100 text-gray-800';
});
</script>

