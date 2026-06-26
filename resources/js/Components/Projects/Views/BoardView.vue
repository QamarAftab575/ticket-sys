<template>
  <div ref="scrollContainerRef" class="overflow-x-auto -mx-6 px-6 md:mx-0 md:px-0">
    <div class="flex gap-6 min-w-full pb-4 md:min-w-0 items-start">

      <div
        v-for="section in sections"
        :key="section.id"
        class="flex-shrink-0 w-80 bg-gradient-to-b from-slate-50 to-slate-50/50 rounded-2xl border border-slate-200/60 p-4 flex flex-col transition-all duration-200 hover:shadow-lg hover:border-slate-300/80"
        :class="{ 'opacity-50 scale-95': draggingSectionId === section.id }"
        draggable="true"
        @dragstart="onSectionDragStart($event, section)"
        @dragend="onSectionDragEnd"
        @dragover.prevent="onSectionDragOver($event, section.id)"
        @drop.prevent="onSectionDrop($event, section.id)"
      >
        <!-- Column header -->
        <div
          class="flex items-center justify-between mb-4 cursor-grab active:cursor-grabbing px-1 transition-all duration-150"
          :class="{ 'border-t-3 border-indigo-500 pt-2': sectionDropTarget === section.id }"
        >
          <div class="flex items-center gap-2.5 flex-1 min-w-0">
            <div class="w-3 h-3 rounded-full flex-shrink-0 shadow-sm" :style="{ backgroundColor: section.color || '#3b82f6' }" />
            <h3 class="font-semibold text-sm text-slate-900 truncate">{{ section.name }}</h3>
            <span class="text-xs font-medium text-slate-500 bg-white/70 px-2 py-0.5 rounded-full flex-shrink-0">{{ getTasksForSection(section.id).length }}</span>
          </div>
          <button
            @click.stop="startCreating(section.id)"
            class="ml-2 p-2 hover:bg-indigo-100 hover:text-indigo-600 rounded-lg text-slate-400 flex-shrink-0 transition-all duration-150"
            title="Add task to this section"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
          </button>
        </div>

        <!-- Task cards container with drop zone -->
        <div 
          class="space-y-3 flex-1 min-h-[240px] px-1"
          @dragover.prevent="onCardDragOver($event, section.id, null)"
          @drop.prevent.stop="onCardDrop($event, section.id, null)"
        >
          <!-- Empty state drop indicator -->
          <div
            v-if="getTasksForSection(section.id).length === 0 && cardDropTarget?.sectionId === section.id"
            class="h-1 bg-gradient-to-r from-indigo-400 to-indigo-500 rounded-full shadow-md"
          />

          <div
            v-for="task in getTasksForSection(section.id)"
            :key="task.id"
            class="relative group"
            @dragover.prevent="onCardDragOver($event, section.id, task.id)"
            @drop.prevent.stop="onCardDrop($event, section.id, task.id)"
          >
            <!-- Insert-before indicator -->
            <div
              v-if="cardDropTarget?.sectionId === section.id && cardDropTarget?.beforeTaskId === task.id"
              class="h-1 bg-gradient-to-r from-indigo-400 to-indigo-500 rounded-full shadow-md mb-2"
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
            class="h-1 bg-gradient-to-r from-indigo-400 to-indigo-500 rounded-full shadow-md"
          />

          <!-- Inline creation -->
          <div
            v-if="creatingInSection === section.id"
            class="bg-white rounded-xl border-2 border-indigo-400 shadow-md p-3 transition-all duration-200"
          >
            <input
              :ref="el => { if (el) inputRefs[section.id] = el }"
              v-model="newTaskName"
              type="text"
              placeholder="Enter task name..."
              class="w-full text-sm font-medium text-slate-900 placeholder-slate-400 outline-none bg-transparent"
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
          class="mt-4 w-full py-2.5 text-sm font-medium text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg flex items-center gap-2 px-2 transition-all duration-150 border border-transparent hover:border-indigo-200"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          <span>Add task</span>
        </button>
      </div>

      <!-- Add section column -->
      <div class="flex-shrink-0 w-80">
        <div v-if="isAddingSection" class="bg-gradient-to-b from-indigo-50 to-indigo-50/50 rounded-2xl border-2 border-indigo-400 p-4 shadow-lg">
          <input
            ref="sectionInputRef"
            v-model="newSectionName"
            type="text"
            placeholder="Section name..."
            class="w-full text-sm font-semibold bg-transparent border-b-2 border-indigo-400 outline-none py-2 text-slate-900 placeholder-slate-400"
            @keydown.enter="commitAddSection"
            @keydown.escape="cancelAddSection"
            @blur="commitAddSection"
          />
        </div>
        <button
          v-else
          @click="startAddSection"
          class="w-full py-3 px-4 text-sm font-medium text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-2xl border-2 border-dashed border-slate-300 hover:border-indigo-400 flex items-center gap-2 transition-all duration-150 group"
        >
          <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          <span>Add section</span>
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

//  helpers 
const getTasksForSection = (sectionId) => {
  const sectionTasks = (Array.isArray(props.tasks) ? props.tasks : []).filter((t) => t.section_id === sectionId)
  // Sort by position to maintain order
  return sectionTasks.sort((a, b) => (a.position ?? 0) - (b.position ?? 0))
}

//  inline task creation 
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

//  add section 
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

//  card (task) drag & drop 
const draggingTaskId = ref(null)
// { sectionId, beforeTaskId }   beforeTaskId = '__end__' means append
const cardDropTarget = ref(null)

//  auto-scroll during drag 
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

//  section column drag & drop 
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

