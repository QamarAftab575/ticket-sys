<template>
  <div class="calendar-view">
    <div class="calendar-header">
      <button @click="previousMonth" class="btn-nav">â†</button>
      <h2 class="month-year">{{ monthYear }}</h2>
      <button @click="nextMonth" class="btn-nav">â†’</button>
    </div>

    <div class="calendar-grid">
      <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day" class="day-header">
        {{ day }}
      </div>

      <div
        v-for="date in calendarDates"
        :key="date.toISOString()"
        class="calendar-day"
        :class="{ 'other-month': date.getMonth() !== currentDate.getMonth(), 'today': isToday(date) }"
      >
        <div class="day-number">{{ date.getDate() }}</div>
        <div class="day-tasks">
          <div
            v-for="task in getTasksForDate(date)"
            :key="task.id"
            class="task-item"
            @click="selectTask(task)"
          >
            <span class="task-dot" :style="{ backgroundColor: getTaskColor(task) }"></span>
            <span class="task-text">{{ task.name }}</span>
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

const currentDate = ref(new Date())
const selectedTaskId = ref(null)

const selectedTask = computed(() => {
  return props.tasks.find(t => t.id === selectedTaskId.value)
})

const monthYear = computed(() => {
  return currentDate.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
})

const calendarDates = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()

  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)

  const startDate = new Date(firstDay)
  startDate.setDate(startDate.getDate() - firstDay.getDay())

  const dates = []
  let date = new Date(startDate)

  while (date <= lastDay || date.getDay() !== 0) {
    dates.push(new Date(date))
    date.setDate(date.getDate() + 1)
  }

  return dates
})

const previousMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1)
}

const nextMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1)
}

const isToday = (date) => {
  const today = new Date()
  return date.toDateString() === today.toDateString()
}

const getTasksForDate = (date) => {
  return props.tasks.filter(task => {
    if (!task.due_date) return false
    const taskDate = new Date(task.due_date)
    return taskDate.toDateString() === date.toDateString()
  })
}

const getTaskColor = (task) => {
  const colors = {
    'urgent': '#c62828',
    'high': '#e65100',
    'medium': '#f57f17',
    'low': '#2e7d32'
  }
  return colors[task.priority] || '#666'
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
.calendar-view {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background: white;
  border-radius: 8px;
}

.btn-nav {
  width: 40px;
  height: 40px;
  border: 1px solid #e0e0e0;
  background: white;
  border-radius: 4px;
  cursor: pointer;
  font-size: 18px;
  transition: background 0.2s;
}

.btn-nav:hover {
  background: #f0f0f0;
}

.month-year {
  font-size: 20px;
  font-weight: 600;
  margin: 0;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  background: #e0e0e0;
  border-radius: 8px;
  overflow: hidden;
}

.day-header {
  background: #f9f9f9;
  padding: 12px;
  font-weight: 600;
  text-align: center;
  font-size: 12px;
  border-right: 1px solid #e0e0e0;
  border-bottom: 1px solid #e0e0e0;
}

.calendar-day {
  background: white;
  padding: 12px;
  min-height: 120px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  border-right: 1px solid #e0e0e0;
  border-bottom: 1px solid #e0e0e0;
}

.calendar-day.other-month {
  background: #fafafa;
  color: #ccc;
}

.calendar-day.today {
  background: #e8f5e9;
}

.day-number {
  font-weight: 600;
  font-size: 14px;
}

.day-tasks {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
  overflow-y: auto;
}

.task-item {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 4px 6px;
  background: #f0f0f0;
  border-radius: 4px;
  cursor: pointer;
  font-size: 11px;
  transition: background 0.2s;
}

.task-item:hover {
  background: #e0e0e0;
}

.task-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}

.task-text {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>

