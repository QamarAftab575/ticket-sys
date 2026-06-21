<template>
  <div class="space-y-3">
    <div class="space-y-2">
      <label v-for="option in groupOptions" :key="option" class="flex items-center">
        <input
          type="radio"
          :value="option"
          v-model="selectedGroup"
          class="border-gray-300"
        />
        <span class="ml-2 text-sm text-gray-700">{{ formatGroupLabel(option) }}</span>
      </label>
    </div>

    <div class="flex gap-2 pt-4 border-t border-gray-200">
      <button
        @click="handleApply"
        class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition"
      >
        Apply
      </button>
      <button
        @click="$emit('close')"
        class="flex-1 px-3 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition"
      >
        Close
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import type { TaskGroupBy } from '@/Types/tasks';

interface Emits {
  (e: 'apply', groupBy: TaskGroupBy): void;
  (e: 'close'): void;
}

defineEmits<Emits>();

const groupOptions: TaskGroupBy[] = [null, 'assignee', 'due_date', 'priority', 'section', 'project', 'status'];
const selectedGroup = ref<TaskGroupBy>(null);

function formatGroupLabel(option: TaskGroupBy): string {
  const labels: Record<string, string> = {
    null: 'None',
    assignee: 'Assignee',
    due_date: 'Due Date',
    priority: 'Priority',
    section: 'Section',
    project: 'Project',
    status: 'Status',
  };
  return labels[String(option)] || 'None';
}

function handleApply() {
  // Emit apply event
}
</script>

