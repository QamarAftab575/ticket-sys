# Timeline View - Comprehensive Audit Report

**Date:** May 13, 2026  
**Project:** Asana-like Project Management System  
**Scope:** Timeline/Gantt View Implementation Review

---

## Executive Summary

The Timeline View implementation has **significant architectural and UX issues** that prevent it from being production-ready. While the basic structure exists, there are critical problems with:

1. **Date calculation logic** (off-by-one errors, timezone issues)
2. **Navigation behavior** (zoom/navigation not properly synchronized)
3. **Task positioning accuracy** (lane packing algorithm issues)
4. **Performance concerns** (unnecessary re-renders, inefficient computations)
5. **UX polish** (missing features, inconsistent behavior)

**Recommendation:** Major refactoring required before production deployment.

---

## Critical Issues Found

### 1. ❌ CRITICAL: Date Parsing & Timezone Issues

**Location:** `TimelineView.vue` - `parseLocalDate()` function

```javascript
const parseLocalDate = (dateStr) => {
  const [y, m, d] = dateStr.split('-').map(Number)
  return new Date(y, m - 1, d) // local midnight — no timezone shift
}
```

**Problems:**
- ✅ **GOOD:** Correctly avoids UTC offset issues by using local date constructor
- ❌ **BAD:** Not consistently used throughout the codebase
- ❌ **BAD:** `timelineStart` initialization uses `new Date()` which includes time component
- ❌ **BAD:** Date comparisons mix Date objects and ISO strings

**Impact:** Tasks may appear on wrong days, especially for users in different timezones.

**Fix Required:**
```javascript
// Standardize all date handling
const normalizeDate = (dateInput) => {
  if (dateInput instanceof Date) {
    return new Date(dateInput.getFullYear(), dateInput.getMonth(), dateInput.getDate())
  }
  const [y, m, d] = dateInput.split('-').map(Number)
  return new Date(y, m - 1, d)
}
```

---

### 2. ❌ CRITICAL: Navigation Logic Broken

**Location:** `TimelineView.vue` - Navigation functions

**Current Implementation:**
```javascript
const shiftMonths = (delta) => {
  const d = new Date(timelineStart.value)
  d.setMonth(d.getMonth() + delta)
  timelineStart.value = d
}
```

**Problems:**
- ❌ Navigation always shifts by **months**, regardless of zoom level
- ❌ When zoomed to "Days" or "Weeks", shifting by months is jarring
- ❌ "Today" button scrolls but doesn't update `timelineStart`
- ❌ No visual feedback when navigation reaches boundaries

**Expected Behavior:**
- **Days zoom:** Previous/Next should shift by 1-2 weeks
- **Weeks zoom:** Previous/Next should shift by 1 month
- **Months zoom:** Previous/Next should shift by 3 months
- **Today button:** Should center today AND update timelineStart if needed

**Fix Required:**
```javascript
const shiftTimeline = (direction) => {
  const d = new Date(timelineStart.value)
  switch (zoomLevel.value) {
    case 'Days':
      d.setDate(d.getDate() + (direction * 14)) // 2 weeks
      break
    case 'Weeks':
      d.setMonth(d.getMonth() + direction) // 1 month
      break
    case 'Months':
      d.setMonth(d.getMonth() + (direction * 3)) // 3 months
      break
  }
  timelineStart.value = normalizeDate(d)
}
```

---

### 3. ❌ CRITICAL: Zoom Behavior Issues

**Location:** `TimelineView.vue` - Zoom logic

**Problems:**
- ❌ Zoom levels are **hardcoded** as `['Weeks', 'Days', 'Months']` but displayed in wrong order
- ❌ No "Quarters" or "Years" zoom level for long-term planning
- ❌ Cell width doesn't adjust smoothly between zoom levels
- ❌ Zooming doesn't preserve the current viewport center
- ❌ Task bar widths don't recalculate correctly after zoom

**Current Zoom Levels:**
```javascript
const ZOOM_LEVELS = ['Weeks', 'Days', 'Months'] // Wrong order!
```

**Expected Order (least to most detail):**
```javascript
const ZOOM_LEVELS = ['Years', 'Quarters', 'Months', 'Weeks', 'Days']
```

**Fix Required:**
```javascript
const ZOOM_CONFIG = {
  Years: { cellWidth: 8, daysPerCell: 7, visibleDays: 730 },
  Quarters: { cellWidth: 12, daysPerCell: 7, visibleDays: 365 },
  Months: { cellWidth: 30, daysPerCell: 1, visibleDays: 180 },
  Weeks: { cellWidth: 40, daysPerCell: 1, visibleDays: 90 },
  Days: { cellWidth: 60, daysPerCell: 1, visibleDays: 60 }
}
```

