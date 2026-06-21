<template>
  <div class="flex flex-col h-full bg-white">
    <!-- Timeline Header -->
    <div class="flex items-center justify-between gap-4 p-4 bg-gray-50 border-b">
      <div class="flex items-center gap-2">
        <button
          @click="zoomOut"
          class="px-3 py-1 border rounded hover:bg-gray-200 text-sm"
          title="Zoom out"
        >
          âˆ’
        </button>
        <span class="text-sm font-medium min-w-16 text-center">{{ zoomLevel }}</span>
        <button
          @click="zoomIn"
          class="px-3 py-1 border rounded hover:bg-gray-200 text-sm"
          title="Zoom in"
        >
          +
        </button>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="previousPeriod"
          class="px-3 py-1 border rounded hover:bg-gray-200 text-sm"
        >
          â† Previous
        </button>
        <button
          @click="goToToday"
          class="px-3 py-1 border rounded hover:bg-gray-200 text-sm font-medium"
        >
          Today
        </button>
        <button
          @click="nextPeriod"
          class="px-3 py-1 border rounded hover:bg-gray-200 text-sm"
        >
          Next â†’
        </button>
      </div>

      <div class="flex items-center gap-2">
        <label class="text-sm font-medium">Group by:</label>
        <select
          v-model="groupingField"
          class="px-2 py-1 border rounded text-sm"
        >
          <option value="section">Section</option>
          <option value="assignee">Assignee</option>
          <option value="priority">Priority</option>
        </select>
      </div>
    </div>

    <!-- Timeline Container -->
    <div class="flex flex-1 overflow-hidden">
      <!-- Left Panel: Task List -->
      <div class="w-64 border-r overflow-y-auto bg-gray-50">
        <div class="sticky top-0 bg-gray-100 border-b p-2 text-xs font-semibold">
          Tasks
        </div>
        <div class="space-y-0">
          <div
            v-for="group in groupedTasks"
            :key="group.name"
            class="border-b"
          >
            <!-- Group Header -->
            <button
              @click="toggleGroup(group.name)"
              class="w-full flex items-center gap-2 p-2 hover:bg-gray-200 text-sm font-medium"
            >
              <svg
                :class="{ 'rotate-90': !collapsedGroups.has(group.name) }"
                class="w-4 h-4 transition-transform"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
              {{ group.name }} ({{ group.tasks.length }})
            </button>

            <!-- Group Tasks -->
            <div v-if="!collapsedGroups.has(group.name)" class="space-y-0">
              <div
                v-for="task in group.tasks"
                :key="task.id"
                class="flex items-center gap-2 p-2 text-xs hover:bg-blue-50 cursor-pointer border-b"
                @click="$emit('select-task', task)"
              >
                <div class="flex-1 truncate">{{ task.name }}</div>
                <div
                  v-if="task.assignee"
                  class="w-5 h-5 rounded-full flex-shrink-0"
                  :title="task.assignee.name"
                >
                  <img
                    :src="task.assignee.avatar"
                    :alt="task.assignee.name"
                    class="w-full h-full rounded-full"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Panel: Timeline -->
      <div class="flex-1 overflow-x-auto overflow-y-auto">
        <!-- Date Headers -->
        <div class="sticky top-0 bg-white border-b z-10">
          <!-- Month/Quarter/Year Header -->
          <div class="flex border-b">
            <div
              v-for="period in periodHeaders"
              :key="period.label"
              class="flex-shrink-0 border-r text-center text-xs font-semibold p-1"
              :style="{ width: period.width + 'px' }"
            >
              {{ period.label }}
            </div>
          </div>

          <!-- Day/Week Header -->
          <div class="flex border-b">
            <div
              v-for="date in dateRange"
              :key="date"
              class="flex-shrink-0 border-r text-center text-xs p-1"
              :style="{ width: cellWidth + 'px' }"
            >
              {{ formatDateHeader(date) }}
            </div>
          </div>
        </div>

        <!-- Timeline Grid -->
        <div class="relative">
          <!-- Today Line -->
          <div
            v-if="todayPosition !== null"
            class="absolute top-0 bottom-0 w-0.5 bg-red-500 z-20"
            :style="{ left: todayPosition + 'px' }"
          />

          <!-- Task Rows -->
          <div class="space-y-0">
            <div
              v-for="group in groupedTasks"
              :key="group.name"
            >
              <!-- Group Header Row -->
              <div
                class="flex items-center h-8 bg-gray-100 border-b font-semibold text-xs px-2"
              >
                {{ group.name }}
              </div>

              <!-- Task Rows -->
              <div v-if="!collapsedGroups.has(group.name)" class="space-y-0">
                <div
                  v-for="task in group.tasks"
                  :key="task.id"
                  class="flex items-center h-10 border-b hover:bg-blue-50 relative"
                >
                  <!-- Timeline cells -->
                  <div class="flex flex-1">
                    <div
                      v-for="(date, idx) in dateRange"
                      :key="date"
                      class="flex-shrink-0 border-r border-gray-200 relative"
                      :style="{ width: cellWidth + 'px' }"
                    >
                      <!-- Task bar -->
                      <GanttTaskBar
                        v-if="isTaskInRange(task, date, idx)"
                        :task="task"
                        :date="date"
                        :date-index="idx"
                        :date-range="dateRange"
                        :cell-width="cellWidth"
                        :color="getTaskColor(task)"
                        @click="$emit('select-task', task)"
                        @drag-start="startDragBar(task, $event)"
                        @drag-edge="dragEdge(task, $event)"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Dependency Arrows -->
          <svg
            v-if="dependencies.length"
            class="absolute top-0 left-0 w-full h-full pointer-events-none"
            style="z-index: 5"
          >
            <g stroke="gray" stroke-width="1" fill="none" marker-end="url(#arrowhead)">
              <line
                v-for="dep in dependencies"
                :key="dep.id"
                :x1="dep.x1"
                :y1="dep.y1"
                :x2="dep.x2"
                :y2="dep.y2"
                :stroke="dep.conflict ? 'red' : 'gray'"
                class="hover:stroke-blue-500 cursor-pointer pointer-events-auto"
                @mouseenter="hoveredDependency = dep.id"
                @mouseleave="hoveredDependency = null"
              />
              <!-- Dependency tooltip -->
              <g v-if="hoveredDependency" class="pointer-events-none">
                <rect
                  :x="(dep.x1 + dep.x2) / 2 - 50"
                  :y="Math.min(dep.y1, dep.y2) - 40"
                  width="100"
                  height="30"
                  fill="black"
                  rx="4"
                  v-for="dep in dependencies.filter(d => d.id === hoveredDependency)"
                  :key="`tooltip-${dep.id}`"
                />
                <text
                  :x="(dep.x1 + dep.x2) / 2"
                  :y="Math.min(dep.y1, dep.y2) - 20"
                  text-anchor="middle"
                  fill="white"
                  font-size="12"
                  v-for="dep in dependencies.filter(d => d.id === hoveredDependency)"
                  :key="`text-${dep.id}`"
                >
                  {{ dep.conflict ? 'Conflict' : 'Dependency' }}
                </text>
              </g>
            </g>
            <defs>
              <marker
                id="arrowhead"
                markerWidth="10"
                markerHeight="10"
                refX="9"
                refY="3"
                orient="auto"
              >
                <polygon points="0 0, 10 3, 0 6" fill="gray" />
              </marker>
            </defs>
          </svg>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import GanttTaskBar from './GanttTaskBar.vue'

