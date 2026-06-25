<template>
  <div class="border rounded overflow-hidden">
    <!-- Day headers -->
    <div class="grid grid-cols-7 gap-px bg-gray-200">
      <div
        v-for="(day, idx) in weekDays"
        :key="idx"
        :class="[
          'p-3 text-center font-semibold bg-white',
          isToday(day) ? 'bg-blue-50 border-b-2 border-blue-500' : '',
        ]"
      >
        <div class="text-xs text-gray-600 uppercase">{{ formatDayName(day) }}</div>
        <div 
          :class="[
            'text-2xl font-bold mt-1',
            isToday(day) ? 'text-blue-600' : 'text-gray-900'
          ]"
        >
          {{ day.getDate() }}
        </div>
      </div>
    </div>

    <!-- Day columns with tasks -->
    <div class="grid grid-cols-7 gap-px bg-gray-200">
      <div
        v-for="(day, dayIdx) in weekDays"
        :key="`day-${dayIdx}`"
        :class="[
          'bg-white min-h-[500px] p-2',
          isToday(day) ? 'bg-blue-50/30' : '',
        ]"
      >
        <!-- Tasks for this day -->
        <div class="space-y-2">
          <div
            v-for="task in getTasksForDay(day)"
            :key="task.id"
            @click="$emit('select-task', task)"
            :style="getTaskStyle(task)"
            :class="getTaskClasses(task)"
            :title="getTaskTitle(task)"
            draggable="true"
            @dragstart="handleDragStart($event, task)"
            @dragend="handleDragEnd"
          >
            <!-- Checkmark for completed tasks -->
            <svg v-if="isTaskCompleted(task)" class="w-3 h-3 inline-block mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            
            <!-- Task name -->
            <span 
              :class="[
                'text-xs font-medium',
                isTaskCompleted(task) ? 'line-through' : ''
              ]"
            >
              {{ task.name }}
            </span>
          </div>

          <!-- Empty state hint on hover -->
          <div
            v-if="getTasksForDay(day).length === 0"
            class="text-xs text-gray-400 text-center py-8 opacity-0 hover:opacity-100 transition-opacity cursor-pointer"
            @click="$emit('create-task', { due_date: dateKey(day) })"
          >
            + Add task
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  startDate: Date,
  tasks: Array,
  filters: Object,
})

const emit = defineEmits(['select-task', 'create-task', 'update-due-date'])

const draggedTask = ref(null)

const weekDays = computed(() => {
  const days = []
  for (let i = 0; i < 7; i++) {
    const date = new Date(props.startDate)
    date.setDate(date.getDate() + i)
    days.push(date)
  }
  return days
})

const isToday = (date) => {
  const today = new Date()
  return (
    date.getDate() === today.getDate() &&
    date.getMonth() === today.getMonth() &&
    date.getFullYear() === today.getFullYear()
  )
}

const formatDayName = (date) => {
  return date.toLocaleDateString('en-US', { weekday: 'short' })
}

const dateKey = (date) => {
  return date.toISOString().split('T')[0]
}

const getTasksForDay = (date) => {
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

const getTaskColor = (task) => {
  if (task.project?.color) {
    return task.project.color
  }
  const priorityColors = {
    high: '#ef4444',
    medium: '#f59e0b',
    low: '#10b981',
  }
  return priorityColors[task.priority] || '#3b82f6'
}

// Check if task is completed
const isTaskCompleted = (task) => {
  return task.status === 'complete' || task.completed_at
}

// Check if this date is the start of the task
const isTaskStart = (task, date) => {
  const dateStr = dateKey(date)
  const taskStart = task.start_date || task.due_date
  return dateStr === taskStart
}

// Check if this date is the end of the task
const isTaskEnd = (task, date) => {
  const dateStr = dateKey(date)
  const taskEnd = task.due_date || task.start_date
  return dateStr === taskEnd
}

// Check if task is a single-day task
const isSingleDay = (task) => {
  const taskStart = task.start_date || task.due_date
  const taskEnd = task.due_date || task.start_date
  return !task.start_date || !task.due_date || taskStart === taskEnd
}

// Get border radius based on task position
const getTaskBorderRadius = (task, date) => {
  if (isSingleDay(task)) {
    return '0.25rem'
  }
  
  const isStart = isTaskStart(task, date)
  const isEnd = isTaskEnd(task, date)
  
  if (isStart && isEnd) {
    return '0.25rem'
  } else if (isStart) {
    return '0.25rem 0.25rem 0 0' // Rounded on top
  } else if (isEnd) {
    return '0 0 0.25rem 0.25rem' // Rounded on bottom
  } else {
    return '0' // No rounding for middle days
  }
}

// Get complete style object for task
const getTaskStyle = (task) => {
  const isCompleted = isTaskCompleted(task)
  
  // Find which day this task is being rendered on
  const currentDay = weekDays.value.find(day => {
    const dateStr = dateKey(day)
    const taskStart = task.start_date || task.due_date
    const taskEnd = task.due_date || task.start_date
    return dateStr >= taskStart && dateStr <= taskEnd
  })
  
  return {
    backgroundColor: getTaskColor(task),
    borderRadius: currentDay ? getTaskBorderRadius(task, currentDay) : '0.25rem',
    opacity: isCompleted ? '0.5' : '1'
  }
}

// Get CSS classes for task
const getTaskClasses = (task) => {
  return 'text-white px-3 py-2 rounded cursor-pointer hover:opacity-80 flex items-center transition-opacity'
}

// Get task title with date range info
const getTaskTitle = (task) => {
  const taskStart = task.start_date || task.due_date
  const taskEnd = task.due_date || task.start_date
  
  if (isSingleDay(task)) {
    return task.name
  }
  
  return `${task.name} (${taskStart}  ${taskEnd})`
}

const handleDragStart = (event, task) => {
  draggedTask.value = task
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('taskId', task.id)
}

const handleDragEnd = () => {
  draggedTask.value = null
}
</script>

