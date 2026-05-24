<template>
  <div>
    <!-- Fixed phantom scrollbar pinned to bottom of viewport -->
    <div
      ref="phantomScrollRef"
      class="fixed bottom-0 z-30 overflow-x-auto"
      :style="phantomStyle"
      @scroll="onPhantomScroll"
    >
      <div :style="{ width: scrollWidth + 'px', height: '1px' }" />
    </div>

    <div ref="tableScrollRef" class="list-view-table bg-white overflow-x-auto" style="scrollbar-width: none; -ms-overflow-style: none;">
      <!-- Column headers -->
      <ListViewHeader
        :project-id="props.project?.id"
        :columns="allColumns"
        :visible-columns="visibleColumns"
        :column-widths="columnWidths"
        :sort-rules="sortRules"
        :hidden-columns="hiddenColumns"
        :available-fields="availableFields"
        @sort="handleSort"
        @resize-column="(d) => resizeColumn(d.columnId, d.delta)"
        @hide-column="hideColumn"
        @show-column="showColumn"
        @add-column="addColumn"
        @create-field="onCreateField"
        @reorder-column="({ fromIndex, toIndex }) => reorderColumns(fromIndex, toIndex)"
      />

      <LoadingSkeleton v-if="isLoading" />

      <div v-else>
        <!-- Sectioned layout -->
        <template v-if="hasSections">
          <div
            v-for="section in localSections"
            :key="section.id"
          >
            <SectionRow
              :section="section"
              :task-count="tasksForSection(section.id).length"
              :is-collapsed="collapsed.has(section.id)"
              :is-dragging="sectionDragId === section.id"
              :is-drop-target="sectionDropTarget === section.id"
              @toggle-collapse="toggleCollapse(section.id)"
              @create-task="groupRefs[section.id]?.start()"
              @rename="(name) => $emit('rename-section', section.id, name)"
              @delete="requestDeleteSection(section)"
              @drag-start="sectionDragStart($event, section)"
              @drag-end="sectionDragEnd"
              @drag-over="sectionDragOver($event, section.id)"
              @drop="sectionDrop($event, section.id)"
            />

            <TaskGroup
              v-if="!collapsed.has(section.id)"
              :ref="el => { if (el) groupRefs[section.id] = el }"
              :tasks="tasksForSection(section.id)"
              :columns="visibleColumns"
              :column-widths="columnWidths"
              :section-id="section.id"
              :is-drop-target="taskDropSection === section.id"
              :members="props.project?.members || []"
              :custom-fields="customFields"
              @select-task="$emit('select-task', $event)"
              @toggle-complete="$emit('task-completed', $event)"
              @menu="$emit('update-preferences', { taskMenuId: $event })"
              @task-created="$emit('task-created', $event)"
              @task-move="$emit('task-move', $event)"
              @update-dates="handleUpdateDates"
              @update-assignee="$emit('update-assignee', $event)"
              @update-custom-field="$emit('update-custom-field', $event)"
              @edit-field="openEditField"
            />
          </div>
        </template>

        <!-- Sectionless layout -->
        <template v-else>
          <TaskGroup
            ref="unsectionedGroupRef"
            :tasks="tasks || []"
            :columns="visibleColumns"
            :column-widths="columnWidths"
            :section-id="null"
            :members="props.project?.members || []"
            :custom-fields="customFields"
            @select-task="$emit('select-task', $event)"
            @toggle-complete="$emit('task-completed', $event)"
            @menu="$emit('update-preferences', { taskMenuId: $event })"
            @task-created="$emit('task-created', $event)"
            @task-move="$emit('task-move', $event)"
            @update-dates="handleUpdateDates"
            @update-assignee="$emit('update-assignee', $event)"
            @update-custom-field="$emit('update-custom-field', $event)"
            @edit-field="openEditField"
          />
        </template>

        <!-- Add Section button — same style as Add task -->
        <div>
          <div v-if="isAddingSection" class="flex items-center gap-2 px-3 py-1 border-t border-gray-200" style="min-height: 36px;">
            <div class="w-5 flex-shrink-0" />
            <input
              ref="sectionInputRef"
              v-model="newSectionName"
              type="text"
              placeholder="Section name"
              class="flex-1 text-[13px] bg-transparent border-none outline-none placeholder-gray-400 text-gray-800 py-0.5"
              @keydown.enter="commitAddSection"
              @keydown.escape="cancelAddSection"
              @blur="commitAddSection"
            />
          </div>
          <button
            v-else
            class="flex items-center gap-1.5 w-full px-3 py-2.5 text-[13px] font-medium text-gray-500 hover:text-indigo-600 hover:bg-indigo-50/50 border-t border-gray-200 transition-colors mt-1"
            @click="startAddSection"
          >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add section
          </button>
        </div>
      </div>
    </div>

    <!-- Delete section modal -->
    <DeleteSectionModal
      v-if="deletingSection"
      :section="deletingSection"
      :task-count="tasksForSection(deletingSection.id).length"
      :all-sections="localSections"
      :loading="deleteLoading"
      @confirm="confirmDeleteSection"
      @cancel="deletingSection = null"
    />

    <!-- Edit field modal (opened from "Edit options" in picker) -->
    <AddFieldModal
      v-if="editingField"
      :project-id="props.project?.id"
      :edit-field="editingField"
      @close="editingField = null"
      @updated="onFieldUpdated"
      @deleted="onFieldDeleted"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'