const props = defineProps({
  project: Object,
  tasks: Array,
  sections: Array,
})

const emit = defineEmits(['select-task', 'task-updated'])

const zoomLevel = ref('Month')
const currentDate = ref(new Date())
const groupingField = ref('section')
const collapsedGroups = ref(new Set())
const cellWidth = ref(40)
const draggingTask = ref(null)
const hoveredDependency = ref(null)

const zoomLevels = ['Year', 'Quarter', 'Month', 'Week', 'Day']

// Calculate date range based on zoom level
const dateRange = computed(() => {
  const dates = []
  const start = new Date(currentDate.value)

  switch (zoomLevel.value) {
    case 'Year':
      start.setMonth(0)
      start.setDate(1)
      for (let i = 0; i < 365; i++) {
        dates.push(new Date(start).toISOString().split('T')[0])
        start.setDate(start.getDate() + 1)
      }
      cellWidth.value = 2
      break
    case 'Quarter':
      start.setDate(1)
      const quarter = Math.floor(start.getMonth() / 3)
      start.setMonth(quarter * 3)
      for (let i = 0; i < 92; i++) {
        dates.push(new Date(start).toISOString().split('T')[0])
        start.setDate(start.getDate() + 1)
      }
      cellWidth.value = 8
      break
    case 'Month':
      start.setDate(1)
      const end = new Date(start)
      end.setMonth(end.getMonth() + 1)
      for (let d = new Date(start); d < end; d.setDate(d.getDate() + 1)) {
        dates.push(new Date(d).toISOString().split('T')[0])
      }
      cellWidth.value = 40
      break
    case 'Week':
      start.setDate(start.getDate() - start.getDay())
      for (let i = 0; i < 84; i++) {
        dates.push(new Date(start).toISOString().split('T')[0])
        start.setDate(start.getDate() + 1)
      }
      cellWidth.value = 30
      break
    case 'Day':
      for (let i = 0; i < 60; i++) {
        dates.push(new Date(start).toISOString().split('T')[0])
        start.setDate(start.getDate() + 1)
      }
      cellWidth.value = 60
      break
  }

  return dates
})

