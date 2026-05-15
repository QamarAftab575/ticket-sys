<template>
  <div
    class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow"
    :class="{ 'opacity-50': isDragging }"
    @dragstart="handleDragStart"
    @dragend="handleDragEnd"
    draggable="true"
  >
    <!-- Widget header -->
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
      <div class="flex items-center gap-2 flex-1">
        <div
          class="cursor-grab active:cursor-grabbing p-1 text-gray-400 hover:text-gray-600"
          title="Drag to reorder"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
          </svg>
        </div>
        <h3 class="font-semibold text-gray-900">{{ title }}</h3>
      </div>
      <button
        @click="$emit('remove')"
        class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors"
        title="Remove widget"
        aria-label="Remove widget"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Widget content -->
    <div class="p-4">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  title: String,
})

defineEmits(['remove', 'drag-start', 'drag-end'])

const isDragging = ref(false)

const handleDragStart = (event) => {
  isDragging.value = true
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/plain', event.currentTarget.getAttribute('data-widget-id'))
  emit('drag-start', event)
}

const handleDragEnd = (event) => {
  isDragging.value = false
  emit('drag-end', event)
}
</script>
