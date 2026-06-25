<template>
  <div
    class="task-row flex items-center border-b border-gray-100 transition-colors group cursor-default"
    :class="[
      isDragging ? 'opacity-30' : 'hover:bg-gray-50',
      task.status === 'complete' ? 'opacity-60' : '',
    ]"
    style="min-height: 36px;"
  >
    <!-- Drag handle (hidden until hover) -->
    <div class="flex-shrink-0 w-5 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-grab active:cursor-grabbing text-gray-300 hover:text-gray-500">
      <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
        <path d="M9 3h2v2H9V3zm0 4h2v2H9V7zm0 4h2v2H9v-2zm4-8h2v2h-2V3zm0 4h2v2h-2V7zm0 4h2v2h-2v-2z"/>
      </svg>
    </div>

    <!-- Complete circle -->
    <button
      @click.stop="$emit('toggle-complete', task.id)"
      class="flex-shrink-0 w-4 h-4 rounded-full border flex items-center justify-center transition-colors mr-2"
      :class="task.status === 'complete'
        ? 'bg-green-500 border-green-500'
        : 'border-gray-300 hover:border-green-400'"
      :title="task.status === 'complete' ? 'Mark incomplete' : 'Mark complete'"
    >
      <svg v-if="task.status === 'complete'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 24 24">
        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
      </svg>
    </button>

    <!-- Columns (single horizontal scroll container) -->
    <div class="flex flex-1 min-w-0">
      <div
        v-for="(column, idx) in columns"
        :key="column.id"
        class="flex-shrink-0 flex items-center px-2 text-[13px]"
        :class="column.id !== 'name' ? 'border-r border-[#e8e8e8]' : ''"
        :style="{ width: columnWidths[column.id] || '200px' }"
      >
        <!-- Task name -->
        <div v-if="column.id === 'name'" class="flex items-center gap-1.5 w-full min-w-0">
          <span
            @click="$emit('click')"
            class="cursor-pointer hover:text-blue-600 truncate text-gray-800"
            :class="{ 'line-through text-gray-400': task.status === 'complete' }"
          >{{ task.name }}</span>
          <span v-if="task.subtasks_count" class="text-gray-400 flex-shrink-0" :title="`${task.subtasks_count} subtask(s)`">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </span>
          <span v-if="task.comment_count" class="text-[11px] text-gray-400 flex-shrink-0">ðŸ’¬ {{ task.comment_count }}</span>
        </div>

        <!-- Assignee -->
        <div v-else-if="column.id === 'assignee'" class="w-full" @click.stop>
          <TaskRowAssigneePicker :task="task" :members="members" @update-assignee="emit('update-assignee', $event)" />
        </div>

        <!-- Due date -->
        <div v-else-if="column.id === 'due_date'" class="w-full" @click.stop>
          <DateRangePicker
            :start-date="task.start_date ?? null"
            :end-date="task.due_date ?? null"
            :completed="task.status === 'complete'"
            @change="({ startDate, endDate }) => emit('update-dates', { taskId: task.id, start_date: startDate, due_date: endDate })"
          />
        </div>

        <!-- Project name -->
        <div v-else-if="column.id === 'project_name'" class="w-full flex items-center gap-2">
          <span
            v-if="task.project"
            class="w-3 h-3 rounded-sm flex-shrink-0"
            :style="{ backgroundColor: task.project.color || '#6366f1' }"
          />
          <span class="truncate text-gray-700 text-[13px]">
            {{ task.project?.name || ' ' }}
          </span>
        </div>

        <!-- Custom field -->
        <div v-else-if="column.isCustom" class="w-full" @click.stop>
          <CustomFieldCell
            :field="getFieldDef(column.id)"
            :task="task"
            :raw-value="getCustomFieldRaw(column.id)"
            :members="members"
            @update="emit('update-custom-field', $event)"
            @edit-field="emit('edit-field', $event)"
          />
        </div>

        <!-- Fallback -->
        <span v-else class="truncate text-gray-500 text-[13px]">{{ task[column.id] ?? ' ' }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import DateRangePicker from '@/Components/Tasks/Shared/DateRangePicker.vue'
import TaskRowAssigneePicker from '@/Components/Tasks/Shared/TaskRowAssigneePicker.vue'
import CustomFieldCell from '@/Components/CustomFields/CustomFieldCell.vue'

const props = defineProps({
  task:         { type: Object, required: true },
  columns:      { type: Array,  required: true },
  columnWidths: { type: Object, required: true },
  members:      { type: Array,  default: () => [] },
  customFields: { type: Array,  default: () => [] },
  isDragging:   { type: Boolean, default: false },
})

const emit = defineEmits([
  'click', 'toggle-complete', 'menu',
  'update-dates', 'update-assignee', 'update-custom-field', 'edit-field',
])

function getFieldDef(fieldId) {
  return props.customFields.find(f => f.id === fieldId) ?? { id: fieldId, field_type: 'text', options: [] }
}

function getCustomFieldRaw(fieldId) {
  const cfv = props.task.custom_field_values?.find(v => v.custom_field_id === fieldId)
  return cfv?.value ?? null
}
</script>

