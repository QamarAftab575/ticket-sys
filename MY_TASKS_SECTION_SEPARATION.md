# My Tasks Section Separation - Complete Implementation

## 🎯 Problem Solved

**Original Issue:** The `tasks` table had only ONE `section_id` column, creating a conflict:
- When a task is in a project → `section_id` = project section
- When viewing in My Tasks → Should be in a My Tasks section
- **But there was only ONE field!** ❌

**Solution:** Added separate columns for My Tasks section tracking:
- `my_tasks_section_id` - Which My Tasks section the task is in
- `my_tasks_position` - Position within that My Tasks section

Now tasks can exist in BOTH contexts independently! ✅

---

## 📊 Database Schema Changes

### New Columns Added to `tasks` Table

```sql
ALTER TABLE tasks ADD COLUMN my_tasks_section_id UUID NULL;
ALTER TABLE tasks ADD COLUMN my_tasks_position INT NULL;
ALTER TABLE tasks ADD FOREIGN KEY (my_tasks_section_id) REFERENCES sections(id) ON DELETE SET NULL;
ALTER TABLE tasks ADD INDEX idx_my_tasks_sorting (assignee_id, my_tasks_section_id, my_tasks_position);
```

### Complete Task Schema

```sql
CREATE TABLE tasks (
    id UUID PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description LONGTEXT,
    
    -- Project context
    project_id UUID NULL,
    section_id UUID NULL,              -- Project section
    position INT NULL,                 -- Position in project section
    
    -- My Tasks context
    my_tasks_section_id UUID NULL,     -- ✅ NEW: My Tasks section
    my_tasks_position INT NULL,        -- ✅ NEW: Position in My Tasks section
    
    -- Assignment
    assignee_id UUID NULL,
    creator_id UUID NULL,
    
    -- Other fields...
    status VARCHAR(50),
    priority VARCHAR(50),
    due_date DATE,
    completed_at TIMESTAMP,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (section_id) REFERENCES sections(id),
    FOREIGN KEY (my_tasks_section_id) REFERENCES sections(id),
    FOREIGN KEY (assignee_id) REFERENCES users(id),
    FOREIGN KEY (creator_id) REFERENCES users(id),
    
    INDEX idx_my_tasks_sorting (assignee_id, my_tasks_section_id, my_tasks_position)
);
```

---

## 🔄 How It Works Now

### Example: Same Task in Two Contexts

```sql
-- Task in database
INSERT INTO tasks (
    id,
    name,
    project_id,
    section_id,           -- Project section
    position,             -- Position in project
    my_tasks_section_id,  -- My Tasks section
    my_tasks_position,    -- Position in My Tasks
    assignee_id
) VALUES (
    'task-123',
    'Design homepage',
    'proj-marketing',
    'proj-sec-todo',      -- Project "To Do" section
    5,                    -- 5th position in project
    'my-sec-today',       -- My Tasks "Do Today" section
    2,                    -- 2nd position in My Tasks
    'user-456'
);
```

**In Project View:**
```
Project "Marketing" → Section "To Do" → Position 5
├─ Task 1
├─ Task 2
├─ Task 3
├─ Task 4
├─ Design homepage ← (position 5)
└─ Task 6
```

**In My Tasks View:**
```
My Tasks → Section "Do Today" → Position 2
├─ Task A
├─ Design homepage ← (position 2)
└─ Task C
```

**Same task, different positions, completely independent!** ✅

---

## 📝 Code Changes

### 1. Task Model (`app/Models/Task.php`)

**Added to fillable:**
```php
protected $fillable = [
    // ... existing fields
    'my_tasks_section_id',
    'my_tasks_position',
];
```

**Added to casts:**
```php
protected $casts = [
    // ... existing casts
    'my_tasks_position' => 'integer',
];
```

**Added relationship:**
```php
public function myTasksSection(): BelongsTo
{
    return $this->belongsTo(Section::class, 'my_tasks_section_id');
}
```

---

