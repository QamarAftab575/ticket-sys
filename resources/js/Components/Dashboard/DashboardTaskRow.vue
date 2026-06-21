<template>
  <div
    class="flex items-center gap-3 py-3 px-2 border-b border-gray-100 hover:bg-gray-50 transition-colors group cursor-default"
    :class="{ 'opacity-60': task.status === 'complete' }"
  >
    <!-- Complete Toggle -->
    <button
      @click.stop="emit('toggle-complete', task.id)"
      class="flex-shrink-0 w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
      :class="task.status === 'complete'
        ? 'bg-green-500 border-green-500'
        : 'border-gray-300 hover:border-green-400 hover:bg-green-50'"
      :title="task.status === 'complete' ? 'Mark incomplete' : 'Mark complete'"
    >
      <svg v-if="task.status === 'complete'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
      </svg>
    </button>

    <!-- Task Name -->
    <div class="flex-1 min-w-0">
      <button
        @click="emit('click', task.id)"
        class="text-sm font-medium text-gray-800 hover:text-blue-600 truncate text-left transition-colors"
        :class="{ 'line-through text-gray-400': task.status === 'complete' }"
      >
        {{ task.name }}
      </button>
      <p v-if="task.project" class="text-xs text-gray-500 mt-0.5">
        {{ task.project.name }}
      </p>
    </div>

    <!-- Assignee Avatar -->
    <div v-if="task.assignee" class="flex-shrink-0">
      <div
        class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold bg-gray-400"
        :title="`Assigned to ${task.assignee.name}`"
        :style="{ backgroundColor: getAvatarColor(task.assignee.id) }"
      >
        {{ (task.assignee.name || '?').charAt(0).toUpperCase() }}
      </div>
    </div>

    <!-- Due Date with Calendar Icon (Clickable) -->
    <div v-if="task.due_date" class="flex-shrink-0" @click.stop>
      <DateRangePicker
        :start-date="task.start_date ?? null"
        :end-date="task.due_date ?? null"
        :completed="task.status === 'complete'"
        @change="emit('update-dates', { taskId: task.id, startDate: $event.startDate, endDate: $event.endDate })"
      />
    </div>
    <div v-else class="flex-shrink-0 text-xs flex items-center gap-1 text-gray-400 hover:text-gray-600 cursor-pointer" @click.stop>
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
      </svg>
      <span class="text-gray-400">Add date</span>
    </div>

    <!-- Comments Count -->
    <div v-if="task.comment_count" class="flex-shrink-0 text-xs text-gray-500">
      💬 {{ task.comment_count }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import DateRangePicker from '@/Components/Tasks/Shared/DateRangePicker.vue'

const props = defineProps({
  task: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['click', 'toggle-complete', 'update-dates'])

// Simple color assignment based on user ID
const getAvatarColor = (userId) => {
  const colors = [
    '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8',
    '#F7DC6F', '#BB8FCE', '#85C1E2', '#F8B88B', '#AED6F1',
  ]
  return colors[userId.charCodeAt(0) % colors.length]
}
</script>