import SectionRow from './SectionRow.vue'
import TaskGroup from './TaskGroup.vue'
import ListViewHeader from './ListViewHeader.vue'
import LoadingSkeleton from '../LoadingSkeleton.vue'
import DeleteSectionModal from '@/Components/Projects/DeleteSectionModal.vue'
import AddFieldModal from '@/Components/CustomFields/AddFieldModal.vue'
import { useListViewColumns } from '@/Composables/useListViewColumns'
import { useCustomFields } from '@/Composables/useCustomFields'

const props = defineProps({
  project: Object,
  tasks: Array,
  sections: Array,
  filters: Array,
  sort: Array,
  grouping: String,
  isLoading: Boolean,
  initialCollapsed: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits([
  'select-task', 'task-created', 'task-completed', 'update-preferences',
  'sort-changed', 'task-move', 'add-section', 'rename-section', 'delete-section',
  'sections-reordered', 'update-dates', 'update-assignee', 'update-custom-field',
  'section-collapsed',
])
// ── local section state (for optimistic reorder) ─────────────────────────
const localSections = ref([...(props.sections || [])])
watch(
  () => props.sections,
  (v) => { localSections.value = [...(v || [])] },
  { deep: true, immediate: true }
)

const collapsed = ref(new Set(props.initialCollapsed || []))
const groupRefs = ref({})
const unsectionedGroupRef = ref(null)
const sortRules = ref(props.sort || [])

const hasSections = computed(() => localSections.value.length > 0)

const {
  allColumns, visibleColumns, hiddenColumns, columnWidths, availableFields,
  fields: customFields,
  initializeColumns, resizeColumn, hideColumn, showColumn, addColumn, reorderColumns, createCustomField,
} = useListViewColumns(props.project?.id)

const { updateField, deleteField: deleteCustomField } = useCustomFields(props.project?.id)

// ── edit field modal ──────────────────────────────────────────────────────────
const editingField = ref(null)

const openEditField = (field) => { editingField.value = field }

const onFieldUpdated = (updatedField) => {
  // useCustomFields cache already updated by the modal's direct PUT call,
  // but we need to sync the column label too
  const col = visibleColumns.value.find(c => c.id === updatedField.id)
    ?? hiddenColumns.value.find(c => c.id === updatedField.id)
  if (col) {
    col.label = updatedField.name
    col.type  = updatedField.field_type
  }
  // Sync the fields ref in the shared cache
  const idx = customFields.value.findIndex(f => f.id === updatedField.id)
  if (idx !== -1) customFields.value[idx] = updatedField
  editingField.value = null
}

const onFieldDeleted = (fieldId) => {
  // Remove from columns
  visibleColumns.value = visibleColumns.value.filter(c => c.id !== fieldId)
  hiddenColumns.value  = hiddenColumns.value.filter(c => c.id !== fieldId)
  // Remove from fields cache
  const idx = customFields.value.findIndex(f => f.id === fieldId)
  if (idx !== -1) customFields.value.splice(idx, 1)
  editingField.value = null
}

// ── fixed phantom scrollbar ───────────────────────────────────────────────
const tableScrollRef  = ref(null)
const phantomScrollRef = ref(null)
const scrollWidth = ref(0)
const phantomStyle = ref({})

function updatePhantomBar() {
  const el = tableScrollRef.value
  if (!el) return
  scrollWidth.value = el.scrollWidth
  const rect = el.getBoundingClientRect()
  phantomStyle.value = {
    left:  rect.left + 'px',
    width: rect.width + 'px',
  }
}

function onTableScroll() {
  if (phantomScrollRef.value && tableScrollRef.value) {
    phantomScrollRef.value.scrollLeft = tableScrollRef.value.scrollLeft
  }
}

function onPhantomScroll() {
  if (tableScrollRef.value && phantomScrollRef.value) {
    tableScrollRef.value.scrollLeft = phantomScrollRef.value.scrollLeft
  }
}

let resizeObserver = null

onMounted(() => {
  initializeColumns()
  nextTick(() => {
    updatePhantomBar()
    tableScrollRef.value?.addEventListener('scroll', onTableScroll)
    resizeObserver = new ResizeObserver(updatePhantomBar)
    if (tableScrollRef.value) resizeObserver.observe(tableScrollRef.value)
    window.addEventListener('resize', updatePhantomBar)
  })
})

onBeforeUnmount(() => {
  tableScrollRef.value?.removeEventListener('scroll', onTableScroll)
  resizeObserver?.disconnect()
  window.removeEventListener('resize', updatePhantomBar)
})

/** When a new field is created via the modal, add it as a visible column */
const onCreateField = (field) => {
  // Cache is already updated by ListViewHeader before this event fires.
  // Just add the column — TaskRow will find the field def in customFields.
  addColumn({
    id:       field.id,
    label:    field.name,
    type:     field.field_type,
    required: false,
    isCustom: true,
  })
}

const toggleCollapse = (id) => {
  collapsed.value.has(id) ? collapsed.value.delete(id) : collapsed.value.add(id)
  emit('section-collapsed', id)
}

const tasksForSection = (sectionId) => {
  const sectionTasks = Array.isArray(props.tasks) ? props.tasks.filter((t) => t.section_id === sectionId) : []
  // Sort by position to maintain order
  return sectionTasks.sort((a, b) => (a.position ?? 0) - (b.position ?? 0))
}

const handleSort = (sortData) => {
  const idx = sortRules.value.findIndex((r) => r.field === sortData.field)
  sortRules.value = idx >= 0
    ? sortRules.value.map((r, i) => i === idx ? sortData : r)
    : [sortData]
  emit('sort-changed', sortRules.value)
}

// ── add section ───────────────────────────────────────────────────────────
const isAddingSection = ref(false)
const newSectionName = ref('')
const sectionInputRef = ref(null)

function startAddSection() {
  isAddingSection.value = true
  newSectionName.value = ''
  nextTick(() => sectionInputRef.value?.focus())
}

function commitAddSection() {
  const name = newSectionName.value.trim()
  if (name) emit('add-section', name)
  isAddingSection.value = false
  newSectionName.value = ''
}

function cancelAddSection() {
  isAddingSection.value = false
  newSectionName.value = ''
}

// ── section drag & drop ───────────────────────────────────────────────────
const sectionDragId = ref(null)
const sectionDropTarget = ref(null)
const taskDropSection = ref(null)

function sectionDragStart(event, section) {
  sectionDragId.value = section.id
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('application/section-drag-id', section.id)
}

function sectionDragEnd() {
  sectionDragId.value = null
  sectionDropTarget.value = null
}

function sectionDragOver(event, sectionId) {
  if (!sectionDragId.value) return
  event.preventDefault()
  sectionDropTarget.value = sectionId
}

function sectionDrop(event, targetId) {
  const fromId = event.dataTransfer.getData('application/section-drag-id')
  sectionDragId.value = null
  sectionDropTarget.value = null

  if (!fromId || fromId === targetId) return

  const ids = localSections.value.map(s => s.id)
  const fromIdx = ids.indexOf(fromId)
  const toIdx = ids.indexOf(targetId)
  if (fromIdx === -1 || toIdx === -1) return

  // Optimistic reorder
  const reordered = [...localSections.value]
  const [moved] = reordered.splice(fromIdx, 1)
  reordered.splice(toIdx, 0, moved)
  localSections.value = reordered

  emit('sections-reordered', reordered.map(s => s.id))
}

// ── delete section modal ──────────────────────────────────────────────────
const deletingSection = ref(null)
const deleteLoading = ref(false)

// Cache CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content

function requestDeleteSection(section) {
  const taskCount = tasksForSection(section.id).length
  if (taskCount === 0) {
    // No tasks — skip modal, delete immediately
    confirmDeleteSection({ deleteTasks: false, targetSectionId: null })
    deletingSection.value = section
    return
  }
  deletingSection.value = section
}

async function confirmDeleteSection({ deleteTasks, targetSectionId }) {
  if (!deletingSection.value) return
  const section = deletingSection.value
  deleteLoading.value = true

  try {
    const body = {}
    if (deleteTasks) body.delete_tasks = true
    if (targetSectionId) body.target_section_id = targetSectionId

    const response = await fetch(`/api/sections/${section.id}`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify(body),
    })

    if (!response.ok) throw new Error('Failed to delete section')

    // Emit up so Show.vue can update its sections + tasks state
    emit('delete-section', {
      sectionId: section.id,
      deleteTasks,
      targetSectionId,
    })
  } catch {
    // Let Show.vue handle error toasts; nothing to revert here since we haven't mutated locally
  } finally {
    deleteLoading.value = false
    deletingSection.value = null
  }
}

// ── expose for Show.vue "+ Add task" button ───────────────────────────────
const handleUpdateDates = (payload) => emit('update-dates', payload)
const startCreatingInFirstSection = () => {
  if (hasSections.value) {
    const first = localSections.value[0]
    if (first) groupRefs.value[first.id]?.start()
  } else {
    unsectionedGroupRef.value?.start()
  }
}

// Expose column state for parent (Show.vue)
defineExpose({ 
  startCreatingInFirstSection,
  allColumns,
  visibleColumns,
  hiddenColumns,
  columnWidths,
  showColumn,
  hideColumn,
})
</script>