### 2. MyTasksService (`app/Services/MyTasksService.php`)

**Updated getUserTasks() query:**
```php
$query = Task::where(function ($q) use ($user, $myTasksSectionIds) {
        $q->where('assignee_id', $user->id)
          ->orWhere(function ($subQ) use ($user, $myTasksSectionIds) {
              $subQ->where('creator_id', $user->id)
                   ->whereIn('my_tasks_section_id', $myTasksSectionIds); // ✅ Changed
          });
    })
    ->select([
        // ... other fields
        'tasks.my_tasks_section_id',  // ✅ Added
        'tasks.my_tasks_position',    // ✅ Added
    ])
    ->with([
        // ... other relations
        'myTasksSection:id,name',     // ✅ Added
    ]);
```

**Updated default sorting:**
```php
if (empty($sort)) {
    $query->orderBy('my_tasks_section_id', 'asc')    // ✅ Changed
          ->orderBy('my_tasks_position', 'asc')      // ✅ Changed
          ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
          ->orderBy('due_date', 'asc');
}
```

**Updated createTask():**
```php
public function createTask(User $user, array $data): Task
{
    $myTasksSectionId = $data['section_id'] ?? null;
    $myTasksPosition = 0;

    if ($myTasksSectionId) {
        $maxPosition = Task::where('my_tasks_section_id', $myTasksSectionId)
            ->where('assignee_id', $user->id)
            ->max('my_tasks_position');
        $myTasksPosition = ($maxPosition ?? -1) + 1;
    }

    $task = Task::create([
        'name'                 => $data['name'],
        'section_id'           => null,  // No project section
        'my_tasks_section_id'  => $myTasksSectionId,  // ✅ My Tasks section
        'position'             => null,  // No project position
        'my_tasks_position'    => $myTasksPosition,   // ✅ My Tasks position
        // ... other fields
    ]);

    return $task->load(['myTasksSection:id,name']);
}
```

---

### 3. TaskService (`app/Services/TaskService.php`)

**Added new method for My Tasks moves:**
```php
public function moveTaskInMyTasks(Task $task, Section $myTasksSection, ?int $position = null): Task
{
    return DB::transaction(function () use ($task, $myTasksSection, $position) {
        $oldMyTasksSectionId = $task->my_tasks_section_id;
        $oldMyTasksPosition = $task->my_tasks_position;

        // Get all tasks in target My Tasks section
        $targetSectionTasks = Task::where('my_tasks_section_id', $myTasksSection->id)
            ->where('assignee_id', $task->assignee_id)
            ->where('id', '!=', $task->id)
            ->orderBy('my_tasks_position')
            ->get();

        // Shift positions if needed
        if ($position !== null) {
            foreach ($targetSectionTasks as $index => $otherTask) {
                if ($index >= $position) {
                    $otherTask->update(['my_tasks_position' => $index + 1]);
                } else {
                    $otherTask->update(['my_tasks_position' => $index]);
                }
            }
        } else {
            $position = $targetSectionTasks->count();
        }

        // Update task's My Tasks section and position
        $task->update([
            'my_tasks_section_id' => $myTasksSection->id,
            'my_tasks_position' => $position,
        ]);

        // Log activity
        if ($oldMyTasksSectionId !== $myTasksSection->id) {
            TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'activity_type' => 'updated',
                'field_name' => 'my_tasks_section_id',
                'old_value' => $oldMyTasksSectionId,
                'new_value' => $myTasksSection->id,
            ]);
        }

        return $task->fresh();
    });
}
```

---

### 4. TaskController (`app/Http/Controllers/TaskController.php`)

