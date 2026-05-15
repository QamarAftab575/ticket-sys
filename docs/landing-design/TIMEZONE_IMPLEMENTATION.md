# Timezone Implementation Guide

## Overview

This application implements proper timezone handling similar to modern SaaS products like Slack, Asana, and Linear. All timestamps are stored in UTC in the database and automatically converted to the user's local timezone on the frontend.

## Core Principles

### 1. **UTC Storage (Backend)**
- All timestamps are stored in UTC in the database
- Laravel's `timezone` config is set to `'UTC'` in `config/app.php`
- Database columns use `timestamp` or `datetime` types for timezone-aware fields

### 2. **Local Display (Frontend)**
- Frontend automatically converts UTC timestamps to user's local timezone
- Uses browser's `Intl.DateTimeFormat` API for timezone detection and formatting
- User timezone is captured and stored in the `users.timezone` column

### 3. **Date vs DateTime Distinction**
- **DateTime fields** (timezone-aware): `created_at`, `updated_at`, `completed_at`, `edited_at`, `read_at`, `last_login_at`
- **Date-only fields** (NOT timezone-shifted): `due_date`, `start_date`, sprint dates, birthdays

## Database Schema

### Users Table
```php
$table->string('timezone', 50)->nullable()->comment('User timezone (e.g., America/New_York, Asia/Karachi)');
```

### Timestamp Fields (Timezone-Aware)
These fields are stored as `timestamp` or `datetime` and are timezone-aware:

- **Users**: `created_at`, `updated_at`, `email_verified_at`, `last_login_at`, `deleted_at`
- **Tasks**: `created_at`, `updated_at`, `completed_at`, `deleted_at`
- **Comments**: `created_at`, `updated_at`, `edited_at`, `deleted_at`
- **Notifications**: `created_at`, `updated_at`, `read_at`
- **Project Activities**: `created_at`
- **Attachments**: `created_at`, `updated_at`
- **All other models**: `created_at`, `updated_at`, `deleted_at`

### Date-Only Fields (NOT Timezone-Shifted)
These fields are stored as `date` and should NOT be timezone-shifted:

- **Tasks**: `due_date`, `start_date`
- **Sprint dates** (if implemented)
- **Birthdays** (if implemented)

## Backend Implementation

### 1. Configuration
```php
// config/app.php
'timezone' => 'UTC',
```

### 2. Middleware
The `CaptureUserTimezone` middleware automatically captures and stores user timezone:

```php
// app/Http/Middleware/CaptureUserTimezone.php
// Registered in bootstrap/app.php
```

**How it works:**
- Frontend sends `X-Timezone` header with every request
- Middleware validates and stores timezone in `users.timezone` column
- Only updates if timezone has changed (performance optimization)

### 3. Model Casts
All models use Laravel's datetime casting for automatic UTC handling:

```php
protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'completed_at' => 'datetime',
    'edited_at' => 'datetime',
    'read_at' => 'datetime',
    // Date-only fields
    'due_date' => 'date:Y-m-d',
    'start_date' => 'date:Y-m-d',
];
```

### 4. API Responses
Laravel automatically serializes datetime fields to ISO 8601 UTC format:
```json
{
  "created_at": "2024-01-15T14:30:00.000000Z",
  "due_date": "2024-01-20"
}
```

## Frontend Implementation

### 1. Timezone Detection
```javascript
// resources/js/bootstrap.js
// Automatically adds X-Timezone header to all axios requests
const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
window.axios.defaults.headers.common['X-Timezone'] = userTimezone;
```

### 2. Utility Functions
```javascript
// resources/js/Utils/timezone.js
import { formatToLocalTime, formatDateOnly } from '@/Utils/timezone'

// For timestamps (timezone-aware)
formatToLocalTime(task.created_at) // "Jan 15, 2024, 10:30 AM"

// For date-only fields (NOT timezone-shifted)
formatDateOnly(task.due_date) // "Jan 15, 2024"
```

### 3. Vue Composable
```javascript
// resources/js/Composables/useTimezone.js
import { useTimezone } from '@/Composables/useTimezone'

const { formatDateTime, formatRelative, formatDateField } = useTimezone()

// In your component
formatDateTime(comment.created_at) // "Jan 15, 2024, 10:30 AM"
formatRelative(notification.created_at) // "2 hours ago"
formatDateField(task.due_date) // "Jan 15, 2024" (no timezone shift)
```

