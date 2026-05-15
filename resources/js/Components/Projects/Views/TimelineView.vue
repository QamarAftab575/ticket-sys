<template>
  <div class="flex flex-col h-full bg-white overflow-hidden">

    <!-- ── Top Controls ──────────────────────────────────────────────────── -->
    <div class="flex items-center justify-between gap-4 px-4 py-2 bg-white border-b flex-shrink-0">
      <div class="flex items-center gap-2">
        <button @click="shiftTimeline(-1)" class="p-1.5 rounded hover:bg-gray-100 transition-colors" :title="`Previous ${zoomLevel.toLowerCase()}`">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>
        <button @click="goToToday" class="px-3 py-1.5 text-sm font-medium border rounded hover:bg-gray-100 transition-colors">Today</button>
        <button @click="shiftTimeline(1)" class="p-1.5 rounded hover:bg-gray-100 transition-colors" :title="`Next ${zoomLevel.toLowerCase()}`">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
        <div class="flex items-center gap-1 border rounded ml-2">
          <button @click="zoomOut" class="px-2 py-1.5 hover:bg-gray-100 transition-colors" title="Zoom out" :disabled="zoomLevel === 'Month'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
            </svg>
          </button>
          <span class="px-2 text-sm font-medium border-x min-w-[70px] text-center">{{ zoomLevel }}</span>
          <button @click="zoomIn" class="px-2 py-1.5 hover:bg-gray-100 transition-colors" title="Zoom in" :disabled="zoomLevel === 'Day'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
          </button>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button
          v-if="noDateTasks.length"
          @click="showNoDatePanel = !showNoDatePanel"
          class="px-3 py-1.5 text-sm border rounded hover:bg-gray-100 transition-colors flex items-center gap-1.5"
          :class="showNoDatePanel ? 'bg-gray-100' : ''"
        >
          <span>No date</span>
          <span class="bg-gray-200 text-gray-700 text-xs font-semibold rounded-full px-1.5 py-0.5">{{ noDateTasks.length }}</span>
        </button>
        <div class="flex items-center gap-2">
          <label class="text-sm text-gray-600">Group by:</label>
          <select v-model="groupBy" class="px-2 py-1.5 text-sm border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="section">Section</option>
            <option value="assignee">Assignee</option>
            <!-- <option value="priority">Priority</option> -->
            <option value="none">None</option>
          </select>
        </div>
      </div>
    </div>

    <!-- ── Main Layout ────────────────────────────────────────────────────── -->
    <div class="flex flex-1 overflow-hidden">

      <!-- ── LEFT SIDEBAR: simple flat task list, fully independent ──────── -->
      <div class="w-64 border-r bg-white flex flex-col flex-shrink-0 z-30">
        <!-- Header spacer matches the two-row date header height -->
        <div class="h-16 border-b bg-gray-50 flex items-end px-4 pb-2 flex-shrink-0">
          <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Task name</span>
        </div>

        <!-- Scrollable list aligned with timeline lanes -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden">
          <template v-for="group in packedGroups" :key="group.id">
            <!-- Section header -->
            <div
              class="flex items-center px-3 bg-gray-50 border-b font-medium text-sm cursor-pointer hover:bg-gray-100 sticky top-0 z-10 group/header"
              :style="{ height: ROW_HEIGHT_GROUP + 'px' }"
              @click="toggleGroup(group.id)"
            >
              <svg
                :class="collapsedGroups.has(group.id) ? '' : 'rotate-90'"
                class="w-3.5 h-3.5 mr-2 flex-shrink-0 transition-transform text-gray-500"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
              <span class="truncate">{{ group.name }}</span>
              <span class="ml-auto text-xs text-gray-400 flex-shrink-0 mr-1">{{ group.tasks.length }}</span>
              <button
                class="opacity-0 group-hover/header:opacity-100 transition-opacity text-blue-500 hover:text-blue-700 flex-shrink-0 p-0.5 rounded"
                title="Add task"
                @click.stop="handleAddTaskClick(group)"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
              </button>
            </div>

            <!-- Lanes aligned with timeline - one row per lane -->
            <template v-if="!collapsedGroups.has(group.id)">
              <!-- Existing lanes with tasks -->
              <div
                v-for="(lane, laneIdx) in group.lanes"
                :key="`${group.id}-lane-${laneIdx}`"
                class="flex items-center px-3 border-b border-gray-100 hover:bg-gray-50 transition-colors flex-shrink-0"
                :style="{ height: ROW_HEIGHT_LANE + 'px' }"
              >
                <!-- Show all tasks in this lane -->
                <div class="flex items-center gap-2 flex-1 min-w-0">
                  <template v-for="(task, taskIdx) in lane" :key="task.id">
                    <div
                      v-if="taskIdx === 0"
                      class="flex items-center gap-2 flex-1 min-w-0 cursor-pointer group/row"
                      @click="$emit('select-task', task)"
                    >
                      <button
                        class="flex-shrink-0 w-4 h-4 rounded-full border flex items-center justify-center transition-colors opacity-0 group-hover/row:opacity-100"
                        :class="task.status === 'complete' ? 'bg-green-500 border-green-500 !opacity-100' : 'border-gray-300 hover:border-green-400'"
                        :title="task.status === 'complete' ? 'Mark incomplete' : 'Mark complete'"
                        @click.stop="$emit('toggle-complete', task.id)"
                      >
                        <svg v-if="task.status === 'complete'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                        </svg>
                      </button>
                      <span
                        class="text-sm flex-1 truncate"
                        :class="[
                          task.status === 'complete' ? 'line-through text-gray-400 opacity-60' : 'text-gray-800',
                        ]"
                      >{{ task.name }}</span>
                      <Avatar v-if="task.assignee" :name="task.assignee.name" :src="task.assignee.avatar" size="xs" class="flex-shrink-0"/>
                    </div>
                    <!-- Additional tasks in same lane shown as count badge -->
                    <span
                      v-if="taskIdx === 1 && lane.length > 1"
                      class="text-xs text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded flex-shrink-0"
                      :title="lane.slice(1).map(t => t.name).join(', ')"
                    >+{{ lane.length - 1 }}</span>
                  </template>
                </div>
              </div>
              
              <!-- Empty row at bottom for visual alignment with timeline -->
              <div
                :key="`${group.id}-lane-empty`"
                class="flex items-center px-3 border-b border-gray-100 hover:bg-blue-50/30 transition-colors flex-shrink-0"
                :style="{ height: ROW_HEIGHT_LANE + 'px' }"
              >
                <span class="text-xs text-gray-400 italic">Click timeline to add task →</span>
              </div>
            </template>
          </template>
        </div>
      </div>

      <!-- ── RIGHT TIMELINE: independent lane-packed scheduler ───────────── -->
      <div class="flex-1 flex flex-col overflow-hidden relative">

        <!-- Empty state when no dated tasks -->
        <div v-if="datedTasks.length === 0" 
          class="flex-1 flex flex-col items-center justify-center py-20 px-6 text-center bg-gray-50">
          <svg class="w-20 h-20 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <h3 class="text-lg font-semibold text-gray-700 mb-2">No tasks with dates</h3>
          <p class="text-gray-500 mb-6 max-w-md">
            Add start and due dates to your tasks to visualize them on the timeline.
          </p>
          <div class="flex gap-3">
            <button 
              v-if="noDateTasks.length > 0"
              @click="showNoDatePanel = true"
              class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              View {{ noDateTasks.length }} tasks without dates
            </button>
          </div>
        </div>

        <!-- Timeline content (when tasks exist) -->
        <template v-else>
        <!-- Two-row sticky date header -->
        <div class="flex-shrink-0 bg-white z-20 border-b overflow-hidden">
          <div class="h-8 border-b bg-gray-50 overflow-hidden">
            <div class="flex h-full" :style="{ width: totalWidth + 'px', transform: `translateX(-${headerScrollLeft}px)` }">
              <div
                v-for="month in visibleMonths"
                :key="month.key"
                class="border-r text-center text-xs font-semibold flex items-center justify-center text-gray-700 flex-shrink-0 px-1"
                :style="{ width: month.width + 'px' }"
              ><span class="truncate">{{ month.label }}</span></div>
            </div>
          </div>
          <div class="h-8 bg-white overflow-hidden">
            <div class="flex h-full" :style="{ width: totalWidth + 'px', transform: `translateX(-${headerScrollLeft}px)` }">
              <div
                v-for="(col, idx) in visibleDates"
                :key="col.key"
                class="border-r text-center text-xs flex items-center justify-center flex-shrink-0 transition-colors"
                :class="[
                  creatingRange && idx >= creatingRange.start && idx <= creatingRange.end
                    ? 'bg-blue-100 text-blue-600 font-semibold'
                    : col.isToday ? 'bg-blue-500 text-white font-bold'
                    : col.isWeekend ? 'bg-gray-50 text-gray-400' : 'text-gray-600'
                ]"
                :style="{ width: CELL_WIDTH + 'px' }"
              >{{ col.label }}</div>
            </div>
          </div>
        </div>

        <!-- Scrollable grid — independent scroll, height driven by lanes only -->
        <div 
          class="flex-1" 
          :class="zoomLevel === 'Day' ? 'overflow-y-auto overflow-x-hidden' : 'overflow-auto'"
          ref="gridRef" 
          @scroll="onGridScroll"
        >
          <div
            class="relative"
            :style="{
              width: totalWidth + 'px',
              minHeight: gridHeight + 'px',
              backgroundImage: columnGridCSS,
              backgroundSize: CELL_WIDTH + 'px 100%',
              backgroundRepeat: 'repeat-x',
            }"
          >
            <!-- Today highlight (when visible) -->
            <div v-if="todayInfo.visible" class="absolute top-0 bottom-0 pointer-events-none z-10"
              :style="{ left: todayInfo.offset + 'px', width: CELL_WIDTH + 'px', backgroundColor: 'rgba(59,130,246,0.06)' }"></div>
            <div v-if="todayInfo.visible" class="absolute top-0 bottom-0 pointer-events-none z-10"
              :style="{ left: todayInfo.offset + Math.floor(CELL_WIDTH / 2) + 'px', width: '2px', backgroundColor: 'rgba(59,130,246,0.5)' }"></div>

            <!-- Off-screen today indicators -->
            <div v-if="!todayInfo.visible && todayInfo.isInPast" 
              class="absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-blue-100 to-transparent pointer-events-none z-10 flex items-center justify-start pl-2"
              title="Today is in the past (scroll left)">
              <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
            </div>

            <div v-if="!todayInfo.visible && todayInfo.isInFuture" 
              class="absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-blue-100 to-transparent pointer-events-none z-10 flex items-center justify-end pr-2"
              title="Today is in the future (scroll right)">
              <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
              </svg>
            </div>

            <!-- Groups: header row + one row per lane + empty lane for new tasks -->
            <template v-for="group in packedGroups" :key="group.id">
              <div class="bg-gray-50/80" :style="{ height: ROW_HEIGHT_GROUP + 'px' }"></div>

              <template v-if="!collapsedGroups.has(group.id)">
                <!-- Existing lanes with tasks -->
                <div
                  v-for="(lane, laneIdx) in group.lanes"
                  :key="`${group.id}-lane-${laneIdx}`"
                  class="relative"
                  :style="{ height: ROW_HEIGHT_LANE + 'px', cursor: 'cell' }"
                  @click="handleLaneClick($event, group, laneIdx)"
                >
                  <div v-for="task in lane" :key="task.id" data-task-bar>
                    <TimelineTaskBar
                      :key="task.id"
                      :task="task"
                      :start-date="timelineStart"
                      :cell-width="CELL_WIDTH"
                      :zoom-level="zoomLevel"
                      @update="handleTaskUpdate"
                      @click="$emit('select-task', task)"
                    />
                  </div>
                </div>
                
                <!-- Empty lane at bottom for creating new tasks -->
                <div
                  :key="`${group.id}-lane-empty`"
                  class="relative hover:bg-blue-50/30 transition-colors"
                  :style="{ height: ROW_HEIGHT_LANE + 'px', cursor: 'cell' }"
                  @click="handleLaneClick($event, group, group.lanes.length)"
                ></div>
              </template>
            </template>

            <!-- Inline creation overlay -->
            <template v-if="creatingTask && creatingOverlayStyle">
              <div
                class="absolute pointer-events-none z-20 rounded"
                :style="{
                  top: creatingRowTop + 4 + 'px',
                  height: ROW_HEIGHT_LANE - 8 + 'px',
                  left: creatingOverlayStyle.left,
                  width: creatingOverlayStyle.width,
                  background: 'rgba(66,133,244,0.08)',
                  border: '1.5px solid rgba(66,133,244,0.35)',
                }"
              ></div>
              <div class="absolute z-30" :style="{ top: creatingRowTop + 6 + 'px', left: creatingOverlayStyle.left, width: creatingOverlayStyle.width }">
                <input
                  ref="createInputRef"
                  v-model="createInputValue"
                  type="text"
                  placeholder="Write a task name"
                  class="w-full h-9 px-2.5 bg-white rounded outline-none"
                  style="border: 1.5px solid #4a90e2; box-shadow: 0 2px 8px rgba(0,0,0,0.15); font-size: 13px;"
                  @keydown.enter.prevent="confirmCreating"
                  @keydown.esc.prevent="cancelCreating"
                  @blur="onInputBlur"
                />
              </div>
            </template>
          </div>
        </div>
        </template>
      </div>

      <!-- No-date side panel -->
      <transition name="slide-panel">
        <div v-if="showNoDatePanel && noDateTasks.length" class="w-64 border-l bg-white flex flex-col flex-shrink-0 z-20">
          <div class="h-16 border-b bg-gray-50 flex items-end px-4 pb-2 justify-between flex-shrink-0">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">No date ({{ noDateTasks.length }})</span>
            <button @click="showNoDatePanel = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="flex-1 overflow-y-auto">
            <div
              v-for="task in noDateTasks"
              :key="task.id"
              class="h-10 flex items-center px-3 border-b hover:bg-blue-50 cursor-pointer"
              @click="$emit('select-task', task)"
            >
              <span class="text-sm truncate">{{ task.name }}</span>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, onUnmounted, watch } from 'vue'
