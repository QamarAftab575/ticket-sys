<template>
  <div ref="scrollContainerRef" class="overflow-x-auto -mx-4 px-4 md:mx-0 md:px-0">
    <div class="flex gap-4 min-w-full pb-4 md:min-w-0 items-start">

      <div
        v-for="section in sections"
        :key="section.id"
        class="flex-shrink-0 w-72 bg-gray-50 rounded-lg p-3 flex flex-col transition-opacity"
        :class="{ 'opacity-40': draggingSectionId === section.id }"
        draggable="true"
        @dragstart="onSectionDragStart($event, section)"
        @dragend="onSectionDragEnd"
        @dragover.prevent="onSectionDragOver($event, section.id)"
        @drop.prevent="onSectionDrop($event, section.id)"
      >
        <!-- Column header -->
        <div
          class="flex items-center justify-between mb-3 cursor-grab active:cursor-grabbing"
          :class="{ 'border-t-2 border-indigo-400 pt-1': sectionDropTarget === section.id }"
        >
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ backgroundColor: section.color || '#6366f1' }" />
            <h3 class="font-semibold text-sm truncate">{{ section.name }}</h3>
            <span class="text-xs text-gray-400">{{ getTasksForSection(section.id).length }}</span>
          </div>
          <button
            @click.stop="startCreating(section.id)"
            class="p-1 hover:bg-gray-200 rounded text-gray-500 flex-shrink-0"
            title="Add task"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
          </button>
        </div>

        <!-- Task cards container with drop zone -->
        <div 
          class="space-y-2 flex-1 min-h-[200px]"
          @dragover.prevent="onCardDragOver($event, section.id, null)"
          @drop.prevent.stop="onCardDrop($event, section.id, null)"
        >
          <!-- Empty state drop indicator -->
          <div
            v-if="getTasksForSection(section.id).length === 0 && cardDropTarget?.sectionId === section.id"
            class="h-0.5 bg-indigo-400 rounded mb-2"
          />

          <div
            v-for="task in getTasksForSection(section.id)"
            :key="task.id"
            class="relative"
            @dragover.prevent="onCardDragOver($event, section.id, task.id)"
            @drop.prevent.stop="onCardDrop($event, section.id, task.id)"
          >
            <!-- Insert-before indicator -->
            <div
              v-if="cardDropTarget?.sectionId === section.id && cardDropTarget?.beforeTaskId === task.id"
              class="h-0.5 bg-indigo-400 rounded mb-1"
            />

            <TaskCard
              :task="task"
              :is-dragging="draggingTaskId === task.id"
              draggable="true"
              @dragstart="onCardDragStart($event, task)"
              @dragend="onCardDragEnd"
              @click="$emit('select-task', task)"
              @update-dates="$emit('update-dates', $event)"
            />
          </div>

          <!-- End-of-list drop indicator -->
          <div
            v-if="getTasksForSection(section.id).length > 0 && cardDropTarget?.sectionId === section.id && cardDropTarget?.beforeTaskId === '__end__'"
            class="h-0.5 bg-indigo-400 rounded"
          />

          <!-- Inline creation -->
          <div
            v-if="creatingInSection === section.id"
            class="bg-white rounded border border-indigo-300 p-2 shadow-sm"
          >
            <input
              :ref="el => { if (el) inputRefs[section.id] = el }"
              v-model="newTaskName"
              type="text"
              placeholder="Task name"
              class="w-full text-sm outline-none"
              @keydown.enter="commit(section.id)"
              @keydown.escape="cancel"
              @blur="onBlur(section.id)"
            />
          </div>
        </div>

        <!-- Add task footer -->
        <button
          v-if="creatingInSection !== section.id"
          @click="startCreating(section.id)"
          class="mt-3 w-full py-1.5 text-sm text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded flex items-center gap-1.5 px-2 transition-colors"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Add task
        </button>
      </div>

      <!-- Add section column -->
      <div class="flex-shrink-0 w-72">
        <div v-if="isAddingSection" class="bg-gray-50 rounded-lg p-3">
          <input
            ref="sectionInputRef"
            v-model="newSectionName"
            type="text"
            placeholder="Section name"
            class="w-full text-sm font-semibold bg-transparent border-b border-indigo-400 outline-none py-0.5 mb-2"
            @keydown.enter="commitAddSection"
            @keydown.escape="cancelAddSection"
            @blur="commitAddSection"
          />
        </div>
        <button
          v-else
          @click="startAddSection"
          class="w-full py-2 px-3 text-sm text-gray-400 hover:text-indigo-600 hover:bg-gray-50 rounded-lg border-2 border-dashed border-gray-200 hover:border-indigo-300 flex items-center gap-1.5 transition-colors"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Add section
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted, onUnmounted } from 'vue'
import TaskCard from './TaskCard.vue'

