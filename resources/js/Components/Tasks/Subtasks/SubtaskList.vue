<template>
  <div class="space-y-2">
    <div v-if="task.subtasks?.length === 0" class="text-sm text-gray-500">
      No subtasks yet
    </div>

    <div v-for="subtask in task.subtasks" :key="subtask.id" class="flex items-center gap-2 p-2 bg-gray-50 rounded">
      <input
        type="checkbox"
        :checked="subtask.completed"
        @change="handleToggleSubtask(subtask.id)"
        class="rounded border-gray-300"
      />
      <span
        class="flex-1 text-sm"
        :class="{ 'line-through text-gray-500': subtask.completed }"
      >
        {{ subtask.name }}
      </span>
      <button
        @click="handleDeleteSubtask(subtask.id)"
        class="text-red-600 hover:text-red-700 text-sm"
      >
        âœ•
      </button>
    </div>

    <button
      @click="showCreateForm = !showCreateForm"
      class="text-sm text-blue-600 hover:text-blue-700 font-medium"
    >
      + Add Subtask
    </button>

    <div v-if="showCreateForm" class="flex gap-2">
      <input
        v-model="newSubtaskName"
        type="text"
        placeholder="Subtask name..."
        class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm"
        @keyup.enter="handleCreateSubtask"
      />
      <button
        @click="handleCreateSubtask"
        class="px-2 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700"
      >
        Add
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import type { TaskDetail } from '@/Types/tasks';

interface Props {
  task: TaskDetail;
}

interface Emits {
  (e: 'update'): void;
}

defineProps<Props>();
defineEmits<Emits>();

const showCreateForm = ref(false);
const newSubtaskName = ref('');

function handleToggleSubtask(subtaskId: string) {
  // TODO: Implement subtask toggle
}

function handleDeleteSubtask(subtaskId: string) {
  // TODO: Implement subtask deletion
}

function handleCreateSubtask() {
  // TODO: Implement subtask creation
  newSubtaskName.value = '';
  showCreateForm.value = false;
}
</script>

