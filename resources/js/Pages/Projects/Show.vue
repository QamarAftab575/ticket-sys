<template>
  <AppLayout :user-workspaces="userWorkspaces" :current-workspace="currentWorkspace">
    <ToastContainer />

    <!-- Project Header -->
    <div class="bg-white border-b border-gray-200 px-6 pt-4">
      <!-- Top row: project name + actions -->
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
          <!-- Project icon/color picker -->
          <ProjectIconPicker
            :project-id="project.id"
            :name="project.name"
            :color="projectColor"
            :icon="projectIcon"
            size="md"
            :can-edit="canEdit"
            @update="handleAppearanceUpdate"
          />
          <h1 class="text-xl font-semibold text-gray-900">{{ project.name }}</h1>
          <!-- Status badge -->
          <span
            :class="[
              'ml-2 text-xs font-medium px-2 py-0.5 rounded-full border',
              project.status === 'on_track' ? 'bg-green-50 text-green-700 border-green-200' :
              project.status === 'at_risk'  ? 'bg-yellow-50 text-yellow-700 border-yellow-200' :
              project.status === 'off_track'? 'bg-red-50 text-red-700 border-red-200' :
                                              'bg-gray-100 text-gray-600 border-gray-200'
            ]"
          >
            {{ formatStatus(project.status) }}
          </span>
        </div>

        <!-- Right actions -->
        <div class="flex items-center gap-2">
          <!-- Member avatars -->
          <div class="flex -space-x-2">
            <Avatar
              v-for="member in (project.members || []).slice(0, 3)"
              :key="member.id"
              :name="member.name"
              :src="member.avatar"
              size="sm"
              class="border-2 border-white"
              :title="member.name"
            />
            <div
              v-if="(project.members || []).length > 3"
              class="w-8 h-8 rounded-full bg-gray-300 border-2 border-white flex items-center justify-center text-gray-600 text-xs font-semibold"
            >
              +{{ project.members.length - 3 }}
            </div>
            <!-- Always-visible dots button -->
            <button
              @click="showShareModal = true"
              class="w-8 h-8 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center text-gray-600 hover:bg-gray-300 transition"
              title="Share / Invite members"
            >
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/>
              </svg>
            </button>
          </div>
          <!-- Share button -->
          <button
            @click="showShareModal = true"
            class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
            </svg>
            Share
          </button>
        </div>
      </div>

      <!-- View tabs + toolbar row -->
      <div class="flex items-center justify-between">
        <!-- Add task button (list view only) -->
        <button
          v-if="activeView === 'list'"
          @click="listViewRef?.startCreatingInFirstSection()"
          class="flex items-center gap-1.5 mr-3 px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition flex-shrink-0"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add task
        </button>

        <!-- Tabs -->
        <div class="flex gap-0 overflow-x-auto" role="tablist">
          <button
            v-for="view in availableViews"
            :key="view"
            @click="switchView(view)"
            :class="[
              'flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors flex-shrink-0',
              activeView === view
                ? 'border-indigo-600 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300',
            ]"
            :aria-selected="activeView === view"
            role="tab"
          >
            <span v-if="view === 'list'">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            </span>
            <span v-else-if="view === 'board'">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/></svg>
            </span>
            <span v-else-if="view === 'timeline'">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </span>
            <span v-else-if="view === 'dashboard'">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </span>
            {{ formatViewName(view) }}
          </button>
        </div>

        <!-- Toolbar actions (Filter / Sort / Group + Columns) -->
        <div v-if="activeView !== 'dashboard'" class="flex items-center gap-2">
          <ViewToolbar
            :filters="filters"
            :sort="sort"
            :grouping="grouping"
            @filter-changed="updateFilters"
            @sort-changed="updateSort"
            @grouping-changed="updateGrouping"
          />

          <!-- Columns button (List View only) -->
          <button
            v-if="activeView === 'list'"
            @click="showColumnsPanel = !showColumnsPanel"
            class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
            title="Show/Hide columns"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            Columns
          </button>
        </div>
      </div>
    </div>

    <!-- Page content -->
    <div class="px-6 py-4">

        <!-- Active View Component -->
        <div v-if="isLoading" class="text-center py-12">
          <p class="text-gray-500">Loading...</p>
        </div>

        <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
          <p class="text-red-800 font-medium">{{ error }}</p>
          <button
            @click="loadViewData"
            class="mt-2 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
          >
            Retry
          </button>
        </div>

        <ListView
          v-else-if="activeView === 'list'"
          ref="listViewRef"
          :project="project"
          :tasks="tasks"
          :sections="sections"
          :filters="filters"
          :sort="sort"
          :grouping="grouping"
          :is-loading="isLoading"
          @select-task="openTaskPanel"
          @create-task="createTask"
          @task-created="handleTaskCreated"
          @task-completed="handleTaskCompleted"
          @task-move="handleTaskMove"
          @update-dates="({ taskId, ...data }) => updateTask(taskId, data)"
          @update-assignee="handleUpdateAssignee"
          @update-custom-field="handleUpdateCustomField"
          @add-section="handleAddSection"
          @rename-section="handleRenameSection"
          @delete-section="handleDeleteSection"
          @sections-reordered="handleSectionsReordered"
        />

        <BoardView
          v-else-if="activeView === 'board'"
          :project="project"
          :tasks="tasks"
          :sections="sections"
          @select-task="openTaskPanel"
          @task-created="handleTaskCreated"
          @task-move="handleTaskMove"
          @add-section="handleAddSection"
          @sections-reordered="handleBoardSectionsReordered"
          @update-dates="({ taskId, ...data }) => updateTask(taskId, data)"
        />

        <TimelineView
          v-else-if="activeView === 'timeline'"
          :project="project"
          :tasks="tasks"
          :sections="sections"
          :is-loading="isLoading"
          @select-task="openTaskPanel"
          @update-task="handleTimelineTaskUpdate"
          @create-task="handleTaskCreated"
          @toggle-complete="handleTaskCompleted"
        />

        <CalendarView
          v-else-if="activeView === 'calendar'"
          :project="project"
          :tasks="tasks"
          :filters="filters"
          @select-task="openTaskPanel"
          @create-task="handleCalendarCreateTask"
          @update-due-date="handleCalendarUpdateDueDate"
        />

        <FilesView
          v-else-if="activeView === 'files'"
          :project="project"
          :files="files"
          @file-uploaded="handleFileUploaded"
          @file-deleted="handleFileDeleted"
          @navigate-to-task="navigateToTask"
        />

        <DashboardView
          v-else-if="activeView === 'dashboard'"
          :project="project"
          :stats="stats"
        />

        <!-- Panels container -->
        <div class="fixed inset-y-0 right-0 flex z-40">
          <!-- Columns Visibility Panel (List View only) -->
          <ColumnsVisibilityPanel
            v-if="activeView === 'list' && showColumnsPanel"
            :all-columns="listViewAllColumns"
            :hidden-columns="listViewHiddenColumns"
            @close="showColumnsPanel = false"
            @toggle-column="toggleColumnVisibility"
            @show-all="showAllColumns"
            @hide-all="hideAllColumns"
          />

          <!-- Task Detail Panel -->
          <TaskDetailPanel
            v-if="selectedTask"
            :task="selectedTask"
            :project="project"
            :current-user="currentUser"
            @close="closeTaskPanel"
            @update="syncTaskFromPanel"
            @open-task="openTaskPanel"
          />
        </div>
    </div>
    <!-- Share / Invite Modal -->
    <InviteModal
      v-if="showShareModal"
      type="project"
      :context="project"
      @close="showShareModal = false"
      @invited="showShareModal = false"
    />
  </AppLayout>

  <!-- Blocked-by warning modal (row checkbox) -->
  <BlockedByWarningModal
    v-if="blockedModalTaskId"
    :tasks="blockedModalDeps"
    @confirm="confirmRowCompleteAnyway"
    @cancel="blockedModalTaskId = null; blockedModalDeps = []"
  />