---

### 4. ❌ CRITICAL: Task Positioning Off-by-One Errors

**Location:** `TimelineTaskBar.vue` - Position calculation

**Current Implementation:**
```javascript
const baseLeft = computed(() => {
  const taskStart = parseLocalDate(props.task.start_date || props.task.due_date)
  const origin = props.startDate instanceof Date
    ? new Date(props.startDate.getFullYear(), props.startDate.getMonth(), props.startDate.getDate())
    : parseLocalDate(props.startDate)
  const diff = Math.floor((taskStart - origin) / 86400000)
  return diff * props.cellWidth
})

const baseWidth = computed(() => {
  if (props.task.is_milestone || !props.task.start_date || !props.task.due_date) {
    return props.cellWidth
  }
  const days = Math.floor((parseLocalDate(props.task.due_date) - parseLocalDate(props.task.start_date)) / 86400000) + 1
  return Math.max(props.cellWidth, days * props.cellWidth)
})
```

**Problems:**
- ⚠️ **POTENTIAL BUG:** `Math.floor` on date diff can cause off-by-one errors with DST transitions
- ⚠️ **INCONSISTENT:** Width calculation adds `+ 1` but position doesn't account for inclusive dates
- ❌ **EDGE CASE:** Same-day tasks (start === due) render as 1 cell, but should they be milestones?

**Test Cases Needed:**
```javascript
// Test 1: Single day task
start_date: '2026-05-13', due_date: '2026-05-13'
// Expected: 1 cell width OR milestone marker

// Test 2: Two day task
start_date: '2026-05-13', due_date: '2026-05-14'
// Expected: 2 cells width (inclusive)

// Test 3: DST transition
start_date: '2026-03-08', due_date: '2026-03-10' // DST in US
// Expected: Correct positioning despite 23-hour day
```

---

### 5. ❌ MAJOR: Lane Packing Algorithm Issues

**Location:** `TimelineView.vue` - `packIntoLanes()` function

**Current Implementation:**
```javascript
const packIntoLanes = (tasks, groupKey) => {
  const fingerprint = tasks.map(t => `${t.id}:${t.start_date}:${t.due_date}`).sort().join('|')
  const cached = laneCache.get(groupKey)
  if (cached && cached.fingerprint === fingerprint) return cached.lanes

  const sorted = [...tasks].sort((a, b) => {
    const aS = a.start_date || a.due_date
    const bS = b.start_date || b.due_date
    return aS < bS ? -1 : aS > bS ? 1 : 0
  })

  const laneEnds = []
  const laneTasks = []
  for (const task of sorted) {
    const tStart = task.start_date || task.due_date
    const tEnd = task.due_date || task.start_date
    let placed = false
    for (let i = 0; i < laneEnds.length; i++) {
      if (laneEnds[i] < tStart) {
        laneTasks[i].push(task)
        laneEnds[i] = tEnd
        placed = true
        break
      }
    }
    if (!placed) { laneTasks.push([task]); laneEnds.push(tEnd) }
  }

  laneCache.set(groupKey, { fingerprint, lanes: laneTasks })
  return laneTasks
}
```

**Problems:**
- ❌ **OVERLAP BUG:** Comparison `laneEnds[i] < tStart` should be `<=` to prevent overlaps
- ❌ **INEFFICIENT:** O(n*m) complexity where m = number of lanes (can be optimized)
- ❌ **CACHE INVALIDATION:** Cache never clears, potential memory leak
- ⚠️ **EDGE CASE:** Tasks with only `due_date` (no start) may pack incorrectly

**Fix Required:**
```javascript
// Correct overlap check
if (laneEnds[i] <= tStart) { // Use <= not <
  laneTasks[i].push(task)
  laneEnds[i] = tEnd
  placed = true
  break
}
```

---

### 6. ❌ MAJOR: Grid Height Calculation Mismatch

**Location:** `TimelineView.vue` - Sidebar vs Timeline height sync

**Problem:**
- Left sidebar shows **one row per task** (fixed height)
- Right timeline shows **one row per lane** (variable height based on packing)
- These are **completely independent** and don't align

