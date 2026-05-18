<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">My Tasks</h1>
            <p class="text-sm text-gray-500 mt-1">
              {{ myTasksStore.tasks.length }} task{{ myTasksStore.tasks.length !== 1 ? 's' : '' }}
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
      <MyTasksToolbar
        @filter-change="handleFilterChange"
        @sort-change="handleSortChange"
        @group-change="handleGroupChange"
      />

      <!-- Content Area -->
      <div class="flex-1 overflow-hidden">
        <div class="h-full flex flex-col">
          <!-- Task Table -->
          <MyTasksTable
            :tasks="displayedTasks"
            :loading="myTasksStore.loading"
            @task-select="handleTaskSelect"
            @task-complete="handleTaskComplete"
            @task-delete="handleTaskDelete"
          />
        </div>
      </div>
    </div>

    <!-- Right Sidebar - Task Detail -->
    <TaskDetailSidebar
      v-if="selectedTask"
      :task="selectedTask"
      :project-id="selectedTask?.project_id"
      @close="handleCloseSidebar"
      @task-update="handleTaskUpdate"
    />

    <!-- Create Task Modal -->
    <TaskCreateForm
      v-if="showCreateForm"
      :project-id="null"
      @close="showCreateForm = false"
      @task-created="handleTaskCreated"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { PlusIcon } from '@heroicons/vue/24/outline';
import { useMyTasksStore } from '@/Stores/useMyTasksStore';
import type { Task, TaskFilter, TaskSort, TaskGroupBy } from '@/Types/tasks';
import MyTasksToolbar from './MyTasksToolbar.vue';
import MyTasksTable from './MyTasksTable.vue';
import TaskDetailSidebar from '@/Components/Tasks/Sidebar/TaskDetailSidebar.vue';
import TaskCreateForm from '@/Components/Tasks/Forms/TaskCreateForm.vue';

const myTasksStore = useMyTasksStore();
const showCreateForm = ref(false);

// Computed
const selectedTask = computed(() => myTasksStore.selectedTask);

const displayedTasks = computed(() => {
  if (myTasksStore.groupBy) {
    // Return grouped tasks
    const grouped = myTasksStore.groupedTasks;
    return Object.values(grouped).flat();
  }
  return myTasksStore.tasks;
});

// Lifecycle
onMounted(async () => {
  await myTasksStore.fetchTasks();
});

// Handlers
async function handleFilterChange(filters: TaskFilter) {
  myTasksStore.setFilters(filters);
  await myTasksStore.fetchTasks();
}

async function handleSortChange(sortRules: TaskSort[]) {
  myTasksStore.setSort(sortRules);
  await myTasksStore.fetchTasks();
}

async function handleGroupChange(groupBy: TaskGroupBy) {
  myTasksStore.setGroupBy(groupBy);
}

async function handleTaskSelect(taskId: string) {
  myTasksStore.selectTask(taskId);
  await myTasksStore.fetchTask(taskId);
}

async function handleTaskComplete(taskId: string) {
  try {
    await myTasksStore.completeTask(taskId);
  } catch (err) {
    console.error('Error completing task:', err);
  }
}

async function handleTaskDelete(taskId: string) {
  if (confirm('Are you sure you want to delete this task?')) {
    try {
      await myTasksStore.deleteTask(taskId);
    } catch (err) {
      console.error('Error deleting task:', err);
    }
  }
}

function handleCloseSidebar() {
  myTasksStore.deselectTask();
}

async function handleTaskUpdate() {
  if (myTasksStore.selectedTaskId) {
    await myTasksStore.fetchTask(myTasksStore.selectedTaskId);
  }
}

async function handleTaskCreated() {
  showCreateForm.value = false;
  await myTasksStore.fetchTasks();
}
</script>