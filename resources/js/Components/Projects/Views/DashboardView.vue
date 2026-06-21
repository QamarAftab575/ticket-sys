<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <!-- Filter button -->
      <button
        @click="showFilterPanel = !showFilterPanel"
        class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors"
        title="Toggle filters"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
        </svg>
        <span class="text-sm font-medium">Filters</span>
      </button>

      <!-- Hidden widgets button -->
      <button
        v-if="hiddenWidgets.length > 0"
        @click="showWidgetPanel = !showWidgetPanel"
        class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors"
        :title="`Show ${hiddenWidgets.length} hidden widget(s)`"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span class="text-sm font-medium">{{ hiddenWidgets.length }} Hidden</span>
      </button>
    </div>

    <!-- Filter Panel -->
    <div v-if="showFilterPanel" class="animate-in">
      <DashboardFilterPanel
        :project-members="projectMembers"
        :loading="isLoadingFilters"
        @filters-changed="handleFiltersChanged"
      />
    </div>

    <!-- Hidden Widgets Panel -->
    <div v-if="showWidgetPanel && hiddenWidgets.length > 0" class="bg-white border border-gray-200 rounded-lg p-4 animate-in">
      <h3 class="font-semibold text-gray-900 text-sm mb-3">Show Hidden Widgets</h3>
      <div class="space-y-1">
        <button
          v-for="widgetId in hiddenWidgets"
          :key="widgetId"
          @click="addWidget(widgetId)"
          class="w-full text-left px-3 py-2 rounded hover:bg-gray-50 text-sm text-gray-700 transition-colors flex items-center gap-2"
        >
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          {{ WIDGET_LABELS[widgetId] || widgetId }}
        </button>
      </div>
    </div>

    <!-- Widgets grid â€” 2 draggable columns -->
    <div class="flex gap-6">
      <!-- Left column -->
      <div
        class="flex-1 space-y-4"
        :class="{ 'ring-2 ring-blue-300 ring-offset-2 rounded-lg': dragOverColumn === 'left' }"
        @dragover.prevent="handleColumnDragOver($event, 'left')"
        @dragleave="handleColumnDragLeave($event, 'left')"
        @drop.prevent="handleColumnDrop($event, 'left')"
      >
        <template v-for="(widgetId, index) in leftColumn" :key="widgetId">
          <!-- Drop indicator ABOVE widget -->
          <div
            v-if="dropTarget?.column === 'left' && dropTarget?.index === index"
            class="h-1 bg-blue-500 rounded-full mx-2 transition-all"
          />

          <DashboardWidget
            :title="WIDGET_LABELS[widgetId]"
            :widget-id="widgetId"
            :is-dragging-over="dragOverWidget === widgetId"
            @hide="hideWidget(widgetId)"
            @dragstart="handleWidgetDragStart($event, widgetId)"
            @dragend="handleWidgetDragEnd"
            @dragover-widget="handleWidgetDragOver($event, widgetId, 'left', index)"
          >
            <component :is="widgetComponents[widgetId]" v-bind="widgetProps(widgetId)" />
          </DashboardWidget>
        </template>

        <!-- Drop indicator at end of left column -->
        <div
          v-if="dropTarget?.column === 'left' && dropTarget?.index === leftColumn.length"
          class="h-1 bg-blue-500 rounded-full mx-2 transition-all"
        />

        <!-- Empty state drop zone -->
        <div
          v-if="leftColumn.length === 0"
          class="h-24 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center text-gray-400 text-sm"
        >
          Drop widgets here
        </div>
      </div>

      <!-- Right column -->
      <div
        class="flex-1 space-y-4"
        :class="{ 'ring-2 ring-blue-300 ring-offset-2 rounded-lg': dragOverColumn === 'right' }"
        @dragover.prevent="handleColumnDragOver($event, 'right')"
        @dragleave="handleColumnDragLeave($event, 'right')"
        @drop.prevent="handleColumnDrop($event, 'right')"
      >
        <template v-for="(widgetId, index) in rightColumn" :key="widgetId">
          <!-- Drop indicator ABOVE widget -->
          <div
            v-if="dropTarget?.column === 'right' && dropTarget?.index === index"
            class="h-1 bg-blue-500 rounded-full mx-2 transition-all"
          />

          <DashboardWidget
            :title="WIDGET_LABELS[widgetId]"
            :widget-id="widgetId"
            :is-dragging-over="dragOverWidget === widgetId"
            @hide="hideWidget(widgetId)"
            @dragstart="handleWidgetDragStart($event, widgetId)"
            @dragend="handleWidgetDragEnd"
            @dragover-widget="handleWidgetDragOver($event, widgetId, 'right', index)"
          >
            <component :is="widgetComponents[widgetId]" v-bind="widgetProps(widgetId)" />
          </DashboardWidget>
        </template>

        <!-- Drop indicator at end of right column -->
        <div
          v-if="dropTarget?.column === 'right' && dropTarget?.index === rightColumn.length"
          class="h-1 bg-blue-500 rounded-full mx-2 transition-all"
        />

        <!-- Empty state drop zone -->
        <div
          v-if="rightColumn.length === 0"
          class="h-24 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center text-gray-400 text-sm"
        >
          Drop widgets here
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import DashboardWidget from './DashboardWidget.vue'
import DashboardFilterPanel from './DashboardFilterPanel.vue'
import ProjectStatusWidget from './ProjectStatusWidget.vue'
import TaskCompletionWidget from './TaskCompletionWidget.vue'
import TasksByAssigneeWidget from './TasksByAssigneeWidget.vue'
import TasksByPriorityWidget from './TasksByPriorityWidget.vue'
import UpcomingMilestonesWidget from './UpcomingMilestonesWidget.vue'
import OverdueTasksWidget from './OverdueTasksWidget.vue'
import RecentActivityWidget from './RecentActivityWidget.vue'
import { useDashboardState } from '@/Composables/useDashboardState'

