# Timezone Handling - Quick Reference

## 🎯 Quick Decision Guide

### When to use what?

| Field Type | Example | Use Function | Timezone Shift? |
|------------|---------|--------------|-----------------|
| **Timestamp** | `created_at`, `updated_at`, `completed_at` | `formatDateTime()` | ✅ YES |
| **Date Only** | `due_date`, `start_date` | `formatDateField()` | ❌ NO |
| **Recent Activity** | Comments, notifications | `formatRelative()` | ✅ YES |
| **Time Only** | Show just time part | `formatTime()` | ✅ YES |

## 📦 Import

```javascript
import { useTimezone } from '@/Composables/useTimezone'

const { 
  formatDateTime,      // For timestamps
  formatDateField,     // For date-only fields
  formatRelative,      // For "2 hours ago"
  checkOverdue,        // Check if task is overdue
  checkIsToday         // Check if date is today
} = useTimezone()
```

## 🔥 Common Use Cases

### 1. Display Comment Timestamp
```vue
<template>
  <div>
    <p>{{ comment.content }}</p>
    <span class="text-sm text-gray-500">
      {{ formatRelative(comment.created_at) }}
    </span>
  </div>
</template>
```

### 2. Display Task Due Date
```vue
<template>
  <div>
    <p>Due: {{ formatDateField(task.due_date) }}</p>
    <span v-if="checkOverdue(task.due_date, task.status)" class="text-red-500">
      Overdue
    </span>
  </div>
</template>
```

### 3. Display Notification
```vue
<template>
  <div>
    <p>{{ notification.title }}</p>
    <span class="text-xs text-gray-500">
      {{ formatDateTime(notification.created_at) }}
    </span>
  </div>
</template>
```

### 4. Display Activity Feed
```vue
<template>
  <div v-for="activity in activities" :key="activity.id">
    <p>{{ activity.description }}</p>
    <span class="text-sm text-gray-500">
      {{ formatRelative(activity.created_at) }}
    </span>
  </div>
</template>
```

### 5. Display Task Completion
```vue
<template>
  <div v-if="task.completed_at">
    <p>Completed {{ formatRelative(task.completed_at) }}</p>
    <p class="text-xs text-gray-500">
      {{ formatDateTime(task.completed_at) }}
    </p>
  </div>
</template>
```

## 🚫 Common Mistakes

### ❌ DON'T: Apply timezone conversion to date-only fields
```javascript
// WRONG - This will shift the date by timezone offset
formatDateTime(task.due_date) // May show Jan 19 instead of Jan 20!
```

### ✅ DO: Use formatDateField for date-only fields
```javascript
// CORRECT - Date stays consistent across timezones
formatDateField(task.due_date) // Always shows Jan 20
```

### ❌ DON'T: Use new Date() directly for display
```javascript
// WRONG - Inconsistent formatting
new Date(task.created_at).toLocaleString()
```

### ✅ DO: Use the provided utilities
```javascript
// CORRECT - Consistent formatting
formatDateTime(task.created_at)
```

### ❌ DON'T: Forget to handle null values
```javascript
// WRONG - Will error if null
formatDateTime(task.completed_at)
```

### ✅ DO: Check for null first
```javascript
// CORRECT - Safe handling
task.completed_at ? formatDateTime(task.completed_at) : 'Not completed'
```

## 📋 Field Reference

### Timestamp Fields (Use `formatDateTime` or `formatRelative`)
- ✅ `created_at`
- ✅ `updated_at`
- ✅ `deleted_at`
- ✅ `completed_at`
- ✅ `edited_at`
- ✅ `read_at`
- ✅ `last_login_at`
- ✅ `email_verified_at`

### Date-Only Fields (Use `formatDateField`)
- ✅ `due_date`
- ✅ `start_date`
- ✅ Sprint dates
- ✅ Birthdays

## 🎨 Format Examples

```javascript
const timestamp = "2024-01-15T14:30:00.000000Z" // UTC from API
const dateOnly = "2024-01-20" // Date-only field

// Timestamp formatting (timezone-aware)
formatDateTime(timestamp)     // "Jan 15, 2024, 10:30 AM" (EST)
formatRelative(timestamp)     // "2 hours ago"
formatTime(timestamp)         // "10:30 AM"

// Date-only formatting (NO timezone shift)
formatDateField(dateOnly)     // "Jan 20, 2024" (same for all users)

// Utility functions
checkOverdue(dateOnly, 'in_progress')  // true/false
checkIsToday(dateOnly)                 // true/false
```

## 🔧 Backend Reference

### Model Casts
```php
protected $casts = [
    // Timestamp fields (timezone-aware)
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'completed_at' => 'datetime',
    
    // Date-only fields (NOT timezone-shifted)
    'due_date' => 'date:Y-m-d',
    'start_date' => 'date:Y-m-d',
];
```

### API Response Format
```json
{
  "created_at": "2024-01-15T14:30:00.000000Z",  // UTC timestamp
  "due_date": "2024-01-20"                       // Date only
}
```

## 🧪 Testing

### Test with Different Timezones
1. Open browser DevTools
2. Console → Settings → Sensors → Location
3. Change timezone to test:
   - `America/New_York` (EST/EDT)
   - `Asia/Karachi` (PKT)
   - `Europe/London` (GMT/BST)
   - `Asia/Tokyo` (JST)

### Verify Correct Behavior
- ✅ Timestamps show different times in different timezones
- ✅ Date-only fields show same date in all timezones
- ✅ Relative times update correctly
- ✅ Overdue detection works correctly

## 📚 Full Documentation

For complete documentation, see: `TIMEZONE_IMPLEMENTATION.md`

## 🆘 Need Help?

### Issue: Date shifting by one day
**Solution:** Use `formatDateField()` instead of `formatDateTime()`

### Issue: "Invalid date" errors
**Solution:** Check for null/undefined before formatting

### Issue: Inconsistent formats
**Solution:** Use the provided utilities consistently

### Issue: Timezone not detected
**Solution:** Check browser console for errors, verify `bootstrap.js`
