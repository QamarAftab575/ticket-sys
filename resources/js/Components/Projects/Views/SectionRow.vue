<template>
  <div
    class="flex items-center gap-1 px-3 py-1.5 group select-none"
    :class="{ 'opacity-40': isDragging, 'border-t-2 border-t-indigo-400': isDropTarget }"
    draggable="true"
    @dragstart="$emit('drag-start', $event)"
    @dragend="$emit('drag-end', $event)"
    @dragover.prevent="$emit('drag-over', $event)"
    @drop.prevent="$emit('drop', $event)"
  >
    <!-- Drag handle -->
    <span class="flex-shrink-0 cursor-grab active:cursor-grabbing text-gray-300 hover:text-gray-500 opacity-0 group-hover:opacity-100 transition-opacity">
      <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M9 3h2v2H9V3zm0 4h2v2H9V7zm0 4h2v2H9v-2zm4-8h2v2h-2V3zm0 4h2v2h-2V7zm0 4h2v2h-2v-2z"/>
      </svg>
    </span>

    <!-- Collapse arrow -->
    <button
      @click="$emit('toggle-collapse')"
      class="flex-shrink-0 w-4 h-4 flex items-center justify-center text-gray-500 hover:text-gray-700 transition-colors"
    >
      <svg
        :class="{ 'rotate-90': !isCollapsed }"
        class="w-3 h-3 transition-transform"
        fill="none" stroke="currentColor" viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
    </button>

    <!-- Section name -->
    <input
      v-if="isEditing"
      ref="nameInput"
      v-model="editName"
      class="flex-1 text-[13px] font-semibold bg-transparent border-b border-indigo-400 outline-none py-0.5"
      @keydown.enter="commitRename"
      @keydown.escape="cancelRename"
      @blur="commitRename"
    />
    <span
      v-else
      class="flex-1 text-[13px] font-semibold text-gray-800 cursor-pointer hover:text-indigo-600 truncate"
      @dblclick="startRename"
      :title="section.name"
    >{{ section.name }}</span>

    <!-- Task count -->
    <span class="text-[12px] text-gray-400 flex-shrink-0 ml-1">{{ taskCount }}</span>

    <!-- Hover actions -->
    <button
      @click="$emit('create-task')"
      class="flex-shrink-0 px-1.5 py-0.5 text-[12px] text-indigo-500 hover:bg-indigo-50 rounded opacity-0 group-hover:opacity-100 transition-opacity ml-1"
    >+ Add task</button>

    <!-- Section menu -->
    <div class="relative flex-shrink-0">
      <button
        @click.stop="menuOpen = !menuOpen"
        class="p-0.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded opacity-0 group-hover:opacity-100 transition-opacity"
      >
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
        </svg>
      </button>
      <div
        v-if="menuOpen"
        v-click-outside="() => menuOpen = false"
        class="absolute right-0 top-6 z-20 bg-white border border-gray-200 rounded-lg shadow-lg py-1 w-40"
      >
        <button @click="startRename(); menuOpen = false" class="w-full text-left px-3 py-1.5 text-[13px] text-gray-700 hover:bg-gray-50">Rename section</button>
        <button @click="$emit('delete'); menuOpen = false" class="w-full text-left px-3 py-1.5 text-[13px] text-red-600 hover:bg-red-50">Delete section</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue'

const props = defineProps({
  section: Object,
  taskCount: Number,
  isCollapsed: Boolean,
  isDragging: Boolean,
  isDropTarget: Boolean,
})

const emit = defineEmits(['toggle-collapse', 'create-task', 'rename', 'delete', 'drag-start', 'drag-end', 'drag-over', 'drop'])

const isEditing = ref(false)
const editName = ref('')
const nameInput = ref(null)
const menuOpen = ref(false)

function startRename() {
  editName.value = props.section.name
  isEditing.value = true
  nextTick(() => nameInput.value?.select())
}

function commitRename() {
  const name = editName.value.trim()
  if (name && name !== props.section.name) emit('rename', name)
  isEditing.value = false
}

function cancelRename() {
  isEditing.value = false
}

const vClickOutside = {
  mounted(el, binding) {
    el._clickOutside = (e) => { if (!el.contains(e.target)) binding.value(e) }
    document.addEventListener('click', el._clickOutside)
  },
  unmounted(el) {
    document.removeEventListener('click', el._clickOutside)
  },
}
</script>

