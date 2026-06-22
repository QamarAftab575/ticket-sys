<template>
  <div class="p-5">
    <CalendarView
      :project="null"
      :tasks="tasks"
      :is-loading="isLoading"
      @select-task="$emit('select-task', $event)"
      @task-completed="$emit('task-completed', $event)"
    />
  </div>
</template>

<script setup lang="ts">
import CalendarView from '@/Components/Projects/Views/CalendarView.vue';
import type { Task } from '@/Types/tasks';

interface Props {
  tasks: Task[];
  isLoading: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
  'select-task': [taskId: string];
  'task-completed': [taskId: string];
}>();
</script>

