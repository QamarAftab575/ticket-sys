<template>
  <div class="space-y-3">
    <div v-for="(rule, index) in sortRules" :key="index" class="flex items-center gap-2">
      <select
        v-model="rule.field"
        class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option value="name">Task Name</option>
        <option value="due_date">Due Date</option>
        <option value="priority">Priority</option>
        <option value="created_at">Created Date</option>
        <option value="assignee">Assignee</option>
        <option value="status">Status</option>
      </select>

      <select
        v-model="rule.direction"
        class="px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option value="asc">Ascending</option>
        <option value="desc">Descending</option>
      </select>

      <button
        @click="removeSort(index)"
        class="p-1 text-red-600 hover:bg-red-50 rounded transition"
      >
        âœ•
      </button>
    </div>

    <button
      @click="addSort"
      class="w-full px-3 py-2 text-sm text-blue-600 border border-blue-300 rounded-lg hover:bg-blue-50 transition"
    >
      + Add Sort
    </button>

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
import type { TaskSort } from '@/Types/tasks';

interface Emits {
  (e: 'apply', sort: TaskSort[]): void;
  (e: 'close'): void;
}

defineEmits<Emits>();

const sortRules = ref<TaskSort[]>([
  { field: 'created_at', direction: 'desc' },
]);

function addSort() {
  sortRules.value.push({ field: 'name', direction: 'asc' });
}

function removeSort(index: number) {
  sortRules.value.splice(index, 1);
}

function handleApply() {
  // Emit apply event
}
</script>