**Updated move() method:**
```php
public function move(MoveTaskRequest $request, Task $task): JsonResponse
{
    $this->authorize('update', $task);
    $validated = $request->validated();

    try {
        // Check if this is a My Tasks move
        if (isset($validated['my_tasks_section_id'])) {
            $myTasksSection = Section::findOrFail($validated['my_tasks_section_id']);
            
            // Verify it's a My Tasks section
            if (!$myTasksSection->is_my_tasks || $myTasksSection->user_id !== auth()->id()) {
                return response()->json(['message' => 'Invalid My Tasks section'], 422);
            }
            
            $movedTask = $this->taskService->moveTaskInMyTasks(
                $task, 
                $myTasksSection, 
                $validated['my_tasks_position'] ?? null
            );
            
            return response()->json([
                'data' => $movedTask->load([
                    'assignee:id,name,email,avatar',
                    'section:id,name',
                    'myTasksSection:id,name',  // ✅ Added
                    'tags:id,name,color',
                ]),
            ]);
        }
        
        // Otherwise, it's a project section move
        if (isset($validated['section_id'])) {
            $section = Section::findOrFail($validated['section_id']);
            $movedTask = $this->taskService->moveTask($task, $section, $validated['position'] ?? null);
        } else {
            $movedTask = $this->taskService->repositionTask($task, $validated['position'] ?? null);
        }

        return response()->json([
            'data' => $movedTask->load([
                'assignee:id,name,email,avatar',
                'section:id,name',
                'myTasksSection:id,name',  // ✅ Added
                'tags:id,name,color',
            ]),
        ]);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 422);
    }
}
```

---

### 5. MoveTaskRequest (`app/Http/Requests/MoveTaskRequest.php`)

**Added validation rules:**
```php
public function rules(): array
{
    return [
        'section_id'           => 'nullable|exists:sections,id',
        'project_id'           => 'nullable|exists:projects,id',
        'position'             => 'nullable|integer|min:0',
        'my_tasks_section_id'  => 'nullable|exists:sections,id',  // ✅ Added
        'my_tasks_position'    => 'nullable|integer|min:0',       // ✅ Added
    ];
}
```

---

### 6. Frontend Store (`resources/js/Stores/useMyTasksStore.ts`)

**Updated moveTask():**
```typescript
async function moveTask(taskId: string, toSectionId: string, position: number = 0) {
  const task = tasks.value.find(t => t.id === taskId);
  if (!task) return;

  const originalState = JSON.parse(JSON.stringify(tasks.value));

  try {
    // Optimistic update
    task.my_tasks_section_id = toSectionId;      // ✅ Changed
    task.my_tasks_position = position;           // ✅ Changed

    // ... array manipulation logic ...

    // API call with My Tasks specific fields
    const response = await fetch(`/api/tasks/${taskId}/move`, {
      method: 'POST',
      headers: { /* ... */ },
      body: JSON.stringify({ 
        my_tasks_section_id: toSectionId,   // ✅ Changed
        my_tasks_position: position         // ✅ Changed
      }),
    });

    if (!response.ok) throw new Error('Failed to move task');

    const result = await response.json();
    if (result.data) {
      Object.assign(task, result.data);
    }
  } catch (err) {
    // Rollback
    tasks.value = originalState;
    throw err;
  }
}
```

---

## 🎯 Key Benefits

### 1. **Independent Positioning**
```
Same task can be:
- 5th in project "Marketing" → "To Do" section
- 2nd in My Tasks → "Do Today" section
```

### 2. **No Conflicts**
```
Moving task in My Tasks → Doesn't affect project position
Moving task in Project → Doesn't affect My Tasks position
```

### 3. **Proper Sorting**
```sql
-- My Tasks query
SELECT * FROM tasks
WHERE assignee_id = 'user-id'
ORDER BY my_tasks_section_id, my_tasks_position;

-- Project query
SELECT * FROM tasks
WHERE project_id = 'proj-id'
ORDER BY section_id, position;
```

### 4. **Clean Data Model**
```
tasks table:
├─ section_id + position       → Project context
└─ my_tasks_section_id + my_tasks_position → My Tasks context
```

---

## 📊 Data Migration

### Existing Tasks Handling

All existing tasks assigned to users were automatically migrated:
- `my_tasks_section_id` → Set to user's "Recently Assigned" section
- `my_tasks_position` → Calculated based on creation order

