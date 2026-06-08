<template>
  <div
    :data-widget-id="widgetId"
    class="bg-white rounded-lg border border-gray-200 shadow-sm transition-all select-none"
    :class="{
      'opacity-50 scale-95 shadow-lg': isDragging,
      'ring-2 ring-blue-400': isDraggingOver,
    }"
    draggable="true"
    @dragstart="handleDragStart"
    @dragend="handleDragEnd"
    @dragover.prevent="emit('dragover-widget', $event)"
  >
    <!-- Widget header -->
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
      <div class="flex items-center gap-2 flex-1">
        <!-- Drag handle -->
        <div
          class="cursor-grab active:cursor-grabbing p-1 text-gray-400 hover:text-gray-600 transition-colors flex-shrink-0"
          title="Drag to reorder"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <circle cx="9" cy="5" r="1.5" />
            <circle cx="9" cy="12" r="1.5" />
            <circle cx="9" cy="19" r="1.5" />
            <circle cx="15" cy="5" r="1.5" />
            <circle cx="15" cy="12" r="1.5" />
            <circle cx="15" cy="19" r="1.5" />
          </svg>
        </div>
        <h3 class="font-semibold text-gray-900">{{ title }}</h3>
      </div>

      <!-- Menu button -->
      <div class="relative flex-shrink-0">
        <button
          @click.stop="showMenu = !showMenu"
          class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors"
          title="Widget options"
          aria-label="Widget options"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <circle cx="12" cy="5" r="2" />
            <circle cx="12" cy="12" r="2" />
            <circle cx="12" cy="19" r="2" />
          </svg>
        </button>

        <!-- Dropdown menu -->
        <div
          v-if="showMenu"
          class="absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50"
          @click.stop
        >
          <button
            @click="handleHide"
            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Hide widget
          </button>
        </div>
      </div>
    </div>

    <!-- Widget content -->
    <div class="p-4">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  title: String,
  widgetId: String,
  isDraggingOver: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['hide', 'dragstart', 'dragend', 'dragover-widget'])

const isDragging = ref(false)
const showMenu = ref(false)

const handleDragStart = (event) => {
  isDragging.value = true
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/plain', props.widgetId)
  emit('dragstart', event)
}

const handleDragEnd = (event) => {
  isDragging.value = false
  emit('dragend', event)
}

const handleHide = () => {
  showMenu.value = false
  emit('hide')
}
</script>
