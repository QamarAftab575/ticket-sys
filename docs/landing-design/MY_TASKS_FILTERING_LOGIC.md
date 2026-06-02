# My Tasks Filtering Logic

## 📋 What Shows in My Tasks?

My Tasks displays **two types of tasks**:

### 1. ✅ Tasks Assigned to You
**Any task where you are the assignee, regardless of where it was created**

Examples:
- Task created in Project "Marketing" and assigned to you → **Shows in My Tasks**
- Task created in Project "Website Redesign" and assigned to you → **Shows in My Tasks**
- Task created by another user in any project and assigned to you → **Shows in My Tasks**
- Task created in My Tasks page and assigned to you → **Shows in My Tasks**

### 2. ✅ Tasks You Created in My Tasks Sections
**Tasks you created directly in My Tasks, even if not assigned to you**

Examples:
- Task created in My Tasks "Do Today" section, no assignee → **Shows in My Tasks**
- Task created in My Tasks "Do Later" section, assigned to someone else → **Shows in My Tasks**
- Task created in My Tasks, then moved to a project → **Shows in My Tasks** (if still in My Tasks section)

---

## 🔍 Query Logic

### SQL Query (Simplified)
```sql
SELECT * FROM tasks
WHERE 
  -- Condition 1: Tasks assigned to you
  assignee_id = {current_user_id}
  
  OR
  
  -- Condition 2: Tasks you created in My Tasks sections
  (
    creator_id = {current_user_id}
    AND section_id IN (
      SELECT id FROM sections 
      WHERE user_id = {current_user_id} 
      AND is_my_tasks = TRUE
    )
  )
```

### PHP Implementation
```php
// app/Services/MyTasksService.php

public function getUserTasks(User $user, ...): array
{
    // Get user's My Tasks section IDs
    $myTasksSectionIds = Section::myTasks($user->id)
        ->pluck('id')
        ->toArray();

    // Build query with two conditions
    $query = Task::where(function ($q) use ($user, $myTasksSectionIds) {
        // Condition 1: Assigned to user
        $q->where('assignee_id', $user->id)
          
          // OR Condition 2: Created by user in My Tasks sections
          ->orWhere(function ($subQ) use ($user, $myTasksSectionIds) {
              $subQ->where('creator_id', $user->id)
                   ->whereIn('section_id', $myTasksSectionIds);
          });
    });

    // ... rest of query (sorting, pagination, etc.)
}
```

---

## 📊 Examples

### Scenario 1: Project Task Assigned to You

**Task Details:**
```
Task: "Design homepage mockup"
Created by: John (another user)
Created in: Project "Website Redesign" → Section "To Do"
Assigned to: You
```

**Result:** ✅ **Shows in My Tasks**
- Reason: `assignee_id = your_id`
- Shows in: My Tasks (in appropriate section based on your preferences)
- Also shows in: Project "Website Redesign" → Section "To Do"

---

### Scenario 2: Task You Created in My Tasks

**Task Details:**
```
Task: "Review quarterly goals"
Created by: You
Created in: My Tasks → Section "Do Today"
Assigned to: Nobody (null)
```

**Result:** ✅ **Shows in My Tasks**
- Reason: `creator_id = your_id` AND `section_id` is in your My Tasks sections
- Shows in: My Tasks → Section "Do Today"
- Does NOT show in: Any project (no project_id)

---

### Scenario 3: Task You Created in My Tasks, Assigned to Someone Else

**Task Details:**
```
Task: "Prepare presentation"
Created by: You
Created in: My Tasks → Section "Do Later"
Assigned to: Sarah
```

**Result:** ✅ **Shows in My Tasks**
- Reason: `creator_id = your_id` AND `section_id` is in your My Tasks sections
- Shows in: My Tasks → Section "Do Later"
- Also shows in: Sarah's My Tasks (because she's the assignee)

---

### Scenario 4: Task You Created in a Project

**Task Details:**
```
Task: "Update documentation"
Created by: You
Created in: Project "Marketing" → Section "Backlog"
Assigned to: Nobody (null)
```

**Result:** ❌ **Does NOT show in My Tasks**
- Reason: `assignee_id ≠ your_id` AND `section_id` is NOT in your My Tasks sections
- Shows in: Project "Marketing" → Section "Backlog" only
- To make it show in My Tasks: Assign it to yourself

---

### Scenario 5: Task You Created in a Project, Assigned to Yourself

**Task Details:**
```
Task: "Fix login bug"
Created by: You
Created in: Project "Website Redesign" → Section "In Progress"
Assigned to: You
```

**Result:** ✅ **Shows in My Tasks**
- Reason: `assignee_id = your_id`
- Shows in: My Tasks (in appropriate section)
- Also shows in: Project "Website Redesign" → Section "In Progress"

---

## 🎯 Key Points

### 1. **Assignment is Primary**
If a task is assigned to you, it **always** shows in My Tasks, regardless of:
- Who created it
- Which project it belongs to
- Which section it's in

