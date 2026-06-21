<template>
  <div
    :class="[
      'bg-white p-2 min-h-24 border transition-colors',
      isToday ? 'bg-blue-50 border-blue-300' : 'border-gray-200',
      isOtherMonth ? 'bg-gray-50' : '',
      'hover:bg-gray-100 cursor-pointer',
      isDragOver ? 'bg-blue-100 border-blue-400' : '',
    ]"
    @mouseenter="isHovered = true"
    @mouseleave="isHovered = false"
    @click="handleCellClick"
    @dragover.prevent="isDragOver = true"
    @dragleave="isDragOver = false"
    @drop.prevent="handleDrop"
  >
    <!-- Date number -->
    <div
      :class="[
        'text-sm font-semibold mb-1',
        isOtherMonth ? 'text-gray-400' : 'text-gray-900',
      ]"
    >
      {{ date.getDate() }}
    </div>

    <!-- Expanded view for many tasks -->
    <div v-if="isExpanded" class="space-y-1">
      <div
        v-for="task in tasks"
        :key="task.id"
        @click.stop="$emit('select-task', task)"
        @dragstart="handleDragStart($event, task)"
        @dragend="handleDragEnd"
        :style="getTaskStyle(task)"
        :class="getTaskClasses(task)"
        :title="getTaskTitle(task)"
        draggable="true"
      >
        <svg v-if="isTaskCompleted(task)" class="w-3 h-3 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
        </svg>
        <span v-if="isTaskStart(task)" :class="isTaskCompleted(task) ? 'line-through' : ''">{{ task.name }}</span>
        <span v-else class="text-white/70 italic" :class="isTaskCompleted(task) ? 'line-through' : ''">{{ task.name }}</span>
      </div>
      <button
        @click.stop="$emit('toggle-expand', date)"
        class="text-xs text-blue-600 hover:text-blue-800 font-medium"
      >
        Show less
      </button>
    </div>

    <!-- Collapsed view (first 3 tasks) -->
    <div v-else class="space-y-1">
      <div
        v-for="(task, idx) in displayedTasks"
        :key="task.id"
        @click.stop="$emit('select-task', task)"
        @dragstart="handleDragStart($event, task)"
        @dragend="handleDragEnd"
        :style="getTaskStyle(task)"
        :class="getTaskClasses(task)"
        :title="getTaskTitle(task)"
        draggable="true"
      >
        <svg v-if="isTaskCompleted(task)" class="w-3 h-3 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
        </svg>
        <span v-if="isTaskStart(task)" :class="isTaskCompleted(task) ? 'line-through' : ''">{{ task.name }}</span>
        <span v-else class="text-white/70 italic" :class="isTaskCompleted(task) ? 'line-through' : ''">{{ task.name }}</span>
      </div>

      <!-- More indicator -->
      <button
        v-if="tasks.length > 3"
        @click.stop="$emit('toggle-expand', date)"
        class="text-xs text-blue-600 hover:text-blue-800 font-medium"
      >
        +{{ tasks.length - 3 }} more
      </button>

      <!-- Create task button on hover -->
      <button
        v-if="isHovered && !isOtherMonth"
        @click.stop="$emit('create-task', date)"
        class="text-xs text-gray-500 hover:text-gray-700 font-medium"
      >
        + Add task
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  date: Date,
  tasks: Array,
  isToday: Boolean,
  isOtherMonth: Boolean,
  expandedDays: Set,
})

const emit = defineEmits(['select-task', 'toggle-expand', 'create-task', 'update-due-date'])

const isHovered = ref(false)
const isDragOver = ref(false)
const draggedTask = ref(null)

const displayedTasks = computed(() => {
  return (props.tasks || []).slice(0, 3)
})

const isExpanded = computed(() => {
  const dateStr = props.date.toISOString().split('T')[0]
  return props.expandedDays.has(dateStr)
})

// Helper function to get task color
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

// Check if this date is the start of the task
const isTaskStart = (task) => {
  const dateStr = props.date.toISOString().split('T')[0]
  const taskStart = task.start_date || task.due_date
  return dateStr === taskStart
}

// Check if this date is the end of the task
const isTaskEnd = (task) => {
  const dateStr = props.date.toISOString().split('T')[0]
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
const getTaskBorderRadius = (task) => {
  if (isSingleDay(task)) {
    return '0.25rem'
  }
  
  const isStart = isTaskStart(task)
  const isEnd = isTaskEnd(task)
  
  if (isStart && isEnd) {
    return '0.25rem'
  } else if (isStart) {
    return '0.25rem 0 0 0.25rem'
  } else if (isEnd) {
    return '0 0.25rem 0.25rem 0'
  } else {
    return '0'
  }
}

// Check if task is completed
const isTaskCompleted = (task) => {
  return task.status === 'complete' || task.completed_at
}

// Get complete style object for task
const getTaskStyle = (task) => {
  const isCompleted = isTaskCompleted(task)
  
  return {
    backgroundColor: getTaskColor(task),
    borderRadius: getTaskBorderRadius(task),
    opacity: isCompleted ? '0.5' : '1'
  }
}

// Get CSS classes for task position styling
const getTaskClasses = (task) => {
  const baseClasses = 'text-xs text-white px-2 py-1 cursor-move hover:opacity-80 truncate relative'
  
  if (isSingleDay(task)) {
    return baseClasses
  }
  
  const isStart = isTaskStart(task)
  const isEnd = isTaskEnd(task)
  
  if (!isStart && !isEnd) {
    return `${baseClasses} border-l-2 border-r-2 border-white/30`
  } else if (isStart) {
    return `${baseClasses} border-r-2 border-white/30`
  } else if (isEnd) {
    return `${baseClasses} border-l-2 border-white/30`
  }
  
  return baseClasses
}

// Get task title with date range info
const getTaskTitle = (task) => {
  const taskStart = task.start_date || task.due_date
  const taskEnd = task.due_date || task.start_date
  
  if (isSingleDay(task)) {
    return task.name
  }
  
  return `${task.name} (${taskStart} â†’ ${taskEnd})`
}

const handleCellClick = () => {
  // Only allow create task on empty cells in current month
  if (!props.isOtherMonth && (!props.tasks || props.tasks.length === 0)) {
    // Emit create-task event
  }
}

const handleDragStart = (event, task) => {
  draggedTask.value = task
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('taskId', task.id)
}

const handleDragEnd = () => {
  draggedTask.value = null
  isDragOver.value = false
}

const handleDrop = (event) => {
  event.preventDefault()
  isDragOver.value = false
  
  const taskId = event.dataTransfer.getData('taskId')
  if (taskId && draggedTask.value) {
    emit('update-due-date', taskId, props.date)
  }
}
</script>