import TimelineTaskBar from './TimelineTaskBar.vue'
import Avatar from '@/Components/Avatar.vue'

const props = defineProps({
  project: Object,
  tasks: { type: Array, default: () => [] },
  sections: { type: Array, default: () => [] },
  isLoading: { type: Boolean, default: false }
})

const emit = defineEmits(['select-task', 'update-task', 'create-task', 'toggle-complete'])

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
  const diff = Math.round((d2 - d1) / 86400000)
  return diff + 1 // Inclusive
}

/**
 * Add days to a date
 */
const addDays = (date, days) => {
  const d = normalizeDate(date)
  if (!d) return null
  d.setDate(d.getDate() + days)
  return dateToISO(d)
}

// ─── Constants ────────────────────────────────────────────────────────────────
const ROW_HEIGHT_GROUP = 40  // section header row height
const ROW_HEIGHT_LANE = 40   // timeline lane row height

// Zoom configuration
const ZOOM_CONFIG = {
  Day: {
    cellWidth: null, // Will be calculated dynamically to fit viewport
    visibleDays: 1,
    shiftAmount: 1, // Navigate by 1 day
    dateLabel: (date) => date.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }),
    monthLabel: (date) => date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
  },
  Week: {
    cellWidth: 140, // ~980px total for 7 days (fits most screens)
    visibleDays: 7,
    shiftAmount: 7, // Navigate by 1 week
    dateLabel: (date) => date.toLocaleDateString('en-US', { weekday: 'short', day: 'numeric' }),
    monthLabel: (date) => date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
  },
  Month: {
    cellWidth: 40, // 12-18 columns visible (480-720px viewport)
    visibleDays: 90, // 3 months
    shiftAmount: 30, // Navigate by ~1 month
    dateLabel: (date) => String(date.getDate()),
    monthLabel: (date) => date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
  }
}

