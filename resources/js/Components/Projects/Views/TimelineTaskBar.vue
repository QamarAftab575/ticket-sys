<template>
  <div
    v-if="isVisible"
    class="absolute top-1/2 -translate-y-1/2 h-8 rounded z-10 select-none"
    :class="[
      isDragging ? 'cursor-grabbing shadow-xl' : 'cursor-grab hover:shadow-lg',
      isCompleted ? 'opacity-50' : ''
    ]"
    :style="{
      left: currentLeft + 'px',
      width: currentWidth + 'px',
      backgroundColor: taskColor,
      transition: isDragging ? 'none' : 'box-shadow 0.15s',
    }"
    @mousedown="handleBarMouseDown"
    @click.stop="onBarClick"
    @mouseenter="showTooltip = true"
    @mouseleave="showTooltip = false"
  >
    <!-- Left Resize Handle -->
    <div
      v-if="!task.is_milestone"
      class="absolute left-0 top-0 bottom-0 w-3 flex items-center justify-center cursor-ew-resize z-20 group/handle"
      @mousedown.stop="handleLeftResize"
      title="Drag to change start date"
    >
      <div class="w-0.5 h-4 bg-white opacity-0 group-hover/handle:opacity-70 rounded-full transition-opacity"></div>
    </div>

    <!-- Task Content -->
    <div class="h-full flex items-center px-3 text-white text-xs font-medium overflow-hidden pointer-events-none gap-1.5">
      <span v-if="task.is_milestone" class="flex-shrink-0">◆</span>
      <!-- Checkmark for completed -->
      <svg v-if="isCompleted" class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
      </svg>
      <span 
        :class="isCompleted ? 'line-through' : ''"
        class="whitespace-nowrap"
        :style="{ 
          overflow: zoomLevel === 'Day' ? 'visible' : 'hidden',
          textOverflow: zoomLevel === 'Day' ? 'clip' : 'ellipsis'
        }"
      >{{ task.name }}</span>
    </div>

    <!-- Right Resize Handle -->
    <div
      v-if="!task.is_milestone"
      class="absolute right-0 top-0 bottom-0 w-3 flex items-center justify-center cursor-ew-resize z-20 group/handle"
      @mousedown.stop="handleRightResize"
      title="Drag to change due date"
    >
      <div class="w-0.5 h-4 bg-white opacity-0 group-hover/handle:opacity-70 rounded-full transition-opacity"></div>
    </div>

    <!-- Tooltip -->
    <Teleport to="body">
      <div
        v-if="showTooltip && !isDragging"
        class="fixed z-[9999] bg-gray-900 text-white text-xs rounded px-3 py-2 whitespace-nowrap shadow-lg pointer-events-none"
        :style="{ left: tooltipX + 'px', top: tooltipY + 'px', transform: 'translate(-50%, -110%)' }"
      >
        <div class="font-semibold mb-1">{{ task.name }}</div>
        <div v-if="task.assignee" class="text-gray-300">{{ task.assignee.name }}</div>
        <div class="text-gray-300">
          {{ formatDate(task.start_date || task.due_date) }}
          <span v-if="task.start_date && task.due_date && task.start_date !== task.due_date">
            → {{ formatDate(task.due_date) }}
          </span>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onUnmounted } from 'vue'

const props = defineProps({
  task: { type: Object, required: true },
  startDate: { type: Date, required: true },
  cellWidth: { type: Number, required: true, validator: (value) => !isNaN(value) && value > 0 },
  zoomLevel: { type: String, default: 'Weeks' },
})

const emit = defineEmits(['update', 'click'])

// ─── Date Utilities ───────────────────────────────────────────────────────────
/**
 * Normalize any date input to local midnight Date object
 * Prevents timezone offset issues
 */
const normalizeDate = (input) => {
  if (!input) return null
  if (input instanceof Date) {
    return new Date(input.getFullYear(), input.getMonth(), input.getDate())
  }
  if (typeof input === 'string') {
    const [y, m, d] = input.split('-').map(Number)
    return new Date(y, m - 1, d)
  }
  return null
}

/**
 * Convert Date object to ISO date string (YYYY-MM-DD)
 */
