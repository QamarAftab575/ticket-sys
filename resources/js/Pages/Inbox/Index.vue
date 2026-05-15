<template>
  <AppLayout>
    <div class="flex h-[calc(100vh-64px)]">
      <!-- Left sidebar: Notification list -->
      <div class="w-96 border-r border-gray-200 flex flex-col bg-white">
        <!-- Header -->
        <div class="px-4 py-4 border-b border-gray-100">
          <div class="flex items-center justify-between mb-3">
            <h1 class="text-xl font-semibold text-gray-900">Inbox</h1>
            <button
              v-if="unreadCount > 0"
              @click="markAllAsRead"
              class="text-xs text-indigo-600 hover:text-indigo-700 font-medium"
            >
              Mark all as read
            </button>
          </div>
          <div class="text-sm text-gray-500">
            {{ unreadCount }} unread notification{{ unreadCount !== 1 ? 's' : '' }}
          </div>
        </div>

        <!-- Notification list -->
        <div class="flex-1 overflow-y-auto">
          <!-- Loading state -->
          <div v-if="loading && notifications.length === 0" class="flex items-center justify-center py-12">
            <svg class="w-6 h-6 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
          </div>

          <!-- Grouped notifications -->
          <div v-else>
            <div v-for="group in groupedNotifications" :key="group.label" class="border-b border-gray-100">
              <div class="px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                {{ group.label }}
              </div>
              <NotificationItem
                v-for="notification in group.items"
                :key="notification.id"
                :notification="notification"
                :selected="selectedNotification?.id === notification.id"
                @click="selectNotification(notification)"
                @mark-read="markAsRead(notification)"
                @mark-unread="markAsUnread(notification)"
                @delete="deleteNotification(notification)"
              />
            </div>

            <!-- Load more -->
            <div v-if="hasMore" class="flex justify-center py-4">
              <button
                @click="loadMore"
                :disabled="loading"
                class="px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50 rounded-md border border-indigo-200 transition-colors disabled:opacity-50"
              >
                <span v-if="loading">Loading...</span>
                <span v-else>Load more</span>
              </button>
            </div>

            <!-- Empty state -->
            <div v-if="notifications.length === 0 && !loading" class="flex flex-col items-center justify-center py-12 px-4">
              <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
              </svg>
              <p class="text-gray-500 text-sm">No notifications yet</p>
              <p class="text-gray-400 text-xs mt-1">You'll see mentions and assignments here</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right panel: Task detail -->
      <div class="flex-1 bg-gray-50 overflow-hidden">
        <!-- Task detail panel -->
        <TaskDetailPanel
          v-if="selectedTask"
          :task="selectedTask"
          :project="selectedTask.project"
          :current-user="$page.props.auth.user"
          @close="closeTaskDetail"
          @update="handleTaskUpdate"
          @open-task="openRelatedTask"
        />

        <!-- Empty state when no notification selected -->
        <div v-else class="h-full"></div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import NotificationItem from '@/Components/Inbox/NotificationItem.vue'
import TaskDetailPanel from '@/Components/Projects/TaskDetailPanel.vue'
import { api } from '@/Services/api'

const props = defineProps({
  unreadCount: { type: Number, default: 0 },
})

const notifications = ref([])
const selectedNotification = ref(null)
const selectedTask = ref(null)
const loading = ref(false)
const hasMore = ref(false)
const nextPage = ref(1)
const unreadCount = ref(props.unreadCount)

// Group notifications by date
const groupedNotifications = computed(() => {
  const groups = {
    today: [],
    yesterday: [],
    older: [],
  }

  const now = new Date()
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
  const yesterday = new Date(today)
  yesterday.setDate(yesterday.getDate() - 1)

  notifications.value.forEach(notification => {
    const notifDate = new Date(notification.created_at)
    const notifDay = new Date(notifDate.getFullYear(), notifDate.getMonth(), notifDate.getDate())

    if (notifDay.getTime() === today.getTime()) {
      groups.today.push(notification)
    } else if (notifDay.getTime() === yesterday.getTime()) {
      groups.yesterday.push(notification)
    } else {
      groups.older.push(notification)
    }
  })

  const result = []
  if (groups.today.length) result.push({ label: 'Today', items: groups.today })
  if (groups.yesterday.length) result.push({ label: 'Yesterday', items: groups.yesterday })
  if (groups.older.length) result.push({ label: 'Older', items: groups.older })

  return result
})

async function loadNotifications(page = 1) {
  loading.value = true
  try {
    const response = await api.get(`/inbox/notifications?page=${page}`)
    
    if (page === 1) {
      notifications.value = response.data
    } else {
      notifications.value.push(...response.data)
    }
    
    hasMore.value = response.has_more
    nextPage.value = response.next_page || page + 1
    unreadCount.value = response.unread_count
  } catch (error) {
    console.error('Failed to load notifications:', error)
  } finally {
    loading.value = false
  }
}

function loadMore() {
  if (!loading.value && hasMore.value) {
    loadNotifications(nextPage.value)
  }
}

async function selectNotification(notification) {
  selectedNotification.value = notification
  selectedTask.value = null

  // Mark as read automatically
  if (!notification.is_read) {
    await markAsRead(notification, false) // Don't deselect
  }

  // Get task ID and create minimal task object for TaskDetailPanel
  const taskId = notification.task_id || notification.task?.id
  
  if (!taskId) {
    console.warn('No task ID found in notification')
    return
  }

  // Set minimal task object - TaskDetailPanel will load full details
  selectedTask.value = {
    id: taskId,
    name: notification.task?.name || 'Loading...',
    project: notification.task?.project || null
  }
}

async function markAsRead(notification, deselectIfCurrent = true) {
  try {
    const response = await api.post(`/notifications/${notification.id}/mark-read`)
    notification.is_read = true
    notification.read_at = new Date().toISOString()
    unreadCount.value = response.unread_count
    
    if (deselectIfCurrent && selectedNotification.value?.id === notification.id) {
      selectedNotification.value = null
      selectedTask.value = null
    }
  } catch (error) {
    console.error('Failed to mark as read:', error)
  }
}

async function markAsUnread(notification) {
  try {
    const response = await api.post(`/notifications/${notification.id}/mark-unread`)
    notification.is_read = false
    notification.read_at = null
    unreadCount.value = response.unread_count
  } catch (error) {
    console.error('Failed to mark as unread:', error)
  }
}

async function markAllAsRead() {
  try {
    const response = await api.post('/inbox/mark-all-read')
    notifications.value.forEach(n => {
      n.is_read = true
      n.read_at = new Date().toISOString()
    })
    unreadCount.value = 0
  } catch (error) {
    console.error('Failed to mark all as read:', error)
  }
}

async function deleteNotification(notification) {
  if (!confirm('Delete this notification?')) return

  try {
    const response = await api.delete(`/notifications/${notification.id}`)
    notifications.value = notifications.value.filter(n => n.id !== notification.id)
    unreadCount.value = response.unread_count
    
    if (selectedNotification.value?.id === notification.id) {
      selectedNotification.value = null
      selectedTask.value = null
    }
  } catch (error) {
    console.error('Failed to delete notification:', error)
  }
}

function closeTaskDetail() {
  selectedNotification.value = null
  selectedTask.value = null
}

function handleTaskUpdate(taskId, updates) {
  if (selectedTask.value && selectedTask.value.id === taskId) {
    Object.assign(selectedTask.value, updates)
  }
}

async function openRelatedTask(task) {
  selectedTask.value = task
}

onMounted(() => {
  loadNotifications()
})
</script>