### 2. **My Tasks Creation is Secondary**
If you created a task in a My Tasks section, it shows in My Tasks even if:
- Not assigned to anyone
- Assigned to someone else
- Later moved to a project

### 3. **Project Tasks Need Assignment**
Tasks created in projects only show in My Tasks if:
- They are assigned to you
- OR you move them to a My Tasks section

### 4. **Section Context Matters**
The same task can appear in different sections depending on context:
- In My Tasks: Shows in your personal sections (e.g., "Do Today")
- In Project: Shows in project sections (e.g., "In Progress")

---

## 🔄 Task Lifecycle Examples

### Example 1: Project Task → My Tasks

**Step 1:** Task created in Project "Marketing"
```
Task: "Write blog post"
Section: "Backlog" (project section)
Assigned to: Nobody
Shows in: Project "Marketing" only
```

**Step 2:** Task assigned to you
```
Task: "Write blog post"
Section: "Backlog" (project section)
Assigned to: You
Shows in: Project "Marketing" + My Tasks ✅
```

---

### Example 2: My Tasks → Project

**Step 1:** Task created in My Tasks
```
Task: "Research competitors"
Section: "Do Today" (My Tasks section)
Assigned to: You
Project: None
Shows in: My Tasks only
```

**Step 2:** Task moved to project
```
Task: "Research competitors"
Section: "To Do" (project section)
Assigned to: You
Project: "Marketing"
Shows in: My Tasks + Project "Marketing" ✅
```

---

### Example 3: Unassigned My Tasks Task

**Step 1:** Task created in My Tasks, no assignee
```
Task: "Plan vacation"
Section: "Do Later" (My Tasks section)
Assigned to: Nobody
Shows in: My Tasks only ✅ (because you created it in My Tasks)
```

**Step 2:** Task assigned to someone else
```
Task: "Plan vacation"
Section: "Do Later" (My Tasks section)
Assigned to: Sarah
Shows in: Your My Tasks ✅ (you created it) + Sarah's My Tasks ✅ (she's assigned)
```

---

## 🛠️ Technical Implementation

### Database Query Breakdown

```php
// Get user's My Tasks section IDs
$myTasksSectionIds = Section::myTasks($user->id)->pluck('id')->toArray();
// Result: ['sec-1', 'sec-2', 'sec-3', 'sec-4']

// Build query
Task::where(function ($q) use ($user, $myTasksSectionIds) {
    // Condition 1: Tasks assigned to user
    $q->where('assignee_id', $user->id)
    
    // OR Condition 2: Tasks created by user in My Tasks sections
    ->orWhere(function ($subQ) use ($user, $myTasksSectionIds) {
        $subQ->where('creator_id', $user->id)
             ->whereIn('section_id', $myTasksSectionIds);
    });
})
```

### Generated SQL

```sql
SELECT * FROM tasks
WHERE (
  assignee_id = 'user-123'
  OR (
    creator_id = 'user-123'
    AND section_id IN ('sec-1', 'sec-2', 'sec-3', 'sec-4')
  )
)
ORDER BY section_id, position, due_date
```

---

## ✅ Testing Checklist

- [x] Project task assigned to me → Shows in My Tasks
- [x] Project task not assigned to me → Does NOT show in My Tasks
- [x] Project task I created but not assigned to me → Does NOT show in My Tasks
- [x] Project task I created and assigned to me → Shows in My Tasks
- [x] My Tasks task with no assignee → Shows in My Tasks
- [x] My Tasks task assigned to me → Shows in My Tasks
- [x] My Tasks task assigned to someone else → Shows in My Tasks (I created it)
- [x] Task moved from My Tasks to project → Shows in both
- [x] Task moved from project to My Tasks → Shows in both (if assigned to me)

---

## 🎨 User Experience

### What Users See

**My Tasks Page:**
```
Do Today
  ├─ Design homepage mockup (from Project "Website")
  ├─ Review quarterly goals (personal task)
  └─ Fix login bug (from Project "Website")

Do Next Week
  ├─ Write blog post (from Project "Marketing")
  └─ Prepare presentation (personal task, assigned to Sarah)

Do Later
  ├─ Research competitors (personal task)
  └─ Update documentation (from Project "Marketing")
```

**Project "Website" Page:**
```
To Do
  └─ Design homepage mockup

In Progress
  └─ Fix login bug

Done
  └─ (completed tasks)
```

**Key Insight:** The same task ("Design homepage mockup") appears in both views but in different sections, maintaining independent organization.

---

## 📝 Summary

**My Tasks shows:**
1. ✅ All tasks assigned to you (from anywhere)
2. ✅ All tasks you created in My Tasks sections (even if not assigned to you)

**My Tasks does NOT show:**
- ❌ Tasks created in projects that are not assigned to you
- ❌ Tasks created by others in projects that are not assigned to you

This logic ensures:
- You see all work assigned to you
- You see all personal tasks you created
- You don't see irrelevant project tasks
- You maintain control over your personal task list