## Usage Examples

### Example 1: Display Comment Timestamp
```vue
<template>
  <div>
    <p>{{ formatRelative(comment.created_at) }}</p>
    <p class="text-sm text-gray-500">{{ formatDateTime(comment.created_at) }}</p>
  </div>
</template>

<script setup>
import { useTimezone } from '@/Composables/useTimezone'

const { formatDateTime, formatRelative } = useTimezone()
</script>
```

### Example 2: Display Task Due Date
```vue
<template>
  <div>
    <p>Due: {{ formatDateField(task.due_date) }}</p>
    <p v-if="checkOverdue(task.due_date, task.status)" class="text-red-500">
      Overdue
    </p>
  </div>
</template>

<script setup>
import { useTimezone } from '@/Composables/useTimezone'

const { formatDateField, checkOverdue } = useTimezone()
</script>
```

### Example 3: Display Activity Feed
```vue
<template>
  <div v-for="activity in activities" :key="activity.id">
    <p>{{ activity.description }}</p>
    <p class="text-sm text-gray-500">{{ formatRelative(activity.created_at) }}</p>
  </div>
</template>

<script setup>
import { useTimezone } from '@/Composables/useTimezone'

const { formatRelative } = useTimezone()
</script>
```

### Example 4: Display Notification
```vue
<template>
  <div v-for="notification in notifications" :key="notification.id">
    <p>{{ notification.title }}</p>
    <p class="text-sm">{{ formatDateTime(notification.created_at) }}</p>
  </div>
</template>

<script setup>
import { useTimezone } from '@/Composables/useTimezone'

const { formatDateTime } = useTimezone()
</script>
```

### Example 5: Submit Form with Datetime
```vue
<script setup>
import { useTimezone } from '@/Composables/useTimezone'

const { toUTC } = useTimezone()

const submitForm = () => {
  const localDate = new Date() // User's local time
  const utcDate = toUTC(localDate) // Convert to UTC for API
  
  axios.post('/api/tasks', {
    completed_at: utcDate // Send as UTC
  })
}
</script>
```

## Available Functions

### Timezone Utilities (`resources/js/Utils/timezone.js`)

| Function | Purpose | Example |
|----------|---------|---------|
| `getUserTimezone()` | Get user's IANA timezone | `"America/New_York"` |
| `formatToLocalTime(utc)` | Format UTC to local datetime | `"Jan 15, 2024, 10:30 AM"` |
| `formatToLocalDate(utc)` | Format UTC to local date | `"Jan 15, 2024"` |
| `formatToLocalTimeOnly(utc)` | Format UTC to local time | `"10:30 AM"` |
| `formatToRelativeTime(utc)` | Format UTC to relative time | `"2 hours ago"` |
| `formatDateOnly(date)` | Format date-only field | `"Jan 15, 2024"` |
| `convertToUTC(local)` | Convert local to UTC | `"2024-01-15T14:30:00.000Z"` |
| `isOverdue(date, status)` | Check if task is overdue | `true/false` |
| `isToday(date)` | Check if date is today | `true/false` |
| `getCurrentDate()` | Get current date | `"2024-01-15"` |

### Composable (`resources/js/Composables/useTimezone.js`)

| Function | Purpose |
|----------|---------|
| `formatDateTime(utc)` | Format UTC datetime to local |
| `formatDate(utc)` | Format UTC to local date |
| `formatTime(utc)` | Format UTC to local time |
| `formatRelative(utc)` | Format UTC to relative time |
| `formatDateField(date)` | Format date-only field (no timezone shift) |
| `toUTC(local)` | Convert local to UTC |
| `checkOverdue(date, status)` | Check if overdue |
| `checkIsToday(date)` | Check if today |
| `todayDate()` | Get current date |

## Testing Scenarios

### Scenario 1: USA User Creates Task
1. USA user (EST timezone) creates task at 10:00 AM local time
2. Backend stores `created_at` as `2024-01-15 15:00:00` (UTC)
3. USA user sees: "Jan 15, 2024, 10:00 AM"
4. Pakistan user (PKT timezone) sees: "Jan 15, 2024, 8:00 PM"

