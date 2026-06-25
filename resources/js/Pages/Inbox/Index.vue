<template>
  <AppLayout>
    <div class="flex h-[calc(100vh-64px)]">
      <!-- Left sidebar: Notification list -->
      <div class="w-96 border-r border-gray-200 flex flex-col bg-white">
        <!-- Header -->
        <div class="px-4 py-4 border-b border-gray-100">
          <div class="flex items-center justify-between mb-3">
            <h1 class="text-xl font-semibold text-gray-900">{{ $t('inbox') }}</h1>
            <button
              v-if="unreadCount > 0"
              @click="markAllAsRead"
              class="text-xs text-indigo-600 hover:text-indigo-700 font-medium"
            >
              {{ $t('mark_all_as_read') }}
            </button>
          </div>
          <div class="text-sm text-gray-500">
            {{ unreadCount }} {{ unreadCount === 1 ? $t('unread_notification') : $t('unread_notifications') }}
          </div>
        </div>

        <!-- Notification list -->
        <div class="flex-1 overflow-y-auto flex flex-col">
          <!-- Loading state -->
          <div v-if="loading && notifications.length === 0" class="flex items-center justify-center flex-1">
            <svg class="w-6 h-6 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
          </div>

          <!-- Empty state -->
          <div v-else-if="notifications.length === 0 && !loading" class="flex-1">
          </div>

          <!-- Grouped notifications -->
          <div v-else class="flex-1">
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
                <span v-if="loading">{{ $t('loading') }}</span>
                <span v-else>{{ $t('load_more') }}</span>
              </button>
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

        <!-- Empty state when no notification selected or no notifications exist -->
        <div v-else class="h-full flex flex-col items-center justify-center">
          <EmptyStateAnimation
            height="300px"
            width="300px"
            :message="$t('no_notifications')"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import NotificationItem from '@/Components/Inbox/NotificationItem.vue'
import TaskDetailPanel from '@/Components/Projects/TaskDetailPanel.vue'
import EmptyStateAnimation from '@/Components/EmptyState/EmptyStateAnimation.vue'
import { api } from '@/Services/api'
import { useNotificationStore } from '@/Stores/useNotificationStore'

const page = usePage()
const props = defineProps({
  unreadCount: { type: Number, default: 0 },
})

const notificationStore = useNotificationStore()

const notifications = ref([])
const selectedNotification = ref(null)
const selectedTask = ref(null)
const loading = ref(false)
const hasMore = ref(false)
const nextPage = ref(1)

// Derive unread count from the global store (kept in sync by Echo via AppLayout)
const unreadCount = computed({
  get: () => notificationStore.unreadCount,
  set: (v) => notificationStore.setUnreadCount(v),
})

// Group notifications by date
const groupedNotifications = computed(() => {
  console.log('Computing groupedNotifications...')
  console.log('Current notifications:', notifications.value)
  console.log('Notifications is array?', Array.isArray(notifications.value))
  console.log('Notifications length:', notifications.value.length)
  
  const groups = {
    today: [],
    yesterday: [],
    older: [],
  }

  const now = new Date()
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
  const yesterday = new Date(today)
  yesterday.setDate(yesterday.getDate() - 1)

  // Ensure notifications is an array
  const notifList = Array.isArray(notifications.value) ? notifications.value : []

  notifList.forEach(notification => {
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
  if (groups.today.length) result.push({ label: page.props.translations?.today || 'Today', items: groups.today })
  if (groups.yesterday.length) result.push({ label: page.props.translations?.yesterday || 'Yesterday', items: groups.yesterday })
  if (groups.older.length) result.push({ label: page.props.translations?.older || 'Older', items: groups.older })

  console.log('Grouped notifications result:', result)
  return result
})

async function loadNotifications(page = 1) {
  loading.value = true
  console.log('Loading notifications, page:', page)
  try {
    const response = await api.get(`/inbox/notifications?page=${page}`)
    console.log('Response from API:', response)
    console.log('Response data:', response.data)
    
    // Extract the actual notifications array from response.data.data
    const notificationsArray = response.data.data || []
    console.log('Notifications array:', notificationsArray)
    
    if (page === 1) {
      notifications.value = notificationsArray
    } else {
      notifications.value.push(...notificationsArray)
    }
    
    console.log('Notifications after assignment:', notifications.value)
    console.log('Notifications length:', notifications.value.length)
    
    hasMore.value = response.data.has_more
    nextPage.value = response.data.next_page || page + 1
    // Sync the global store (Echo may already have updated it, take the max)
    notificationStore.setUnreadCount(response.data.unread_count)
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
    notificationStore.setUnreadCount(response.unread_count)
    
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
    notificationStore.setUnreadCount(response.unread_count)
  } catch (error) {
    console.error('Failed to mark as unread:', error)
  }
}

async function markAllAsRead() {
  try {
    await api.post('/inbox/mark-all-read')
    notifications.value.forEach(n => {
      n.is_read = true
      n.read_at = new Date().toISOString()
    })
    notificationStore.setUnreadCount(0)
  } catch (error) {
    console.error('Failed to mark all as read:', error)
  }
}

async function deleteNotification(notification) {
  if (!confirm(page.props.translations?.delete_notification_confirm || 'Delete this notification?')) return

  try {
    const response = await api.delete(`/notifications/${notification.id}`)
    notifications.value = notifications.value.filter(n => n.id !== notification.id)
    notificationStore.setUnreadCount(response.unread_count)
    
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
  console.log('Inbox page mounted')
  console.log('Initial unreadCount prop:', props.unreadCount)
  // Seed the store from the server-rendered prop (first page load)
  notificationStore.setUnreadCount(props.unreadCount)
  console.log('Calling loadNotifications...')
  loadNotifications()
  console.log('After loadNotifications call')
})
</script>

