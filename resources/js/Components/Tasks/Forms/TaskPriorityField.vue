<template>
  <select
    :value="task.priority"
    @change="handlePriorityChange"
    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
  >
    <option value="none">None</option>
    <option value="low">Low</option>
    <option value="medium">Medium</option>
    <option value="high">High</option>
    <option value="urgent">Urgent</option>
  </select>
</template>

<script setup lang="ts">
import { useTasksStore } from '@/Stores/useTasksStore';
import type { TaskDetail, TaskPriority } from '@/Types/tasks';

interface Props {
  task: TaskDetail;
  projectId?: string | null;
}

interface Emits {
  (e: 'update'): void;
}

const props = withDefaults(defineProps<Props>(), {
  projectId: null,
});

defineEmits<Emits>();

const tasksStore = useTasksStore();

async function handlePriorityChange(event: Event) {
  const target = event.target as HTMLSelectElement;
  const priority = target.value as TaskPriority;

  try {
    await tasksStore.updateTask(
      props.task.id,
      { priority },
      props.projectId || null
    );
  } catch (err) {
    console.error('Error updating priority:', err);
  }
}
</script>
