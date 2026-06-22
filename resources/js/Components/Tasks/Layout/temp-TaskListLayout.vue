<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">
              {{ projectId ? project?.name : 'All Tasks' }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
              {{ tasksStore.tasks.length }} task{{ tasksStore.tasks.length !== 1 ? 's' : '' }}
            </p>
          </div>
          <button
            @click="showCreateForm = true"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            <PlusIcon class="w-5 h-5 mr-2" />
            Add Task
          </button>
        </div>
      </div>

      <!-- Toolbar -->
      <TaskToolbar
        :project-id="projectId"
        @filter-change="handleFilterChange"
        @sort-change="handleSortChange"
        @group-change="handleGroupChange"
      />

      <!-- Content Area -->
      <div class="flex-1 overflow-hidden">
        <div class="h-full flex flex-col">
          <!-- Task Table -->
          <TaskTable
            :project-id="projectId"
            :tasks="displayedTasks"
            :loading="tasksStore.loading"
            @task-select="handleTaskSelect"
            @task-complete="handleTaskComplete"
            @task-delete="handleTaskDelete"
          />
        </div>
      </div>
    </div>

    <!-- Right Sidebar - Task Detail -->
    <TaskDetailPanel
      v-if="selectedTask"
      :task="selectedTask"
      :project="project || null"
      :current-user="currentUser"
      @close="handleCloseSidebar"
      @update="syncTaskFromPanel"
      @open-task="openTaskPanel"
    />

    <!-- Create Task Modal -->
    <TaskCreateForm
      v-if="showCreateForm"
      :project-id="projectId"
      @close="showCreateForm = false"
      @task-created="handleTaskCreated"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { PlusIcon } from '@heroicons/vue/24/outline';
import { useTasksStore } from '@/Stores/useTasksStore';
import type { Task, TaskFilter, TaskSort, TaskGroupBy } from '@/Types/tasks';
import TaskToolbar from './TaskToolbar.vue';
import TaskTable from './TaskTable.vue';
import TaskDetailPanel from '@/Components/Projects/TaskDetailPanel.vue';
import TaskCreateForm from '../Forms/TaskCreateForm.vue';

interface Props {
  projectId?: string | null;
  project?: any;
}

const props = withDefaults(defineProps<Props>(), {
  projectId: null,
});

const tasksStore = useTasksStore();
const showCreateForm = ref(false);

// Computed
const selectedTask = computed(() => tasksStore.selectedTask);

const displayedTasks = computed(() => {
  if (tasksStore.groupBy) {
    // Return grouped tasks
    const grouped = tasksStore.groupedTasks;
    return Object.values(grouped).flat();
  }
  return tasksStore.tasks;
});

// Lifecycle
onMounted(async () => {
  await tasksStore.fetchTasks(props.projectId || null);
});

// Watch for projectId changes
watch(
  () => props.projectId,
  async (newProjectId) => {
    await tasksStore.fetchTasks(newProjectId || null);
  }
);

// Handlers
async function handleFilterChange(filters: TaskFilter) {
  tasksStore.setFilters(filters);
  await tasksStore.fetchTasks(props.projectId || null);
}

async function handleSortChange(sortRules: TaskSort[]) {
  tasksStore.setSort(sortRules);
  await tasksStore.fetchTasks(props.projectId || null);
}

async function handleGroupChange(groupBy: TaskGroupBy) {
  tasksStore.setGroupBy(groupBy);
}

async function handleTaskSelect(taskId: string) {
  tasksStore.selectTask(taskId);
  await tasksStore.fetchTask(taskId);
}

async function handleTaskComplete(taskId: string) {
  try {
    await tasksStore.completeTask(taskId, props.projectId || null);
  } catch (err) {
    console.error('Error completing task:', err);
  }
}

async function handleTaskDelete(taskId: string) {
  if (confirm('Are you sure you want to delete this task?')) {
    try {
      await tasksStore.deleteTask(taskId, props.projectId || null);
    } catch (err) {
      console.error('Error deleting task:', err);
    }
  }
}

function handleCloseSidebar() {
  tasksStore.deselectTask();
}

async function handleTaskUpdate() {
  if (tasksStore.selectedTaskId) {
    await tasksStore.fetchTask(tasksStore.selectedTaskId);
  }
}

async function handleTaskCreated() {
  showCreateForm.value = false;
  await tasksStore.fetchTasks(props.projectId || null);
}
</script>