**Current Architecture:**
```
LEFT SIDEBAR (flatGroups)          RIGHT TIMELINE (packedGroups)
├─ Section Header                  ├─ Section Header
├─ Task 1 (40px)                   ├─ Lane 1: [Task 1, Task 3] (40px)
├─ Task 2 (40px)                   ├─ Lane 2: [Task 2, Task 4] (40px)
├─ Task 3 (40px)                   └─ Lane 3: [Task 5] (40px)
├─ Task 4 (40px)
└─ Task 5 (40px)
```

**Result:** Rows don't align! Task 2 in sidebar is at different vertical position than Task 2 bar in timeline.

**This is a FUNDAMENTAL ARCHITECTURAL FLAW.**

**Two Solutions:**

**Option A: Align sidebar to lanes (Recommended)**
- Show lane numbers in sidebar instead of individual tasks
- Click lane to see tasks in that lane
- Matches Asana/Linear behavior

**Option B: Show all tasks in timeline (Performance issue)**
- Don't pack lanes, show one row per task
- Simpler but wastes vertical space
- Better for small projects (<100 tasks)

---

### 7. ❌ MAJOR: Today Indicator Issues

**Location:** `TimelineView.vue` - Today line rendering

**Current Implementation:**
```javascript
const todayOffset = computed(() => {
  const idx = visibleDates.value.findIndex(d => d.isToday)
  return idx >= 0 ? idx * CELL_WIDTH : null
})
```

**Problems:**
- ✅ **GOOD:** Correctly finds today's index
- ❌ **BAD:** Only works if today is in `visibleDates` (365-day window)
- ❌ **BAD:** If user navigates far into past/future, today line disappears
- ❌ **BAD:** No visual indicator that today is off-screen

**Fix Required:**
```javascript
const todayOffset = computed(() => {
  const today = new Date()
  const todayStr = today.toISOString().split('T')[0]
  const idx = visibleDates.value.findIndex(d => d.key === todayStr)
  
  if (idx >= 0) {
    return { visible: true, offset: idx * CELL_WIDTH }
  }
  
  // Today is off-screen - calculate direction
  const firstDate = new Date(visibleDates.value[0].key)
  const isInPast = today < firstDate
  return { visible: false, isInPast }
})
```

---

### 8. ⚠️ MODERATE: Drag & Drop Issues

**Location:** `TimelineTaskBar.vue` - Drag handlers

**Problems:**
- ❌ **NO SNAP FEEDBACK:** While dragging, no visual grid snap indicator
- ❌ **NO CONSTRAINTS:** Can drag tasks to invalid dates (before project start, etc.)
- ❌ **NO COLLISION DETECTION:** Can drag task to overlap with another in same lane
- ⚠️ **PERFORMANCE:** Drag updates trigger full re-render instead of just visual update

**Missing Features:**
- Drag preview/ghost
- Snap-to-grid visual feedback
- Constraint validation (min/max dates)
- Undo/redo for drag operations

---

### 9. ⚠️ MODERATE: Empty State Handling

**Location:** `TimelineView.vue` - Task filtering

**Problems:**
- ❌ **NO EMPTY STATE:** When all tasks have no dates, timeline shows blank grid
- ❌ **NO GUIDANCE:** No message explaining why timeline is empty
- ❌ **NO ACTION:** No quick way to add dates to tasks from timeline view

**Fix Required:**
```vue
<div v-if="datedTasks.length === 0" class="empty-state">
  <svg><!-- Calendar icon --></svg>
  <h3>No tasks with dates</h3>
  <p>Add start and due dates to tasks to see them on the timeline</p>
  <button @click="showNoDatePanel = true">
    View {{ noDateTasks.length }} tasks without dates
  </button>
</div>
```

---

### 10. ⚠️ MODERATE: Performance Issues

**Location:** Multiple files

**Problems Identified:**

1. **Excessive Re-renders:**
   ```javascript
   // visibleDates computed runs on every scroll
   const visibleDates = computed(() => {
     const dates = []
     const cur = new Date(timelineStart.value)
     for (let i = 0; i < 365; i++) { // Generates 365 objects every time!
       dates.push({ key: cur.toISOString().split('T')[0], ... })
       cur.setDate(cur.getDate() + 1)
     }
     return dates
   })
   ```

2. **No Virtual Scrolling:**
   - Renders all 365 days even if only 30 are visible
   - Should use virtual scrolling for horizontal axis

3. **Inefficient Lane Packing:**
   - Runs on every task update
   - Should debounce or use worker thread for large datasets

4. **No Memoization:**
   - Month headers recalculated on every render
   - Should memoize expensive computations

