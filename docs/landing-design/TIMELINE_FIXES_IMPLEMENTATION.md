# Timeline View - Implementation Plan & Fixes

This document provides step-by-step implementation guidance for fixing the Timeline View issues identified in the audit.

---

## Phase 1: Critical Fixes (Priority: URGENT)

### Fix 1: Standardize Date Handling

**File:** `resources/js/Components/Projects/Views/TimelineView.vue`

**Problem:** Inconsistent date parsing causes timezone issues and off-by-one errors.

**Solution:** Add utility functions and use consistently throughout.

```javascript
// Add after imports, before component setup
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
```

**Then update:**
```javascript
// Replace parseLocalDate usage
const initTimelineStart = () => {
  const d = new Date()
  d.setDate(1)
  d.setMonth(d.getMonth() - 3)
  return normalizeDate(d) // Use normalizeDate
}

// Update visibleDates
const visibleDates = computed(() => {
  const dates = []
  const cur = normalizeDate(timelineStart.value) // Normalize
  const todayStr = dateToISO(new Date())
  
  for (let i = 0; i < 365; i++) {
    const key = dateToISO(cur)
    dates.push({
      key,
      label: cur.getDate(),
      isToday: key === todayStr,
      isWeekend: cur.getDay() === 0 || cur.getDay() === 6,
    })
    cur.setDate(cur.getDate() + 1)
  }
  return dates
})
```

---

### Fix 2: Correct Zoom Levels & Configuration

**File:** `resources/js/Components/Projects/Views/TimelineView.vue`

**Problem:** Zoom levels in wrong order, no configuration for cell widths.

**Solution:**

```javascript
// Replace ZOOM_LEVELS constant
const ZOOM_CONFIG = {
  Months: {
    cellWidth: 30,
    visibleDays: 180,
    shiftAmount: 90, // 3 months
    dateLabel: (date) => date.getDate(),
    monthLabel: (date) => date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
  },
  Weeks: {
    cellWidth: 40,
    visibleDays: 90,
    shiftAmount: 30, // ~1 month
    dateLabel: (date) => date.getDate(),
    monthLabel: (date) => date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
  },
  Days: {
    cellWidth: 60,
    visibleDays: 60,
    shiftAmount: 14, // 2 weeks
    dateLabel: (date) => date.getDate(),
    monthLabel: (date) => date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
  }
}

const ZOOM_LEVELS = ['Months', 'Weeks', 'Days'] // Correct order: least to most detail

// Update CELL_WIDTH to be reactive
const CELL_WIDTH = computed(() => ZOOM_CONFIG[zoomLevel.value].cellWidth)

// Update visibleDates to use config
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

// Update totalWidth
const totalWidth = computed(() => visibleDates.value.length * CELL_WIDTH.value)
```

---

### Fix 3: Zoom-Aware Navigation

**File:** `resources/js/Components/Projects/Views/TimelineView.vue`

**Problem:** Navigation always shifts by months regardless of zoom level.

**Solution:**

```javascript
// Replace shiftMonths function
const shiftTimeline = (direction) => {
  const config = ZOOM_CONFIG[zoomLevel.value]
  const d = normalizeDate(timelineStart.value)
  d.setDate(d.getDate() + (direction * config.shiftAmount))
  timelineStart.value = d
}

// Update button handlers
// Replace @click="shiftMonths(-1)" with @click="shiftTimeline(-1)"
// Replace @click="shiftMonths(1)" with @click="shiftTimeline(1)"

// Improve goToToday
const goToToday = () => {
  const today = normalizeDate(new Date())
  const todayStr = dateToISO(today)
  
  // Check if today is in visible range
  const idx = visibleDates.value.findIndex(d => d.key === todayStr)
  
  if (idx >= 0) {
    // Today is visible, just scroll to it
    if (gridRef.value) {
      gridRef.value.scrollLeft = Math.max(0, idx * CELL_WIDTH.value - gridRef.value.clientWidth / 2 + CELL_WIDTH.value / 2)
    }
  } else {
    // Today is not visible, update timelineStart to center today
    const config = ZOOM_CONFIG[zoomLevel.value]
    const d = normalizeDate(today)
    d.setDate(d.getDate() - Math.floor(config.visibleDays / 2))
    timelineStart.value = d
    
    // Wait for re-render, then scroll to today
    nextTick(() => {
      const newIdx = visibleDates.value.findIndex(d => d.key === todayStr)
      if (newIdx >= 0 && gridRef.value) {
        gridRef.value.scrollLeft = Math.max(0, newIdx * CELL_WIDTH.value - gridRef.value.clientWidth / 2 + CELL_WIDTH.value / 2)
      }
    })
  }
}
```

