# Timezone Migration Guide for Existing Components

## 🎯 Purpose

This guide helps you update existing Vue components to use the new timezone utilities.

## 📦 Step-by-Step Migration

### Step 1: Import the Composable

Add this to your component's `<script setup>`:

```javascript
import { useTimezone } from '@/Composables/useTimezone'

const { 
  formatDateTime,    // For timestamps (created_at, updated_at, etc.)
  formatDateField,   // For date-only fields (due_date, start_date)
  formatRelative,    // For "2 hours ago" format
  checkOverdue,      // Check if task is overdue
  checkIsToday       // Check if date is today
} = useTimezone()
```

### Step 2: Replace Date Formatting

#### Before (Old Code)
```vue
<template>
  <div>
    <!-- ❌ Old way - inconsistent and timezone-unaware -->
    <p>{{ new Date(comment.created_at).toLocaleString() }}</p>
    <p>{{ new Date(task.due_date).toLocaleDateString() }}</p>
  </div>
</template>
```

#### After (New Code)
```vue
<template>
  <div>
    <!-- ✅ New way - consistent and timezone-aware -->
    <p>{{ formatDateTime(comment.created_at) }}</p>
    <p>{{ formatDateField(task.due_date) }}</p>
  </div>
</template>

<script setup>
import { useTimezone } from '@/Composables/useTimezone'
const { formatDateTime, formatDateField } = useTimezone()
</script>
```

## 🔄 Common Migration Patterns

### Pattern 1: Comment Timestamps

#### Before
```vue
<template>
  <div class="comment">
    <p>{{ comment.content }}</p>
    <span class="text-sm text-gray-500">
      {{ new Date(comment.created_at).toLocaleString() }}
    </span>
  </div>
</template>
```

#### After
```vue
<template>
  <div class="comment">
    <p>{{ comment.content }}</p>
    <span class="text-sm text-gray-500">
      {{ formatRelative(comment.created_at) }}
    </span>
  </div>
</template>

<script setup>
import { useTimezone } from '@/Composables/useTimezone'
const { formatRelative } = useTimezone()
</script>
```

### Pattern 2: Task Due Dates

#### Before
```vue
<template>
  <div class="task">
    <p>{{ task.name }}</p>
    <p v-if="task.due_date">
      Due: {{ new Date(task.due_date).toLocaleDateString() }}
    </p>
  </div>
</template>
```

#### After
```vue
<template>
  <div class="task">
    <p>{{ task.name }}</p>
    <p v-if="task.due_date">
      Due: {{ formatDateField(task.due_date) }}
      <span v-if="checkOverdue(task.due_date, task.status)" class="text-red-500">
        (Overdue)
      </span>
    </p>
  </div>
</template>

<script setup>
import { useTimezone } from '@/Composables/useTimezone'
const { formatDateField, checkOverdue } = useTimezone()
</script>
```

### Pattern 3: Activity Feed

#### Before
```vue
<template>
  <div v-for="activity in activities" :key="activity.id">
    <p>{{ activity.description }}</p>
    <span class="text-sm">
      {{ formatActivityTime(activity.created_at) }}
    </span>
  </div>
</template>

<script setup>
const formatActivityTime = (timestamp) => {
  const date = new Date(timestamp)
  const now = new Date()
  const diff = now - date
  const minutes = Math.floor(diff / 60000)
  
  if (minutes < 60) return `${minutes} minutes ago`
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `${hours} hours ago`
  return date.toLocaleDateString()
}
</script>
```

#### After
```vue
<template>
  <div v-for="activity in activities" :key="activity.id">
    <p>{{ activity.description }}</p>
    <span class="text-sm">
      {{ formatRelative(activity.created_at) }}
    </span>
  </div>
</template>

<script setup>
import { useTimezone } from '@/Composables/useTimezone'
const { formatRelative } = useTimezone()
</script>
```

### Pattern 4: Notifications

#### Before
```vue
<template>
  <div v-for="notification in notifications" :key="notification.id">
    <p>{{ notification.title }}</p>
    <span class="text-xs">
      {{ new Date(notification.created_at).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }) }}
    </span>
  </div>
</template>
```