const ZOOM_LEVELS = ['Month', 'Week', 'Day'] // Order: least to most detail

// ─── Helper Functions ─────────────────────────────────────────────────────────
const initTimelineStart = () => {
  const d = new Date()
  // For Month view (default), start 1 month back to show prev + current + next
  d.setDate(1)
  d.setMonth(d.getMonth() - 1)
  return normalizeDate(d)
}

// Get start of week (Monday)
const getWeekStart = (date) => {
  const d = normalizeDate(date)
  const day = d.getDay()
  const diff = day === 0 ? -6 : 1 - day // If Sunday (0), go back 6 days; otherwise go to Monday
  d.setDate(d.getDate() + diff)
  return d
}

// ─── State ────────────────────────────────────────────────────────────────────
const zoomLevel = ref('Month') // Default to Month view
const groupBy = ref('section')
const collapsedGroups = ref(new Set())
const showNoDatePanel = ref(false)
const timelineStart = ref(initTimelineStart())
const headerScrollLeft = ref(0)
const gridRef = ref(null)

// ─── Computed Cell Width ──────────────────────────────────────────────────────
const CELL_WIDTH = computed(() => {
  const config = ZOOM_CONFIG[zoomLevel.value]
  
  // For Day view, calculate width to fill viewport (no horizontal scroll)
  if (zoomLevel.value === 'Day' && gridRef.value) {
    const viewportWidth = gridRef.value.clientWidth || window.innerWidth - 256 // Subtract sidebar width
    return Math.max(viewportWidth, 600) // Minimum 600px for task titles
  }
  
  return config?.cellWidth ?? 40 // Default to 40 if undefined
})

