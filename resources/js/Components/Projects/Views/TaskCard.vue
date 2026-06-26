<template>
  <div
    @click="$emit('click')"
    class="bg-white p-4 rounded-xl border-2 transition-all duration-200 select-none cursor-pointer shadow-sm"
    :class="[
      isDragging 
        ? 'opacity-40 shadow-none scale-95' 
        : 'border-slate-200 hover:border-indigo-300 hover:shadow-md hover:scale-105 group',
      isCompleted 
        ? 'bg-gradient-to-br from-slate-50 to-emerald-50/30 border-emerald-200' 
        : 'hover:bg-slate-50'
    ]"
  >
    <!-- Completed checkmark badge (top right) -->
    <div v-if="isCompleted" class="flex items-center justify-between mb-3">
      <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-full shadow-sm">
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
          <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
        </svg>
        <span>Done</span>
      </span>
    </div>

    <!-- Task name -->
    <h4 
      class="font-semibold text-sm mb-3 leading-snug text-slate-900 transition-colors duration-150"
      :class="isCompleted ? 'text-slate-500 line-through' : 'group-hover:text-indigo-900'"
    >
      {{ task.name }}
    </h4>

    <!-- Task metadata -->
    <div class="space-y-2.5" :class="isCompleted ? 'opacity-70' : ''">
      <!-- Due date with DateRangePicker (always show, even if no date set) -->
      <div class="flex items-center" @click.stop>
        <DateRangePicker
          :start-date="task.start_date ?? null"
          :end-date="task.due_date ?? null"
          :completed="isCompleted"
          @change="handleDateChange"
        />
      </div>

      <!-- Assignee -->
      <div v-if="task.assignee" class="flex items-center gap-2.5">
        <Avatar :name="task.assignee.name" :src="task.assignee.avatar" size="xs" class="ring-2 ring-slate-100" />
        <span class="text-xs font-medium text-slate-600 truncate">{{ task.assignee.name }}</span>
      </div>
    </div>

    <!-- Status indicators -->
    <div v-if="hasStatusIndicators" class="flex flex-wrap gap-2 mt-4 pt-3 border-t border-slate-100" :class="isCompleted ? 'opacity-70' : ''">
      <span v-if="task.subtasks_count" class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-700 hover:bg-slate-200 px-2.5 py-1 rounded-lg text-xs font-medium transition-colors duration-150">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        {{ task.subtasks_count }}
      </span>
      <span v-if="task.comment_count" class="inline-flex items-center gap-1.5 bg-blue-100 text-blue-700 hover:bg-blue-200 px-2.5 py-1 rounded-lg text-xs font-medium transition-colors duration-150">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        {{ task.comment_count }}
      </span>
      <span v-if="task.attachment_count" class="inline-flex items-center gap-1.5 bg-amber-100 text-amber-700 hover:bg-amber-200 px-2.5 py-1 rounded-lg text-xs font-medium transition-colors duration-150">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
        </svg>
        {{ task.attachment_count }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import Avatar from '@/Components/Avatar.vue'
import DateRangePicker from '@/Components/Tasks/Shared/DateRangePicker.vue'

const props = defineProps({
  task: Object,
  isDragging: { type: Boolean, default: false },
})

const emit = defineEmits(['click', 'update-dates'])

const isCompleted = computed(() => {
  return props.task.status === 'complete'
})

const hasStatusIndicators = computed(() => {
  return props.task.subtasks_count || props.task.comment_count || props.task.attachment_count
})

function handleDateChange({ startDate, endDate }) {
  emit('update-dates', {
    taskId: props.task.id,
    start_date: startDate,
    due_date: endDate
  })
}
</script>