</template>

<script setup>
import { ref, reactive, onMounted, nextTick, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import InviteModal from '@/Components/InviteProjectModal.vue'
import ColumnsVisibilityPanel from '@/Components/Projects/ColumnsVisibilityPanel.vue'
import ListView from '@/Components/Projects/Views/ListView.vue'
import BoardView from '@/Components/Projects/Views/BoardView.vue'
import TimelineView from '@/Components/Projects/Views/TimelineView.vue'
import CalendarView from '@/Components/Projects/Views/CalendarView.vue'
import FilesView from '@/Components/Projects/Views/FilesView.vue'
import DashboardView from '@/Components/Projects/Views/DashboardView.vue'
import TaskDetailPanel from '@/Components/Projects/TaskDetailPanel.vue'
import ViewToolbar from '@/Components/Projects/ViewToolbar.vue'
import ToastContainer from '@/Components/ToastContainer.vue'
import BlockedByWarningModal from '@/Components/Tasks/BlockedByWarningModal.vue'
import Avatar from '@/Components/Avatar.vue'
import ProjectIconPicker from '@/Components/Projects/ProjectIconPicker.vue'
import { useViewFilters } from '@/Composables/useViewFilters'
import { useViewSort } from '@/Composables/useViewSort'
import { useViewGrouping } from '@/Composables/useViewGrouping'
import { useToast } from '@/Composables/useToast'
import { useKeyboardNavigation } from '@/Composables/useKeyboardNavigation'

const props = defineProps({
  project: Object,
  canEdit: Boolean,
  canManageMembers: Boolean,
  userWorkspaces: Array,
  currentWorkspace: Object,
  currentUser: Object,
})

// Use URL search params instead of vue-router
const getQueryParam = (key) => new URLSearchParams(window.location.search).get(key)
const setQueryParams = (params) => {
  const url = new URL(window.location.href)
  Object.entries(params).forEach(([key, value]) => {
    if (value === undefined || value === null) {
      url.searchParams.delete(key)
    } else {
      url.searchParams.set(key, value)
    }
  })
  window.history.pushState({}, '', url.toString())
}

const availableViews = ['list', 'board', 'timeline', 'calendar', 'files', 'dashboard']
const activeView = ref(getQueryParam('view') || 'list')
const showShareModal = ref(false)
const showColumnsPanel = ref(false)
const selectedTask = ref(null)
const listViewRef = ref(null)
const isLoading = ref(false)
const error = ref(null)
const tasks = ref([])
const sections = ref(props.project.sections || [])

// Columns visibility state - synced from ListView component
const listViewAllColumns = ref([])
const listViewVisibleColumns = ref([])
const listViewHiddenColumns = ref([])
const files = ref([])
const stats = ref({})

// Reactive appearance — updated optimistically when picker saves
const projectColor = ref(props.project.color || '#6366f1')
const projectIcon  = ref(props.project.icon  || null)

const handleAppearanceUpdate = ({ color, icon }) => {
  projectColor.value = color
  projectIcon.value  = icon
  showSuccess('Project appearance updated')
}

const { filters } = useViewFilters()
const { sortRules: sort } = useViewSort()
const { groupingField: grouping } = useViewGrouping()
const { error: showError, success: showSuccess } = useToast()

// Cache CSRF token once instead of querying DOM on every request
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content

// Keyboard navigation
useKeyboardNavigation({
  onEscape: () => closeTaskPanel(),
})

const formatViewName = (view) => {
  return view.charAt(0).toUpperCase() + view.slice(1)
}

const formatStatus = (status) => {
  const map = { on_track: 'On track', at_risk: 'At risk', off_track: 'Off track', archived: 'Archived' }
  return map[status] || status
}

const switchView = (view) => {
  activeView.value = view
  setQueryParams({ view })
  localStorage.setItem(`project_${props.project.id}_view`, view)
  loadViewData()
  
  // If switching to list view, sync columns from ListView
  if (view === 'list') {
    nextTick(() => syncColumnsFromListView())
  }
}

const loadViewData = async () => {
  isLoading.value = true
  error.value = null
  try {
    if (activeView.value === 'list' || activeView.value === 'board' || activeView.value === 'timeline' || activeView.value === 'calendar') {
      const response = await fetch(
        `/projects/${props.project.id}/views/tasks?filters=${JSON.stringify(filters.value)}&sort=${JSON.stringify(sort.value)}&grouping=${grouping.value}`
      )
      if (!response.ok) {
        throw new Error('Failed to load tasks')
      }
      const data = await response.json()
      // If grouping is active, data.tasks is an object — flatten it to an array
      tasks.value = Array.isArray(data.tasks)
        ? data.tasks
        : Object.values(data.tasks || {}).flat()
    } else if (activeView.value === 'files') {
      const response = await fetch(`/projects/${props.project.id}/views/files`)
      if (!response.ok) {
        throw new Error('Failed to load files')
      }
      const data = await response.json()
      files.value = data.files
    } else if (activeView.value === 'dashboard') {
      const response = await fetch(`/projects/${props.project.id}/views/dashboard`)
      if (!response.ok) {
        throw new Error('Failed to load dashboard data')
      }
      const data = await response.json()
      stats.value = data.stats
    }
  } catch (err) {
    error.value = err.message || 'An error occurred while loading data'
    showError(error.value)
    console.error('Failed to load view data:', err)
  } finally {
    isLoading.value = false
  }
}

const openTaskPanel = (task) => {
  selectedTask.value = task
  setQueryParams({ task: task.id })
}

const closeTaskPanel = () => {
  selectedTask.value = null
  setQueryParams({ task: null })
}

const updateTask = async (taskId, data) => {
  const task = tasks.value.find((t) => t.id === taskId)
  if (!task) return
  const original = { ...task }
  Object.assign(task, data)

  try {
    const response = await fetch(`/api/tasks/${taskId}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify(data),
    })
    if (!response.ok) throw new Error('Failed to update task')
    showSuccess('Task updated')
  } catch (err) {
    Object.assign(task, original)
    showError('Failed to update task')
  }
}

// Called by TaskDetailPanel after it has already saved — just sync local state
const syncTaskFromPanel = (taskId, data) => {
  const task = tasks.value.find((t) => t.id === taskId)
  if (task) Object.assign(task, data)
}

const handleUpdateAssignee = async ({ taskId, assignee_id, assignee, rollback }) => {
  const task = tasks.value.find((t) => t.id === taskId)
  if (!task) return

  // Save originals before optimistic mutation
  const previousAssignee = task.assignee
  const previousAssigneeId = task.assignee_id

  // Sync parent state (picker already updated its own local state)
  task.assignee = assignee
  task.assignee_id = assignee_id

  try {
    const response = await fetch(`/api/tasks/${taskId}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ assignee_id }),
    })
    if (!response.ok) throw new Error('Failed to update assignee')
  } catch (err) {
    // Revert parent state
    task.assignee = previousAssignee
    task.assignee_id = previousAssigneeId
    // Revert picker local state
    rollback?.()
    showError('Failed to update assignee')
  }
}

