<template>
  <div class="space-y-3">
    <div v-if="!activities || activities.length === 0" class="text-center py-6 text-gray-500">
      <p class="text-sm">No recent activity</p>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="activity in activities.slice(0, 10)"
        :key="activity.id"
        class="flex gap-3 p-2 rounded hover:bg-gray-50 transition-colors"
      >
        <img
          v-if="activity.user?.avatar"
          :src="activity.user.avatar"
          :alt="activity.user.name"
          class="w-8 h-8 rounded-full flex-shrink-0"
        />
        <div v-else class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
          {{ activity.user?.name?.charAt(0).toUpperCase() || '?' }}
        </div>

        <div class="flex-1 min-w-0">
          <p class="text-sm text-gray-900">
            <span class="font-medium">{{ activity.user?.name || 'Unknown' }}</span>
            <span class="text-gray-600">{{ activity.action }}</span>
          </p>
          <p
            v-if="activity.task"
            class="text-sm text-blue-600 hover:underline cursor-pointer truncate"
            @click="$emit('open-task', activity.task.id)"
          >
            {{ activity.task.name }}
          </p>
          <p class="text-xs text-gray-500">{{ timeAgo(activity.created_at) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  activities: Array,
})

defineEmits(['open-task'])

const timeAgo = (date) => {
  const now = new Date()
  const activityDate = new Date(date)
  const diffMs = now - activityDate
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return 'just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`

  return activityDate.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
  })
}
</script>