const props = defineProps({
  project: Object,
  tasks: Array,
  sections: Array,
})

const emit = defineEmits([
  'select-task', 'task-created', 'task-move', 'add-section', 'sections-reordered', 'update-dates',
])

// â”€â”€ helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const getTasksForSection = (sectionId) => {
  const sectionTasks = (Array.isArray(props.tasks) ? props.tasks : []).filter((t) => t.section_id === sectionId)
  // Sort by position to maintain order
  return sectionTasks.sort((a, b) => (a.position ?? 0) - (b.position ?? 0))
}

// â”€â”€ inline task creation â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const creatingInSection = ref(null)
const newTaskName = ref('')
const inputRefs = ref({})
let suppressBlur = false

const startCreating = (sectionId) => {
  suppressBlur = false
  creatingInSection.value = sectionId
  newTaskName.value = ''
  nextTick(() => inputRefs.value[sectionId]?.focus())
}

const commit = (sectionId) => {
  suppressBlur = true
  const name = newTaskName.value.trim()
  if (name) emit('task-created', { name, section_id: sectionId })
  newTaskName.value = ''
  nextTick(() => { suppressBlur = false; inputRefs.value[sectionId]?.focus() })
}

const cancel = () => {
  suppressBlur = true
  creatingInSection.value = null
  newTaskName.value = ''
}

const onBlur = (sectionId) => {
  if (suppressBlur) return
  const name = newTaskName.value.trim()
  if (name) emit('task-created', { name, section_id: sectionId })
  creatingInSection.value = null
  newTaskName.value = ''
}

// â”€â”€ add section â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const isAddingSection = ref(false)
const newSectionName = ref('')
const sectionInputRef = ref(null)

const startAddSection = () => {
  isAddingSection.value = true
  newSectionName.value = ''
  nextTick(() => sectionInputRef.value?.focus())
}

const commitAddSection = () => {
  const name = newSectionName.value.trim()
  if (name) emit('add-section', name)
  isAddingSection.value = false
  newSectionName.value = ''
}

const cancelAddSection = () => {
  isAddingSection.value = false
  newSectionName.value = ''
}

// â”€â”€ card (task) drag & drop â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const draggingTaskId = ref(null)
// { sectionId, beforeTaskId } â€” beforeTaskId = '__end__' means append
const cardDropTarget = ref(null)

// â”€â”€ auto-scroll during drag â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const scrollContainerRef = ref(null)
let autoScrollInterval = null
const SCROLL_EDGE_SIZE = 100 // pixels from edge to trigger scroll
const SCROLL_SPEED = 10 // pixels per frame
let currentScrollDirections = { horizontal: null, vertical: null }

function startAutoScroll() {
  if (autoScrollInterval) return
  
  autoScrollInterval = setInterval(() => {
    // Horizontal scroll (board container)
    if (scrollContainerRef.value && currentScrollDirections.horizontal) {
      const container = scrollContainerRef.value
      if (currentScrollDirections.horizontal === 'left') {
        container.scrollLeft -= SCROLL_SPEED
      } else if (currentScrollDirections.horizontal === 'right') {
        container.scrollLeft += SCROLL_SPEED
      }
    }
    
    // Vertical scroll (window/viewport)
    if (currentScrollDirections.vertical) {
      if (currentScrollDirections.vertical === 'up') {
        window.scrollBy(0, -SCROLL_SPEED)
      } else if (currentScrollDirections.vertical === 'down') {
        window.scrollBy(0, SCROLL_SPEED)
      }
    }
  }, 16) // ~60fps
}

function stopAutoScroll() {
  if (autoScrollInterval) {
    clearInterval(autoScrollInterval)
    autoScrollInterval = null
  }
  currentScrollDirections = { horizontal: null, vertical: null }
}

