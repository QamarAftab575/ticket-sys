<template>
  <span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
    :class="priorityClasses"
  >
    {{ priorityLabel }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { TaskPriority } from '@/Types/tasks';

interface Props {
  priority: TaskPriority;
}

const props = defineProps<Props>();

const priorityLabel = computed(() => {
  const labels: Record<TaskPriority, string> = {
    none: 'None',
    low: 'Low',
    medium: 'Medium',
    high: 'High',
    urgent: 'Urgent',
  };
  return labels[props.priority] || 'None';
});

const priorityClasses = computed(() => {
  const classes: Record<TaskPriority, string> = {
    none: 'bg-gray-100 text-gray-800',
    low: 'bg-blue-100 text-blue-800',
    medium: 'bg-yellow-100 text-yellow-800',
    high: 'bg-orange-100 text-orange-800',
    urgent: 'bg-red-100 text-red-800',
  };
  return classes[props.priority] || 'bg-gray-100 text-gray-800';
});
</script>