// ─── Zoom ─────────────────────────────────────────────────────────────────────
const zoomIn = () => {
  const i = ZOOM_LEVELS.indexOf(zoomLevel.value)
  if (i < ZOOM_LEVELS.length - 1) zoomLevel.value = ZOOM_LEVELS[i + 1]
}
const zoomOut = () => {
  const i = ZOOM_LEVELS.indexOf(zoomLevel.value)
  if (i > 0) zoomLevel.value = ZOOM_LEVELS[i - 1]
}

// ─── Visible dates ────────────────────────────────────────────────────────────
const visibleDates = computed(() => {
  const config = ZOOM_CONFIG[zoomLevel.value]
  const dates = []
  const cur = normalizeDate(timelineStart.value)
  const todayStr = dateToISO(new Date())
  
  for (let i = 0; i < config.visibleDays; i++) {
    const key = dateToISO(cur)
    dates.push({
      key,
      label: config.dateLabel(cur),
      isToday: key === todayStr,
      isWeekend: cur.getDay() === 0 || cur.getDay() === 6,
    })
    cur.setDate(cur.getDate() + 1)
  }
  return dates
})

// ─── Month spans (with memoization) ───────────────────────────────────────────
const monthHeadersCache = new Map()

const visibleMonths = computed(() => {
  const cacheKey = `${dateToISO(timelineStart.value)}-${zoomLevel.value}`
  if (monthHeadersCache.has(cacheKey)) {
    return monthHeadersCache.get(cacheKey)
  }

  const months = []
  let lastKey = null, count = 0, label = ''
  
  visibleDates.value.forEach((col, idx) => {
    const [year, month] = col.key.split('-')
    const key = `${year}-${month}`
    if (key !== lastKey) {
      if (lastKey !== null) {
        months.push({ key: lastKey, label, width: count * CELL_WIDTH.value })
      }
      lastKey = key
      count = 0
      // Create date using year and month (month is 1-indexed in the string, 0-indexed in Date)
      const monthDate = new Date(parseInt(year), parseInt(month) - 1, 1)
      label = monthDate.toLocaleDateString('en-US', { 
        month: 'long', 
        year: 'numeric' 
      })
    }
    count++
    if (idx === visibleDates.value.length - 1) {
      months.push({ key, label, width: count * CELL_WIDTH.value })
    }
  })

  monthHeadersCache.set(cacheKey, months)
  return months
})