// Period headers (months, quarters, etc.)
const periodHeaders = computed(() => {
  const headers = []
  let currentPeriod = null
  let periodStart = 0

  dateRange.value.forEach((date, idx) => {
    const d = new Date(date)
    let period

    switch (zoomLevel.value) {
      case 'Year':
        period = d.getFullYear()
        break
      case 'Quarter':
        period = `Q${Math.floor(d.getMonth() / 3) + 1} ${d.getFullYear()}`
        break
      case 'Month':
        period = d.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
        break
      case 'Week':
        const weekStart = new Date(d)
        weekStart.setDate(d.getDate() - d.getDay())
        period = `W${Math.ceil((d.getDate() - d.getDay() + 1) / 7)}`
        break
      case 'Day':
        period = d.toLocaleDateString('en-US', { month: 'short' })
        break
    }

    if (period !== currentPeriod) {
      if (currentPeriod !== null) {
        headers.push({
          label: currentPeriod,
          width: (idx - periodStart) * cellWidth.value,
        })
      }
      currentPeriod = period
      periodStart = idx
    }
  })

  if (currentPeriod !== null) {
    headers.push({
      label: currentPeriod,
      width: (dateRange.value.length - periodStart) * cellWidth.value,
    })
  }

  return headers
})

// Group tasks
const groupedTasks = computed(() => {
  const groups = {}

  props.tasks?.forEach((task) => {
    let groupKey

    switch (groupingField.value) {
      case 'section':
        groupKey = task.section?.name || 'No Section'
        break
      case 'assignee':
        groupKey = task.assignee?.name || 'Unassigned'
        break
      case 'priority':
        groupKey = task.priority || 'No Priority'
        break
    }

    if (!groups[groupKey]) {
      groups[groupKey] = []
    }
    groups[groupKey].push(task)
  })

  return Object.entries(groups).map(([name, tasks]) => ({
    name,
    tasks,
  }))
})

// Today position
const todayPosition = computed(() => {
  const today = new Date().toISOString().split('T')[0]
  const idx = dateRange.value.indexOf(today)
  return idx >= 0 ? idx * cellWidth.value : null
})

// Dependencies
const dependencies = computed(() => {
  const deps = []
  const taskPositions = new Map()

  // Calculate position of each task
  groupedTasks.value.forEach((group, groupIdx) => {
    group.tasks.forEach((task, taskIdx) => {
      const rowIndex = groupIdx * 100 + taskIdx // Rough row calculation
      taskPositions.set(task.id, {
        x: barPositionX(task),
        y: rowIndex * 40 + 20,
        endX: barPositionX(task) + barWidth(task),
      })
    })
  })

  // Get all dependencies for tasks in this project
  props.tasks?.forEach((task) => {
    if (task.dependencies && Array.isArray(task.dependencies)) {
      task.dependencies.forEach((depTask) => {
        const fromPos = taskPositions.get(task.id)
        const toPos = taskPositions.get(depTask.id)

        if (fromPos && toPos) {
          const hasConflict =
            depTask.due_date && task.start_date && depTask.due_date > task.start_date

          deps.push({
            id: `${task.id}-${depTask.id}`,
            x1: fromPos.endX,
            y1: fromPos.y,
            x2: toPos.x,
            y2: toPos.y,
            conflict: hasConflict,
          })
        }
      })
    }
  })

  return deps
})

// Helper to calculate bar position X
const barPositionX = (task) => {
  if (!task.start_date) return 0
  const startIdx = dateRange.value.indexOf(task.start_date)
  return startIdx >= 0 ? startIdx * cellWidth.value : 0
}

