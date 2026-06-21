<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header with View Switcher -->
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-4">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">My Tasks</h1>
              <p class="text-sm text-gray-500 mt-1">
                {{ myTasksStore.tasks.length }} task{{ myTasksStore.tasks.length !== 1 ? 's' : '' }}
              </p>
            </div>
            <!-- View Type Selector -->
            <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1 ml-4">
              <button
                v-for="view in viewOptions"
                :key="view.id"
                @click="currentView = view.id"
                :title="view.label"
                :class="[
                  'p-2 rounded transition-colors',
                  currentView === view.id
                    ? 'bg-white text-blue-600 shadow-sm'
                    : 'text-gray-600 hover:text-gray-900'
                ]"
              >
                <component :is="view.icon" class="w-5 h-5" />
              </button>
            </div>
          </div>
          <!-- Removed Add Task button - use inline creation in sections instead -->
        </div>
      </div>

      <!-- Toolbar -->
      <MyTasksToolbar
        :initial-sort="myTasksStore.sort"
        :initial-group="myTasksStore.groupBy"
        :initial-filters="myTasksStore.filters"
        @filter-change="handleFilterChange"
        @sort-change="handleSortChange"
        @group-change="handleGroupChange"
      />

      <!-- Content Area -->
      <div class="flex-1 overflow-hidden">
        <div class="h-full flex flex-col">
          <!-- List View (Default) -->
          <MyTasksListView
            v-if="currentView === 'list'"
            :tasks="displayedTasks"
            :filters="myTasksStore.filters"
            :sort="myTasksStore.sort"
            :grouping="myTasksStore.groupBy"
            :section-order="myTasksStore.sectionOrder"
            :sections="myTasksStore.sections"
            :collapsed-sections="myTasksStore.collapsedSections"
            :is-loading="myTasksStore.loading"
            :workspace-members="workspaceMembers"
            @select-task="handleTaskSelect"
            @task-completed="handleTaskComplete"
            @task-created="handleTaskCreatedInline"
            @task-move="handleTaskMove"
            @update-dates="handleUpdateDates"
            @update-assignee="handleUpdateAssignee"
            @update-custom-field="handleUpdateCustomField"
            @section-order-change="handleSectionOrderChange"
            @section-collapse-change="handleSectionCollapseChange"
            @add-section="handleAddSection"
            @rename-section="handleRenameSection"
            @delete-section="handleDeleteSection"
          />

          <!-- Board View -->
          <MyTasksBoardView
            v-else-if="currentView === 'board'"
            :tasks="displayedTasks"
            :is-loading="myTasksStore.loading"
            @select-task="handleTaskSelect"
            @task-completed="handleTaskComplete"
            @task-move="handleTaskMove"
          />

          <!-- Calendar View -->
          <MyTasksCalendarView
            v-else-if="currentView === 'calendar'"
            :tasks="displayedTasks"
            :is-loading="myTasksStore.loading"
            @select-task="handleTaskSelect"
            @task-completed="handleTaskComplete"
          />

          <!-- Files View -->
          <MyTasksFilesView
            v-else-if="currentView === 'files'"
            :tasks="displayedTasks"
            :is-loading="myTasksStore.loading"
            @select-task="handleTaskSelect"
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

    <!-- Removed Task Create Modal - use inline creation instead -->
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { PlusIcon, ListBulletIcon, Squares2X2Icon, CalendarIcon, PaperClipIcon } from '@heroicons/vue/24/outline';
import { useMyTasksStore } from '@/Stores/useMyTasksStore';
import { useRealtimeListeners } from '@/Composables/useRealtimeListeners';
import type { Task, TaskFilter, TaskSort, TaskGroupBy } from '@/Types/tasks';
import MyTasksToolbar from './MyTasksToolbar.vue';
import MyTasksTable from './MyTasksTable.vue';
import MyTasksListView from '@/Components/MyTasks/Views/MyTasksListView.vue';
import MyTasksBoardView from '@/Components/MyTasks/Views/MyTasksBoardView.vue';
import MyTasksCalendarView from '@/Components/MyTasks/Views/MyTasksCalendarView.vue';
import MyTasksFilesView from '@/Components/MyTasks/Views/MyTasksFilesView.vue';
import TaskDetailSidebar from '@/Components/Tasks/Sidebar/TaskDetailSidebar.vue';
// Removed TaskCreateForm import - using inline creation only

interface Props {
  savedPreferences?: {
    sort?: any[] | null;
    grouping?: string | null;
    section_order?: string[] | null;
    collapsed_sections?: string[] | null;
    filters?: any | null;
  } | null;
  myTasksSections?: Array<{ id: string; name: string; position: number }>;
  workspaceMembers?: any[];
}