/**
 * Optimistically update a custom field value on a task, then persist to backend.
 * Payload: { taskId, fieldId, value }
 */
const handleUpdateCustomField = async ({ taskId, fieldId, value }) => {
  const task = tasks.value.find(t => t.id === taskId)
  if (!task) return

  // Ensure the array exists
  if (!task.custom_field_values) task.custom_field_values = []

  const existing = task.custom_field_values.find(v => v.custom_field_id === fieldId)
  const previousValue = existing?.value ?? null

  // Optimistic update
  if (value === null || value === '' || (Array.isArray(value) && value.length === 0)) {
    task.custom_field_values = task.custom_field_values.filter(v => v.custom_field_id !== fieldId)
  } else {
    const serialized = Array.isArray(value) ? JSON.stringify(value) : String(value)
    if (existing) {
      existing.value = serialized
    } else {
      task.custom_field_values.push({ custom_field_id: fieldId, value: serialized })
    }
  }

  try {
    const res = await fetch(`/api/tasks/${taskId}/custom-fields/${fieldId}/value`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ value }),
    })
    if (!res.ok) throw new Error('Failed to save field value')
  } catch {
    // Revert
    if (previousValue === null) {
      task.custom_field_values = task.custom_field_values.filter(v => v.custom_field_id !== fieldId)
    } else {
      const cfv = task.custom_field_values.find(v => v.custom_field_id === fieldId)
      if (cfv) cfv.value = previousValue
      else task.custom_field_values.push({ custom_field_id: fieldId, value: previousValue })
    }
    showError('Failed to save field value')
  }
}