### Scenario 2: Due Date Consistency
1. USA user sets due date to "Jan 20, 2024"
2. Backend stores `due_date` as `2024-01-20` (date only)
3. USA user sees: "Jan 20, 2024"
4. Pakistan user sees: "Jan 20, 2024" (same date, no shift)

### Scenario 3: Comment Timestamps
1. Pakistan user posts comment at 8:00 PM PKT
2. Backend stores `created_at` as `2024-01-15 15:00:00` (UTC)
3. Pakistan user sees: "just now" or "8:00 PM"
4. USA user sees: "10:00 AM" (same moment, different timezone)

## Migration Guide

### Running the Migration
```bash
php artisan migrate
```

This will add the `timezone` column to the `users` table.

### Updating Existing Components

1. **Replace manual date formatting:**
```javascript
// Before
new Date(task.created_at).toLocaleString()

// After
import { useTimezone } from '@/Composables/useTimezone'
const { formatDateTime } = useTimezone()
formatDateTime(task.created_at)
```

2. **Update date-only fields:**
```javascript
// Before
new Date(task.due_date).toLocaleDateString()

// After
import { useTimezone } from '@/Composables/useTimezone'
const { formatDateField } = useTimezone()
formatDateField(task.due_date)
```

3. **Update relative time:**
```javascript
// Before
// Custom relative time logic

// After
import { useTimezone } from '@/Composables/useTimezone'
const { formatRelative } = useTimezone()
formatRelative(comment.created_at)
```

## Future Enhancements

### 1. Reminders & Notifications
```php
// Store reminder time in UTC
$task->reminder_at = Carbon::parse($localTime)->utc();

// Frontend displays in user's local time
formatDateTime(task.reminder_at)
```

### 2. Recurring Tasks
```php
// Store recurrence pattern with timezone awareness
$task->recurrence_timezone = $user->timezone;
$task->next_occurrence_at = Carbon::parse($nextOccurrence)->utc();
```

### 3. Calendar Integration
```php
// Export to iCal with proper timezone
$event->timezone = $user->timezone;
$event->start_time = $task->due_date->setTimezone($user->timezone);
```

### 4. Scheduled Jobs
```php
// Schedule jobs in UTC, display in user's timezone
$job->scheduled_at = Carbon::parse($userTime)->utc();
```

### 5. Realtime Collaboration
```javascript
// Broadcast events with UTC timestamps
// Each client converts to their local timezone
Echo.channel('project.' + projectId)
  .listen('TaskUpdated', (event) => {
    const localTime = formatDateTime(event.updated_at)
  })
```

## Best Practices

### ✅ DO
- Always store timestamps in UTC in the database
- Use `formatDateTime()` for timezone-aware fields
- Use `formatDateField()` for date-only fields
- Test with users in different timezones
- Use relative time for recent activities ("2 hours ago")
- Validate timezone strings before storing

### ❌ DON'T
- Don't store local timezone times in the database
- Don't apply timezone conversion to date-only fields
- Don't hardcode timezone offsets
- Don't use `new Date()` directly for display
- Don't forget to handle null/undefined dates
- Don't mix date and datetime logic

## Troubleshooting

### Issue: Dates shifting by one day
**Cause:** Applying timezone conversion to date-only fields
**Solution:** Use `formatDateField()` instead of `formatDateTime()`

### Issue: Timezone not updating
**Cause:** Middleware not registered or header not sent
**Solution:** Check `bootstrap/app.php` and `resources/js/bootstrap.js`

### Issue: Invalid date errors
**Cause:** Malformed date strings or null values
**Solution:** Always check for null/undefined before formatting

### Issue: Inconsistent date formats
**Cause:** Using different formatting methods
**Solution:** Use the provided utility functions consistently

## Support

For questions or issues related to timezone handling, please refer to:
- Laravel Timezone Documentation: https://laravel.com/docs/11.x/helpers#method-now
- MDN Intl.DateTimeFormat: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/DateTimeFormat
- IANA Timezone Database: https://www.iana.org/time-zones

## Summary

This implementation provides:
- ✅ UTC storage in database
- ✅ Automatic timezone detection
- ✅ Local timezone rendering
- ✅ Date vs DateTime distinction
- ✅ Reusable utilities and composables
- ✅ Consistent formatting across the app
- ✅ Future-ready architecture

The system now behaves like modern SaaS products with proper timezone handling!