const props = defineProps({
  project: Object,
  stats: Object,
  projectMembers: Array,
})

const emit = defineEmits(['filter', 'open-task', 'view-all-overdue'])

const WIDGET_LABELS = {
  'project-status': 'Project Status',
  'task-completion': 'Task Completion',
  'tasks-by-assignee': 'Tasks by Assignee',
  'tasks-by-priority': 'Tasks by Priority',
  'upcoming-milestones': 'Upcoming Milestones',
  'overdue-tasks': 'Overdue Tasks',
  'recent-activity': 'Recent Activity',
}

// Map widgetId â†’ component
const widgetComponents = {
  'project-status': ProjectStatusWidget,
  'task-completion': TaskCompletionWidget,
  'tasks-by-assignee': TasksByAssigneeWidget,
  'tasks-by-priority': TasksByPriorityWidget,
  'upcoming-milestones': UpcomingMilestonesWidget,
  'overdue-tasks': OverdueTasksWidget,
  'recent-activity': RecentActivityWidget,
}

// Map widgetId â†’ props to pass down
const widgetProps = (widgetId) => {
  switch (widgetId) {
    case 'project-status':
      return { project: props.project }
    case 'task-completion':
      return { stats: dashboardStats.value }
    case 'tasks-by-assignee':
      return {
        assigneeStats: dashboardStats.value?.tasks_by_assignee,
        onFilter: (f) => emit('filter', f),
      }
    case 'tasks-by-priority':
      return {
        priorityStats: dashboardStats.value?.tasks_by_priority,
        onFilter: (f) => emit('filter', f),
      }
    case 'upcoming-milestones':
      return {
        milestones: dashboardStats.value?.upcoming_milestones,
        onOpenTask: (id) => emit('open-task', id),
      }
    case 'overdue-tasks':
      return {
        overdueTasks: dashboardStats.value?.overdue_tasks,
        onOpenTask: (id) => emit('open-task', id),
        onViewAll: () => emit('view-all-overdue'),
      }
    case 'recent-activity':
      return {
        activities: dashboardStats.value?.recent_activity,
        onOpenTask: (id) => emit('open-task', id),
      }
    default:
      return {}
  }
}