const totalWidth = computed(() => visibleDates.value.length * CELL_WIDTH.value)

// ─── Today indicator with off-screen detection ────────────────────────────────
const todayInfo = computed(() => {
  const today = normalizeDate(new Date())
  const todayStr = dateToISO(today)
  const idx = visibleDates.value.findIndex(d => d.key === todayStr)
  
  if (idx >= 0) {
    return {
      visible: true,
      offset: idx * CELL_WIDTH.value,
      isInView: true,
      isInPast: false,
      isInFuture: false
    }
  }
  
  // Today is off-screen - determine direction
  const firstDate = normalizeDate(visibleDates.value[0]?.key)
  const lastDate = normalizeDate(visibleDates.value[visibleDates.value.length - 1]?.key)
  
  return {
    visible: false,
    offset: null,
    isInView: false,
    isInPast: today < firstDate,
    isInFuture: today > lastDate
  }
})

const columnGridCSS = computed(() =>
  `repeating-linear-gradient(to right, transparent 0px, transparent ${CELL_WIDTH.value - 1}px, #e5e7eb ${CELL_WIDTH.value - 1}px, #e5e7eb ${CELL_WIDTH.value}px)`
)

// ─── Task data ────────────────────────────────────────────────────────────────
const datedTasks = computed(() => (props.tasks ?? []).filter(t => t.start_date || t.due_date))
const noDateTasks = computed(() => (props.tasks ?? []).filter(t => !t.start_date && !t.due_date))

