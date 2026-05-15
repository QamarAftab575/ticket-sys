# Timeline View - Developer Guide

Quick reference for working with the Timeline View component.

---

## Component Structure

```
TimelineView.vue (Main component)
├── Date utilities (normalizeDate, dateToISO, etc.)
├── Zoom configuration (ZOOM_CONFIG)
├── State management (refs, computed)
├── Task grouping (flatGroups, packedGroups)
├── Lane packing algorithm
├── Navigation functions
└── Event handlers

TimelineTaskBar.vue (Task bar component)
├── Date utilities (same as parent)
├── Position calculation (baseLeft, baseWidth)
├── Drag & drop handlers
├── Resize handlers
└── Visual rendering
```

---

## Date Handling (CRITICAL)

**Always use these utilities for date operations:**

```javascript
// Normalize any date input to local midnight
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

// Convert Date to ISO string
const dateToISO = (date) => {
  if (!date) return null
  const d = normalizeDate(date)
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

// Calculate days between (inclusive)
const daysBetween = (start, end) => {
  const d1 = normalizeDate(start)
  const d2 = normalizeDate(end)
  if (!d1 || !d2) return 0
  return Math.round((d2 - d1) / 86400000) + 1
}

// Add days to a date
const addDays = (date, days) => {
  const d = normalizeDate(date)
  if (!d) return null
  d.setDate(d.getDate() + days)
  return dateToISO(d)
}
```

**Why?**
- Prevents timezone offset issues
- Ensures consistent date calculations
- Avoids off-by-one errors
- Handles DST transitions correctly

**DON'T:**
```javascript
// ❌ WRONG - timezone issues
new Date('2026-05-13') // Creates date at midnight UTC, not local

// ❌ WRONG - loses time component
someDate.toISOString().split('T')[0] // Can shift date by 1 day

// ❌ WRONG - DST issues
Math.floor((date2 - date1) / 86400000) // Can be off by 1 during DST
```

**DO:**
```javascript
// ✅ CORRECT
normalizeDate('2026-05-13') // Local midnight
dateToISO(someDate) // Correct ISO string
daysBetween(start, end) // Handles DST
```

---

## Zoom Configuration

```javascript
const ZOOM_CONFIG = {
  Months: {
    cellWidth: 30,        // Width of each day cell in pixels
    visibleDays: 180,     // Number of days to show
    shiftAmount: 90,      // Days to shift on prev/next (3 months)
    dateLabel: (date) => date.getDate(),
    monthLabel: (date) => date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
  },
  Weeks: {
    cellWidth: 40,
    visibleDays: 90,
    shiftAmount: 30,      // ~1 month
    dateLabel: (date) => date.getDate(),
    monthLabel: (date) => date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
  },
  Days: {
    cellWidth: 60,
    visibleDays: 60,
    shiftAmount: 14,      // 2 weeks
    dateLabel: (date) => date.getDate(),
    monthLabel: (date) => date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
  }
}

const ZOOM_LEVELS = ['Months', 'Weeks', 'Days'] // Order: least to most detail
```

**To add a new zoom level:**

1. Add to `ZOOM_CONFIG`:
```javascript
Quarters: {
  cellWidth: 20,
  visibleDays: 365,
  shiftAmount: 90,
  dateLabel: (date) => date.getDate(),
  monthLabel: (date) => `Q${Math.floor(date.getMonth() / 3) + 1} ${date.getFullYear()}`
}
```

2. Add to `ZOOM_LEVELS` array in correct order:
```javascript
const ZOOM_LEVELS = ['Quarters', 'Months', 'Weeks', 'Days']
```

---

## Lane Packing Algorithm

**Purpose:** Pack non-overlapping tasks into the same horizontal lane to save vertical space.