function checkAutoScroll(event) {
  if (!draggingTaskId.value) return
  
  let needsScroll = false
  const mouseX = event.clientX
  const mouseY = event.clientY
  
  // Reset directions
  currentScrollDirections = { horizontal: null, vertical: null }
  
  // Check horizontal scrolling (board container)
  if (scrollContainerRef.value) {
    const container = scrollContainerRef.value
    const rect = container.getBoundingClientRect()
    
    // Near left edge
    if (mouseX - rect.left < SCROLL_EDGE_SIZE && container.scrollLeft > 0) {
      currentScrollDirections.horizontal = 'left'
      needsScroll = true
    }
    // Near right edge
    else if (rect.right - mouseX < SCROLL_EDGE_SIZE && 
             container.scrollLeft < container.scrollWidth - container.clientWidth) {
      currentScrollDirections.horizontal = 'right'
      needsScroll = true
    }
  }
  
  // Check vertical scrolling (viewport)
  const viewportHeight = window.innerHeight
  const maxScroll = document.documentElement.scrollHeight - viewportHeight
  
  // Near top edge
  if (mouseY < SCROLL_EDGE_SIZE && window.scrollY > 0) {
    currentScrollDirections.vertical = 'up'
    needsScroll = true
  }
  // Near bottom edge
  else if (viewportHeight - mouseY < SCROLL_EDGE_SIZE && window.scrollY < maxScroll) {
    currentScrollDirections.vertical = 'down'
    needsScroll = true
  }
  
  // Start or stop scrolling based on whether we need it
  if (needsScroll) {
    startAutoScroll()
  } else {
    stopAutoScroll()
  }
}

onMounted(() => {
  document.addEventListener('dragover', checkAutoScroll)
})

onUnmounted(() => {
  document.removeEventListener('dragover', checkAutoScroll)
  stopAutoScroll()
})

function onCardDragStart(event, task) {
  draggingTaskId.value = task.id
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('application/task-id', task.id)
  event.dataTransfer.setData('application/section-id', task.section_id ?? '')
  event.stopPropagation()
}

function onCardDragEnd() {
  draggingTaskId.value = null
  cardDropTarget.value = null
  stopAutoScroll()
}

function onCardDragOver(event, sectionId, taskId) {
  if (!draggingTaskId.value) return
  cardDropTarget.value = { sectionId, beforeTaskId: taskId ?? '__end__' }
  event.stopPropagation() // Prevent parent dragover handlers from overriding
}

function onCardDrop(event, sectionId, beforeTaskId) {
  const taskId = event.dataTransfer.getData('application/task-id')
  const fromSectionId = event.dataTransfer.getData('application/section-id') || null
  cardDropTarget.value = null
  draggingTaskId.value = null
  if (!taskId) return

  const sectionTasks = getTasksForSection(sectionId)
  let position = sectionTasks.length
  if (beforeTaskId && beforeTaskId !== '__end__') {
    const idx = sectionTasks.findIndex(t => t.id === beforeTaskId)
    if (idx >= 0) position = idx
  }

  emit('task-move', { taskId, fromSectionId, toSectionId: sectionId, position })
}

// â”€â”€ section column drag & drop â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const draggingSectionId = ref(null)
const sectionDropTarget = ref(null)

function onSectionDragStart(event, section) {
  // Only trigger if dragging from the header area (not a card)
  if (draggingTaskId.value) return
  draggingSectionId.value = section.id
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('application/section-drag-id', section.id)
}

function onSectionDragEnd() {
  draggingSectionId.value = null
  sectionDropTarget.value = null
}

function onSectionDragOver(event, sectionId) {
  const hasSectionDrag = event.dataTransfer.types.includes('application/section-drag-id')
  if (!hasSectionDrag || draggingTaskId.value) return
  sectionDropTarget.value = sectionId
}

function onSectionDrop(event, targetId) {
  const fromId = event.dataTransfer.getData('application/section-drag-id')
  draggingSectionId.value = null
  sectionDropTarget.value = null
  if (!fromId || fromId === targetId) return

  const ids = (props.sections || []).map(s => s.id)
  const fromIdx = ids.indexOf(fromId)
  const toIdx = ids.indexOf(targetId)
  if (fromIdx === -1 || toIdx === -1) return

  const reordered = [...ids]
  reordered.splice(fromIdx, 1)
  reordered.splice(toIdx, 0, fromId)
  emit('sections-reordered', reordered)
}
</script>

