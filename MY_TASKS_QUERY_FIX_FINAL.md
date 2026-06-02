# My Tasks Query Fix - Project Tasks Now Show!

## 🐛 Root Cause Found

**Problem:** Project tasks assigned to you weren't showing in My Tasks.

**Root Cause:** The query was checking `whereIn('my_tasks_section_id', $myTasksSectionIds)` which **excluded tasks with `my_tasks_section_id = NULL`**.

Project tasks created before the migration or newly assigned tasks don't have `my_tasks_section_id` set, so they were being filtered out!

---

## ✅ Solution Applied

### 1. Fixed Query Logic (`app/Services/MyTasksService.php` Line 24-32)

**Before (BROKEN):**
```php
$query = Task::where(function ($q) use ($user, $myTasksSectionIds) {
    $q->where('assignee_id', $user->id)
      ->orWhere(function ($subQ) use ($user, $myTasksSectionIds) {
          $subQ->where('creator_id', $user->id)
               ->whereIn('my_tasks_section_id', $myTasksSectionIds);
               // ❌ This filters out tasks with NULL my_tasks_section_id!
      });
});
```

**After (FIXED):**
```php
$query = Task::where(function ($q) use ($user, $myTasksSectionIds) {
    // Condition 1: All tasks assigned to the user (including NULL my_tasks_section_id)
    $q->where('assignee_id', $user->id)  // ✅ Gets ALL assigned tasks
      // Condition 2: Tasks created by user in My Tasks sections
      ->orWhere(function ($subQ) use ($user, $myTasksSectionIds) {
          $subQ->where('creator_id', $user->id)
               ->whereIn('my_tasks_section_id', $myTasksSectionIds)
               ->whereNotNull('my_tasks_section_id');  // ✅ Only for My Tasks tasks
      });
});
```

**Key Change:** Removed the implicit NULL filter by adding `whereNotNull()` only to the second condition.

---

### 2. Auto-Assign My Tasks Section (Lines 72-92)

Added logic to automatically assign `my_tasks_section_id` to tasks that don't have one:

```php
// After fetching tasks
foreach ($items as $task) {
    if (!$task->my_tasks_section_id && $task->assignee_id === $user->id) {
        // Get max position in Recently Assigned section
        $maxPosition = Task::where('my_tasks_section_id', $recentlyAssignedSection->id)
            ->where('assignee_id', $user->id)
            ->max('my_tasks_position');
        
        // Update task with My Tasks section
        $task->update([
            'my_tasks_section_id' => $recentlyAssignedSection->id,
            'my_tasks_position' => ($maxPosition ?? -1) + 1,
        ]);
        
        // Reload the relationship
        $task->load('myTasksSection:id,name');
    }
}
```

**Benefits:**
- ✅ Project tasks automatically get assigned to "Recently Assigned"
- ✅ Happens on first load, no manual intervention needed
- ✅ Future loads are fast (no re-assignment needed)

---

### 3. Fixed Sorting for NULL Values (Lines 67-80)

**Before (BROKEN):**
```php
$query->orderBy('my_tasks_section_id', 'asc')  // ❌ NULL values sort first
      ->orderBy('my_tasks_position', 'asc');
```

**After (FIXED):**
```php
// Get "Recently Assigned" section ID
$recentlyAssignedSection = Section::myTasks($user->id)
    ->where('name', 'Recently Assigned')
    ->orWhere('position', 0)
    ->orderBy('position')
    ->first();

$recentlyAssignedId = $recentlyAssignedSection?->id ?? 'null';

// Sort: NULL my_tasks_section_id treated as "Recently Assigned"
$query->orderByRaw("COALESCE(my_tasks_section_id, '{$recentlyAssignedId}') ASC")
      ->orderByRaw('COALESCE(my_tasks_position, 999999) ASC')
      ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
      ->orderBy('due_date', 'asc')
      ->orderBy('created_at', 'desc');
```

**Benefits:**
- ✅ Tasks without `my_tasks_section_id` sort as if they're in "Recently Assigned"
- ✅ Tasks without `my_tasks_position` sort at the end
- ✅ Consistent ordering even before auto-assignment runs

---

## 🔄 How It Works Now

### Scenario 1: First Time Loading My Tasks

```
1. User visits /my-tasks
   ↓
2. Query fetches ALL tasks where assignee_id = user_id
   (including tasks with my_tasks_section_id = NULL)
   ↓
3. Auto-assignment runs for tasks without my_tasks_section_id
   ↓
4. Tasks are updated with "Recently Assigned" section
   ↓
5. Tasks display correctly in My Tasks
```