```javascript
const packIntoLanes = (tasks, groupKey) => {
  // 1. Check cache
  const fingerprint = tasks.map(t => `${t.id}:${t.start_date}:${t.due_date}`).sort().join('|')
  const cached = laneCache.get(groupKey)
  if (cached && cached.fingerprint === fingerprint) return cached.lanes

  // 2. Sort tasks by start date
  const sorted = [...tasks].sort((a, b) => {
    const aS = a.start_date || a.due_date
    const bS = b.start_date || b.due_date
    if (!aS) return 1
    if (!bS) return -1
    return aS < bS ? -1 : aS > bS ? 1 : 0
  })

  // 3. Pack into lanes
  const laneEnds = []   // Track end date of last task in each lane
  const laneTasks = []  // Tasks in each lane
  
  for (const task of sorted) {
    const tStart = task.start_date || task.due_date
    const tEnd = task.due_date || task.start_date
    
    if (!tStart || !tEnd) continue
    
    let placed = false
    for (let i = 0; i < laneEnds.length; i++) {
      // CRITICAL: Use <= not < to prevent overlaps
      if (laneEnds[i] <= tStart) {
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

  // 4. Cache result
  laneCache.set(groupKey, { fingerprint, lanes: laneTasks })
  return laneTasks
}
```

**Key Points:**
- Uses `<=` comparison to prevent overlaps (tasks touching at boundaries go to different lanes)
- Caches results with fingerprint for performance
- Handles null/undefined dates gracefully
- Returns array of lanes, each lane is array of tasks

**Example:**
```javascript
// Input tasks:
Task A: 2026-05-01 to 2026-05-05
Task B: 2026-05-06 to 2026-05-10
Task C: 2026-05-03 to 2026-05-07
Task D: 2026-05-11 to 2026-05-15

// Output lanes:
Lane 0: [Task A, Task B, Task D]  // Non-overlapping
Lane 1: [Task C]                   // Overlaps with A and B
```

---

## Task Positioning

**In TimelineTaskBar.vue:**

```javascript
// Calculate left position
const baseLeft = computed(() => {
  const taskStart = normalizeDate(props.task.start_date || props.task.due_date)
  const origin = normalizeDate(props.startDate)
  if (!taskStart || !origin) return 0
  
  const diff = Math.round((taskStart - origin) / 86400000)
  return diff * props.cellWidth
})

// Calculate width
const baseWidth = computed(() => {
  if (props.task.is_milestone || !props.task.start_date || !props.task.due_date) {
    return props.cellWidth
  }
  
  const days = daysBetween(props.task.start_date, props.task.due_date)
  return Math.max(props.cellWidth, days * props.cellWidth)
})
```

**Key Points:**
- Use `Math.round` not `Math.floor` for date diff (handles DST)
- Use `daysBetween` utility for width (inclusive calculation)
- Milestones render as single cell width
- Minimum width is one cell

---

## Navigation Functions

```javascript
// Shift timeline left/right (zoom-aware)
const shiftTimeline = (direction) => {
  const config = ZOOM_CONFIG[zoomLevel.value]
  const d = normalizeDate(timelineStart.value)
  d.setDate(d.getDate() + (direction * config.shiftAmount))
  timelineStart.value = d
}

// Go to today (handles visible and off-screen)
const goToToday = () => {
  const today = normalizeDate(new Date())
  const todayStr = dateToISO(today)
  
  const idx = visibleDates.value.findIndex(d => d.key === todayStr)
  
  if (idx >= 0) {
    // Today is visible, scroll to it
    if (gridRef.value) {
      gridRef.value.scrollLeft = Math.max(0, idx * CELL_WIDTH.value - gridRef.value.clientWidth / 2 + CELL_WIDTH.value / 2)
    }
  } else {
    // Today is off-screen, update timelineStart
    const config = ZOOM_CONFIG[zoomLevel.value]
    const d = normalizeDate(today)
    d.setDate(d.getDate() - Math.floor(config.visibleDays / 2))
    timelineStart.value = d
    
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

## Performance Tips

### 1. Use Memoization

```javascript
const monthHeadersCache = new Map()

const visibleMonths = computed(() => {
  const cacheKey = `${dateToISO(timelineStart.value)}-${zoomLevel.value}`
  if (monthHeadersCache.has(cacheKey)) {
    return monthHeadersCache.get(cacheKey)
  }
  
  // ... expensive computation ...
  
  monthHeadersCache.set(cacheKey, result)
  return result
})
```

### 2. Clear Caches

```javascript
// Clear when zoom changes
watch(zoomLevel, () => {
  monthHeadersCache.clear()
})