// ─── Visible tasks filter (only tasks in visible timeline range) ─────────────
const visibleDatedTasks = computed(() => {
  if (!visibleDates.value.length) return datedTasks.value
  
  const firstVisibleDate = visibleDates.value[0].key
  const lastVisibleDate = visibleDates.value[visibleDates.value.length - 1].key
  
  return datedTasks.value.filter(task => {
    const taskStart = task.start_date || task.due_date
    const taskEnd = task.due_date || task.start_date
    
    // Include task if it overlaps with visible range
    // Task is visible if: task_end >= first_visible AND task_start <= last_visible
    return taskEnd >= firstVisibleDate && taskStart <= lastVisibleDate
  })
})

// ─── LEFT SIDEBAR: flat groups, one row per task, fixed height ────────────────
// Shows only tasks visible in the current timeline range
const flatGroups = computed(() => {
  if (groupBy.value === 'none') {
    return [{ id: 'all', name: 'All Tasks', sectionId: null, tasks: visibleDatedTasks.value }]
  }
  const map = new Map()
  visibleDatedTasks.value.forEach(task => {
    let key, name, sectionId
    if (groupBy.value === 'section') {
      key = task.section?.id || 'no-section'
      name = task.section?.name || 'No Section'
      sectionId = task.section?.id ?? null
    } else if (groupBy.value === 'assignee') {
      key = task.assignee?.id || 'unassigned'
      name = task.assignee?.name || 'Unassigned'
      sectionId = null
    } 
   // else {
   //   key = task.priority || 'none'
    //  name = task.priority ? task.priority.charAt(0).toUpperCase() + task.priority.slice(1) : 'No Priority'
    //  sectionId = null
  //  }
    if (!map.has(key)) map.set(key, { id: key, name, sectionId, tasks: [] })
    map.get(key).tasks.push(task)
  })
  return Array.from(map.values())
})

// ─── RIGHT TIMELINE: lane-packed groups, independent heights ─────────────────
const laneCache = new Map()

const packIntoLanes = (tasks, groupKey) => {
  const fingerprint = tasks.map(t => `${t.id}:${t.start_date}:${t.due_date}`).sort().join('|')
  const cached = laneCache.get(groupKey)
  if (cached && cached.fingerprint === fingerprint) return cached.lanes

  const sorted = [...tasks].sort((a, b) => {
    const aS = a.start_date || a.due_date
    const bS = b.start_date || b.due_date
    if (!aS) return 1
    if (!bS) return -1
    return aS < bS ? -1 : aS > bS ? 1 : 0
  })

  const laneEnds = []
  const laneTasks = []
  
  for (const task of sorted) {
    const tStart = task.start_date || task.due_date
    const tEnd = task.due_date || task.start_date
    
    if (!tStart || !tEnd) continue // Skip tasks without dates
    
    let placed = false
    for (let i = 0; i < laneEnds.length; i++) {
      // Task can be placed in this lane if previous task ends BEFORE this one starts
      // Using < instead of <= ensures no visual overlap even if dates touch
      if (laneEnds[i] < tStart) {
        laneTasks[i].push(task)
        laneEnds[i] = tEnd
        placed = true
        break
      }
    }
    
    if (!placed) {
      // No available lane found, create a new one
      laneTasks.push([task])
      laneEnds.push(tEnd)
    }
  }

  laneCache.set(groupKey, { fingerprint, lanes: laneTasks })
  return laneTasks
}

const packedGroups = computed(() => {
  return flatGroups.value.map(g => ({ ...g, lanes: packIntoLanes(g.tasks, g.id) }))
})

