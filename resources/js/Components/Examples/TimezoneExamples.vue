<template>
  <div class="space-y-6 p-6">
    <h2 class="text-2xl font-bold">Timezone Handling Examples</h2>
    
    <!-- Example 1: Comment Timestamp -->
    <div class="border rounded-lg p-4">
      <h3 class="font-semibold mb-2">Example 1: Comment Timestamp</h3>
      <div class="bg-gray-50 p-3 rounded">
        <p class="text-sm text-gray-600">{{ formatRelative(sampleComment.created_at) }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ formatDateTime(sampleComment.created_at) }}</p>
      </div>
      <p class="text-xs text-gray-500 mt-2">
        UTC: {{ sampleComment.created_at }}
      </p>
    </div>

    <!-- Example 2: Task Due Date -->
    <div class="border rounded-lg p-4">
      <h3 class="font-semibold mb-2">Example 2: Task Due Date (Date-Only Field)</h3>
      <div class="bg-gray-50 p-3 rounded">
        <p>Due: {{ formatDateField(sampleTask.due_date) }}</p>
        <p 
          v-if="checkOverdue(sampleTask.due_date, sampleTask.status)" 
          class="text-red-500 text-sm mt-1"
        >
          ⚠️ Overdue
        </p>
        <p 
          v-else-if="checkIsToday(sampleTask.due_date)" 
          class="text-blue-500 text-sm mt-1"
        >
          📅 Due Today
        </p>
      </div>
      <p class="text-xs text-gray-500 mt-2">
        Raw: {{ sampleTask.due_date }} (no timezone shift)
      </p>
    </div>

    <!-- Example 3: Activity Feed -->
    <div class="border rounded-lg p-4">
      <h3 class="font-semibold mb-2">Example 3: Activity Feed</h3>
      <div class="space-y-2">
        <div 
          v-for="activity in sampleActivities" 
          :key="activity.id"
          class="bg-gray-50 p-3 rounded"
        >
          <p class="text-sm">{{ activity.description }}</p>
          <p class="text-xs text-gray-500">{{ formatRelative(activity.created_at) }}</p>
        </div>
      </div>
    </div>

    <!-- Example 4: Notification -->
    <div class="border rounded-lg p-4">
      <h3 class="font-semibold mb-2">Example 4: Notification</h3>
      <div class="bg-gray-50 p-3 rounded">
        <div class="flex justify-between items-start">
          <div>
            <p class="font-medium">{{ sampleNotification.title }}</p>
            <p class="text-sm text-gray-600 mt-1">{{ sampleNotification.message }}</p>
          </div>
          <span 
            v-if="!sampleNotification.is_read" 
            class="w-2 h-2 bg-blue-500 rounded-full"
          ></span>
        </div>
        <p class="text-xs text-gray-500 mt-2">
          {{ formatDateTime(sampleNotification.created_at) }}
        </p>
      </div>
    </div>

    <!-- Example 5: Task Completed Timestamp -->
    <div class="border rounded-lg p-4">
      <h3 class="font-semibold mb-2">Example 5: Task Completed Timestamp</h3>
      <div class="bg-gray-50 p-3 rounded">
        <p class="text-sm">
          <span class="text-green-600">✓</span> 
          Completed {{ formatRelative(sampleCompletedTask.completed_at) }}
        </p>
        <p class="text-xs text-gray-500 mt-1">
          by {{ sampleCompletedTask.completed_by_name }} on 
          {{ formatDateTime(sampleCompletedTask.completed_at) }}
        </p>
      </div>
    </div>

    <!-- User Timezone Info -->
    <div class="border rounded-lg p-4 bg-blue-50">
      <h3 class="font-semibold mb-2">Your Timezone Information</h3>
      <p class="text-sm">
        <strong>Detected Timezone:</strong> {{ userTimezone }}
      </p>
      <p class="text-sm mt-1">
        <strong>Current Local Time:</strong> {{ currentLocalTime }}
      </p>
      <p class="text-xs text-gray-600 mt-2">
        All timestamps are stored in UTC and automatically converted to your local timezone.
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useTimezone } from '@/Composables/useTimezone'

const {
  userTimezone,
  formatDateTime,
  formatRelative,
  formatDateField,
  checkOverdue,
  checkIsToday
} = useTimezone()

// Sample data (simulating API responses)
const sampleComment = {
  id: 1,
  content: 'This is a sample comment',
  created_at: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString() // 2 hours ago
}

const sampleTask = {
  id: 1,
  name: 'Sample Task',
  due_date: '2024-01-20', // Date-only field
  status: 'in_progress'
}

const sampleActivities = [
  {
    id: 1,
    description: 'John created this task',
    created_at: new Date(Date.now() - 5 * 60 * 1000).toISOString() // 5 minutes ago
  },
  {
    id: 2,
    description: 'Sarah added a comment',
    created_at: new Date(Date.now() - 30 * 60 * 1000).toISOString() // 30 minutes ago
  },
  {
    id: 3,
    description: 'Mike updated the status',
    created_at: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString() // 2 hours ago
  }
]

const sampleNotification = {
  id: 1,
  title: 'You were mentioned in a comment',
  message: 'John mentioned you in "Project Planning"',
  is_read: false,
  created_at: new Date(Date.now() - 15 * 60 * 1000).toISOString() // 15 minutes ago
}

const sampleCompletedTask = {
  id: 2,
  name: 'Completed Task',
  completed_at: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString(), // 1 day ago
  completed_by_name: 'Jane Doe'
}

// Current local time (updates every second)
const currentLocalTime = ref('')

const updateCurrentTime = () => {
  currentLocalTime.value = formatDateTime(new Date().toISOString())
}

let intervalId = null

onMounted(() => {
  updateCurrentTime()
  intervalId = setInterval(updateCurrentTime, 1000)
})

onUnmounted(() => {
  if (intervalId) {
    clearInterval(intervalId)
  }
})
</script>
