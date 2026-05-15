<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Create Task</h2>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Task Name</label>
          <input
            v-model="formData.name"
            type="text"
            placeholder="Enter task name..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
          <select
            v-model="formData.priority"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="none">None</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="urgent">Urgent</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
          <input
            v-model="formData.due_date"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div class="flex gap-2 pt-4">
          <button
            type="submit"
            :disabled="loading"
            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition"
          >
            {{ loading ? 'Creating...' : 'Create Task' }}
          </button>
          <button
            type="button"
            @click="$emit('close')"
            class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition"
          >
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useTasksStore } from '@/Stores/useTasksStore';
import type { TaskFormData, TaskPriority } from '@/Types/tasks';

interface Props {
  projectId?: string | null;
}

interface Emits {
  (e: 'close'): void;
  (e: 'task-created'): void;
}

const props = withDefaults(defineProps<Props>(), {
  projectId: null,
});

defineEmits<Emits>();

const tasksStore = useTasksStore();
const loading = ref(false);
const formData = ref<TaskFormData>({
  name: '',
  priority: 'none' as TaskPriority,
  visibility: 'everyone',
});

async function handleSubmit() {
  loading.value = true;
  try {
    await tasksStore.createTask(formData.value, props.projectId || null);
    // Emit success event
  } catch (err) {
    console.error('Error creating task:', err);
  } finally {
    loading.value = false;
  }
}
</script>