// ─── Grid height (timeline only — sidebar is independent) ─────────────────────
const gridHeight = computed(() => {
  let h = 0
  packedGroups.value.forEach(group => {
    h += ROW_HEIGHT_GROUP
    if (!collapsedGroups.value.has(group.id)) {
      // Add height for existing lanes + 1 empty lane at bottom
      h += (group.lanes.length + 1) * ROW_HEIGHT_LANE
    }
  })
  return Math.max(h, 200)
})

// ─── Inline task creation ─────────────────────────────────────────────────────
const creatingTask = ref(null)
const createInputRef = ref(null)
const createInputValue = ref('')
let blurSuppressed = false

const creatingRange = computed(() => {
  if (!creatingTask.value) return null
  const start = creatingTask.value.dayIndex
  const end = Math.min(start + 3, visibleDates.value.length - 1)
  return { start, end }
})

const creatingOverlayStyle = computed(() => {
  if (!creatingTask.value || !creatingRange.value) return null
  const { start, end } = creatingRange.value
  return {
    left: start * CELL_WIDTH.value + 'px',
    width: (end - start + 1) * CELL_WIDTH.value - 2 + 'px',
  }
})

const creatingRowTop = computed(() => {
  if (!creatingTask.value) return 0
  const { groupId, laneIndex } = creatingTask.value
  let top = 0
  for (const group of packedGroups.value) {
    top += ROW_HEIGHT_GROUP
    if (group.id === groupId) {
      top += laneIndex * ROW_HEIGHT_LANE
      return top
    }
    if (!collapsedGroups.value.has(group.id)) {
      top += group.lanes.length * ROW_HEIGHT_LANE
    }
  }
  return top
})

const cancelCreating = () => {
  if (blurSuppressed) return
  creatingTask.value = null
  createInputValue.value = ''
}

const onInputBlur = () => {
  if (blurSuppressed) return
  window.setTimeout(() => { if (!blurSuppressed) cancelCreating() }, 80)
}

const confirmCreating = () => {
  const name = createInputValue.value.trim()
  if (!name || !creatingTask.value) return cancelCreating()
  const { dayIndex, sectionId } = creatingTask.value
  const endIdx = Math.min(dayIndex + 3, visibleDates.value.length - 1)
  emit('create-task', {
    name,
    section_id: sectionId,
    start_date: visibleDates.value[dayIndex].key,
    due_date: visibleDates.value[endIdx].key,
  })
  cancelCreating()
}

const handleLaneClick = (event, group, laneIndex) => {
  if (event.target.closest('[data-task-bar]')) return
  if (creatingTask.value) {
    blurSuppressed = true
    creatingTask.value = null
    createInputValue.value = ''
    blurSuppressed = false
    return
  }
  const gridEl = gridRef.value
  if (!gridEl) return
  const rect = gridEl.getBoundingClientRect()
  const clickX = event.clientX - rect.left + gridEl.scrollLeft
  const dayIndex = Math.max(0, Math.min(Math.floor(clickX / CELL_WIDTH.value), visibleDates.value.length - 1))
  blurSuppressed = true
  creatingTask.value = { groupId: group.id, sectionId: group.sectionId ?? null, laneIndex, dayIndex }
  createInputValue.value = ''
  nextTick(() => { blurSuppressed = false; createInputRef.value?.focus() })
}

const handleAddTaskClick = (group) => {
  if (creatingTask.value) {
    blurSuppressed = true
    creatingTask.value = null
    createInputValue.value = ''
    blurSuppressed = false
    return
  }
  const todayIdx = visibleDates.value.findIndex(d => d.isToday)
  const dayIndex = todayIdx >= 0 ? todayIdx : 0
  // Find the matching packed group for lane count
  const packed = packedGroups.value.find(g => g.id === group.id)
  const laneIndex = Math.max(0, (packed?.lanes.length ?? 1) - 1)
  blurSuppressed = true
  creatingTask.value = { groupId: group.id, sectionId: group.sectionId ?? null, laneIndex, dayIndex }
  createInputValue.value = ''
  nextTick(() => {
    blurSuppressed = false
    createInputRef.value?.focus()
    if (gridRef.value) gridRef.value.scrollLeft = Math.max(0, dayIndex * CELL_WIDTH.value - gridRef.value.clientWidth / 2)
  })
}