---

### Fix 4: Lane Packing Overlap Bug

**File:** `resources/js/Components/Projects/Views/TimelineView.vue`

**Problem:** Tasks can overlap in same lane due to incorrect comparison.

**Solution:**

```javascript
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
      // FIX: Use <= instead of < to prevent overlaps
      if (laneEnds[i] <= tStart) { // Changed from <
        laneTasks[i].push(task)
        laneEnds[i] = tEnd
        placed = true
        break
      }
    }
    
    if (!placed) {
      laneTasks.push([task])
      laneEnds.push(tEnd)
    }
  }

  laneCache.set(groupKey, { fingerprint, lanes: laneTasks })
  return laneTasks
}

// Add cache cleanup on unmount
onUnmounted(() => {
  laneCache.clear()
})
```

---

### Fix 5: Update TimelineTaskBar Date Handling

**File:** `resources/js/Components/Projects/Views/TimelineTaskBar.vue`

**Problem:** Inconsistent date parsing, potential DST issues.

**Solution:**

```javascript
// Add utility functions at top of script
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

const dateToISO = (date) => {
  if (!date) return null
  const d = normalizeDate(date)
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const daysBetween = (start, end) => {
  const d1 = normalizeDate(start)
  const d2 = normalizeDate(end)
  if (!d1 || !d2) return 0
  return Math.round((d2 - d1) / 86400000) + 1 // Inclusive
}

// Replace parseLocalDate usage
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

// Update addDays helper
const addDays = (dateStr, days) => {
  const d = normalizeDate(dateStr)
  if (!d) return dateStr
  d.setDate(d.getDate() + days)
  return dateToISO(d)
}
```

---

### Fix 6: Improved Today Indicator

**File:** `resources/js/Components/Projects/Views/TimelineView.vue`

**Problem:** Today line disappears when navigating far from current date.

**Solution:**

```javascript
// Replace todayOffset computed
const todayInfo = computed(() => {
  const today = normalizeDate(new Date())
  const todayStr = dateToISO(today)
  const idx = visibleDates.value.findIndex(d => d.key === todayStr)
  
  if (idx >= 0) {
    return {
      visible: true,
      offset: idx * CELL_WIDTH.value,
      isInView: true
    }
  }
  
  // Today is off-screen - determine direction
  const firstDate = normalizeDate(visibleDates.value[0]?.key)
  const lastDate = normalizeDate(visibleDates.value[visibleDates.value.length - 1]?.key)
  
  return {
    visible: false,
    isInView: false,
    isInPast: today < firstDate,
    isInFuture: today > lastDate
  }
})

// Update template
// Replace todayOffset with todayInfo.offset
// Add indicator when today is off-screen:
```

```vue
<!-- Today highlight (when visible) -->
<div v-if="todayInfo.visible" class="absolute top-0 bottom-0 pointer-events-none z-10"
  :style="{ left: todayInfo.offset + 'px', width: CELL_WIDTH + 'px', backgroundColor: 'rgba(59,130,246,0.06)' }"></div>
<div v-if="todayInfo.visible" class="absolute top-0 bottom-0 pointer-events-none z-10"
  :style="{ left: todayInfo.offset + Math.floor(CELL_WIDTH / 2) + 'px', width: '2px', backgroundColor: 'rgba(59,130,246,0.5)' }"></div>

<!-- Off-screen indicator -->
<div v-if="!todayInfo.visible && todayInfo.isInPast" 
  class="absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-blue-100 to-transparent pointer-events-none z-10 flex items-center justify-start pl-2">
  <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
  </svg>
</div>

<div v-if="!todayInfo.visible && todayInfo.isInFuture" 
  class="absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-blue-100 to-transparent pointer-events-none z-10 flex items-center justify-end pr-2">
  <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
  </svg>
</div>
```