#### After
```vue
<template>
  <div v-for="notification in notifications" :key="notification.id">
    <p>{{ notification.title }}</p>
    <span class="text-xs">
      {{ formatDateTime(notification.created_at) }}
    </span>
  </div>
</template>

<script setup>
import { useTimezone } from '@/Composables/useTimezone'
const { formatDateTime } = useTimezone()
</script>
```

### Pattern 5: Task Completion

#### Before
```vue
<template>
  <div v-if="task.completed_at">
    <p>Completed on {{ new Date(task.completed_at).toLocaleDateString() }}</p>
  </div>
</template>
```

#### After
```vue
<template>
  <div v-if="task.completed_at">
    <p>Completed {{ formatRelative(task.completed_at) }}</p>
    <p class="text-xs text-gray-500">
      {{ formatDateTime(task.completed_at) }}
    </p>
  </div>
</template>

<script setup>
import { useTimezone } from '@/Composables/useTimezone'
const { formatDateTime, formatRelative } = useTimezone()
</script>
```

### Pattern 6: Calendar/Timeline View

#### Before
```vue
<template>
  <div v-for="task in tasks" :key="task.id">
    <div class="task-card">
      <p>{{ task.name }}</p>
      <p class="text-sm">
        {{ new Date(task.start_date).toLocaleDateString() }} - 
        {{ new Date(task.due_date).toLocaleDateString() }}
      </p>
    </div>
  </div>
</template>
```

#### After
```vue
<template>
  <div v-for="task in tasks" :key="task.id">
    <div class="task-card">
      <p>{{ task.name }}</p>
      <p class="text-sm">
        {{ formatDateField(task.start_date) }} - 
        {{ formatDateField(task.due_date) }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { useTimezone } from '@/Composables/useTimezone'
const { formatDateField } = useTimezone()
</script>
```

### Pattern 7: Computed Properties

#### Before
```vue
<script setup>
import { computed } from 'vue'

const props = defineProps(['task'])

const formattedDueDate = computed(() => {
  if (!props.task.due_date) return ''
  return new Date(props.task.due_date).toLocaleDateString()
})

const isOverdue = computed(() => {
  if (!props.task.due_date || props.task.status === 'complete') return false
  return new Date(props.task.due_date) < new Date()
})
</script>
```

#### After
```vue
<script setup>
import { computed } from 'vue'
import { useTimezone } from '@/Composables/useTimezone'

const props = defineProps(['task'])
const { formatDateField, checkOverdue } = useTimezone()

const formattedDueDate = computed(() => {
  return formatDateField(props.task.due_date)
})

const isOverdue = computed(() => {
  return checkOverdue(props.task.due_date, props.task.status)
})
</script>
```

### Pattern 8: Date Filters/Comparisons

#### Before
```vue
<script setup>
const filterOverdueTasks = (tasks) => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  
  return tasks.filter(task => {
    if (!task.due_date || task.status === 'complete') return false
    const dueDate = new Date(task.due_date)
    return dueDate < today
  })
}
</script>
```

#### After
```vue
<script setup>
import { useTimezone } from '@/Composables/useTimezone'

const { checkOverdue } = useTimezone()

const filterOverdueTasks = (tasks) => {
  return tasks.filter(task => checkOverdue(task.due_date, task.status))
}
</script>
```

## 🔍 Finding Components to Update

### Search Commands

```bash
# Find components using new Date()
grep -r "new Date" resources/js/Components/

# Find components using toLocaleString
grep -r "toLocaleString" resources/js/Components/

# Find components using toLocaleDateString
grep -r "toLocaleDateString" resources/js/Components/

# Find components with date formatting
grep -r "\.toLocale" resources/js/

# Find components with custom date logic
grep -r "getDate\|getMonth\|getFullYear" resources/js/Components/
```

### Priority Order

1. **High Priority** (User-facing, frequently used)
   - Comments section
   - Task list
   - Notifications
   - Activity feed
   - Dashboard

2. **Medium Priority** (Important but less frequent)
   - Task details
   - Project activities
   - User profile
   - Timeline view
   - Calendar view

3. **Low Priority** (Admin/reports)
   - Reports
   - Search results
   - Filters
   - Exports

## ✅ Verification Checklist

