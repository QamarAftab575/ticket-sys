<template>
  <div
    class="relative"
    @dragover.prevent="onGroupDragOver"
    @drop.prevent="onGroupDrop"
  >
    <!-- Drop indicator at top when dragging over empty section -->
    <div
      v-if="isDropTarget && tasks.length === 0"
      class="h-1 bg-indigo-400 rounded mx-3 my-1"
    />

    <div
      v-for="(task, index) in tasks"
      :key="task.id"
      class="relative"
      @dragover.prevent="onTaskDragOver($event, task.id)"
      @drop.prevent="onTaskDrop($event, task.id)"
    >
      <!-- Insert-before indicator -->
      <div
        v-if="insertBeforeId === task.id"
        class="h-0.5 bg-indigo-400 mx-3 rounded"
      />

      <TaskRow
        :task="task"
        :columns="columns"
        :column-widths="columnWidths"
        :members="members"
        :custom-fields="customFields"
        :is-dragging="draggingTaskId === task.id"
        draggable="true"
        @dragstart="onTaskDragStart($event, task)"
        @dragend="onTaskDragEnd"
        @click="$emit('select-task', task)"
        @toggle-complete="$emit('toggle-complete', task.id)"
        @menu="$emit('menu', task.id)"
        @update-dates="$emit('update-dates', $event)"
        @update-assignee="$emit('update-assignee', $event)"
        @update-custom-field="$emit('update-custom-field', $event)"
        @edit-field="$emit('edit-field', $event)"
      />
    </div>

    <!-- Insert-after-last indicator -->
    <div
      v-if="insertBeforeId === '__end__'"
      class="h-0.5 bg-indigo-400 mx-3 rounded"
    />

    <!-- Inline creation row -->
    <div
      v-if="isCreating"
      class="flex items-center gap-2 px-3 py-1 bg-white border-b border-gray-100"
      style="min-height: 36px;"
    >
      <div class="w-5 flex-shrink-0" />
      <div class="flex-shrink-0 w-4 h-4 rounded-full border border-gray-300 mr-2" />
      <input
        ref="inputRef"
        v-model="newName"
        type="text"
        placeholder="Write a task name"
        class="flex-1 py-0.5 text-[13px] bg-transparent border-none outline-none placeholder-gray-400 text-gray-800"
        @keydown.enter="commit"
        @keydown.escape="cancel"
        @blur="onBlur"
      />
    </div>

    <!-- Add task button -->
    <button
      v-else
      class="flex items-center gap-1.5 w-full px-3 py-1.5 text-[13px] text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors"
      @click="start"
    >
      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      Add task
    </button>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue'
import TaskRow from './TaskRow.vue'

const props = defineProps({
  tasks:        { type: Array,  default: () => [] },
  columns:      { type: Array,  default: () => [] },
  columnWidths: { type: Object, default: () => ({}) },
  sectionId:    { type: String, default: null },
  isDropTarget: { type: Boolean, default: false },
  members:      { type: Array,  default: () => [] },
  customFields: { type: Array,  default: () => [] },
})

const emit = defineEmits([
  'select-task', 'toggle-complete', 'menu', 'task-created',
  'task-move', 'update-dates', 'update-assignee', 'update-custom-field', 'edit-field',
])

// ── inline creation ──────────────────────────────────────────────────────
const isCreating = ref(false)
const newName = ref('')
const inputRef = ref(null)
let suppressBlur = false

const start = () => {
  suppressBlur = false
  isCreating.value = true
  newName.value = ''
  nextTick(() => inputRef.value?.focus())
}

const commit = () => {
  suppressBlur = true
  const name = newName.value.trim()
  if (name) emit('task-created', { name, section_id: props.sectionId })
  newName.value = ''
  nextTick(() => { suppressBlur = false; inputRef.value?.focus() })
}

const cancel = () => {
  suppressBlur = true
  isCreating.value = false
  newName.value = ''
}

const onBlur = () => {
  if (suppressBlur) return
  const name = newName.value.trim()
  if (name) emit('task-created', { name, section_id: props.sectionId })
  isCreating.value = false
  newName.value = ''
}

defineExpose({ start })

// ── drag & drop ──────────────────────────────────────────────────────────
const draggingTaskId = ref(null)
const insertBeforeId = ref(null)   // task id or '__end__'

function onTaskDragStart(event, task) {
  draggingTaskId.value = task.id
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('application/task-id', task.id)
  event.dataTransfer.setData('application/section-id', task.section_id ?? '')
}

function onTaskDragEnd() {
  draggingTaskId.value = null
  insertBeforeId.value = null
}

function onTaskDragOver(event, taskId) {
  event.stopPropagation()
  const taskData = event.dataTransfer.types.includes('application/task-id')
  if (!taskData) return
  insertBeforeId.value = taskId
}

function onTaskDrop(event, taskId) {
  event.stopPropagation()
  const fromTaskId = event.dataTransfer.getData('application/task-id')
  const fromSectionId = event.dataTransfer.getData('application/section-id') || null
  if (!fromTaskId || fromTaskId === taskId) {
    insertBeforeId.value = null
    return
  }

  const idx = props.tasks.findIndex(t => t.id === taskId)
  emit('task-move', {
    taskId: fromTaskId,
    fromSectionId,
    toSectionId: props.sectionId,
    position: idx >= 0 ? idx : props.tasks.length,
  })
  insertBeforeId.value = null
}

function onGroupDragOver(event) {
  const taskData = event.dataTransfer.types.includes('application/task-id')
  if (!taskData) return
  // Only set end-indicator if not hovering a specific task row
  if (!insertBeforeId.value) insertBeforeId.value = '__end__'
}

function onGroupDrop(event) {
  const fromTaskId = event.dataTransfer.getData('application/task-id')
  const fromSectionId = event.dataTransfer.getData('application/section-id') || null
  if (!fromTaskId) return

  emit('task-move', {
    taskId: fromTaskId,
    fromSectionId,
    toSectionId: props.sectionId,
    position: props.tasks.length,
  })
  insertBeforeId.value = null
}
</script>
