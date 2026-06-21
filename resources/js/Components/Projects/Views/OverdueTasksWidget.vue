<template>
  <div class="space-y-4">
    <div class="text-center">
      <div class="text-4xl font-bold text-red-600 mb-1">{{ overdueCount }}</div>
      <p class="text-sm text-gray-600">tasks overdue</p>
    </div>

    <div v-if="overdueTasks && overdueTasks.length > 0" class="space-y-2 pt-2 border-t border-gray-200">
      <div
        v-for="task in overdueTasks.slice(0, 5)"
        :key="task.id"
        class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 cursor-pointer transition-colors"
        @click="$emit('open-task', task.id)"
      >
        <img
          v-if="task.assignee?.avatar"
          :src="task.assignee.avatar"
          :alt="task.assignee.name"
          class="w-6 h-6 rounded-full"
        />
        <div v-else class="w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center text-xs font-bold text-white">
          {{ task.assignee?.name?.charAt(0).toUpperCase() || '?' }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-900 truncate">{{ task.name }}</p>
          <p class="text-xs text-red-600">{{ daysOverdue(task.due_date) }} days overdue</p>
        </div>
      </div>

      <button
        v-if="overdueTasks.length > 5"
        @click="$emit('view-all')"
        class="w-full px-3 py-2 text-sm text-blue-600 hover:text-blue-800 font-medium text-center"
      >
        View all {{ overdueTasks.length }} overdue tasks
      </button>
    </div>

    <div v-else class="text-center py-4 text-gray-500">
      <p class="text-sm">No overdue tasks</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  overdueTasks: Array,
})

defineEmits(['open-task', 'view-all'])

const overdueCount = computed(() => {
  return props.overdueTasks?.length || 0
})

const daysOverdue = (dueDate) => {
  const now = new Date()
  const due = new Date(dueDate)
  const diffTime = Math.abs(now - due)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays
}
</script>

