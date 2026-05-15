<template>
  <div class="activity-log">
    <div class="mb-4">
      <h3 class="text-lg font-semibold text-gray-900">Project Activity</h3>
      <p class="text-sm text-gray-600">All changes to this project are logged here</p>
    </div>

    <!-- Activity list -->
    <div class="space-y-4">
      <div
        v-for="activity in activities"
        :key="activity.id"
        class="flex gap-4 pb-4 border-b border-gray-200 last:border-b-0"
      >
        <!-- Avatar -->
        <div class="flex-shrink-0">
          <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-sm font-medium text-gray-700">
            {{ activity.user.name.charAt(0).toUpperCase() }}
          </div>
        </div>

        <!-- Activity details -->
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-gray-900">
              {{ activity.user.name }}
              <span class="font-normal text-gray-600">{{ getActionLabel(activity.action) }}</span>
            </p>
            <p class="text-xs text-gray-500">{{ formatDate(activity.created_at) }}</p>
          </div>

          <!-- Activity description -->
          <p v-if="activity.description" class="text-sm text-gray-600 mt-1">
            {{ activity.description }}
          </p>

          <!-- Changes -->
          <div v-if="activity.old_value || activity.new_value" class="mt-2 text-xs text-gray-600">
            <div v-if="activity.old_value" class="mb-1">
              <span class="font-medium">Before:</span>
              {{ JSON.stringify(activity.old_value) }}
            </div>
            <div v-if="activity.new_value">
              <span class="font-medium">After:</span>
              {{ JSON.stringify(activity.new_value) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="activities.length === 0" class="text-center py-8">
        <p class="text-gray-600">No activity yet</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  activities: Array,
})

const actionLabels = {
  created: 'created the project',
  updated: 'updated the project',
  archived: 'archived the project',
  unarchived: 'unarchived the project',
  deleted: 'deleted the project',
  duplicated: 'duplicated the project',
  member_added: 'added a member',
  member_removed: 'removed a member',
  member_left: 'left the project',
  role_changed: 'changed a member role',
  owner_changed: 'changed the project owner',
  privacy_changed: 'changed privacy settings',
  status_changed: 'changed the project status',
  lead_changed: 'changed the project lead',
  visibility_changed: 'changed visibility settings',
  dates_updated: 'updated project dates',
  teams_changed: 'changed team assignments',
  team_removed: 'removed a team',
}

const getActionLabel = (action) => {
  return actionLabels[action] || action
}

const formatDate = (date) => {
  const d = new Date(date)
  const now = new Date()
  const diff = now - d

  // Less than a minute
  if (diff < 60000) {
    return 'just now'
  }

  // Less than an hour
  if (diff < 3600000) {
    const minutes = Math.floor(diff / 60000)
    return `${minutes}m ago`
  }

  // Less than a day
  if (diff < 86400000) {
    const hours = Math.floor(diff / 3600000)
    return `${hours}h ago`
  }

  // Less than a week
  if (diff < 604800000) {
    const days = Math.floor(diff / 86400000)
    return `${days}d ago`
  }

  // Format as date
  return d.toLocaleDateString()
}
</script>