const createTask = async (sectionId) => {
  // Implementation for creating task
}

const handleTaskCreated = async (taskData) => {
  try {
    const tempId = `temp_${Date.now()}`
    // Look up the section object so the optimistic task lands in the right group immediately
    const section = taskData.section_id
      ? (sections.value.find(s => s.id === taskData.section_id) ?? null)
      : null
    const newTask = {
      id: tempId,
      name: taskData.name,
      section_id: taskData.section_id,
      section,
      start_date: taskData.start_date ?? null,
      due_date: taskData.due_date ?? null,
      status: 'todo',
      created_at: new Date().toISOString(),
    }
    tasks.value.push(newTask)
    showSuccess('Task created')

    const response = await fetch(`/api/projects/${props.project.id}/tasks`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({
        name: taskData.name,
        section_id: taskData.section_id,
        start_date: taskData.start_date ?? null,
        due_date: taskData.due_date ?? null,
      }),
    })

    if (!response.ok) throw new Error('Failed to create task')

    const { data: createdTask } = await response.json()
    const index = tasks.value.findIndex((t) => t.id === tempId)
    if (index >= 0) tasks.value[index] = createdTask
  } catch (err) {
    showError('Failed to create task')
    console.error('Failed to create task:', err)
    tasks.value = tasks.value.filter((t) => !String(t.id).startsWith('temp_'))
  }
}