---

### Fix 7: Empty State Handling

**File:** `resources/js/Components/Projects/Views/TimelineView.vue`

**Problem:** No guidance when timeline is empty.

**Solution:**

```vue
<!-- Add after main timeline container, before no-date panel -->
<div v-if="datedTasks.length === 0 && !isLoading" 
  class="flex flex-col items-center justify-center py-20 px-6 text-center">
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
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      View {{ noDateTasks.length }} tasks without dates
    </button>
    <button 
      @click="$emit('select-task', null)"
      class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition"
    >
      Go to List View
    </button>
  </div>
</div>
```

---

## Phase 2: Architecture Improvements

### Fix 8: Resolve Sidebar/Timeline Alignment

**Decision Required:** Choose between two approaches:

#### Option A: Lane-Based Sidebar (Recommended)

This matches Asana's behavior - sidebar shows lanes, not individual tasks.

```vue
<!-- Replace left sidebar in TimelineView.vue -->
<div class="w-64 border-r bg-white flex flex-col flex-shrink-0 z-30">
  <div class="h-16 border-b bg-gray-50 flex items-end px-4 pb-2 flex-shrink-0">
    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Timeline</span>
  </div>

  <div class="flex-1 overflow-y-auto overflow-x-hidden">
    <template v-for="group in packedGroups" :key="group.id">
      <!-- Section header -->
      <div
        class="h-10 flex items-center px-3 bg-gray-50 border-b font-medium text-sm cursor-pointer hover:bg-gray-100 sticky top-0 z-10"
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
        <span class="ml-auto text-xs text-gray-400">{{ group.tasks.length }} tasks</span>
      </div>

      <!-- Lane rows (aligned with timeline) -->
      <template v-if="!collapsedGroups.has(group.id)">
        <div
          v-for="(lane, laneIdx) in group.lanes"
          :key="`${group.id}-lane-${laneIdx}`"
          class="h-10 flex items-center px-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer"
          @click="showLaneTasksPopover(group, laneIdx)"
        >
          <span class="text-sm text-gray-600">Lane {{ laneIdx + 1 }}</span>
          <span class="ml-auto text-xs text-gray-400">{{ lane.length }} tasks</span>
        </div>
      </template>
    </template>
  </div>
</div>
```

#### Option B: Task-Based Sidebar (Simpler, less efficient)

Keep current sidebar, but don't pack lanes - show one row per task.

```javascript
// Remove lane packing, show tasks in simple rows
const timelineRows = computed(() => {
  return flatGroups.value.map(group => ({
    ...group,
    rows: group.tasks.map(task => ({ task, lane: 0 }))
  }))
})

// Update grid height
const gridHeight = computed(() => {
  let h = 0
  timelineRows.value.forEach(group => {
    h += ROW_HEIGHT_GROUP
    if (!collapsedGroups.value.has(group.id)) {
      h += group.rows.length * ROW_HEIGHT_LANE
    }
  })
  return Math.max(h, 200)
})
```

---

### Fix 9: Performance Optimization - Memoization

**File:** `resources/js/Components/Projects/Views/TimelineView.vue`

**Problem:** Expensive computations run on every render.

**Solution:**

```javascript
import { ref, computed, onMounted, nextTick, onUnmounted, watch } from 'vue'

// Memoize month headers
const monthHeadersCache = new Map()

const visibleMonths = computed(() => {
  const cacheKey = `${timelineStart.value.toISOString()}-${zoomLevel.value}`
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
      label = new Date(`${year}-${month}-01`).toLocaleDateString('en-US', { 
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

// Clear cache when zoom changes
watch(zoomLevel, () => {
  monthHeadersCache.clear()
})

// Clear cache on unmount
onUnmounted(() => {
  monthHeadersCache.clear()
  laneCache.clear()
})
```