// Helper to calculate bar width
const barWidth = (task) => {
  if (!task.start_date || !task.due_date) return cellWidth.value
  const startIdx = dateRange.value.indexOf(task.start_date)
  const endIdx = dateRange.value.indexOf(task.due_date)
  if (startIdx < 0 || endIdx < 0) return cellWidth.value
  return (endIdx - startIdx + 1) * cellWidth.value
}

const zoomIn = () => {
  const idx = zoomLevels.indexOf(zoomLevel.value)
  if (idx < zoomLevels.length - 1) {
    zoomLevel.value = zoomLevels[idx + 1]
  }
}

const zoomOut = () => {
  const idx = zoomLevels.indexOf(zoomLevel.value)
  if (idx > 0) {
    zoomLevel.value = zoomLevels[idx - 1]
  }
}

const goToToday = () => {
  currentDate.value = new Date()
}

const previousPeriod = () => {
  const d = new Date(currentDate.value)
  switch (zoomLevel.value) {
    case 'Year':
      d.setFullYear(d.getFullYear() - 1)
      break
    case 'Quarter':
      d.setMonth(d.getMonth() - 3)
      break
    case 'Month':
      d.setMonth(d.getMonth() - 1)
      break
    case 'Week':
      d.setDate(d.getDate() - 7)
      break
    case 'Day':
      d.setDate(d.getDate() - 30)
      break
  }
  currentDate.value = d
}

const nextPeriod = () => {
  const d = new Date(currentDate.value)
  switch (zoomLevel.value) {
    case 'Year':
      d.setFullYear(d.getFullYear() + 1)
      break
    case 'Quarter':
      d.setMonth(d.getMonth() + 3)
      break
    case 'Month':
      d.setMonth(d.getMonth() + 1)
      break
    case 'Week':
      d.setDate(d.getDate() + 7)
      break
    case 'Day':
      d.setDate(d.getDate() + 30)
      break
  }
  currentDate.value = d
}

const toggleGroup = (groupName) => {
  if (collapsedGroups.value.has(groupName)) {
    collapsedGroups.value.delete(groupName)
  } else {
    collapsedGroups.value.add(groupName)
  }
}

const formatDateHeader = (date) => {
  const d = new Date(date)
  switch (zoomLevel.value) {
    case 'Year':
      return d.toLocaleDateString('en-US', { month: 'short' })
    case 'Quarter':
      return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
    case 'Month':
      return d.toLocaleDateString('en-US', { day: 'numeric' })
    case 'Week':
      return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
    case 'Day':
      return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
  }
}

const getTaskColor = (task) => {
  if (task.priority === 'high') return '#ef4444'
  if (task.priority === 'medium') return '#f59e0b'
  if (task.priority === 'low') return '#10b981'
  return '#3b82f6'
}

const isTaskInRange = (task, date, idx) => {
  if (!task.start_date && !task.due_date) return false
  const start = task.start_date || task.due_date
  const end = task.due_date || task.start_date
  return date >= start && date <= end
}

const startDragBar = (task, event) => {
  draggingTask.value = task
}

const dragEdge = (task, event) => {
  // Update task dates based on drag
  const { mode, newDate } = event

  if (mode === 'move') {
    // Move entire task
    const daysDelta = calculateDaysDelta(task.start_date, newDate)
    const newStartDate = addDays(task.start_date, daysDelta)
    const newDueDate = addDays(task.due_date, daysDelta)

    emit('task-updated', {
      taskId: task.id,
      startDate: newStartDate,
      dueDate: newDueDate,
    })
  } else if (mode === 'left') {
    // Change start date
    emit('task-updated', {
      taskId: task.id,
      startDate: newDate,
      dueDate: task.due_date,
    })
  } else if (mode === 'right') {
    // Change end date
    emit('task-updated', {
      taskId: task.id,
      startDate: task.start_date,
      dueDate: newDate,
    })
  }
}

// Helper functions
const calculateDaysDelta = (fromDate, toDate) => {
  const from = new Date(fromDate)
  const to = new Date(toDate)
  return Math.floor((to - from) / (1000 * 60 * 60 * 24))
}

const addDays = (date, days) => {
  const d = new Date(date)
  d.setDate(d.getDate() + days)
  return d.toISOString().split('T')[0]
}
</script>

