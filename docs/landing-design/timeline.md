## 🗓️ Asana-Style Timeline View — Fix Guide for Your AI Agent

### **Core Problems to Fix**

Looking at your two screenshots, here are the exact issues:

---

### **Problem 1: Column Width — Fixed vs Dynamic**

**Current:** Your columns are tiny, squished, and all equal width regardless of content.

**Asana does:** Each date column has a **fixed minimum width** (around `40–50px` per day in "Days" view, wider in "Month" view). The grid doesn't compress — it scrolls horizontally instead.

**Fix instruction for agent:**
```
Set each day column to a fixed width of 40px minimum. 
The timeline container should overflow-x: scroll, NOT compress columns to fit.
Never shrink columns — add a horizontal scrollbar instead.
```

---

### **Problem 2: Task Bars Spanning Across Date Columns**

**Current:** Tasks show text in a single cell, not spanning bars.

**Asana does:** A task with `start_date = Feb 1` and `due_date = Feb 5` renders as a **colored bar spanning columns 1–5**, positioned absolutely over the grid.

**Fix instruction for agent:**
```
For each task with a due date (and optionally a start date):
- Calculate the pixel offset from the timeline start: offsetX = (startDay - timelineStart) * columnWidth
- Calculate bar width: width = (endDay - startDay + 1) * columnWidth
- Render the task as an absolutely-positioned div over the grid rows
- Tasks without dates should be hidden from the timeline or shown in a "No date" sidebar
- The bar should show the task name inside it, truncated with ellipsis if too long
- Bars should be draggable left/right to reschedule (optional but Asana core feature)
```

---

### **Problem 3: Date Headers Don't Align with Columns**

**Current:** Month name shown but individual date numbers don't align with their columns.

**Asana does:** Two-row header — **Month name** spans across its days, and below it each day number sits centered in its own column.

**Fix instruction for agent:**
```
Build a two-level sticky header:
- Row 1: Month labels (e.g., "April", "May") — each spanning the exact pixel width of days in that month
- Row 2: Day numbers (1, 2, 3...) — each in its own fixed-width column
Both rows must be pixel-perfect aligned with the grid columns below.
Use the same columnWidth variable for both header and grid to guarantee alignment.
```

---

### **Problem 4: Today Highlight**

**Asana does:** Today's column has a **blue highlighted vertical line/column** and the "Today" button scrolls to it.

**Fix instruction for agent:**
```
- Identify today's column index relative to timeline start
- Apply a blue top border or background tint to today's column header
- Render a vertical blue line (1–2px wide, full height) at today's position
- "Today" button should call scrollIntoView() or set scrollLeft to today's offset
```

---

### **Problem 5: Tasks Without Due Dates**

**Asana does:** Shows "No date (1)" button in top-right, clicking reveals a side panel with undated tasks.

**Fix instruction for agent:**
```
Filter tasks into two groups:
1. Tasks with dates → render as timeline bars
2. Tasks without dates → show count in a "No date (N)" button, 
   clicking opens a right-side drawer listing them
```

---

### **Summary of Key Variables Your Agent Needs**

```js
const COLUMN_WIDTH = 40; // px per day
const TIMELINE_START_DATE = new Date('2025-04-01'); // leftmost visible date

// For each task bar:
const startOffset = daysBetween(TIMELINE_START_DATE, task.startDate) * COLUMN_WIDTH;
const barWidth = daysBetween(task.startDate, task.dueDate) * COLUMN_WIDTH;
```

---

### **Layout Structure to Implement**

```
┌─────────────────────────────────────────────────┐
│ [Sticky Left Panel: Task Names] │ [Scrollable Timeline Grid]  │
│                                 │ Header Row 1: Month spans   │
│                                 │ Header Row 2: Day numbers   │
│  Task A                         │ ████████ (bar Apr 22–25)    │
│  Task B                         │      ██████ (bar Apr 27–30) │
│  Task C (no date)               │ (hidden)                    │
└─────────────────────────────────────────────────┘
```

The left task name panel should be **sticky/fixed**, while the right grid scrolls horizontally. Both panels scroll **vertically in sync**.