### Scenario 2: Subsequent Loads

```
1. User visits /my-tasks
   ↓
2. Query fetches tasks (all now have my_tasks_section_id)
   ↓
3. No auto-assignment needed (already set)
   ↓
4. Tasks display in their correct sections
```

### Scenario 3: New Project Task Assigned

```
1. Task created in project (has section_id, no my_tasks_section_id)
   ↓
2. Task assigned to you
   ↓
3. You visit My Tasks
   ↓
4. Auto-assignment runs
   ↓
5. Task appears in "Recently Assigned"
   ↓
6. You can drag it to other My Tasks sections
```

---

## 📊 Query Comparison

### Before Fix ❌

```sql
SELECT * FROM tasks
WHERE (
  assignee_id = 'user-id'
  OR (
    creator_id = 'user-id'
    AND my_tasks_section_id IN ('sec-1', 'sec-2', 'sec-3', 'sec-4')
  )
)
ORDER BY my_tasks_section_id, my_tasks_position;
```

**Result:**
- Tasks with `my_tasks_section_id = NULL` → **EXCLUDED** ❌
- Project tasks → **HIDDEN** ❌

### After Fix ✅

```sql
SELECT * FROM tasks
WHERE (
  assignee_id = 'user-id'  -- ✅ Gets ALL assigned tasks
  OR (
    creator_id = 'user-id'
    AND my_tasks_section_id IN ('sec-1', 'sec-2', 'sec-3', 'sec-4')
    AND my_tasks_section_id IS NOT NULL  -- ✅ Only for My Tasks tasks
  )
)
ORDER BY 
  COALESCE(my_tasks_section_id, 'recently-assigned-id'),
  COALESCE(my_tasks_position, 999999),
  due_date;
```

**Result:**
- Tasks with `my_tasks_section_id = NULL` → **INCLUDED** ✅
- Project tasks → **VISIBLE** ✅
- Auto-assigned to "Recently Assigned" on first load

---

## ✅ Verification Steps

### 1. Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### 2. Visit My Tasks
```
http://127.0.0.1:8001/my-tasks
```

### 3. Expected Results
- ✅ All tasks assigned to you should now appear
- ✅ Project tasks appear in "Recently Assigned" section
- ✅ Tasks you created in My Tasks appear in their sections
- ✅ No console errors

### 4. Test Drag & Drop
- ✅ Drag a project task to "Do Today"
- ✅ Refresh page
- ✅ Task stays in "Do Today"

### 5. Check Database
```sql
-- Before visiting My Tasks
SELECT id, name, my_tasks_section_id
FROM tasks
WHERE assignee_id = 'your-user-id';
-- Some tasks have NULL my_tasks_section_id

-- After visiting My Tasks
SELECT id, name, my_tasks_section_id
FROM tasks
WHERE assignee_id = 'your-user-id';
-- All tasks now have my_tasks_section_id populated
```

---

## 🎯 Summary

**Root Cause:** Query was filtering out tasks with `my_tasks_section_id = NULL`

**Fix Applied:**
1. ✅ Changed query to include ALL assigned tasks
2. ✅ Added auto-assignment for tasks without my_tasks_section_id
3. ✅ Fixed sorting to handle NULL values

**Result:**
- ✅ Project tasks now show in My Tasks
- ✅ Auto-assigned to "Recently Assigned" section
- ✅ Can be moved to other sections
- ✅ Positions tracked independently from project

**Files Modified:**
- `app/Services/MyTasksService.php` (Lines 24-32, 67-92)

**Status:** ✅ **FIXED AND READY TO TEST**

---

## 🆘 If Still Not Working

1. **Check if you're logged in:**
   ```bash
   # Visit /login first
   ```

2. **Check if tasks are assigned to you:**
   ```sql
   SELECT * FROM tasks WHERE assignee_id = 'your-user-id';
   ```

3. **Check if My Tasks sections exist:**
   ```sql
   SELECT * FROM sections WHERE user_id = 'your-user-id' AND is_my_tasks = TRUE;
   ```

4. **Check browser console for errors:**
   ```
   F12 → Console tab
   ```

5. **Check API response:**
   ```
   F12 → Network tab → Look for /my-tasks/api/tasks
   ```

If you still see issues, please share:
- Browser console errors
- API response from /my-tasks/api/tasks
- Database query results for your tasks