// Clear on unmount
onUnmounted(() => {
  monthHeadersCache.clear()
  laneCache.clear()
})
```

### 3. Avoid Unnecessary Re-renders

```javascript
// ❌ BAD - recalculates on every render
const cellWidth = 40

// ✅ GOOD - only recalculates when zoom changes
const CELL_WIDTH = computed(() => ZOOM_CONFIG[zoomLevel.value].cellWidth)
```

---

## Common Tasks

### Add a New Task Property to Timeline

1. Update task query in backend to include new field
2. Add field to task type definition (if using TypeScript)
3. Use field in TimelineTaskBar.vue:

```vue
<template>
  <div class="task-bar" :style="{ backgroundColor: getTaskColor() }">
    <span>{{ task.name }}</span>
    <span v-if="task.priority" class="priority-badge">{{ task.priority }}</span>
  </div>
</template>

<script setup>
const getTaskColor = () => {
  if (props.task.priority === 'high') return '#ef4444'
  if (props.task.priority === 'medium') return '#f59e0b'
  return '#3b82f6'
}
</script>
```

### Add a New Grouping Option

1. Update `groupBy` options in template:

```vue
<select v-model="groupBy">
  <option value="section">Section</option>
  <option value="assignee">Assignee</option>
  <option value="priority">Priority</option>
  <option value="status">Status</option> <!-- NEW -->
</select>
```

2. Update `flatGroups` computed to handle new grouping:

```javascript
const flatGroups = computed(() => {
  if (groupBy.value === 'none') {
    return [{ id: 'all', name: 'All Tasks', sectionId: null, tasks: datedTasks.value }]
  }
  
  const map = new Map()
  datedTasks.value.forEach(task => {
    let key, name, sectionId
    
    if (groupBy.value === 'section') {
      key = task.section?.id || 'no-section'
      name = task.section?.name || 'No Section'
      sectionId = task.section?.id ?? null
    } else if (groupBy.value === 'status') { // NEW
      key = task.status || 'no-status'
      name = task.status ? task.status.charAt(0).toUpperCase() + task.status.slice(1) : 'No Status'
      sectionId = null
    }
    // ... other groupings ...
    
    if (!map.has(key)) map.set(key, { id: key, name, sectionId, tasks: [] })
    map.get(key).tasks.push(task)
  })
  
  return Array.from(map.values())
})
```

### Add a New Event Handler

1. Define emit in parent component:

```javascript
const emit = defineEmits(['select-task', 'update-task', 'create-task', 'toggle-complete', 'custom-event'])
```

2. Emit event from child:

```javascript
const handleCustomAction = (data) => {
  emit('custom-event', data)
}
```

3. Handle in parent (Show.vue):

```vue
<TimelineView
  @custom-event="handleCustomEvent"
/>
```

```javascript
const handleCustomEvent = async (data) => {
  // Handle event
}
```

---

## Debugging Tips

### Date Issues

```javascript
// Add console logs to track date transformations
console.log('Input:', dateStr)
console.log('Normalized:', normalizeDate(dateStr))
console.log('ISO:', dateToISO(normalizeDate(dateStr)))
console.log('Days between:', daysBetween(start, end))
```

### Lane Packing Issues

```javascript
// Log lane packing results
const lanes = packIntoLanes(tasks, groupKey)
console.log('Lanes:', lanes.map((lane, i) => ({
  lane: i,
  tasks: lane.map(t => ({ id: t.id, name: t.name, start: t.start_date, end: t.due_date }))
})))
```

### Performance Issues

```javascript
// Measure computation time
console.time('visibleDates')
const dates = visibleDates.value
console.timeEnd('visibleDates')

console.time('packIntoLanes')
const lanes = packIntoLanes(tasks, groupKey)
console.timeEnd('packIntoLanes')
```

### Cache Issues

```javascript
// Check cache hit rate
let cacheHits = 0
let cacheMisses = 0

