<template>
  <div class="w-72 bg-white border-l border-gray-200 flex flex-col overflow-hidden">
    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
      <h3 class="font-semibold text-gray-900 text-sm">Columns</h3>
      <button
        @click="$emit('close')"
        class="p-1 hover:bg-gray-100 rounded transition"
        title="Close"
      >
        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Search -->
    <div class="px-3 py-2 border-b border-gray-100">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search columns..."
        class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded bg-gray-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500"
      />
    </div>

    <!-- Confirmation Dialog - Show All -->
    <div v-if="showConfirmShowAll" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm">
        <h3 class="text-sm font-semibold text-gray-900 mb-2">Show all columns?</h3>
        <p class="text-xs text-gray-600 mb-4">This will display all hidden columns in your list view.</p>
        <div class="flex gap-2 justify-end">
          <button
            @click="showConfirmShowAll = false"
            class="px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded transition"
          >
            Cancel
          </button>
          <button
            @click="confirmShowAll"
            class="px-3 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded transition"
          >
            Show all
          </button>
        </div>
      </div>
    </div>

    <!-- Confirmation Dialog - Hide All -->
    <div v-if="showConfirmHideAll" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm">
        <h3 class="text-sm font-semibold text-gray-900 mb-2">Hide all columns?</h3>
        <p class="text-xs text-gray-600 mb-4">This will hide all columns except the required ones. You can show them again later.</p>
        <div class="flex gap-2 justify-end">
          <button
            @click="showConfirmHideAll = false"
            class="px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded transition"
          >
            Cancel
          </button>
          <button
            @click="confirmHideAll"
            class="px-3 py-2 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded transition"
          >
            Hide all
          </button>
        </div>
      </div>
    </div>

    <!-- Columns List -->
    <div class="flex-1 overflow-y-auto">
      <div class="space-y-2 p-3">
        <!-- Show all button -->
        <button
          @click="showConfirmShowAll = true"
          class="w-full text-left px-2 py-1.5 text-xs text-blue-600 hover:bg-blue-50 rounded transition font-medium mb-2"
        >
          Show all
        </button>

        <!-- Column items with toggle switches -->
        <div
          v-for="column in filteredColumns"
          :key="column.id"
          class="flex items-center justify-between px-2 py-2 rounded hover:bg-gray-50 transition group"
          :class="column.required ? 'opacity-60' : ''"
        >
          <label
            class="flex-1 text-xs text-gray-700 cursor-pointer select-none"
            :class="column.required ? 'cursor-not-allowed' : ''"
          >
            {{ column.label }}
          </label>
          
          <!-- Toggle Switch -->
          <ToggleSwitch
            :model-value="isColumnVisible(column.id)"
            :disabled="column.required"
            @update:model-value="toggleColumnVisibility(column.id)"
          />

          <!-- Required badge -->
          <span
            v-if="column.required"
            class="text-[10px] text-gray-400 font-medium ml-2"
          >
            FIXED
          </span>
        </div>

        <!-- Empty state -->
        <div v-if="filteredColumns.length === 0" class="text-center py-4">
          <p class="text-xs text-gray-400">No columns found</p>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="border-t border-gray-100 px-3 py-2">
      <button
        @click="showConfirmHideAll = true"
        class="w-full text-left px-2 py-1.5 text-xs text-gray-600 hover:bg-gray-50 rounded transition"
      >
        Hide all
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import ToggleSwitch from '@/Components/ToggleSwitch.vue'

const props = defineProps({
  allColumns: {
    type: Array,
    required: true,
  },
  hiddenColumns: {
    type: Array,
    required: true,
  },
})

const emit = defineEmits(['close', 'toggle-column', 'show-all', 'hide-all'])

const searchQuery = ref('')
const showConfirmShowAll = ref(false)
const showConfirmHideAll = ref(false)

const filteredColumns = computed(() => {
  if (!searchQuery.value) return props.allColumns
  
  const query = searchQuery.value.toLowerCase()
  return props.allColumns.filter(col => 
    col.label.toLowerCase().includes(query)
  )
})

function isColumnVisible(columnId) {
  // Column is visible if it's NOT in hiddenColumns
  return !props.hiddenColumns.find(c => c.id === columnId)
}

function toggleColumnVisibility(columnId) {
  const column = props.allColumns.find(c => c.id === columnId)
  if (column?.required) return
  
  emit('toggle-column', columnId)
}

function confirmShowAll() {
  showConfirmShowAll.value = false
  emit('show-all')
}

function confirmHideAll() {
  showConfirmHideAll.value = false
  // Only hide non-required columns
  const nonRequiredVisible = props.allColumns
    .filter(c => !c.required && !props.hiddenColumns.find(hc => hc.id === c.id))
    .map(c => c.id)
  
  if (nonRequiredVisible.length > 0) {
    nonRequiredVisible.forEach(columnId => {
      emit('toggle-column', columnId)
    })
  }
}
</script>
