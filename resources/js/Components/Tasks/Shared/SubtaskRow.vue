<template>
  <div
    class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-gray-50 group transition-colors border-b border-gray-100 last:border-b-0"
    :class="subtask.status === 'complete' ? 'opacity-60' : ''"
  >
    <!-- Complete toggle -->
    <button
      @click.stop="toggleComplete"
      class="flex-shrink-0 w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
      :class="subtask.status === 'complete' ? 'bg-green-500 border-green-500' : 'border-gray-300 hover:border-green-500'"
    >
      <svg v-if="subtask.status === 'complete'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
      </svg>
    </button>

    <!-- Name -->
    <span
      class="flex-1 text-sm text-gray-800 truncate"
      :class="{ 'line-through text-gray-400': subtask.status === 'complete' }"
    >{{ subtask.name }}</span>

    <!-- Actions (visible on hover) -->
    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
      <!-- Date picker -->
      <DateRangePicker
        :start-date="subtask.start_date ?? null"
        :end-date="subtask.due_date ?? null"
        :completed="subtask.status === 'complete'"
        :compact="true"
        @change="({ startDate, endDate }) => emit('update-dates', { subtaskId: subtask.id, start_date: startDate, due_date: endDate })"
      />

      <!-- Assignee picker -->
      <TaskRowAssigneePicker
        :task="subtask"
        :members="members"
        @update-assignee="emit('update-assignee', $event)"
      />

      <!-- Open detail arrow -->
      <button
        @click.stop="emit('open', subtask)"
        class="p-1 text-gray-400 hover:text-gray-600 rounded"
        title="Open subtask"
      >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import DateRangePicker from '@/Components/Tasks/Shared/DateRangePicker.vue'
import TaskRowAssigneePicker from '@/Components/Tasks/Shared/TaskRowAssigneePicker.vue'

const props = defineProps({
  subtask: { type: Object, required: true },
  members: { type: Array, default: () => [] },
})

const emit = defineEmits(['toggle-complete', 'update-dates', 'update-assignee', 'open'])

function toggleComplete() {
  emit('toggle-complete', props.subtask.id)
}
</script>
