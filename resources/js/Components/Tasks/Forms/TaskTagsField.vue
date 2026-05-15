<template>
  <div class="flex flex-wrap gap-2">
    <span
      v-for="tag in task.tags"
      :key="tag.id"
      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
      :style="{
        backgroundColor: tag.color + '20',
        color: tag.color,
      }"
    >
      {{ tag.name }}
      <button
        @click="handleRemoveTag(tag.id)"
        class="ml-1 text-current hover:opacity-70"
      >
        ✕
      </button>
    </span>
    <button
      @click="showTagPicker = !showTagPicker"
      class="px-2 py-1 text-xs text-gray-600 border border-gray-300 rounded hover:bg-gray-50"
    >
      + Add Tag
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
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

const showTagPicker = ref(false);

function handleRemoveTag(tagId: string) {
  // TODO: Implement tag removal
}
</script>