// ── Blocked-by modal state (for row checkbox) ─────────────────────────────
const blockedModalTaskId = ref(null)
const blockedModalDeps = ref([])

const handleTaskCompleted = async (taskId) => {
  const task = tasks.value.find((t) => t.id === taskId)
  if (!task) return

  // If marking complete, check for incomplete blocking deps
  if (task.status !== 'complete') {
    const incomplete = (task.dependencies ?? []).filter(t => t.status !== 'complete')
    if (incomplete.length) {
      blockedModalTaskId.value = taskId
      blockedModalDeps.value = incomplete
      return
    }
  }

  await doToggleComplete(task)
}

const doToggleComplete = async (task) => {
  const newStatus = task.status === 'complete' ? 'to_do' : 'complete'
  const originalStatus = task.status
  task.status = newStatus

  try {
    const response = await fetch(`/api/tasks/${task.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ status: newStatus }),
    })
    if (!response.ok) throw new Error('Failed to update task')
    showSuccess(newStatus === 'complete' ? 'Task marked complete' : 'Task marked incomplete')
  } catch (err) {
    task.status = originalStatus
    showError('Failed to update task')
    console.error('Failed to update task:', err)
  }
}

const confirmRowCompleteAnyway = async () => {
  const task = tasks.value.find((t) => t.id === blockedModalTaskId.value)
  blockedModalTaskId.value = null

  if (!task) return

  // Remove incomplete blocking deps
  for (const dep of blockedModalDeps.value) {
    await fetch(`/api/tasks/${task.id}/dependencies/${dep.id}`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
  }
  task.dependencies = (task.dependencies ?? []).filter(t => t.status === 'complete')
  blockedModalDeps.value = []

  await doToggleComplete(task)
}

const handleTimelineTaskUpdate = async (updateData) => {
  try {
    const task = tasks.value.find((t) => t.id === updateData.taskId)
    if (!task) return

    // Optimistic UI: update dates immediately
    const originalStartDate = task.start_date
    const originalDueDate = task.due_date
    task.start_date = updateData.start_date
    task.due_date = updateData.due_date

    // Send to backend
    const response = await fetch(`/api/tasks/${updateData.taskId}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ start_date: updateData.start_date, due_date: updateData.due_date }),
    })

    if (!response.ok) {
      throw new Error('Failed to update task dates')
    }

    showSuccess('Task dates updated')
  } catch (err) {
    // Revert on error
    const task = tasks.value.find((t) => t.id === updateData.taskId)
    if (task) {
      task.start_date = originalStartDate
      task.due_date = originalDueDate
    }
    showError('Failed to update task dates')
    console.error('Failed to update task dates:', err)
  }
}

const updateFilters = (newFilters) => {
  filters.value = newFilters
  loadViewData()
}

const updateSort = (newSort) => {
  sort.value = newSort
  loadViewData()
}

