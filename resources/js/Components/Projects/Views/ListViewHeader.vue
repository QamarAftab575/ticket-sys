<template>
  <div class="flex items-center bg-white border-b border-gray-200 sticky top-0 z-10" style="min-height: 32px;">
    <!-- Drag handle space -->
    <div class="w-5 flex-shrink-0" />
    <!-- Checkbox space -->
    <div class="w-6 flex-shrink-0 mr-2" />

    <!-- Column headers -->
    <div class="flex flex-1 min-w-0">
      <div
        v-for="(column, idx) in visibleColumns"
        :key="column.id"
        class="flex items-center group relative flex-shrink-0 select-none"
        :class="[
          column.id !== 'name' ? 'border-r border-[#e8e8e8]' : '',
          dragOverIdx === idx && dragOverIdx !== draggingIdx ? 'border-l-2 border-l-blue-500' : '',
          draggingIdx === idx ? 'opacity-40' : '',
        ]"
        :style="{ width: columnWidths[column.id] || '200px' }"
        :draggable="!column.required && !isResizing"
        @dragstart="onDragStart($event, idx)"
        @dragend="onDragEnd"
        @dragover.prevent="onDragOver($event, idx)"
        @dragleave="onDragLeave"
        @drop.prevent="onDrop($event, idx)"
      >
        <button
          @click="!isDraggingCol && !isResizing && handleSort(column.id)"
          @contextmenu.prevent="activeColumnMenu = column.id"
          class="flex-1 px-2 py-1 text-left text-[11px] font-medium text-gray-400 uppercase tracking-wide hover:text-gray-600 flex items-center gap-1 truncate"
          :class="column.required ? 'cursor-pointer' : 'cursor-grab active:cursor-grabbing'"
        >
          {{ column.label }}
          <svg v-if="getSortDirection(column.id)" class="w-3 h-3 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
            <path v-if="getSortDirection(column.id) === 'asc'" d="M7 14l5-5 5 5z"/>
            <path v-else d="M7 10l5 5 5-5z"/>
          </svg>
        </button>

        <!-- Resize handle — overlays the column border on hover -->
        <div
          v-if="idx < visibleColumns.length - 1"
          @mousedown="startResize(column.id, $event)"
          class="absolute right-0 top-1 bottom-1 w-1 hover:bg-blue-400 cursor-col-resize opacity-0 group-hover:opacity-100 transition-opacity z-10"
        />

        <!-- Column context menu -->
        <div
          v-if="activeColumnMenu === column.id"
          class="absolute top-full right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-20 py-1 min-w-[140px]"
          @mouseleave="activeColumnMenu = null"
        >
          <button v-if="!column.required" @click="hideColumn(column.id)" class="block w-full text-left px-3 py-1.5 text-[13px] hover:bg-gray-50">Hide column</button>
          <button @click="activeColumnMenu = null" class="block w-full text-left px-3 py-1.5 text-[13px] hover:bg-gray-50 text-gray-400">Close</button>
        </div>
      </div>

      <!-- Add field -->
      <button
        @click="showAddFieldModal = true"
        class="flex-shrink-0 flex items-center gap-1 px-2 py-1 text-[11px] text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 border-l border-gray-100 transition"
      >
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add field
      </button>
    </div>

    <AddFieldModal
      v-if="showAddFieldModal"
      :project-id="projectId"
      @close="showAddFieldModal = false"
      @created="onFieldCreated"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import AddFieldModal from '@/Components/CustomFields/AddFieldModal.vue'
import { useCustomFields } from '@/Composables/useCustomFields'

const props = defineProps({
  projectId:       { type: String, required: true },
  visibleColumns:  Array,
  columnWidths:    Object,
  sortRules:       Array,
  hiddenColumns:   Array,
  availableFields: Array,
})

const emit = defineEmits([
  'sort', 'resize-column', 'hide-column', 'show-column',
  'add-column', 'create-field', 'reorder-column',
])

const { fields } = useCustomFields(props.projectId)
const activeColumnMenu  = ref(null)
const showAddFieldModal = ref(false)
const resizingColumn    = ref(null)
const resizeStartX      = ref(0)
const isResizing        = ref(false)

// ── column drag-to-reorder ────────────────────────────────────────────────
const draggingIdx  = ref(null)
const dragOverIdx  = ref(null)
const isDraggingCol = ref(false)

function onDragStart(event, idx) {
  const col = props.visibleColumns[idx]
  if (col?.required) { event.preventDefault(); return }
  draggingIdx.value  = idx
  isDraggingCol.value = true
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/plain', String(idx))
}

function onDragEnd() {
  draggingIdx.value  = null
  dragOverIdx.value  = null
  // small delay so click handler doesn't fire a sort after drop
  setTimeout(() => { isDraggingCol.value = false }, 50)
}

function onDragOver(event, idx) {
  if (draggingIdx.value === null) return
  dragOverIdx.value = idx
}

function onDragLeave() {
  dragOverIdx.value = null
}

function onDrop(event, toIdx) {
  const fromIdx = draggingIdx.value
  draggingIdx.value = null
  dragOverIdx.value = null
  if (fromIdx === null || fromIdx === toIdx) return
  // Don't allow dropping onto the required (name) column position 0
  const targetCol = props.visibleColumns[toIdx]
  if (targetCol?.required) return
  emit('reorder-column', { fromIndex: fromIdx, toIndex: toIdx })
}

// ── sort ──────────────────────────────────────────────────────────────────
const getSortDirection = (columnId) => props.sortRules?.find(r => r.field === columnId)?.direction ?? null

const handleSort = (columnId) => {
  const current = getSortDirection(columnId)
  emit('sort', { field: columnId, direction: current === 'asc' ? 'desc' : 'asc' })
}

// ── hide column ───────────────────────────────────────────────────────────
const hideColumn = (columnId) => {
  emit('hide-column', columnId)
  activeColumnMenu.value = null
}

// ── resize ────────────────────────────────────────────────────────────────
const startResize = (columnId, event) => {
  event.preventDefault()   // prevent text selection
  event.stopPropagation()  // prevent drag start on the parent
  isResizing.value  = true
  resizingColumn.value = columnId
  resizeStartX.value   = event.clientX
  document.addEventListener('mousemove', handleResize)
  document.addEventListener('mouseup', stopResize)
}

const handleResize = (event) => {
  if (!resizingColumn.value) return
  const delta = event.clientX - resizeStartX.value
  emit('resize-column', { columnId: resizingColumn.value, delta })
  resizeStartX.value = event.clientX
}

const stopResize = () => {
  resizingColumn.value = null
  document.removeEventListener('mousemove', handleResize)
  document.removeEventListener('mouseup', stopResize)
  // delay clearing so the click event that fires after mouseup doesn't trigger sort
  setTimeout(() => { isResizing.value = false }, 50)
}

// ── add field ─────────────────────────────────────────────────────────────
const onFieldCreated = (field) => {
  if (!fields.value.find(f => f.id === field.id)) fields.value.push(field)
  emit('create-field', field)
  showAddFieldModal.value = false
}
</script>
