<template>
  <div class="border border-gray-200 rounded-lg overflow-hidden focus-within:border-indigo-300 focus-within:ring-1 focus-within:ring-indigo-200 transition-all">
    <RichEditor
      v-model="localDescription"
      placeholder="Add a description ¦"
      :show-toolbar="true"
      :task-id="task.id"
      :project-id="projectId"
      @blur="handleBlur"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useTasksStore } from '@/Stores/useTasksStore';
import type { TaskDetail } from '@/Types/tasks';
import RichEditor from '@/Components/Projects/RichEditor.vue';

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
const localDescription = ref(props.task.description ?? '');

// Keep in sync if parent task changes (e.g. switching tasks)
watch(() => props.task.description, (val) => {
  localDescription.value = val ?? '';
});

async function handleBlur(html: string) {
  localDescription.value = html;
  try {
    await tasksStore.updateTask(
      props.task.id,
      { description: html },
      props.projectId || null
    );
  } catch (err) {
    console.error('Error updating description:', err);
  }
}
</script>

