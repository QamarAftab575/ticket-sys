<template>
  <div class="space-y-6">
    <!-- Add widget button (Phase 2) -->
    <div class="flex justify-end">
      <button
        @click="showAddWidgetPanel = true"
        class="px-4 py-2 bg-blue-600 text-white rounded text-sm font-medium hover:bg-blue-700 transition-colors"
        aria-label="Add widget"
      >
        + Add widget
      </button>
    </div>

    <!-- Widgets grid -->
    <div
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6"
      @dragover.prevent="handleDragOver"
      @drop.prevent="handleDrop"
    >
      <!-- Project Status Widget -->
      <DashboardWidget
        v-if="isWidgetVisible('project-status')"
        title="Project Status"
        data-widget-id="project-status"
        @remove="removeWidget('project-status')"
      >
        <ProjectStatusWidget :project="project" />
      </DashboardWidget>

      <!-- Task Completion Widget -->
      <DashboardWidget
        v-if="isWidgetVisible('task-completion')"
        title="Task Completion"
        data-widget-id="task-completion"
        @remove="removeWidget('task-completion')"
      >
        <TaskCompletionWidget :stats="stats" />
      </DashboardWidget>

      <!-- Tasks by Assignee Widget -->
      <DashboardWidget
        v-if="isWidgetVisible('tasks-by-assignee')"
        title="Tasks by Assignee"
        data-widget-id="tasks-by-assignee"
        @remove="removeWidget('tasks-by-assignee')"
      >
        <TasksByAssigneeWidget
          :assignee-stats="stats.tasks_by_assignee"
          @filter="handleFilter"
        />
      </DashboardWidget>

      <!-- Tasks by Priority Widget -->
      <DashboardWidget
        v-if="isWidgetVisible('tasks-by-priority')"
        title="Tasks by Priority"
        data-widget-id="tasks-by-priority"
        @remove="removeWidget('tasks-by-priority')"
      >
        <TasksByPriorityWidget
          :priority-stats="stats.tasks_by_priority"
          @filter="handleFilter"
        />
      </DashboardWidget>

      <!-- Upcoming Milestones Widget -->
      <DashboardWidget
        v-if="isWidgetVisible('upcoming-milestones')"
        title="Upcoming Milestones"
        data-widget-id="upcoming-milestones"
        @remove="removeWidget('upcoming-milestones')"
      >
        <UpcomingMilestonesWidget
          :milestones="stats.upcoming_milestones"
          @open-task="openTask"
        />
      </DashboardWidget>

      <!-- Overdue Tasks Widget -->
      <DashboardWidget
        v-if="isWidgetVisible('overdue-tasks')"
        title="Overdue Tasks"
        data-widget-id="overdue-tasks"
        @remove="removeWidget('overdue-tasks')"
      >
        <OverdueTasksWidget
          :overdue-tasks="stats.overdue_tasks"
          @open-task="openTask"
          @view-all="handleViewAllOverdue"
        />
      </DashboardWidget>

      <!-- Recent Activity Widget -->
      <DashboardWidget
        v-if="isWidgetVisible('recent-activity')"
        title="Recent Activity"
        data-widget-id="recent-activity"
        @remove="removeWidget('recent-activity')"
      >
        <RecentActivityWidget
          :activities="stats.recent_activity"
          @open-task="openTask"
        />
      </DashboardWidget>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import DashboardWidget from './DashboardWidget.vue'
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
})

const emit = defineEmits(['filter', 'open-task', 'view-all-overdue'])

const showAddWidgetPanel = ref(false)
const { isWidgetVisible, removeWidget, loadState } = useDashboardState(props.project.id)

// Load dashboard state on mount
loadState()

const handleFilter = (filter) => {
  emit('filter', filter)
}

const openTask = (taskId) => {
  emit('open-task', taskId)
}

const handleViewAllOverdue = () => {
  emit('view-all-overdue')
}

const handleDragOver = (event) => {
  event.preventDefault()
  event.dataTransfer.dropEffect = 'move'
}

const handleDrop = (event) => {
  event.preventDefault()
  // Widget reordering logic can be implemented here
}
</script>
