# Testing My Tasks Query Fix

## Issue
Project tasks assigned to users weren't showing in My Tasks because:
1. They had `my_tasks_section_id = NULL`
2. The query was filtering them out

## Solution Applied

### 1. Fixed Query Logic
```php
// OLD (BROKEN):
$q->where('assignee_id', $user->id)
  ->orWhere(function ($subQ) use ($user, $myTasksSectionIds) {
      $subQ->where('creator_id', $user->id)
           ->whereIn('my_tasks_section_id', $myTasksSectionIds); // ❌ Filters out NULL
  });

// NEW (FIXED):
$q->where('assignee_id', $user->id)  // ✅ Gets ALL assigned tasks
  ->orWhere(function ($subQ) use ($user, $myTasksSectionIds) {
      $subQ->where('creator_id', $user->id)
           ->whereIn('my_tasks_section_id', $myTasksSectionIds)
           ->whereNotNull('my_tasks_section_id'); // ✅ Only for My Tasks created tasks
  });
```

### 2. Auto-Assign My Tasks Section
```php
// After fetching tasks, auto-assign section for tasks without one
foreach ($items as $task) {
    if (!$task->my_tasks_section_id && $task->assignee_id === $user->id) {
        $task->update([
            'my_tasks_section_id' => $recentlyAssignedSection->id,
            'my_tasks_position' => ($maxPosition ?? -1) + 1,
        ]);
    }
}
```

### 3. Fixed Sorting
```php
// OLD (BROKEN):
$query->orderBy('my_tasks_section_id', 'asc')  // ❌ NULL values go first
      ->orderBy('my_tasks_position', 'asc');

// NEW (FIXED):
$query->orderByRaw("COALESCE(my_tasks_section_id, '{$recentlyAssignedId}') ASC")
      ->orderByRaw('COALESCE(my_tasks_position, 999999) ASC');
// ✅ NULL values treated as "Recently Assigned" section
```

## Testing Steps

### 1. Check Database
```sql
-- Check if you have tasks assigned to you
SELECT id, name, assignee_id, project_id, section_id, my_tasks_section_id
FROM tasks
WHERE assignee_id = 'your-user-id'
LIMIT 10;

-- Expected: Some tasks with my_tasks_section_id = NULL
```

### 2. Visit My Tasks Page
```
http://127.0.0.1:8001/my-tasks
```

**Expected Result:**
- ✅ All tasks assigned to you should now show
- ✅ Project tasks appear in "Recently Assigned" section
- ✅ Tasks you created in My Tasks appear in their sections

### 3. Check Browser Console
```javascript
// Should see no errors
// Tasks should have my_tasks_section_id populated after first load
```

### 4. Verify Auto-Assignment
```sql
-- After visiting My Tasks, check again
SELECT id, name, assignee_id, my_tasks_section_id
FROM tasks
WHERE assignee_id = 'your-user-id'
AND my_tasks_section_id IS NOT NULL;

-- Expected: All your tasks now have my_tasks_section_id
```

## What Happens Now

### First Time Loading My Tasks
1. Query fetches ALL tasks assigned to you (including NULL my_tasks_section_id)
2. Auto-assignment runs for tasks without my_tasks_section_id
3. Tasks are updated with "Recently Assigned" section
4. Tasks display correctly

### Subsequent Loads
1. Query fetches tasks (now all have my_tasks_section_id)
2. No auto-assignment needed
3. Tasks display in their correct sections

### When New Project Task is Assigned
1. Task created in project with section_id but no my_tasks_section_id
2. User visits My Tasks
3. Auto-assignment runs
4. Task appears in "Recently Assigned"
5. User can drag to other My Tasks sections

## Verification Checklist

- [ ] Visit My Tasks page
- [ ] Check that project tasks show
- [ ] Verify tasks appear in "Recently Assigned"
- [ ] Try dragging task to another section
- [ ] Refresh page - task stays in new section
- [ ] Check database - my_tasks_section_id is populated
- [ ] Create new project task and assign to yourself
- [ ] Visit My Tasks - new task appears
- [ ] No console errors

## If Still Not Working

### Debug Steps

1. **Check User ID:**
```php
php artisan tinker
>>> auth()->id()
```

2. **Check Tasks:**
```php
>>> \App\Models\Task::where('assignee_id', auth()->id())->count()
```

3. **Check Sections:**
```php
>>> \App\Models\Section::myTasks(auth()->id())->get(['id', 'name'])
```

4. **Test Query Directly:**
```php
>>> $user = auth()->user();
>>> $myTasksSectionIds = \App\Models\Section::myTasks($user->id)->pluck('id')->toArray();
>>> $tasks = \App\Models\Task::where('assignee_id', $user->id)->get();
>>> $tasks->count()
```

5. **Check API Response:**
```bash
# In browser DevTools Network tab
# Look for: /my-tasks/api/tasks
# Check response JSON
```

## Expected Behavior

### Before Fix ❌
```
My Tasks Query:
WHERE assignee_id = 'user-id'
  OR (creator_id = 'user-id' AND my_tasks_section_id IN (...))

Result: Only tasks with my_tasks_section_id show
Project tasks: HIDDEN ❌
```

### After Fix ✅
```
My Tasks Query:
WHERE assignee_id = 'user-id'  ← Gets ALL assigned tasks
  OR (creator_id = 'user-id' AND my_tasks_section_id IN (...) AND my_tasks_section_id IS NOT NULL)

Result: All assigned tasks show
Project tasks: VISIBLE ✅
Auto-assigned to "Recently Assigned"
```

## Summary

The fix ensures:
1. ✅ ALL tasks assigned to you show in My Tasks
2. ✅ Tasks without my_tasks_section_id are auto-assigned
3. ✅ Sorting handles NULL values correctly
4. ✅ Project tasks appear in "Recently Assigned"
5. ✅ No data loss or conflicts
