<template>
  <div class="relative">
    <input
      type="date"
      :value="task.due_date?.split('T')[0]"
      @change="handleDateChange"
      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
    />
  </div>
</template>

<script setup lang="ts">
import { useTasksStore } from '@/Stores/useTasksStore';
import type { TaskDetail } from '@/Types/tasks';

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

async function handleDateChange(event: Event) {
  const target = event.target as HTMLInputElement;
  const dueDate = target.value ? new Date(target.value).toISOString() : null;

  try {
    await tasksStore.updateTask(
      props.task.id,
      { due_date: dueDate },
      props.projectId || null
    );
  } catch (err) {
    console.error('Error updating due date:', err);
  }
}
</script>