const dateToISO = (date) => {
  if (!date) return null
  const d = normalizeDate(date)
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

/**
 * Calculate days between two dates (inclusive)
 */
const daysBetween = (start, end) => {
  const d1 = normalizeDate(start)
  const d2 = normalizeDate(end)
  if (!d1 || !d2) return 0
  return Math.round((d2 - d1) / 86400000) + 1 // Inclusive
}

/**
 * Add days to a date
 */
const addDays = (dateStr, days) => {
  const d = normalizeDate(dateStr)
  if (!d) return dateStr
  d.setDate(d.getDate() + days)
  return dateToISO(d)
}

// ─── State ────────────────────────────────────────────────────────────────────
const showTooltip = ref(false)
const isDragging = ref(false)
const dragMode = ref(null) // 'move' | 'resize-left' | 'resize-right'
const tooltipX = ref(0)
const tooltipY = ref(0)

// Live visual offsets while dragging (in pixels, snapped to days)
const dragDeltaDays = ref(0)

// ─── Colors ───────────────────────────────────────────────────────────────────
const isCompleted = computed(() => !!props.task.completed_at || props.task.status === 'complete')

const taskColor = computed(() => {
  if (isCompleted.value) return '#6ee7b7' // muted green — opacity-50 on the bar handles the fade
  const colors = { urgent: '#dc2626', high: '#ef4444', medium: '#f59e0b', low: '#3b82f6' }
  return colors[props.task.priority] || '#6366f1'
})

// ─── Parse ISO date string as LOCAL date (avoids UTC-offset shift) ───────────
// DEPRECATED: Use normalizeDate instead
const parseLocalDate = (dateStr) => {
  const [y, m, d] = dateStr.split('-').map(Number)
  return new Date(y, m - 1, d) // local midnight — no timezone shift
}

// ─── Base position (from saved dates) ────────────────────────────────────────
const baseLeft = computed(() => {
  const taskStart = normalizeDate(props.task.start_date || props.task.due_date)
  const origin = normalizeDate(props.startDate)
  if (!taskStart || !origin) return 0
  
  const diff = Math.round((taskStart - origin) / 86400000)
  return diff * props.cellWidth
})

const baseWidth = computed(() => {
  if (props.task.is_milestone || !props.task.start_date || !props.task.due_date) {
    return props.cellWidth
  }
  
  const days = daysBetween(props.task.start_date, props.task.due_date)
  return Math.max(props.cellWidth, days * props.cellWidth)
})

// ─── Live position (includes drag delta) ─────────────────────────────────────
const currentLeft = computed(() => {
  if (!isDragging.value) return baseLeft.value
  if (dragMode.value === 'move') return baseLeft.value + dragDeltaDays.value * props.cellWidth
  if (dragMode.value === 'resize-left') return baseLeft.value + dragDeltaDays.value * props.cellWidth
  return baseLeft.value
})

const currentWidth = computed(() => {
  if (!isDragging.value) return baseWidth.value
  if (dragMode.value === 'move') return baseWidth.value
  if (dragMode.value === 'resize-left') return Math.max(props.cellWidth, baseWidth.value - dragDeltaDays.value * props.cellWidth)
  if (dragMode.value === 'resize-right') return Math.max(props.cellWidth, baseWidth.value + dragDeltaDays.value * props.cellWidth)
  return baseWidth.value
})

// Only render bars that could be visible — bars more than 1 viewport-width
// outside the timeline start are skipped. 20000px ≈ 500 days at 40px/day,
// which covers the full 365-day window plus drag headroom.
const isVisible = computed(() => {
  const left = baseLeft.value
  const width = baseWidth.value
  // Hidden if entirely before timeline start or beyond 365 days
  return left + width > 0 && left < 365 * props.cellWidth
})

// ─── Drag logic ───────────────────────────────────────────────────────────────
let dragStartX = 0
let clickPrevented = false

const startDrag = (e, mode) => {
  if (e.button !== 0) return
  dragMode.value = mode
  isDragging.value = true
  dragStartX = e.clientX
  dragDeltaDays.value = 0
  clickPrevented = false

  document.addEventListener('mousemove', onMouseMove)
  document.addEventListener('mouseup', onMouseUp)
  e.preventDefault()
}

const handleBarMouseDown = (e) => startDrag(e, 'move')
const handleLeftResize = (e) => startDrag(e, 'resize-left')
const handleRightResize = (e) => startDrag(e, 'resize-right')

const onMouseMove = (e) => {
  if (!isDragging.value) return
  const rawDelta = e.clientX - dragStartX
  const days = Math.round(rawDelta / props.cellWidth)
  dragDeltaDays.value = days
  if (Math.abs(rawDelta) > 3) clickPrevented = true

  // Update tooltip position
  tooltipX.value = e.clientX
  tooltipY.value = e.clientY
}

const onMouseUp = () => {
  if (!isDragging.value) return

  const days = dragDeltaDays.value

  if (days !== 0) {
    const mode = dragMode.value

    if (mode === 'move') {
      emit('update', {
        taskId: props.task.id,
        start_date: props.task.start_date ? addDays(props.task.start_date, days) : null,
        due_date: props.task.due_date ? addDays(props.task.due_date, days) : null,
      })
    } else if (mode === 'resize-left' && props.task.start_date) {
      const newStart = addDays(props.task.start_date, days)
      if (!props.task.due_date || newStart <= props.task.due_date) {
        emit('update', { taskId: props.task.id, start_date: newStart, due_date: props.task.due_date })
      }
    } else if (mode === 'resize-right') {
      const base = props.task.due_date || props.task.start_date
      const newDue = addDays(base, days)
      if (!props.task.start_date || newDue >= props.task.start_date) {
        emit('update', { taskId: props.task.id, start_date: props.task.start_date, due_date: newDue })
      }
    }
  }

  isDragging.value = false
  dragMode.value = null
  dragDeltaDays.value = 0
  document.removeEventListener('mousemove', onMouseMove)
  document.removeEventListener('mouseup', onMouseUp)
}

const onBarClick = () => {
  if (!clickPrevented) emit('click')
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
// DEPRECATED: Use addDays utility instead
const addDaysOld = (dateStr, days) => {
  const d = new Date(dateStr)
  d.setDate(d.getDate() + days)
  return d.toISOString().split('T')[0]
}

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A'
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

onUnmounted(() => {
  document.removeEventListener('mousemove', onMouseMove)
  document.removeEventListener('mouseup', onMouseUp)
})
</script>