const visibleMonths = computed(() => {
  const cacheKey = `${dateToISO(timelineStart.value)}-${zoomLevel.value}`
  if (monthHeadersCache.has(cacheKey)) {
    cacheHits++
    console.log('Cache hit rate:', (cacheHits / (cacheHits + cacheMisses) * 100).toFixed(1) + '%')
    return monthHeadersCache.get(cacheKey)
  }
  cacheMisses++
  // ... compute ...
})
```

---

## Testing Checklist

### Unit Tests (Recommended)

```javascript
describe('Date Utilities', () => {
  test('normalizeDate handles string input', () => {
    const result = normalizeDate('2026-05-13')
    expect(result.getFullYear()).toBe(2026)
    expect(result.getMonth()).toBe(4) // 0-indexed
    expect(result.getDate()).toBe(13)
  })
  
  test('daysBetween calculates inclusive days', () => {
    expect(daysBetween('2026-05-13', '2026-05-13')).toBe(1)
    expect(daysBetween('2026-05-13', '2026-05-14')).toBe(2)
    expect(daysBetween('2026-05-13', '2026-05-20')).toBe(8)
  })
  
  test('packIntoLanes prevents overlaps', () => {
    const tasks = [
      { id: 1, start_date: '2026-05-01', due_date: '2026-05-05' },
      { id: 2, start_date: '2026-05-05', due_date: '2026-05-10' },
      { id: 3, start_date: '2026-05-03', due_date: '2026-05-07' }
    ]
    const lanes = packIntoLanes(tasks, 'test')
    expect(lanes.length).toBe(2)
    expect(lanes[0]).toContain(tasks[0])
    expect(lanes[0]).toContain(tasks[1])
    expect(lanes[1]).toContain(tasks[2])
  })
})
```

### Manual Testing

- [ ] Create task with same start and due date → renders as 1 cell
- [ ] Create task spanning 7 days → renders as 7 cells
- [ ] Zoom in/out → cell widths change correctly
- [ ] Navigate prev/next → shifts by correct amount
- [ ] Click "Today" → centers today
- [ ] Navigate far from today → see off-screen indicators
- [ ] Drag task → updates dates
- [ ] Resize task → updates start/due date
- [ ] Create task inline → persists to backend
- [ ] Toggle task complete → updates status

---

## Common Pitfalls

### 1. Timezone Issues

```javascript
// ❌ WRONG
const date = new Date('2026-05-13') // UTC midnight, not local

// ✅ CORRECT
const date = normalizeDate('2026-05-13') // Local midnight
```

### 2. Off-by-One Errors

```javascript
// ❌ WRONG - exclusive end
const days = Math.floor((end - start) / 86400000)

// ✅ CORRECT - inclusive end
const days = Math.round((end - start) / 86400000) + 1
```

### 3. Lane Overlap

```javascript
// ❌ WRONG - tasks touching at boundaries overlap
if (laneEnds[i] < tStart) { ... }

// ✅ CORRECT - tasks touching go to different lanes
if (laneEnds[i] <= tStart) { ... }
```

### 4. Cache Invalidation

```javascript
// ❌ WRONG - cache never clears
const cache = new Map()

// ✅ CORRECT - clear on zoom change and unmount
watch(zoomLevel, () => cache.clear())
onUnmounted(() => cache.clear())
```

### 5. Reactive Cell Width

```javascript
// ❌ WRONG - not reactive
const CELL_WIDTH = 40

// ✅ CORRECT - reactive based on zoom
const CELL_WIDTH = computed(() => ZOOM_CONFIG[zoomLevel.value].cellWidth)
```

---

## Resources

- **Audit Report:** `TIMELINE_AUDIT_REPORT.md` - Comprehensive analysis of issues
- **Implementation Guide:** `TIMELINE_FIXES_IMPLEMENTATION.md` - Step-by-step fixes
- **Changes Applied:** `TIMELINE_FIXES_APPLIED.md` - What was fixed and how to test

---

## Support

For questions or issues:
1. Check this guide first
2. Review audit report for architectural context
3. Check implementation guide for detailed solutions
4. Review git history for recent changes
5. Ask team lead or senior developer

---

**Last Updated:** May 13, 2026  
**Version:** 1.0 (Phase 1 Complete)