const updateGrouping = (newGrouping) => {
  grouping.value = newGrouping
  loadViewData()
}

const handleTaskMove = async ({ taskId, fromSectionId, toSectionId, position }) => {
  const task = tasks.value.find((t) => t.id === taskId)
  if (!task) return

  // Save original state for rollback
  const originalSectionId = task.section_id
  const originalPosition = task.position

  // Get tasks in the target section (before the move)
  const targetSectionTasks = tasks.value
    .filter((t) => t.section_id === toSectionId && t.id !== taskId)
    .sort((a, b) => (a.position ?? 0) - (b.position ?? 0))

  // Optimistic update: move the task
  task.section_id = toSectionId
  task.position = position

  // Update positions of other tasks in the target section
  targetSectionTasks.forEach((t, index) => {
    if (index >= position) {
      t.position = index + 1
    } else {
      t.position = index
    }
  })

  try {
    const response = await fetch(`/api/tasks/${taskId}/move`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ section_id: toSectionId, position }),
    })
    if (!response.ok) throw new Error('Failed to move task')
    
    // Optionally refresh from server to ensure consistency
    const result = await response.json()
    if (result.data) {
      // Update task with server response
      Object.assign(task, result.data)
    }
  } catch (err) {
    // Revert on error
    task.section_id = originalSectionId
    task.position = originalPosition
    
    // Revert other tasks' positions
    targetSectionTasks.forEach((t, index) => {
      t.position = index
    })
    
    showError('Failed to move task')
  }
}

const handleAddSection = async (name) => {
  // Optimistic: add temp section
  const tempId = `temp_section_${Date.now()}`
  sections.value.push({ id: tempId, name, position: sections.value.length })

  try {
    const response = await fetch(`/api/projects/${props.project.id}/sections`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ name }),
    })
    if (!response.ok) throw new Error('Failed to create section')
    const { data } = await response.json()
    const idx = sections.value.findIndex((s) => s.id === tempId)
    if (idx >= 0) sections.value[idx] = data
    showSuccess('Section created')
  } catch (err) {
    sections.value = sections.value.filter((s) => s.id !== tempId)
    showError('Failed to create section')
  }
}

const handleRenameSection = async (sectionId, name) => {
  const section = sections.value.find((s) => s.id === sectionId)
  if (!section) return
  const original = section.name
  section.name = name

  try {
    const response = await fetch(`/api/sections/${sectionId}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ name }),
    })
    if (!response.ok) throw new Error('Failed to rename section')
  } catch (err) {
    section.name = original
    showError('Failed to rename section')
  }
}

const handleDeleteSection = async ({ sectionId, deleteTasks, targetSectionId }) => {
  const idx = sections.value.findIndex((s) => s.id === sectionId)
  if (idx === -1) return

  // Remove section from state
  sections.value.splice(idx, 1)

  if (deleteTasks) {
    // Remove all tasks that belonged to this section
    tasks.value = tasks.value.filter((t) => t.section_id !== sectionId)
  } else if (targetSectionId) {
    // Reassign tasks to the chosen section
    tasks.value.forEach((t) => {
      if (t.section_id === sectionId) t.section_id = targetSectionId
    })
  } else {
    // Tasks moved to "Untitled Section" on backend — reload to get correct state
    await loadViewData()
  }

  showSuccess('Section deleted')
}

const handleSectionsReordered = async (sectionIds) => {
  // ListView applies optimistic reorder locally; mirror it on the shared sections ref
  sections.value = sectionIds
    .map(id => sections.value.find(s => s.id === id))
    .filter(Boolean)
  await syncSectionOrder(sectionIds)
}

// BoardView reorder — needs to apply optimistic update to sections ref too
const handleBoardSectionsReordered = async (sectionIds) => {
  const original = [...sections.value]
  sections.value = sectionIds
    .map(id => sections.value.find(s => s.id === id))
    .filter(Boolean)
  await syncSectionOrder(sectionIds, original)
}

const syncSectionOrder = async (sectionIds, rollback = null) => {
  try {
    const response = await fetch(`/api/projects/${props.project.id}/sections/reorder`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ section_ids: sectionIds }),
    })
    if (!response.ok) throw new Error('Failed to reorder sections')
  } catch (err) {
    showError('Failed to reorder sections')
    if (rollback) sections.value = rollback
    else loadViewData()
  }
}

const handleCalendarCreateTask = async (data) => {
  try {
    // Show inline create task input
    const tempId = `temp_${Date.now()}`
    const newTask = {
      id: tempId,
      name: '',
      due_date: data.due_date,
      status: 'todo',
      created_at: new Date().toISOString(),
    }
    tasks.value.push(newTask)
    // Open task panel for editing
    selectedTask.value = newTask
  } catch (err) {
    showError('Failed to create task')
    console.error('Failed to create task:', err)
  }
}

const handleCalendarUpdateDueDate = async (data) => {
  try {
    const task = tasks.value.find((t) => t.id === data.taskId)
    if (!task) return

    // Optimistic UI: update due date immediately
    const originalDueDate = task.due_date
    task.due_date = data.newDate

    // Send to backend
    const response = await fetch(`/api/tasks/${data.taskId}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ due_date: data.newDate }),
    })

    if (!response.ok) {
      throw new Error('Failed to update task due date')
    }

    showSuccess('Task due date updated')
  } catch (err) {
    // Revert on error
    const task = tasks.value.find((t) => t.id === data.taskId)
    if (task) {
      task.due_date = originalDueDate
    }
    showError('Failed to update task due date')
    console.error('Failed to update task due date:', err)
  }
}

