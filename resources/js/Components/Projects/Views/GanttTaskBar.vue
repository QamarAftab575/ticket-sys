<template>
  <div
    class="absolute h-8 rounded cursor-move group"
    :class="[
      'hover:shadow-md transition-shadow',
      isMilestone ? 'w-4' : '',
    ]"
    :style="{
      left: barLeft + 'px',
      width: isMilestone ? '4px' : barWidth + 'px',
      backgroundColor: color,
      top: '50%',
      transform: 'translateY(-50%)',
    }"
    @mousedown="handleMouseDown"
    @click.stop="$emit('click')"
  >
    <!-- Task bar content -->
    <div
      v-if="!isMilestone"
      class="h-full flex items-center px-2 text-white text-xs font-medium truncate"
      :class="{ 'hidden': barWidth < 60 }"
    >
      {{ task.name }}
    </div>

    <!-- Task name outside bar (if bar is too narrow) -->
    <div
      v-if="!isMilestone && barWidth < 60"
      class="absolute left-full ml-2 top-1/2 transform -translate-y-1/2 text-xs font-medium whitespace-nowrap pointer-events-none"
    >
      {{ task.name }}
    </div>

    <!-- Milestone diamond marker -->
    <div
      v-else
      class="w-4 h-4 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"
      :style="{ clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)' }"
    />

    <!-- Drag handles -->
    <div
      v-if="!isMilestone"
      class="absolute left-0 top-0 bottom-0 w-1 bg-gray-700 opacity-0 group-hover:opacity-100 cursor-col-resize"
      @mousedown.stop="handleLeftEdgeDrag"
      title="Drag to change start date"
    />
    <div
      v-if="!isMilestone"
      class="absolute right-0 top-0 bottom-0 w-1 bg-gray-700 opacity-0 group-hover:opacity-100 cursor-col-resize"
      @mousedown.stop="handleRightEdgeDrag"
      title="Drag to change end date"
    />

    <!-- Dependency drag handle (right edge) -->
    <div
      v-if="!isMilestone"
      class="absolute right-0 top-1/2 transform -translate-y-1/2 w-2 h-2 bg-red-500 rounded-full opacity-0 group-hover:opacity-100 cursor-crosshair"
      @mousedown.stop="handleDependencyDrag"
      title="Drag to create dependency"
    />

    <!-- Tooltip -->
    <div
      v-if="showTooltip"
      class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 bg-gray-900 text-white text-xs rounded p-2 whitespace-nowrap z-50 pointer-events-none"
    >
      <div class="font-semibold">{{ task.name }}</div>
      <div v-if="task.assignee" class="text-gray-300">{{ task.assignee.name }}</div>
      <div class="text-gray-300">
        {{ formatDate(task.start_date) }} - {{ formatDate(task.due_date) }}
      </div>
      <div class="text-gray-300">{{ task.status }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  task: {
    type: Object,
    required: true,
  },
  date: {
    type: String,
    required: true,
  },
  dateIndex: {
    type: Number,
    required: true,
  },
  dateRange: {
    type: Array,
    required: true,
  },
  cellWidth: {
    type: Number,
    required: true,
  },
  color: {
    type: String,
    default: '#3b82f6',
  },
})

const emit = defineEmits(['click', 'drag-start', 'drag-edge'])

const showTooltip = ref(false)
const dragMode = ref(null) // 'move', 'left', 'right'
const dragStartX = ref(0)
const dragStartDate = ref(null)

// Check if task is a milestone (no start date or point in time)
const isMilestone = computed(() => {
  return !props.task.start_date || props.task.start_date === props.task.due_date
})

// Calculate bar position and width
const barLeft = computed(() => {
  if (!props.task.start_date) return 0
  const startIdx = props.dateRange.indexOf(props.task.start_date)
  return startIdx >= 0 ? startIdx * props.cellWidth : 0
})

const barWidth = computed(() => {
  if (!props.task.start_date || !props.task.due_date) return props.cellWidth
  const startIdx = props.dateRange.indexOf(props.task.start_date)
  const endIdx = props.dateRange.indexOf(props.task.due_date)
  if (startIdx < 0 || endIdx < 0) return props.cellWidth
  return (endIdx - startIdx + 1) * props.cellWidth
})

// Format date for display
const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

// Handle mouse down for dragging
const handleMouseDown = (e) => {
  if (e.button !== 0) return // Only left click
  dragMode.value = 'move'
  dragStartX.value = e.clientX
  dragStartDate.value = props.task.start_date
  showTooltip.value = true
  emit('drag-start', { task: props.task, mode: 'move' })

  document.addEventListener('mousemove', handleMouseMove)
  document.addEventListener('mouseup', handleMouseUp)
}

// Handle left edge drag
const handleLeftEdgeDrag = (e) => {
  dragMode.value = 'left'
  dragStartX.value = e.clientX
  dragStartDate.value = props.task.start_date
  emit('drag-start', { task: props.task, mode: 'left' })

  document.addEventListener('mousemove', handleMouseMove)
  document.addEventListener('mouseup', handleMouseUp)
}

// Handle right edge drag
const handleRightEdgeDrag = (e) => {
  dragMode.value = 'right'
  dragStartX.value = e.clientX
  dragStartDate.value = props.task.due_date
  emit('drag-start', { task: props.task, mode: 'right' })

  document.addEventListener('mousemove', handleMouseMove)
  document.addEventListener('mouseup', handleMouseUp)
}

// Handle dependency drag
const handleDependencyDrag = (e) => {
  dragMode.value = 'dependency'
  dragStartX.value = e.clientX
  emit('drag-start', { task: props.task, mode: 'dependency' })

  document.addEventListener('mousemove', handleDependencyMouseMove)
  document.addEventListener('mouseup', handleDependencyMouseUp)
}

// Handle mouse move during drag
const handleMouseMove = (e) => {
  if (!dragMode.value) return

  const deltaX = e.clientX - dragStartX.value
  const daysDelta = Math.round(deltaX / props.cellWidth)

  if (daysDelta === 0) return

  const startDate = new Date(dragStartDate.value)
  startDate.setDate(startDate.getDate() + daysDelta)
  const newDate = startDate.toISOString().split('T')[0]

  emit('drag-edge', {
    task: props.task,
    mode: dragMode.value,
    newDate,
    daysDelta,
  })
}

// Handle mouse up
const handleMouseUp = () => {
  dragMode.value = null
  dragStartX.value = 0
  dragStartDate.value = null
  showTooltip.value = false

  document.removeEventListener('mousemove', handleMouseMove)
  document.removeEventListener('mouseup', handleMouseUp)
}

// Handle dependency mouse move
const handleDependencyMouseMove = (e) => {
  if (dragMode.value !== 'dependency') return
  // This will be handled by the parent component to draw the dependency line
  emit('drag-edge', {
    task: props.task,
    mode: 'dependency',
    clientX: e.clientX,
    clientY: e.clientY,
  })
}

// Handle dependency mouse up
const handleDependencyMouseUp = () => {
  dragMode.value = null
  dragStartX.value = 0

  document.removeEventListener('mousemove', handleDependencyMouseMove)
  document.removeEventListener('mouseup', handleDependencyMouseUp)
}
</script>

