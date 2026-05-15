<template>
  <div class="space-y-3">
    <div class="space-y-2">
      <label v-for="column in columns" :key="column.id" class="flex items-center">
        <input
          type="checkbox"
          :checked="column.visible"
          @change="toggleColumn(column.id)"
          class="rounded border-gray-300"
        />
        <span class="ml-2 text-sm text-gray-700">{{ column.label }}</span>
      </label>
    </div>

    <div class="flex gap-2 pt-4 border-t border-gray-200">
      <button
        @click="$emit('close')"
        class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition"
      >
        Done
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useTasksStore } from '@/Stores/useTasksStore';

interface Emits {
  (e: 'close'): void;
}

defineEmits<Emits>();

const tasksStore = useTasksStore();

const columns = tasksStore.columns;

function toggleColumn(columnId: string) {
  tasksStore.toggleColumn(columnId);
}
</script>