**Migration Script:**
```php
// For each task with assignee
foreach ($tasks as $task) {
    $recentlyAssignedSection = Section::myTasks($task->assignee_id)
        ->where('name', 'Recently Assigned')
        ->first();

    $maxPosition = Task::where('my_tasks_section_id', $recentlyAssignedSection->id)
        ->where('assignee_id', $task->assignee_id)
        ->max('my_tasks_position');

    $task->update([
        'my_tasks_section_id' => $recentlyAssignedSection->id,
        'my_tasks_position' => ($maxPosition ?? -1) + 1,
    ]);
}
```

---

## 🧪 Testing Scenarios

### Scenario 1: Create Task in My Tasks
```
Action: Create task in My Tasks "Do Today"
Result:
  - my_tasks_section_id = "Do Today" section ID
  - my_tasks_position = next available position
  - section_id = NULL (no project)
  - position = NULL (no project)
```

### Scenario 2: Assign Project Task to User
```
Action: Assign existing project task to user
Result:
  - section_id = project section (unchanged)
  - position = project position (unchanged)
  - my_tasks_section_id = "Recently Assigned" section
  - my_tasks_position = next available position
```

### Scenario 3: Move Task in My Tasks
```
Action: Drag task from "Do Today" to "Do Later"
API Call: POST /api/tasks/{id}/move
Body: {
  "my_tasks_section_id": "do-later-section-id",
  "my_tasks_position": 3
}
Result:
  - my_tasks_section_id = "Do Later" section
  - my_tasks_position = 3
  - section_id = unchanged (project section)
  - position = unchanged (project position)
```

### Scenario 4: Move Task in Project
```
Action: Drag task from "To Do" to "In Progress" in project
API Call: POST /api/tasks/{id}/move
Body: {
  "section_id": "in-progress-section-id",
  "position": 2
}
Result:
  - section_id = "In Progress" section
  - position = 2
  - my_tasks_section_id = unchanged (My Tasks section)
  - my_tasks_position = unchanged (My Tasks position)
```

---

## ✅ Verification Checklist

- [x] Migration created and run successfully
- [x] Data migration populated existing tasks
- [x] Task model updated with new fields
- [x] MyTasksService uses new columns
- [x] TaskService has moveTaskInMyTasks() method
- [x] TaskController handles both move types
- [x] MoveTaskRequest validates new fields
- [x] Frontend store sends correct fields
- [x] Drag-drop works in My Tasks
- [x] Drag-drop works in Projects
- [x] Positions are independent
- [x] No conflicts between views

---

## 🎨 User Experience

### Before Fix ❌
```
User drags task in My Tasks
  ↓
Task position changes in My Tasks
  ↓
User opens project
  ↓
Task position ALSO changed in project! 😱
  ↓
Confusion and data corruption
```

### After Fix ✅
```
User drags task in My Tasks
  ↓
Task position changes in My Tasks
  ↓
User opens project
  ↓
Task position UNCHANGED in project! 😊
  ↓
Each view maintains its own order
```

---

## 📚 Summary

**Problem:** Single `section_id` column caused conflicts between project and My Tasks views.

**Solution:** Added separate columns:
- `my_tasks_section_id` - My Tasks section
- `my_tasks_position` - My Tasks position

**Result:** Tasks can now exist in both contexts independently with proper sorting and positioning in each view.

**Files Modified:**
1. Migration: `2026_05_26_094912_add_my_tasks_section_to_tasks_table.php`
2. Data Migration: `2026_05_26_095843_populate_my_tasks_section_for_existing_tasks.php`
3. Model: `app/Models/Task.php`
4. Service: `app/Services/MyTasksService.php`
5. Service: `app/Services/TaskService.php`
6. Controller: `app/Http/Controllers/TaskController.php`
7. Request: `app/Http/Requests/MoveTaskRequest.php`
8. Store: `resources/js/Stores/useMyTasksStore.ts`

**All changes are backward compatible and existing data has been migrated automatically!** ✅