const showFilterPanel = ref(false)
const showWidgetPanel = ref(false)
const isLoadingFilters = ref(false)
const dashboardStats = ref(props.stats)

const { leftColumn, rightColumn, hiddenWidgets, loadState, hideWidget, addWidget, reorderWidget } =
  useDashboardState(props.project.id)

// â”€â”€â”€ Drag & Drop state â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const draggingWidgetId = ref(null)
const dragOverColumn = ref(null)   // 'left' | 'right' | null  â€” highlight column
const dragOverWidget = ref(null)   // widgetId being hovered
const dropTarget = ref(null)       // { column, index } for the indicator line

const handleWidgetDragStart = (event, widgetId) => {
  draggingWidgetId.value = widgetId
}

const handleWidgetDragEnd = () => {
  draggingWidgetId.value = null
  dragOverColumn.value = null
  dragOverWidget.value = null
  dropTarget.value = null
}

const handleColumnDragOver = (event, column) => {
  if (!draggingWidgetId.value) return
  dragOverColumn.value = column

  // If not hovering a specific widget, target end of column
  if (dragOverWidget.value === null || !_widgetInColumn(dragOverWidget.value, column)) {
    const col = column === 'left' ? leftColumn.value : rightColumn.value
    dropTarget.value = { column, index: col.length }
  }
}

const handleColumnDragLeave = (event, column) => {
  // Only clear if leaving the column container itself (not entering a child)
  if (!event.currentTarget.contains(event.relatedTarget)) {
    if (dragOverColumn.value === column) {
      dragOverColumn.value = null
      dropTarget.value = null
      dragOverWidget.value = null
    }
  }
}

const handleColumnDrop = (event, column) => {
  if (!draggingWidgetId.value) return

  const col = column === 'left' ? leftColumn.value : rightColumn.value
  const targetIndex = dropTarget.value?.column === column ? dropTarget.value.index : col.length

  // Find which widget is at targetIndex so reorderWidget can insert before it
  const targetId = col[targetIndex] || null

  reorderWidget(draggingWidgetId.value, targetId, column)

  // Reset drag state
  draggingWidgetId.value = null
  dragOverColumn.value = null
  dragOverWidget.value = null
  dropTarget.value = null
}

const handleWidgetDragOver = (event, widgetId, column, index) => {
  if (!draggingWidgetId.value || draggingWidgetId.value === widgetId) return

  dragOverWidget.value = widgetId

  // Determine upper or lower half to decide where to insert
  const rect = event.currentTarget?.getBoundingClientRect?.()
  let insertIndex = index
  if (rect) {
    const midY = rect.top + rect.height / 2
    if (event.clientY > midY) {
      insertIndex = index + 1
    }
  }

  dropTarget.value = { column, index: insertIndex }
}

const _widgetInColumn = (widgetId, column) => {
  return column === 'left'
    ? leftColumn.value.includes(widgetId)
    : rightColumn.value.includes(widgetId)
}

// â”€â”€â”€ Lifecycle â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
onMounted(() => {
  loadState()
})

// â”€â”€â”€ Filters â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const handleFiltersChanged = async (filters) => {
  isLoadingFilters.value = true
  try {
    const params = new URLSearchParams()
    if (filters.dateFrom) params.append('date_from', filters.dateFrom)
    if (filters.dateTo) params.append('date_to', filters.dateTo)
    if (filters.memberIds?.length > 0) params.append('member_ids', filters.memberIds.join(','))

    const response = await fetch(`/projects/${props.project.id}/views/dashboard?${params}`)
    if (!response.ok) throw new Error('Failed to fetch dashboard data')

    const data = await response.json()
    dashboardStats.value = data.stats
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
  } finally {
    isLoadingFilters.value = false
  }
}
</script>

<style scoped>
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-in {
  animation: slideIn 0.2s ease-in-out;
}
</style>

