<template>
  <div class="space-y-6">
    <!-- Calendar header -->
    <div class="flex items-center justify-between">
      <!-- Navigation buttons -->
      <div class="flex gap-2">
        <button
          @click="navigatePrevious"
          class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors font-medium text-gray-700 cursor-pointer"
          :aria-label="viewMode === 'month' ? 'Previous month' : 'Previous week'"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Previous
        </button>
        <button
          @click="goToToday"
          class="px-4 py-2.5 bg-white border border-gray-200 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition-colors font-medium text-gray-700 cursor-pointer"
          aria-label="Go to today"
        >
          Today
        </button>
        <button
          @click="navigateNext"
          class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors font-medium text-gray-700 cursor-pointer"
          :aria-label="viewMode === 'month' ? 'Next month' : 'Next week'"
        >
          Next
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <!-- Title -->
      <h3 class="text-2xl font-bold text-gray-900">{{ displayTitle }}</h3>

      <!-- View mode toggle -->
      <div class="flex gap-1 bg-gray-100 rounded-lg p-1">
        <button
          @click="viewMode = 'month'"
          :class="[
            'px-4 py-2 text-sm font-medium rounded transition-all cursor-pointer',
            viewMode === 'month'
              ? 'bg-indigo-600 text-white shadow-md'
              : 'bg-transparent text-gray-700 hover:text-gray-900'
          ]"
          :aria-pressed="viewMode === 'month'"
        >
          Month
        </button>
        <button
          @click="viewMode = 'week'"
          :class="[
            'px-4 py-2 text-sm font-medium rounded transition-all cursor-pointer',
            viewMode === 'week'
              ? 'bg-indigo-600 text-white shadow-md'
              : 'bg-transparent text-gray-700 hover:text-gray-900'
          ]"
          :aria-pressed="viewMode === 'week'"
        >
          Week
        </button>
      </div>
    </div>

    <!-- Month View -->
    <div v-if="viewMode === 'month'" class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
      <!-- Day headers -->
      <div class="grid grid-cols-7 bg-gradient-to-r from-gray-50 to-gray-50 border-b border-gray-200">
        <div
          v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']"
          :key="day"
          class="p-4 text-center font-bold text-sm text-gray-700"
        >
          {{ day }}
        </div>
      </div>

      <!-- Calendar cells -->
      <div class="divide-y divide-gray-200">
        <div class="grid grid-cols-7 divide-x divide-gray-200">
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
    </div>

    <!-- Week View -->
    <div v-else class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
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
    
    // If same month: "May 10  " 16"
    if (start.getMonth() === end.getMonth()) {
      return `${startMonth} ${startDay}  " ${endDay}`
    }
    // If different months: "May 30  " Jun 5"
    else {
      return `${startMonth} ${startDay}  " ${endMonth} ${endDay}`
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
