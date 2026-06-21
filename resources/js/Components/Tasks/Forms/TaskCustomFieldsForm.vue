<template>
  <div class="space-y-3">
    <div v-for="value in task.customFieldValues" :key="value.id" class="flex items-center gap-2">
      <label class="text-sm font-medium text-gray-700 flex-1">
        {{ value.custom_field?.name }}
      </label>
      <input
        :value="value.value"
        @change="handleFieldChange(value.id, $event)"
        class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import type { TaskDetail } from '@/Types/tasks';

interface Props {
  task: TaskDetail;
}

interface Emits {
  (e: 'update'): void;
}

defineProps<Props>();
defineEmits<Emits>();

function handleFieldChange(fieldId: string, event: Event) {
  // TODO: Implement custom field update
}
</script>

