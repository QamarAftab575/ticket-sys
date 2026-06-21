<template>
  <div class="flex-1 overflow-auto">
    <table class="w-full border-collapse">
      <thead class="bg-gray-50 sticky top-0 border-b border-gray-200">
        <tr>
          <th class="px-6 py-3 text-left">
            <input
              type="checkbox"
              class="rounded border-gray-300"
              @change="toggleSelectAll"
            />
          </th>
          <th
            v-for="column in visibleColumns"
            :key="column.id"
            class="px-6 py-3 text-left text-sm font-semibold text-gray-700 cursor-pointer hover:bg-gray-100"
            @click="handleColumnSort(column.id)"
          >
            <div class="flex items-center gap-2">
              {{ column.label }}
              <ArrowsUpDownIcon v-if="column.sortable" class="w-4 h-4 text-gray-400" />
            </div>
          </th>
          <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700 w-12">
            Actions
          </th>
        </tr>
      </thead>
      <tbody>
        <template v-if="loading">
          <tr v-for="i in 5" :key="`skeleton-${i}`">
            <td colspan="100%" class="px-6 py-4">
              <div class="h-8 bg-gray-200 rounded animate-pulse" />
            </td>
          </tr>
        </template>

        <template v-else-if="tasks.length === 0">
          <tr>
            <td colspan="100%" class="px-6 py-12 text-center">
              <div class="text-gray-500">
                <p class="text-lg font-medium">No tasks yet</p>
                <p class="text-sm mt-1">Create your first task to get started</p>
              </div>
            </td>
          </tr>
        </template>

        <template v-else>
          <TaskRow
            v-for="task in tasks"
            :key="task.id"
            :task="task"
            :project-id="projectId"
            :selected="selectedTaskIds.includes(task.id)"
            @select="handleTaskSelect"
            @click="$emit('task-select', task.id)"
            @complete="$emit('task-complete', task.id)"
            @delete="$emit('task-delete', task.id)"
          />
        </template>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { ArrowsUpDownIcon } from '@heroicons/vue/24/outline';
import { useTasksStore } from '@/Stores/useTasksStore';
import type { Task } from '@/Types/tasks';
import TaskRow from '@/Components/Projects/Views/TaskRow.vue';

interface Props {
  projectId?: string | null;
  tasks: Task[];
  loading?: boolean;
}

interface Emits {
  (e: 'task-select', taskId: string): void;
  (e: 'task-complete', taskId: string): void;
  (e: 'task-delete', taskId: string): void;
}

const props = withDefaults(defineProps<Props>(), {
  projectId: null,
  loading: false,
});

defineEmits<Emits>();

const tasksStore = useTasksStore();
const selectedTaskIds = ref<string[]>([]);

const visibleColumns = computed(() => tasksStore.visibleColumns);

function toggleSelectAll(event: Event) {
  const target = event.target as HTMLInputElement;
  if (target.checked) {
    selectedTaskIds.value = props.tasks.map(t => t.id);
  } else {
    selectedTaskIds.value = [];
  }
}

function handleTaskSelect(taskId: string) {
  const index = selectedTaskIds.value.indexOf(taskId);
  if (index > -1) {
    selectedTaskIds.value.splice(index, 1);
  } else {
    selectedTaskIds.value.push(taskId);
  }
}

function handleColumnSort(columnId: string) {
  const currentSort = tasksStore.sort.find(s => s.field === columnId);
  
  if (!currentSort) {
    tasksStore.addSort({ field: columnId as any, direction: 'asc' });
  } else if (currentSort.direction === 'asc') {
    currentSort.direction = 'desc';
  } else {
    tasksStore.removeSort(columnId);
  }
}
</script>

