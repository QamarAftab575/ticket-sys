<template>
  <div class="space-y-3">
    <div v-if="!milestones || milestones.length === 0" class="text-center py-6 text-gray-500">
      <p class="text-sm">No upcoming milestones</p>
    </div>

    <div v-else class="space-y-2">
      <div
        v-for="milestone in milestones"
        :key="milestone.id"
        class="flex items-center gap-3 p-3 rounded hover:bg-gray-50 cursor-pointer transition-colors"
        @click="$emit('open-task', milestone.id)"
      >
        <div class="text-xl">ðŸ’Ž</div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-900 truncate">{{ milestone.name }}</p>
          <p
            class="text-xs font-medium"
            :class="isOverdue(milestone.due_date) ? 'text-red-600' : 'text-gray-600'"
          >
            {{ formatDate(milestone.due_date) }}
          </p>
        </div>
        <div
          v-if="isOverdue(milestone.due_date)"
          class="px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded"
        >
          Overdue
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  milestones: Array,
})

defineEmits(['open-task'])

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
  })
}

const isOverdue = (dueDate) => {
  return new Date(dueDate) < new Date()
}
</script>

