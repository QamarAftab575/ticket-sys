<template>
  <div class="bg-white border-b border-gray-200 px-6 py-3 flex items-center gap-4 overflow-x-auto">
    <!-- Filter Button -->
    <div class="relative">
      <button
        @click="showFilterPanel = !showFilterPanel"
        class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
      >
        <FunnelIcon class="w-4 h-4 mr-2" />
        Filter
        <span
          v-if="activeFilterCount > 0"
          class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-blue-600 rounded-full"
        >
          {{ activeFilterCount }}
        </span>
      </button>

      <!-- Filter Panel -->
      <div
        v-if="showFilterPanel"
        class="absolute top-full left-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 z-10 p-4"
        @click.stop
      >
        <TaskFilterPanel
          :project-id="projectId"
          @apply="handleApplyFilters"
          @close="showFilterPanel = false"
        />
      </div>
    </div>

    <!-- Sort Button -->
    <div class="relative">
      <button
        @click="showSortPanel = !showSortPanel"
        class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
      >
        <ArrowsUpDownIcon class="w-4 h-4 mr-2" />
        Sort
      </button>

      <!-- Sort Panel -->
      <div
        v-if="showSortPanel"
        class="absolute top-full left-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 z-10 p-4"
        @click.stop
      >
        <TaskSortPanel
          @apply="handleApplySort"
          @close="showSortPanel = false"
        />
      </div>
    </div>

    <!-- Group Button -->
    <div class="relative">
      <button
        @click="showGroupPanel = !showGroupPanel"
        class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
      >
        <Bars3Icon class="w-4 h-4 mr-2" />
        Group
      </button>

      <!-- Group Panel -->
      <div
        v-if="showGroupPanel"
        class="absolute top-full left-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 z-10 p-4"
        @click.stop
      >
        <TaskGroupPanel
          @apply="handleApplyGroup"
          @close="showGroupPanel = false"
        />
      </div>
    </div>

    <!-- Hide Columns Button -->
    <div class="relative">
      <button
        @click="showHidePanel = !showHidePanel"
        class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
      >
        <EyeSlashIcon class="w-4 h-4 mr-2" />
        Hide
      </button>

      <!-- Hide Panel -->
      <div
        v-if="showHidePanel"
        class="absolute top-full left-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 z-10 p-4"
        @click.stop
      >
        <TaskHidePanel
          @close="showHidePanel = false"
        />
      </div>
    </div>

    <!-- Search -->
    <div class="ml-auto flex items-center">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search tasks..."
        class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        @input="handleSearch"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import {
  FunnelIcon,
  ArrowsUpDownIcon,
  Bars3Icon,
  EyeSlashIcon,
} from '@heroicons/vue/24/outline';
import { useTasksStore } from '@/Stores/useTasksStore';
import type { TaskFilter, TaskSort, TaskGroupBy } from '@/Types/tasks';
import TaskFilterPanel from './TaskFilterPanel.vue';
import TaskSortPanel from './TaskSortPanel.vue';
import TaskGroupPanel from './TaskGroupPanel.vue';
import TaskHidePanel from './TaskHidePanel.vue';

interface Props {
  projectId?: string | null;
}

interface Emits {
  (e: 'filter-change', filters: TaskFilter): void;
  (e: 'sort-change', sort: TaskSort[]): void;
  (e: 'group-change', groupBy: TaskGroupBy): void;
}

const props = withDefaults(defineProps<Props>(), {
  projectId: null,
});

defineEmits<Emits>();

const tasksStore = useTasksStore();
const showFilterPanel = ref(false);
const showSortPanel = ref(false);
const showGroupPanel = ref(false);
const showHidePanel = ref(false);
const searchQuery = ref('');

const activeFilterCount = computed(() => {
  let count = 0;
  if (tasksStore.filters.assigneeIds?.length) count++;
  if (tasksStore.filters.priorities?.length) count++;
  if (tasksStore.filters.statuses?.length) count++;
  if (tasksStore.filters.tagIds?.length) count++;
  if (tasksStore.filters.dueDateOption) count++;
  if (tasksStore.filters.searchQuery) count++;
  return count;
});

function handleApplyFilters(filters: TaskFilter) {
  showFilterPanel.value = false;
  // Emit to parent
}

function handleApplySort(sort: TaskSort[]) {
  showSortPanel.value = false;
  // Emit to parent
}

function handleApplyGroup(groupBy: TaskGroupBy) {
  showGroupPanel.value = false;
  // Emit to parent
}

function handleSearch() {
  tasksStore.setFilters({
    ...tasksStore.filters,
    searchQuery: searchQuery.value || undefined,
  });
}
</script>

