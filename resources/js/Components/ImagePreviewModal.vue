<template>
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="src"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/85 backdrop-blur-sm"
        @click.self="$emit('close')"
        @keydown.escape="$emit('close')"
        tabindex="-1"
        ref="backdropRef"
      >
        <div class="relative flex flex-col bg-white rounded-xl shadow-2xl mx-4 my-6 w-full max-w-5xl max-h-[92vh]">

          <!-- Header -->
          <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-100 flex-shrink-0">
            <span class="text-sm font-medium text-gray-700 truncate max-w-xs">{{ filename || 'Image preview' }}</span>

            <div class="flex items-center gap-1">
              <button @click="zoomOut" :disabled="scale <= MIN_SCALE"
                class="p-1.5 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-md transition-colors disabled:opacity-30" title="Zoom out">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0zM8 11h6"/>
                </svg>
              </button>

              <button @click="resetZoom"
                class="px-2 py-1 text-xs font-mono text-gray-600 hover:bg-gray-100 rounded-md min-w-[48px] text-center transition-colors" title="Reset zoom">
                {{ Math.round(scale * 100) }}%
              </button>

              <button @click="zoomIn" :disabled="scale >= MAX_SCALE"
                class="p-1.5 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-md transition-colors disabled:opacity-30" title="Zoom in">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0zM11 8v6M8 11h6"/>
                </svg>
              </button>

              <div class="w-px h-4 bg-gray-200 mx-1"/>

              <a :href="src" :download="filename || 'image'" target="_blank"
                class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-md transition-colors" title="Download">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
              </a>

              <button @click="$emit('close')"
                class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-md transition-colors" title="Close (Esc)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Image area — overflow scroll, drag to pan -->
          <div
            ref="containerRef"
            class="overflow-auto flex-1 bg-gray-50 rounded-b-xl select-none"
            style="min-height: 0"
          >
            <div class="flex items-center justify-center p-4" :style="innerStyle">
              <img
                ref="imgRef"
                :src="src"
                :alt="filename || 'Preview'"
                class="rounded-lg block"
                :style="imgStyle"
                draggable="false"
                @load="onImageLoad"
                @mousedown.prevent="startDrag"
              />
            </div>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'

const props = defineProps({
  src: { type: String, default: null },
  filename: { type: String, default: null },
})

defineEmits(['close'])

const backdropRef = ref(null)
const containerRef = ref(null)
const imgRef = ref(null)

const MIN_SCALE = 0.1
const MAX_SCALE = 5
const STEP = 0.25

const scale = ref(1)
const naturalW = ref(0)
const naturalH = ref(0)

watch(() => props.src, (val) => {
  if (val) {
    scale.value = 1
    naturalW.value = 0
    naturalH.value = 0
    nextTick(() => backdropRef.value?.focus())
  }
})

function onImageLoad() {
  const img = imgRef.value
  if (!img) return
  naturalW.value = img.naturalWidth
  naturalH.value = img.naturalHeight
  fitToContainer()
}

function fitToContainer() {
  const container = containerRef.value
  if (!container || !naturalW.value || !naturalH.value) return
  const padding = 32
  const availW = container.clientWidth - padding
  const availH = container.clientHeight - padding
  const scaleW = availW / naturalW.value
  const scaleH = availH / naturalH.value
  scale.value = Math.min(1, scaleW, scaleH)
}

const innerStyle = computed(() => {
  if (!naturalW.value) return { minHeight: '100%', minWidth: '100%' }
  const w = naturalW.value * scale.value
  const h = naturalH.value * scale.value
  return {
    minWidth: '100%',
    minHeight: '100%',
    width: `${w + 32}px`,
    height: `${h + 32}px`,
  }
})

const imgStyle = computed(() => {
  if (!naturalW.value) return { maxWidth: '100%', maxHeight: 'calc(92vh - 120px)' }
  return {
    width: `${naturalW.value * scale.value}px`,
    height: `${naturalH.value * scale.value}px`,
    cursor: isDragging.value ? 'grabbing' : 'grab',
    transition: isDragging.value ? 'none' : 'width 0.1s, height 0.1s',
  }
})

function zoomIn()    { scale.value = Math.min(MAX_SCALE, +(scale.value + STEP).toFixed(2)) }
function zoomOut()   { scale.value = Math.max(MIN_SCALE, +(scale.value - STEP).toFixed(2)) }
function resetZoom() { fitToContainer() }

// ── Click-and-drag pan ────────────────────────────────────────────────────
const isDragging = ref(false)
let dragStartX = 0
let dragStartY = 0
let scrollStartX = 0
let scrollStartY = 0

function startDrag(e) {
  if (e.button !== 0) return   // left button only
  isDragging.value = true
  dragStartX = e.clientX
  dragStartY = e.clientY
  const c = containerRef.value
  scrollStartX = c.scrollLeft
  scrollStartY = c.scrollTop

  window.addEventListener('mousemove', onDragMove)
  window.addEventListener('mouseup', stopDrag)
}

function onDragMove(e) {
  if (!isDragging.value) return
  const dx = e.clientX - dragStartX
  const dy = e.clientY - dragStartY
  const c = containerRef.value
  c.scrollLeft = scrollStartX - dx
  c.scrollTop  = scrollStartY - dy
}

function stopDrag() {
  isDragging.value = false
  window.removeEventListener('mousemove', onDragMove)
  window.removeEventListener('mouseup', stopDrag)
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.18s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