**Performance Targets:**
- ✅ **Current:** ~100 tasks render smoothly
- ❌ **Target:** 1000+ tasks should render smoothly
- ❌ **Target:** Scroll should be 60fps

---

## Missing Features (Compared to Asana/Linear)

### Critical Missing Features:

1. ❌ **Dependencies Visualization**
   - No arrows showing task dependencies
   - No conflict detection (dependent task starts before blocker ends)
   - GanttTimeline.vue has dependency code but TimelineView.vue doesn't use it

2. ❌ **Milestones**
   - `is_milestone` field exists but no UI to create milestones
   - No diamond marker rendering in TimelineView.vue (only in GanttTaskBar.vue)

3. ❌ **Baseline/Progress Tracking**
   - No way to show planned vs actual dates
   - No progress bars on task bars

4. ❌ **Critical Path Highlighting**
   - No visual indication of critical path
   - Important for project management

5. ❌ **Keyboard Navigation**
   - No arrow key navigation between tasks
   - No keyboard shortcuts for zoom/navigation

6. ❌ **Multi-select & Bulk Operations**
   - Can't select multiple tasks
   - Can't bulk update dates

7. ❌ **Timeline Filters**
   - Can't filter by assignee, priority, etc. in timeline view
   - Filters from toolbar don't apply to timeline

---

## Code Quality Issues

### 1. Duplicate Components

**Problem:** Two timeline implementations exist:
- `TimelineView.vue` (currently used)
- `GanttTimeline.vue` (not used, but has better features)

**Recommendation:** Merge the two, keeping best features from each.

### 2. Inconsistent Naming

```javascript
// Mixing conventions
const ROW_HEIGHT_GROUP = 40  // SCREAMING_SNAKE_CASE
const zoomLevel = ref('Days') // camelCase
const CELL_WIDTH = 40 // SCREAMING_SNAKE_CASE
```

**Recommendation:** Use consistent naming (prefer camelCase for JS, UPPER_CASE for true constants).

### 3. Magic Numbers

```javascript
// What do these numbers mean?
const dates = []
for (let i = 0; i < 365; i++) { // Why 365?
  // ...
}

d.setMonth(d.getMonth() - 3) // Why -3?
```

**Recommendation:** Extract to named constants with comments.

### 4. No TypeScript

**Problem:** No type safety, easy to pass wrong props.

**Recommendation:** Migrate to TypeScript or add JSDoc comments.

---

## UX Issues

### 1. Visual Polish

**Problems:**
- ❌ Task bars have no hover state preview
- ❌ No loading skeleton while fetching tasks
- ❌ No smooth transitions when zooming
- ❌ Grid lines are too subtle (hard to see column boundaries)
- ❌ Weekend highlighting is barely visible

### 2. Accessibility

**Problems:**
- ❌ No ARIA labels on interactive elements
- ❌ No keyboard navigation
- ❌ No screen reader support
- ❌ Color-only indicators (need patterns/icons too)

### 3. Mobile Responsiveness

**Problems:**
- ❌ Timeline is completely unusable on mobile
- ❌ No touch gestures for pan/zoom
- ❌ Sidebar takes too much space on small screens

**Recommendation:** Add mobile-specific view or disable timeline on mobile.

---

## Comparison with Industry Standards

### Asana Timeline:
✅ Smooth zoom with preserved viewport  
✅ Dependencies with conflict detection  
✅ Drag & drop with snap feedback  
✅ Keyboard shortcuts  
✅ Baseline tracking  
✅ Critical path highlighting  

### Linear Roadmap:
✅ Clean, minimal design  
✅ Fast performance (1000+ issues)  
✅ Smooth animations  
✅ Excellent keyboard navigation  
✅ Virtual scrolling  

### ClickUp Timeline:
✅ Multiple view modes (Gantt, Timeline, Roadmap)  
✅ Resource allocation view  
✅ Time tracking integration  
✅ Custom fields in bars  

### **Current Implementation:**
❌ Basic functionality only  
❌ Performance issues with >100 tasks  
❌ No dependencies visualization  
❌ Limited keyboard support  
❌ No mobile support  

---

## Recommended Action Plan

### Phase 1: Critical Fixes (1-2 weeks)
1. ✅ Fix date parsing and timezone handling
2. ✅ Fix navigation logic (zoom-aware shifting)
3. ✅ Fix zoom level ordering and behavior
4. ✅ Fix lane packing overlap bug
5. ✅ Add empty state handling
6. ✅ Fix today indicator edge cases

