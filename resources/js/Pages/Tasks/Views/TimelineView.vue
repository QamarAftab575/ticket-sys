<template>
  <div class="timeline-view">
    <div class="timeline-header">
      <div class="timeline-labels">
        <div class="label-task">Task</div>
        <div class="label-dates">
          <div v-for="date in dateRange" :key="date" class="date-label">
            {{ formatDateLabel(date) }}
          </div>
        </div>
      </div>
    </div>

    <div class="timeline-body">
      <div v-for="task in tasks" :key="task.id" class="timeline-row">
        <div class="row-task">
          <span class="task-name">{{ task.name }}</span>
        </div>
        <div class="row-timeline">
          <div
            v-if="task.start_date && task.due_date"
            class="task-bar"
            :style="getTaskBarStyle(task)"
            @click="selectTask(task)"
          >
            <span class="bar-label">{{ task.name }}</span>
          </div>
          <div
            v-else-if="task.due_date"
            class="task-milestone"
            :style="getMilestoneStyle(task)"
            @click="selectTask(task)"
          >
            ◆
          </div>
        </div>
      </div>
    </div>

    <!-- Task Detail Panel -->
    <TaskDetailPanel
      v-if="selectedTask"
      :task="selectedTask"
      @close="selectedTaskId = null"
      @update="updateTask"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import TaskDetailPanel from '../Components/TaskDetailPanel.vue'

const props = defineProps({
  tasks: Array
})

const selectedTaskId = ref(null)

const selectedTask = computed(() => {
  return props.tasks.find(t => t.id === selectedTaskId.value)
})

const dateRange = computed(() => {
  const dates = []
  const today = new Date()
  const startDate = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000)
  const endDate = new Date(today.getTime() + 60 * 24 * 60 * 60 * 1000)

  for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
    dates.push(new Date(d))
  }

  return dates
})

const formatDateLabel = (date) => {
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const getTaskBarStyle = (task) => {
  if (!task.start_date || !task.due_date) return {}

  const startDate = new Date(task.start_date)
  const dueDate = new Date(task.due_date)
  const rangeStart = dateRange.value[0]
  const rangeEnd = dateRange.value[dateRange.value.length - 1]

  const totalDays = Math.ceil((rangeEnd - rangeStart) / (1000 * 60 * 60 * 24))
  const taskStart = Math.max(0, Math.ceil((startDate - rangeStart) / (1000 * 60 * 60 * 24)))
  const taskDuration = Math.ceil((dueDate - startDate) / (1000 * 60 * 60 * 24))

  const leftPercent = (taskStart / totalDays) * 100
  const widthPercent = (taskDuration / totalDays) * 100

  return {
    left: `${leftPercent}%`,
    width: `${Math.max(widthPercent, 2)}%`
  }
}

const getMilestoneStyle = (task) => {
  if (!task.due_date) return {}

  const dueDate = new Date(task.due_date)
  const rangeStart = dateRange.value[0]
  const rangeEnd = dateRange.value[dateRange.value.length - 1]

  const totalDays = Math.ceil((rangeEnd - rangeStart) / (1000 * 60 * 60 * 24))
  const taskPosition = Math.ceil((dueDate - rangeStart) / (1000 * 60 * 60 * 24))

  const leftPercent = (taskPosition / totalDays) * 100

  return {
    left: `${leftPercent}%`
  }
}

const selectTask = (task) => {
  selectedTaskId.value = task.id
}

const updateTask = async (updates) => {
  try {
    const response = await fetch(`/api/tasks/${selectedTask.value.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(updates)
    })
    const result = await response.json()
    if (result.data) {
      Object.assign(selectedTask.value, result.data)
    }
  } catch (error) {
    console.error('Error updating task:', error)
  }
}
</script>

<style scoped>
.timeline-view {
  display: flex;
  gap: 20px;
  overflow-x: auto;
}

.timeline-header {
  position: sticky;
  top: 0;
  background: white;
  border-bottom: 2px solid #e0e0e0;
  z-index: 10;
}

.timeline-labels {
  display: flex;
}

.label-task {
  width: 200px;
  padding: 12px;
  font-weight: 600;
  border-right: 1px solid #e0e0e0;
  flex-shrink: 0;
}

.label-dates {
  display: flex;
  gap: 0;
}

.date-label {
  width: 40px;
  padding: 12px 4px;
  text-align: center;
  font-size: 11px;
  border-right: 1px solid #f0f0f0;
  flex-shrink: 0;
}

.timeline-body {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.timeline-row {
  display: flex;
  border-bottom: 1px solid #f0f0f0;
  min-height: 50px;
  align-items: center;
}

.row-task {
  width: 200px;
  padding: 12px;
  border-right: 1px solid #e0e0e0;
  flex-shrink: 0;
  overflow: hidden;
}

.task-name {
  font-size: 13px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.row-timeline {
  flex: 1;
  position: relative;
  height: 100%;
  display: flex;
  align-items: center;
  background: linear-gradient(to right, transparent 0%, transparent calc(100% - 1px), #f0f0f0 calc(100% - 1px));
  background-size: 40px 100%;
  background-position: 0 0;
}

.task-bar {
  position: absolute;
  height: 24px;
  background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 0 8px;
  color: white;
  font-size: 11px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  transition: box-shadow 0.2s;
}

.task-bar:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.bar-label {
  overflow: hidden;
  text-overflow: ellipsis;
}

.task-milestone {
  position: absolute;
  font-size: 16px;
  color: #2e7d32;
  cursor: pointer;
  transform: translateX(-50%);
  transition: transform 0.2s;
}

.task-milestone:hover {
  transform: translateX(-50%) scale(1.2);
}
</style>