onMounted(() => {
  // Check for task in URL
  const taskId = new URLSearchParams(window.location.search).get('task')
  if (taskId) {
    const task = tasks.value.find((t) => t.id === taskId)
    if (task) {
      selectedTask.value = task
    }
  }
  loadViewData()
})

// Sync columns state from ListView whenever it updates
const syncColumnsFromListView = () => {
  if (listViewRef.value) {
    listViewAllColumns.value = listViewRef.value.allColumns
    listViewVisibleColumns.value = listViewRef.value.visibleColumns
    listViewHiddenColumns.value = listViewRef.value.hiddenColumns
  }
}

// Watch for changes to ListView columns
watch(
  () => listViewRef.value?.allColumns,
  () => syncColumnsFromListView(),
  { deep: true }
)

const handleFileUploaded = (uploadedFiles) => {
  // Add newly uploaded files to the files list
  if (uploadedFiles && Array.isArray(uploadedFiles)) {
    files.value = [...files.value, ...uploadedFiles]
  }
}

const handleFileDeleted = (fileId) => {
  // Remove deleted file from the files list
  files.value = files.value.filter((f) => f.id !== fileId)
}

const navigateToTask = (taskId) => {
  // Find and open the task
  const task = tasks.value.find((t) => t.id === taskId)
  if (task) {
    openTaskPanel(task)
  } else {
    // If task not in current list, fetch it
    fetch(`/api/tasks/${taskId}`)
      .then((res) => res.json())
      .then((task) => {
        openTaskPanel(task)
      })
      .catch((err) => {
        showError('Failed to load task')
        console.error('Failed to load task:', err)
      })
  }
}

// Column visibility methods
const toggleColumnVisibility = (columnId) => {
  if (listViewRef.value) {
    // Check if the column is currently hidden
    const isHidden = listViewRef.value.hiddenColumns.find(c => c.id === columnId)
    if (isHidden) {
      listViewRef.value.showColumn(columnId)
    } else {
      listViewRef.value.hideColumn(columnId)
    }
    // Sync the state after the change
    nextTick(() => syncColumnsFromListView())
  }
}

const showAllColumns = () => {
  if (listViewRef.value) {
    // Show all hidden columns
    listViewRef.value.hiddenColumns.forEach(col => {
      listViewRef.value.showColumn(col.id)
    })
    nextTick(() => syncColumnsFromListView())
  }
}

const hideAllColumns = () => {
  if (listViewRef.value) {
    // Hide all non-required columns
    const nonRequired = listViewRef.value.visibleColumns.filter(col => !col.required)
    nonRequired.forEach(col => {
      listViewRef.value.hideColumn(col.id)
    })
    nextTick(() => syncColumnsFromListView())
  }
}
</script>