const props = withDefaults(defineProps<Props>(), {
  savedPreferences: null,
  myTasksSections: () => [],
  workspaceMembers: () => [],
});

const myTasksStore = useMyTasksStore();
const { listenToProject } = useRealtimeListeners();
const currentView = ref<'list' | 'board' | 'calendar' | 'files'>('list');

// View options with icons
const viewOptions = [
  { id: 'list', label: 'List', icon: ListBulletIcon },
  { id: 'board', label: 'Board', icon: Squares2X2Icon },
  { id: 'calendar', label: 'Calendar', icon: CalendarIcon },
  { id: 'files', label: 'Files', icon: PaperClipIcon },
];

// Computed
const selectedTask = computed(() => myTasksStore.selectedTask);

const displayedTasks = computed(() => {
  if (myTasksStore.groupBy) {
    const grouped = myTasksStore.groupedTasks;
    return Object.values(grouped).flat();
  }
  return myTasksStore.tasks;
});

// Lifecycle
onMounted(async () => {
  // Hydrate store from server-side saved preferences
  if (props.savedPreferences) {
    myTasksStore.loadPreferences(props.savedPreferences);
  }
  // Load real sections into store
  if (props.myTasksSections?.length) {
    myTasksStore.setSections(props.myTasksSections);
  }
  await myTasksStore.fetchTasks();

  // Subscribe to every project the loaded tasks belong to.
  // This keeps My Tasks in sync when teammates update shared tasks.
  // The user channel (in AppLayout) handles tasks assigned to this user directly.
  const projectIds = [
    ...new Set(
      (myTasksStore.tasks as Task[])
        .map((t: Task) => t.project_id)
        .filter(Boolean) as string[]
    ),
  ];

  projectIds.forEach((projectId) => {
    listenToProject(projectId, {
      onTaskUpdated({ task }) { myTasksStore.patchTask(task); },
      onTaskMoved({ task })   { myTasksStore.patchTaskMove(task); },
    });
  });
});

// Handlers
async function handleFilterChange(filters: TaskFilter) {
  myTasksStore.setFilters(filters);
  await myTasksStore.fetchTasks();
  myTasksStore.savePreferences();
}

async function handleSortChange(sortRules: TaskSort[]) {
  myTasksStore.setSort(sortRules);
  await myTasksStore.fetchTasks();
  myTasksStore.savePreferences();
}

async function handleGroupChange(groupBy: TaskGroupBy) {
  myTasksStore.setGroupBy(groupBy);
  myTasksStore.savePreferences();
}

function handleSectionOrderChange(order: string[]) {
  myTasksStore.setSectionOrder(order);
  myTasksStore.savePreferences();
}

function handleSectionCollapseChange(sectionId: string) {
  myTasksStore.toggleCollapseSection(sectionId);
  myTasksStore.savePreferences();
}

async function handleAddSection(name: string) {
  try {
    await myTasksStore.createSection(name);
  } catch (err) {
    console.error('Error creating section:', err);
  }
}

async function handleRenameSection(sectionId: string, name: string) {
  try {
    await myTasksStore.renameSection(sectionId, name);
  } catch (err) {
    console.error('Error renaming section:', err);
  }
}

async function handleDeleteSection(sectionId: string) {
  try {
    await myTasksStore.deleteSection(sectionId);
  } catch (err) {
    console.error('Error deleting section:', err);
  }
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

async function handleTaskMove(data: any) {
  try {
    const sectionId = data.toSectionId || data.sectionId;
    if (!sectionId) {
      console.error('No section ID provided for task move');
      return;
    }
    await myTasksStore.moveTask(data.taskId, sectionId, data.position ?? 0);
  } catch (err) {
    console.error('Error moving task:', err);
    // Refresh tasks on error to ensure consistency
    await myTasksStore.fetchTasks();
  }
}

async function handleUpdateDates(data: any) {
  try {
    await myTasksStore.updateTask(data.taskId, { due_date: data.due_date });
  } catch (err) {
    console.error('Error updating dates:', err);
  }
}

async function handleUpdateAssignee(data: any) {
  try {
    await myTasksStore.updateTask(data.taskId, { assignee_id: data.assigneeId });
  } catch (err) {
    console.error('Error updating assignee:', err);
  }
}

async function handleUpdateCustomField(data: any) {
  try {
    await myTasksStore.updateTask(data.taskId, { [data.fieldId]: data.value });
  } catch (err) {
    console.error('Error updating custom field:', err);
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

// Removed handleTaskCreated - using inline creation only

async function handleTaskCreatedInline(data: any) {
  try {
    await myTasksStore.createTask({
      name: data.name,
      section_id: data.section_id,
    });
  } catch (err) {
    console.error('Error creating task:', err);
  }
}
</script>