---

### Fix 10: Add Loading States

**File:** `resources/js/Components/Projects/Views/TimelineView.vue`

**Problem:** No visual feedback during data loading.

**Solution:**

```vue
<!-- Add loading skeleton -->
<div v-if="isLoading" class="flex-1 flex flex-col">
  <div class="h-16 bg-gray-100 animate-pulse"></div>
  <div class="flex-1 flex">
    <div class="w-64 border-r bg-gray-50 space-y-2 p-4">
      <div v-for="i in 8" :key="i" class="h-10 bg-gray-200 rounded animate-pulse"></div>
    </div>
    <div class="flex-1 bg-white p-4 space-y-2">
      <div v-for="i in 8" :key="i" class="h-10 bg-gray-100 rounded animate-pulse"></div>
    </div>
  </div>
</div>

<div v-else class="flex flex-col h-full bg-white overflow-hidden">
  <!-- Existing timeline content -->
</div>
```

```javascript
// Add isLoading prop
const props = defineProps({
  project: Object,
  tasks: { type: Array, default: () => [] },
  sections: { type: Array, default: () => [] },
  isLoading: { type: Boolean, default: false }
})
```

---

## Testing Checklist

After implementing fixes, test these scenarios:

### Date Handling Tests:
- [ ] Task spanning single day renders correctly
- [ ] Task spanning multiple days shows correct width
- [ ] Task crossing DST boundary positions correctly
- [ ] Task with only start_date renders
- [ ] Task with only due_date renders
- [ ] Same-day task (start === due) renders as 1 cell

### Navigation Tests:
- [ ] Previous button shifts by correct amount for each zoom level
- [ ] Next button shifts by correct amount for each zoom level
- [ ] Today button centers today when visible
- [ ] Today button updates timelineStart when today is off-screen
- [ ] Today indicator shows correct position
- [ ] Off-screen indicators appear when today is not visible

### Zoom Tests:
- [ ] Zoom in increases detail (Days → Weeks → Months)
- [ ] Zoom out decreases detail (Months → Weeks → Days)
- [ ] Cell widths update correctly
- [ ] Task bars resize correctly
- [ ] Viewport approximately preserves center

### Lane Packing Tests:
- [ ] Non-overlapping tasks pack into same lane
- [ ] Overlapping tasks pack into different lanes
- [ ] Tasks touching at boundaries don't overlap
- [ ] Cache invalidates when tasks change

### Drag & Drop Tests:
- [ ] Dragging task updates position visually
- [ ] Dropping task persists to backend
- [ ] Failed save reverts visual change
- [ ] Dragging snaps to day boundaries
- [ ] Resizing left edge updates start_date
- [ ] Resizing right edge updates due_date

### Performance Tests:
- [ ] 100 tasks render smoothly
- [ ] 500 tasks render acceptably
- [ ] Scrolling is smooth (60fps)
- [ ] No memory leaks after 5 minutes of use
- [ ] Zoom changes are instant

---

## Deployment Checklist

Before deploying to production:

- [ ] All Phase 1 fixes implemented
- [ ] All tests passing
- [ ] Performance benchmarks met
- [ ] Accessibility audit passed
- [ ] Cross-browser testing complete (Chrome, Firefox, Safari, Edge)
- [ ] Mobile responsiveness verified
- [ ] Error handling tested
- [ ] Loading states tested
- [ ] Empty states tested
- [ ] Documentation updated
- [ ] User guide created
- [ ] Beta testing with 10+ users
- [ ] Feedback incorporated

---

## Next Steps

1. **Immediate:** Implement Phase 1 fixes (this document)
2. **Week 2:** Implement Phase 2 architecture improvements
3. **Week 3-4:** Add missing features (dependencies, milestones, keyboard nav)
4. **Week 5:** Polish and optimization
5. **Week 6:** Beta testing and feedback
6. **Week 7-8:** Final refinements and production deployment

---

**Document Version:** 1.0  
**Last Updated:** May 13, 2026  
**Status:** Ready for implementation