### Phase 2: Architecture Improvements (2-3 weeks)
1. ✅ Resolve sidebar/timeline alignment issue (choose Option A or B)
2. ✅ Implement virtual scrolling for performance
3. ✅ Add memoization for expensive computations
4. ✅ Merge TimelineView and GanttTimeline components
5. ✅ Add TypeScript or comprehensive JSDoc

### Phase 3: Feature Parity (3-4 weeks)
1. ✅ Implement dependencies visualization
2. ✅ Add milestone support
3. ✅ Add keyboard navigation
4. ✅ Add multi-select and bulk operations
5. ✅ Add timeline-specific filters
6. ✅ Add drag constraints and validation

### Phase 4: Polish & Optimization (1-2 weeks)
1. ✅ Improve visual design (hover states, transitions)
2. ✅ Add accessibility features (ARIA, keyboard, screen reader)
3. ✅ Optimize for 1000+ tasks
4. ✅ Add mobile view or graceful degradation
5. ✅ Add loading states and error handling

### Phase 5: Advanced Features (Optional, 2-3 weeks)
1. ⭐ Baseline/progress tracking
2. ⭐ Critical path highlighting
3. ⭐ Resource allocation view
4. ⭐ Custom fields in task bars
5. ⭐ Export to PDF/PNG

---

## Testing Recommendations

### Unit Tests Needed:
```javascript
describe('Timeline Date Calculations', () => {
  test('parseLocalDate handles timezone correctly')
  test('task width calculation is accurate')
  test('task position handles DST transitions')
  test('same-day tasks render correctly')
})

describe('Lane Packing Algorithm', () => {
  test('non-overlapping tasks pack into same lane')
  test('overlapping tasks pack into different lanes')
  test('edge case: task with only due_date')
  test('cache invalidation works correctly')
})

describe('Navigation', () => {
  test('previous/next shifts by correct amount per zoom level')
  test('today button centers and updates timelineStart')
  test('zoom preserves viewport center')
})
```

### Integration Tests Needed:
- Drag task and verify backend update
- Create task inline and verify persistence
- Toggle task complete and verify state sync
- Switch between views and verify data consistency

### E2E Tests Needed:
- Load project with 500 tasks, verify performance
- Navigate timeline for 1 minute, verify no memory leaks
- Drag multiple tasks, verify all updates persist
- Test on mobile device, verify graceful degradation

---

## Conclusion

The Timeline View implementation is **not production-ready** in its current state. While the basic structure is sound, there are critical bugs in date handling, navigation, and task positioning that will cause user frustration.

**Estimated effort to reach production quality:** 8-12 weeks

**Priority:** HIGH - Timeline is a core feature for project management tools

**Risk:** MEDIUM - Current bugs are fixable, but architecture needs refactoring

**Recommendation:** 
1. Immediately fix critical date/navigation bugs (Phase 1)
2. Decide on sidebar/timeline alignment strategy (Phase 2)
3. Implement missing features incrementally (Phase 3-4)
4. Launch beta to gather user feedback before Phase 5

---

## Appendix: Code Snippets for Quick Fixes

### Fix 1: Standardized Date Handling
```javascript
// Add to TimelineView.vue
const normalizeDate = (input) => {
  if (!input) return null
  if (input instanceof Date) {
    return new Date(input.getFullYear(), input.getMonth(), input.getDate())
  }
  const [y, m, d] = input.split('-').map(Number)
  return new Date(y, m - 1, d)
}

const dateToISO = (date) => {
  if (!date) return null
  const d = normalizeDate(date)
  return d.toISOString().split('T')[0]
}
```

### Fix 2: Zoom-Aware Navigation
```javascript
const ZOOM_CONFIG = {
  Days: { shift: 14, cellWidth: 60, visibleDays: 60 },
  Weeks: { shift: 30, cellWidth: 40, visibleDays: 90 },
  Months: { shift: 90, cellWidth: 30, visibleDays: 180 }
}

const shiftTimeline = (direction) => {
  const config = ZOOM_CONFIG[zoomLevel.value]
  const d = normalizeDate(timelineStart.value)
  d.setDate(d.getDate() + (direction * config.shift))
  timelineStart.value = d
}
```

### Fix 3: Lane Packing Overlap Fix
```javascript
// Change line in packIntoLanes:
if (laneEnds[i] <= tStart) { // Was: < tStart
  laneTasks[i].push(task)
  laneEnds[i] = tEnd
  placed = true
  break
}
```

---

**Report prepared by:** Kiro AI Development Assistant  
**Review status:** Comprehensive audit complete  
**Next steps:** Prioritize Phase 1 critical fixes