After updating each component:

- [ ] Import `useTimezone` composable
- [ ] Replace all `new Date()` formatting with utilities
- [ ] Use `formatDateField()` for date-only fields
- [ ] Use `formatDateTime()` or `formatRelative()` for timestamps
- [ ] Handle null/undefined values
- [ ] Test component renders correctly
- [ ] Test with different timezones
- [ ] Remove old date formatting code
- [ ] Update any related tests

## 🧪 Testing Each Component

### Manual Testing Steps

1. **Open component in browser**
2. **Check DevTools console for errors**
3. **Verify dates display correctly**
4. **Change browser timezone** (DevTools → Sensors → Location)
5. **Verify timestamps change, but date-only fields don't**
6. **Test with null/undefined dates**
7. **Test edge cases** (overdue, today, future dates)

### Browser Timezone Testing

```javascript
// In browser console, change timezone:
// 1. Open DevTools
// 2. Press Ctrl+Shift+P (Cmd+Shift+P on Mac)
// 3. Type "sensors"
// 4. Select "Show Sensors"
// 5. Change "Location" dropdown to different timezones

// Test with these timezones:
- America/New_York (EST/EDT, UTC-5/-4)
- Asia/Karachi (PKT, UTC+5)
- Europe/London (GMT/BST, UTC+0/+1)
- Asia/Tokyo (JST, UTC+9)
- Australia/Sydney (AEDT, UTC+11)
```

## 📊 Progress Tracking

Create a spreadsheet or checklist to track migration:

| Component | Location | Priority | Status | Tested | Notes |
|-----------|----------|----------|--------|--------|-------|
| CommentList.vue | Components/Tasks/ | High | ✅ Done | ✅ Yes | - |
| TaskCard.vue | Components/Tasks/ | High | 🔄 In Progress | ❌ No | - |
| NotificationList.vue | Components/Layout/ | High | ⏳ Pending | ❌ No | - |

## 🐛 Common Issues

### Issue 1: "formatDateTime is not defined"
**Cause:** Forgot to import composable  
**Solution:** Add `import { useTimezone } from '@/Composables/useTimezone'`

### Issue 2: Dates showing as "Invalid Date"
**Cause:** Null or malformed date string  
**Solution:** Add null check: `task.due_date ? formatDateField(task.due_date) : 'No due date'`

### Issue 3: Due dates shifting by one day
**Cause:** Using `formatDateTime()` on date-only fields  
**Solution:** Use `formatDateField()` instead

### Issue 4: Relative time not updating
**Cause:** Component not re-rendering  
**Solution:** Use computed property or reactive ref

## 💡 Tips

1. **Start with high-priority components** - Get the most visible changes done first
2. **Test thoroughly** - Change browser timezone and verify behavior
3. **Update one component at a time** - Easier to debug issues
4. **Keep old code commented** - Easy to rollback if needed
5. **Document any custom logic** - Help future developers understand
6. **Use consistent formatting** - Stick to the provided utilities
7. **Handle edge cases** - Null, undefined, invalid dates

## 📝 Example Pull Request Description

```markdown
## Timezone Migration: [Component Name]

### Changes
- Replaced manual date formatting with timezone utilities
- Updated timestamp displays to use `formatDateTime()`
- Updated date-only fields to use `formatDateField()`
- Added null handling for date fields

### Testing
- [x] Tested with America/New_York timezone
- [x] Tested with Asia/Karachi timezone
- [x] Verified date-only fields don't shift
- [x] Verified timestamps show correct local time
- [x] Tested with null/undefined dates

### Screenshots
[Before/After screenshots showing timezone-aware formatting]

### Related
- Part of timezone implementation (#123)
- See TIMEZONE_MIGRATION_GUIDE.md for details
```

## 🎓 Training Resources

- **Quick Reference:** `TIMEZONE_QUICK_REFERENCE.md`
- **Full Documentation:** `TIMEZONE_IMPLEMENTATION.md`
- **Example Component:** `resources/js/Components/Examples/TimezoneExamples.vue`
- **This Guide:** `TIMEZONE_MIGRATION_GUIDE.md`

---

**Remember:** The goal is consistent, timezone-aware date/time handling across the entire application!
