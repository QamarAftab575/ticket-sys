<template>
  <div class="flex h-screen bg-white">
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden bg-white">
      <!-- Compact Header - Asana Style (Static, normal layout) -->
      <div class="bg-white border-b border-gray-200">
        <!-- Top Row: Title, Task Count, and Actions -->
        <div class="px-6 py-3 flex items-center justify-between">
          <!-- Left: Avatar + Title -->
          <div class="flex items-center gap-3">
            <Avatar
              :name="currentUser?.name || 'User'"
              :src="currentUser?.avatar"
              size="sm"
              class="flex-shrink-0"
            />
            <div>
              <h1 class="text-lg font-semibold text-gray-900">My tasks</h1>
              <p class="text-xs text-gray-500">
                {{ taskCount }} task{{ taskCount !== 1 ? 's' : '' }}
              </p>
            </div>
          </div>

          <!-- Right: View Switcher + Filter/Sort (Sticky on desktop) -->
          <div class="flex items-center gap-3 md:sticky md:top-0 md:z-20 md:bg-white">
            <!-- View Type Selector -->
            <div class="flex items-center border border-gray-300 rounded-md overflow-hidden">
              <button
                v-for="view in viewOptions"
                :key="view.id"
                @click="currentView = view.id"
                :title="view.label"
                :class="[
                  'px-2.5 py-1.5 text-gray-600 hover:bg-gray-50 transition-colors border-r border-gray-300 last:border-r-0',
                  currentView === view.id
                    ? 'bg-gray-100 text-gray-900'
                    : ''
                ]"
              >
                <component :is="view.icon" class="w-4 h-4" />
              </button>
            </div>

            <!-- Toolbar Inline -->
            <MyTasksToolbar
              :initial-sort="myTasksStore.sort"
              :initial-group="myTasksStore.groupBy"
              :initial-filters="myTasksStore.filters"
              :workspace-members="workspaceMembers"
              @filter-change="handleFilterChange"
              @sort-change="handleSortChange"
              @group-change="handleGroupChange"
            />
          </div>
        </div>
      </div>

      <!-- Content Area (Scrollable) -->
      <div class="flex-1 overflow-auto bg-white">
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
            :sections="myTasksStore.sections"
            :section-order="myTasksStore.sectionOrder"
            :is-loading="myTasksStore.loading"
            @select-task="handleTaskSelect"
            @task-completed="handleTaskComplete"
            @task-move="handleTaskMove"
            @task-created="handleTaskCreatedInline"
            @add-section="handleAddSection"
            @sections-reordered="handleSectionOrderChange"
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

    <!-- Right Sidebar - Task Detail -->
    <TaskDetailPanel
      v-if="selectedTask"
      :task="selectedTask"
      :project="selectedTask?.project || null"
      :current-user="currentUser"
      @close="handleCloseSidebar"
      @update="syncTaskFromPanel"
      @open-task="openTaskPanel"
    />

    <!-- Removed Task Create Modal - use inline creation instead -->
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
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
import TaskDetailPanel from '@/Components/Projects/TaskDetailPanel.vue';
import Avatar from '@/Components/Avatar.vue';

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
const currentPage = usePage();
const currentUser = computed(() => currentPage.props.auth?.user || null);
const currentView = ref<'list' | 'board' | 'calendar' | 'files'>('list');

// URL query parameter helpers
const getQueryParam = (key: string) => new URLSearchParams(window.location.search).get(key);
const setQueryParams = (params: Record<string, any>) => {
  const url = new URL(window.location.href);
  Object.entries(params).forEach(([key, value]) => {
    if (value === undefined || value === null) {
      url.searchParams.delete(key);
    } else {
      url.searchParams.set(key, value);
    }
  });
  window.history.pushState({}, '', url.toString());
};

// View options with icons
const viewOptions = [
  { id: 'list', label: 'List', icon: ListBulletIcon },
  { id: 'board', label: 'Board', icon: Squares2X2Icon },
  { id: 'calendar', label: 'Calendar', icon: CalendarIcon },
  { id: 'files', label: 'Files', icon: PaperClipIcon },
];

// State
const selectedTask = ref(null);

// Initialize view from URL query parameter or default to 'list'
const initializeViewFromUrl = () => {
  const viewParam = getQueryParam('view');
  if (viewParam && ['list', 'board', 'calendar', 'files'].includes(viewParam)) {
    currentView.value = viewParam as 'list' | 'board' | 'calendar' | 'files';
  }
};

// Watch for view changes and update URL
watch(currentView, (newView) => {
  setQueryParams({ view: newView });
});

// Computed
const taskCount = computed(() => {
  return myTasksStore.flatTasks.length;
});

const displayedTasks = computed(() => {
  if (myTasksStore.groupBy) {
    const grouped = myTasksStore.groupedTasks;
    return Object.values(grouped).flat();
  }
  return myTasksStore.tasks;
});

// Lifecycle
onMounted(async () => {
  // Initialize view from URL
  initializeViewFromUrl();

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
  const tasks = Array.isArray(myTasksStore.tasks) 
    ? myTasksStore.tasks 
    : Object.values(myTasksStore.tasks || {}).flat();
    
  const projectIds = [
    ...new Set(
      tasks
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

  // Check for task in URL and open it
  const taskId = getQueryParam('task');
  if (taskId) {
    const task = myTasksStore.tasks.find((t) => t.id === taskId);
    if (task) {
      selectedTask.value = task;
    } else {
      // If task not in current list, fetch it from API
      try {
        const response = await fetch(`/api/tasks/${taskId}`);
        if (response.ok) {
          const data = await response.json();
          const taskData = data.data || data;
          selectedTask.value = taskData;
          myTasksStore.selectTask(taskId);
        }
      } catch (err) {
        console.error('Failed to load task from URL:', err);
      }
    }
  }
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

async function handleTaskSelect(task) {
  // Receive the full task object and set it directly
  myTasksStore.selectTask(task.id);
  selectedTask.value = task;
  // Add task ID to URL
  setQueryParams({ task: task.id });
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
  selectedTask.value = null;
  // Remove task ID from URL
  setQueryParams({ task: undefined });
}

// Called by TaskDetailPanel after it has already saved — just sync local state
function syncTaskFromPanel(taskId, data) {
  const task = myTasksStore.tasks.find((t) => t.id === taskId);
  if (task) {
    Object.assign(task, data);
  }
}

function openTaskPanel(task) {
  myTasksStore.selectTask(task.id);
  selectedTask.value = task;
  // Add task ID to URL
  setQueryParams({ task: task.id });
}

// Handle browser back/forward buttons
const handlePopState = () => {
  const taskId = getQueryParam('task');
  if (taskId) {
    const task = myTasksStore.tasks.find((t) => t.id === taskId);
    if (task) {
      selectedTask.value = task;
    }
  } else {
    selectedTask.value = null;
  }
  
  // Also update view from URL
  const viewParam = getQueryParam('view');
  if (viewParam && ['list', 'board', 'calendar', 'files'].includes(viewParam)) {
    currentView.value = viewParam as 'list' | 'board' | 'calendar' | 'files';
  }
};

// Add popstate listener
window.addEventListener('popstate', handlePopState);

// Cleanup on unmount
onBeforeUnmount(() => {
  window.removeEventListener('popstate', handlePopState);
});

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
