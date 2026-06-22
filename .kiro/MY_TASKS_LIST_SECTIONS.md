# My Tasks List View - Sections Explained

## What Sections Does the List View Have?

The list view at `/my-tasks?view=list` displays **custom user-defined sections** (stored in the database).

Each section contains tasks that are organized by the user, and tasks can be moved between sections.

### Example Sections
- "To Do" (custom section created by user)
- "In Progress" (custom section created by user)
- "Done" (custom section created by user)
- "Backlog" (custom section created by user)
- etc. (any sections the user creates)

---

## Where Do These Sections Come From?

### 1. **Initial Page Load** (`/my-tasks`)
```
User visits /my-tasks
↓
MyTasksController.index() [Backend]
↓
MyTasksSectionService.getOrCreateSections($user) [Backend Service]
↓
Database Query: SELECT * FROM sections WHERE user_id = ? ORDER BY position
↓
Backend returns sections via Inertia props
↓
Frontend receives: myTasksSections prop
```

**File:** `app/Http/Controllers/MyTasksController.php` → `index()` method

**Code Flow:**
```php
$sections = $this->sectionService->getOrCreateSections($user);

return Inertia::render('MyTasks/Index', [
    'myTasksSections' => $sections->map(fn($s) => [
        'id'       => $s->id,
        'name'     => $s->name,
        'position' => $s->position,
    ])->values(),
    // ...
]);
```

### 2. **Store Initialization** (Frontend)
```
MyTasksLayout.vue mounts
↓
props.myTasksSections passed from controller
↓
myTasksStore.setSections(props.myTasksSections)
↓
Sections stored in: useMyTasksStore.sections
```

**File:** `resources/js/Stores/useMyTasksStore.ts`

**Store State:**
```typescript
interface MyTasksSection { 
  id: string; 
  name: string; 
  position: number; 
}
const sections = ref<MyTasksSection[]>([]);
```

### 3. **List View Receives Sections**
```
MyTasksLayout.vue renders MyTasksListView
↓
Props passed: sections, sectionOrder, collapsedSections
↓
MyTasksListView renders ListView (shared project component)
↓
ListView displays sections as collapsible groups
```

**File:** `resources/js/Components/MyTasks/Views/MyTasksListView.vue`

**Props:**
```typescript
interface Props {
  sections?: Section[];           // Real DB sections
  sectionOrder?: string[];        // Saved order of section IDs
  collapsedSections?: Set<string>; // Which sections are collapsed
  tasks: Task[];                  // Tasks to display
}
```

---

## Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│ Browser: /my-tasks?view=list                                │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ Backend: MyTasksController.index()                           │
│ - Gets authenticated user                                   │
│ - Fetches saved preferences (sort, grouping, etc)           │
│ - Loads sections: MyTasksSectionService.getOrCreateSections │
│ - Loads workspace members                                   │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ Database Query: SELECT * FROM sections                       │
│ WHERE user_id = ? AND deleted_at IS NULL                    │
│ ORDER BY position                                           │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ Backend Response (Inertia Props)                             │
│ {                                                            │
│   myTasksSections: [                                        │
│     { id: "uuid-1", name: "To Do", position: 1 },           │
│     { id: "uuid-2", name: "In Progress", position: 2 },     │
│     { id: "uuid-3", name: "Done", position: 3 }            │
│   ],                                                         │
│   savedPreferences: { ... },                                │
│   workspaceMembers: [ ... ]                                 │
│ }                                                            │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ Frontend: MyTasksLayout.vue                                  │
│ - Receives myTasksSections prop                              │
│ - Calls myTasksStore.setSections(props.myTasksSections)      │
│ - Initializes view from URL (?view=list)                    │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ Store: useMyTasksStore                                       │
│ - sections: [{ id, name, position }, ...]                   │
│ - sectionOrder: [uuid-1, uuid-2, uuid-3]                    │
│ - collapsedSections: Set([])                                │
│ - tasks: [{ id, name, section_id, ... }, ...]              │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ Frontend: MyTasksListView.vue                                │
│ - Receives sections from props                               │
│ - Receives tasks from props                                 │
│ - Computes orderedSections (respects sectionOrder)          │
│ - Computes collapsedSectionsArray                           │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ Frontend: ListView.vue (Shared Component)                    │
│ - Renders sections as collapsible groups                     │
│ - Renders tasks within each section                          │
│ - Supports drag-drop, inline creation, reordering            │
└─────────────────────────────────────────────────────────────┘
```

---

## Key Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/MyTasksController.php` | Entry point - loads sections from DB |
| `app/Services/MyTasksSectionService.php` | Business logic for section management |
| `app/Models/Section.php` | Database model for sections |
| `resources/js/Stores/useMyTasksStore.ts` | Frontend state management |
| `resources/js/Components/MyTasks/Layout/MyTasksLayout.vue` | Top-level layout |
| `resources/js/Components/MyTasks/Views/MyTasksListView.vue` | List view wrapper |
| `resources/js/Components/Projects/Views/ListView.vue` | Shared list component (used by both Projects and MyTasks) |

---

## How Sections Are Managed

### Create Section
```
User clicks "Add Section" in list view
↓
Inputs section name
↓
POST /my-tasks/api/sections { name: "..." }
↓
Backend: MyTasksController.storeSection()
↓
MyTasksSectionService.createSection($user, $name)
↓
Database: INSERT INTO sections (user_id, name, position, created_at)
↓
Frontend: Store receives new section and adds to sections array
```

### Reorder Sections
```
User drags section header in list view
↓
New order emitted from ListView
↓
MyTasksLayout.handleSectionOrderChange() called
↓
Store: setSectionOrder([uuid-1, uuid-3, uuid-2])
↓
POST /my-tasks/api/sections/reorder { section_ids: [...] }
↓
Backend updates position field for each section
↓
URL maintains ?view=list parameter
```

### Collapse/Expand Section
```
User clicks section collapse button
↓
Store: toggleCollapseSection(sectionId)
↓
collapsedSections Set updated
↓
Preferences saved to backend (debounced)
↓
Preference restored on next page load
```

---

## Summary

- **List view sections** = Custom sections created by the user (stored in DB)
- **Source**: MyTasksController loads from `sections` table on page load
- **Stored**: In Pinia store (useMyTasksStore.sections)
- **Displayed**: Via shared ListView component (same as Projects)
- **Persistence**: Section order and collapse state saved to user preferences
- **Management**: Can create, rename, delete, and reorder sections

This is different from the **Board view**, which has 4 fixed status-based sections (To Do, In Progress, In Review, Done) that are hardcoded.
