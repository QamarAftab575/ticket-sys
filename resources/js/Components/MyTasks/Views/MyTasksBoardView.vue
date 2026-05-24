<template>
  <BoardView
    :project="null"
    :tasks="tasks"
    :sections="sections"
    :is-loading="isLoading"
    @select-task="$emit('select-task', $event)"
    @task-completed="$emit('task-completed', $event)"
    @task-move="$emit('task-move', $event)"
  />
</template>

<script setup lang="ts">
import BoardView from '@/Components/Projects/Views/BoardView.vue';
import type { Task } from '@/Types/tasks';

interface Props {
  tasks: Task[];
  isLoading: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
  'select-task': [taskId: string];
  'task-completed': [taskId: string];
  'task-move': [data: any];
}>();

// My Tasks board sections based on task status
const sections = [
  { id: 'to_do', name: 'To Do', color: '#6B7280', order: 1 },
  { id: 'in_progress', name: 'In Progress', color: '#3B82F6', order: 2 },
  { id: 'in_review', name: 'In Review', color: '#F59E0B', order: 3 },
  { id: 'complete', name: 'Done', color: '#10B981', order: 4 },
];
</script>