// ─── Grid scroll (header sync only — sidebar is independent) ─────────────────
const onGridScroll = () => {
  headerScrollLeft.value = gridRef.value?.scrollLeft ?? 0
}

// ─── Navigation (zoom-aware) ──────────────────────────────────────────────────
const shiftTimeline = (direction) => {
  const config = ZOOM_CONFIG[zoomLevel.value]
  const d = normalizeDate(timelineStart.value)
  
  if (zoomLevel.value === 'Week') {
    // For week view, shift by weeks and align to Monday
    d.setDate(d.getDate() + (direction * 7))
    timelineStart.value = getWeekStart(d)
  } else if (zoomLevel.value === 'Month') {
    // For month view, shift by months
    d.setMonth(d.getMonth() + direction)
    d.setDate(1) // Start at first of month
    timelineStart.value = d
  } else {
    // For day view, shift by days
    d.setDate(d.getDate() + direction)
    timelineStart.value = d
  }
}

const goToToday = () => {
  const today = normalizeDate(new Date())
  const todayStr = dateToISO(today)
  
  // Reset timeline start based on zoom level
  if (zoomLevel.value === 'Day') {
    // Show today
    timelineStart.value = today
  } else if (zoomLevel.value === 'Week') {
    // Show week containing today (starting Monday)
    timelineStart.value = getWeekStart(today)
  } else {
    // Month view: show prev + current + next month
    const d = normalizeDate(today)
    d.setDate(1)
    d.setMonth(d.getMonth() - 1)
    timelineStart.value = d
  }
  
  // Scroll to today after re-render
  nextTick(() => {
    const idx = visibleDates.value.findIndex(d => d.key === todayStr)
    if (idx >= 0 && gridRef.value) {
      gridRef.value.scrollLeft = Math.max(0, idx * CELL_WIDTH.value - gridRef.value.clientWidth / 2 + CELL_WIDTH.value / 2)
    }
  })
}

const toggleGroup = (id) => {
  const s = new Set(collapsedGroups.value)
  s.has(id) ? s.delete(id) : s.add(id)
  collapsedGroups.value = s
}

const handleTaskUpdate = (update) => emit('update-task', update)

// ─── Lifecycle & Cleanup ──────────────────────────────────────────────────────
// Trigger for cell width recalculation in Day view
const viewportWidth = ref(0)

// Clear cache when zoom changes
watch(zoomLevel, (newZoom, oldZoom) => {
  monthHeadersCache.clear()
  
  // Adjust timeline start when zoom changes to maintain context
  const today = normalizeDate(new Date())
  
  if (newZoom === 'Day') {
    // Switch to day view: show today
    timelineStart.value = today
  } else if (newZoom === 'Week') {
    // Switch to week view: show week containing today
    timelineStart.value = getWeekStart(today)
  } else if (newZoom === 'Month') {
    // Switch to month view: show prev + current + next month
    const d = normalizeDate(today)
    d.setDate(1)
    d.setMonth(d.getMonth() - 1)
    timelineStart.value = d
  }
  
  // Scroll to today after zoom change
  nextTick(() => {
    const todayStr = dateToISO(today)
    const idx = visibleDates.value.findIndex(d => d.key === todayStr)
    if (idx >= 0 && gridRef.value) {
      gridRef.value.scrollLeft = Math.max(0, idx * CELL_WIDTH.value - gridRef.value.clientWidth / 2 + CELL_WIDTH.value / 2)
    }
  })
})

// Resize observer for Day view
let resizeObserver = null

onMounted(() => {
  nextTick(() => goToToday())
  
  // Watch for viewport resize in Day view
  if (gridRef.value) {
    resizeObserver = new ResizeObserver(() => {
      if (zoomLevel.value === 'Day') {
        viewportWidth.value = gridRef.value?.clientWidth || 0
      }
    })
    resizeObserver.observe(gridRef.value)
  }
})

onUnmounted(() => {
  monthHeadersCache.clear()
  laneCache.clear()
  if (resizeObserver) {
    resizeObserver.disconnect()
  }
})
</script>

<style scoped>
.slide-panel-enter-active,
.slide-panel-leave-active {
  transition: width 0.2s ease, opacity 0.2s ease;
  overflow: hidden;
}
.slide-panel-enter-from,
.slide-panel-leave-to {
  width: 0;
  opacity: 0;
}
</style>
