<template>
  <div class="space-y-4">
    <!-- Calendar header -->
    <div class="flex items-center justify-between p-4 bg-gray-50 rounded">
      <div class="flex gap-2">
        <button
          @click="navigatePrevious"
          class="px-3 py-1 border rounded hover:bg-gray-200 transition-colors"
          :aria-label="viewMode === 'month' ? 'Previous month' : 'Previous week'"
        >
          ← Previous
        </button>
        <button
          @click="goToToday"
          class="px-3 py-1 border rounded hover:bg-gray-200 transition-colors"
          aria-label="Go to today"
        >
          Today
        </button>
        <button
          @click="navigateNext"
          class="px-3 py-1 border rounded hover:bg-gray-200 transition-colors"
          :aria-label="viewMode === 'month' ? 'Next month' : 'Next week'"
        >
          Next →
        </button>
      </div>
      <h3 class="text-lg font-semibold">{{ displayTitle }}</h3>
      <div class="flex gap-1 border rounded overflow-hidden">
        <button
          @click="viewMode = 'month'"
          :class="[
            'px-4 py-1.5 text-sm font-medium transition-colors',
            viewMode === 'month' 
              ? 'bg-blue-500 text-white' 
              : 'bg-white text-gray-700 hover:bg-gray-100'
          ]"
          :aria-pressed="viewMode === 'month'"
        >
          Month
        </button>
        <button
          @click="viewMode = 'week'"
          :class="[
            'px-4 py-1.5 text-sm font-medium transition-colors',
            viewMode === 'week' 
              ? 'bg-blue-500 text-white' 
              : 'bg-white text-gray-700 hover:bg-gray-100'
          ]"
          :aria-pressed="viewMode === 'week'"
        >
          Week
        </button>
      </div>
    </div>

    <!-- Month View -->
    <div v-if="viewMode === 'month'" class="border rounded overflow-hidden">
      <!-- Day headers -->
      <div class="grid grid-cols-7 bg-gray-100">
        <div
          v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']"
          :key="day"
          class="p-2 text-center font-semibold text-sm"
        >
          {{ day }}
        </div>
      </div>

      <!-- Calendar cells -->
      <div class="grid grid-cols-7 gap-px bg-gray-200">
        <CalendarCell
          v-for="date in calendarDates"
          :key="dateKey(date)"
          :date="date"
          :tasks="getTasksForDate(date)"
          :is-today="isToday(date)"
          :is-other-month="isOtherMonth(date)"
          :expanded-days="expandedDays"
          @select-task="$emit('select-task', $event)"
          @toggle-expand="toggleExpandDay"
          @create-task="handleCreateTask"
          @update-due-date="handleUpdateDueDate"
        />
      </div>
    </div>

    <!-- Week View -->
    <div v-else class="border rounded overflow-hidden">
      <CalendarWeekView
        :start-date="weekStartDate"
        :tasks="tasks"
        :filters="filters"
        @select-task="$emit('select-task', $event)"
        @create-task="handleCreateTask"
        @update-due-date="handleUpdateDueDate"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import CalendarCell from './CalendarCell.vue'
import CalendarWeekView from './CalendarWeekView.vue'

const props = defineProps({
  project: Object,
  tasks: Array,
  filters: Object,
})

const emit = defineEmits(['select-task', 'create-task', 'update-due-date'])

const currentDate = ref(new Date())
const viewMode = ref('month')
const expandedDays = ref(new Set())

const calendarDates = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()
  const firstDay = new Date(year, month, 1)
  const startDate = new Date(firstDay)
  startDate.setDate(startDate.getDate() - firstDay.getDay())

  const dates = []
  for (let i = 0; i < 42; i++) {
    dates.push(new Date(startDate))
    startDate.setDate(startDate.getDate() + 1)
  }
  return dates
})

const weekStartDate = computed(() => {
  const date = new Date(currentDate.value)
  const day = date.getDay()
  date.setDate(date.getDate() - day)
  return date
})

const weekEndDate = computed(() => {
  const date = new Date(weekStartDate.value)
  date.setDate(date.getDate() + 6)
  return date
})

const displayTitle = computed(() => {
  if (viewMode.value === 'month') {
    return formatMonth(currentDate.value)
  } else {
    // Week view: show date range
    const start = weekStartDate.value
    const end = weekEndDate.value
    
    const startMonth = start.toLocaleDateString('en-US', { month: 'short' })
    const endMonth = end.toLocaleDateString('en-US', { month: 'short' })
    const startDay = start.getDate()
    const endDay = end.getDate()
    
    // If same month: "May 10 – 16"
    if (start.getMonth() === end.getMonth()) {
      return `${startMonth} ${startDay} – ${endDay}`
    }
    // If different months: "May 30 – Jun 5"
    else {
      return `${startMonth} ${startDay} – ${endMonth} ${endDay}`
    }
  }
})

const navigatePrevious = () => {
  if (viewMode.value === 'month') {
    previousMonth()
  } else {
    previousWeek()
  }
}

const navigateNext = () => {
  if (viewMode.value === 'month') {
    nextMonth()
  } else {
    nextWeek()
  }
}

const previousMonth = () => {
  currentDate.value.setMonth(currentDate.value.getMonth() - 1)
  currentDate.value = new Date(currentDate.value)
}

const nextMonth = () => {
  currentDate.value.setMonth(currentDate.value.getMonth() + 1)
  currentDate.value = new Date(currentDate.value)
}

const previousWeek = () => {
  currentDate.value.setDate(currentDate.value.getDate() - 7)
  currentDate.value = new Date(currentDate.value)
}

const nextWeek = () => {
  currentDate.value.setDate(currentDate.value.getDate() + 7)
  currentDate.value = new Date(currentDate.value)
}

const goToToday = () => {
  currentDate.value = new Date()
}

const formatMonth = (date) => {
  return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
}

const isToday = (date) => {
  const today = new Date()
  return (
    date.getDate() === today.getDate() &&
    date.getMonth() === today.getMonth() &&
    date.getFullYear() === today.getFullYear()
  )
}

const isOtherMonth = (date) => {
  return date.getMonth() !== currentDate.value.getMonth()
}

const dateKey = (date) => {
  return date.toISOString().split('T')[0]
}

const getTasksForDate = (date) => {
  const dateStr = dateKey(date)
  return (props.tasks || []).filter((task) => {
    // Include task if this date falls within its date range
    const taskStart = task.start_date || task.due_date
    const taskEnd = task.due_date || task.start_date
    
    if (!taskStart || !taskEnd) return false
    
    // Check if date is within task range (inclusive)
    return dateStr >= taskStart && dateStr <= taskEnd
  })
}

const toggleExpandDay = (date) => {
  const key = dateKey(date)
  if (expandedDays.value.has(key)) {
    expandedDays.value.delete(key)
  } else {
    expandedDays.value.add(key)
  }
}

const handleCreateTask = (date) => {
  emit('create-task', { due_date: dateKey(date) })
}

const handleUpdateDueDate = (taskId, newDate) => {
  emit('update-due-date', { taskId, newDate: dateKey(newDate) })
}
</script>
