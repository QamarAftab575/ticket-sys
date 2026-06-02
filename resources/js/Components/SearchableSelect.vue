<template>
  <div class="relative" ref="containerRef">
    <!-- Trigger button -->
    <button
      type="button"
      @click="toggleDropdown"
      @keydown.escape="close"
      class="w-full flex items-center justify-between px-4 py-2 border rounded-lg bg-white text-left focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
      :class="[
        isOpen ? 'border-blue-500 ring-2 ring-blue-500' : 'border-gray-300 hover:border-gray-400',
        disabled ? 'bg-gray-100 cursor-not-allowed opacity-60' : 'cursor-pointer',
      ]"
      :disabled="disabled"
    >
      <span :class="selectedLabel ? 'text-gray-900' : 'text-gray-400'">
        {{ selectedLabel || placeholder }}
      </span>
      <!-- Chevron -->
      <svg
        class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform duration-150"
        :class="isOpen ? 'rotate-180' : ''"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 20 20"
        fill="currentColor"
      >
        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
      </svg>
    </button>

    <!-- Dropdown panel -->
    <div
      v-if="isOpen"
      class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden"
    >
      <!-- Search input -->
      <div class="p-2 border-b border-gray-100">
        <div class="relative">
          <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
          </svg>
          <input
            ref="searchInputRef"
            v-model="query"
            type="text"
            :placeholder="searchPlaceholder"
            class="w-full pl-8 pr-3 py-1.5 text-sm border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            @keydown.escape="close"
            @keydown.enter.prevent="selectHighlighted"
            @keydown.arrow-down.prevent="moveHighlight(1)"
            @keydown.arrow-up.prevent="moveHighlight(-1)"
          />
        </div>
      </div>

      <!-- Options list -->
      <ul
        ref="listRef"
        class="max-h-56 overflow-y-auto py-1"
        role="listbox"
      >
        <!-- Clear option -->
        <li
          v-if="clearable && modelValue"
          @click="selectOption(null)"
          class="px-4 py-2 text-sm text-gray-400 italic cursor-pointer hover:bg-gray-50"
        >
          {{ clearLabel }}
        </li>

        <li
          v-for="(option, index) in filteredOptions"
          :key="option[valueKey]"
          :ref="el => { if (el) optionRefs[index] = el }"
          @click="selectOption(option)"
          @mouseenter="highlightedIndex = index"
          class="px-4 py-2 text-sm cursor-pointer flex items-center justify-between"
          :class="[
            highlightedIndex === index ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50',
            option[valueKey] === modelValue ? 'font-medium' : '',
          ]"
          role="option"
          :aria-selected="option[valueKey] === modelValue"
        >
          <span>{{ option[labelKey] }}</span>
          <!-- Checkmark for selected -->
          <svg
            v-if="option[valueKey] === modelValue"
            class="w-4 h-4 text-blue-600 flex-shrink-0"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
          </svg>
        </li>

        <!-- Empty state -->
        <li v-if="filteredOptions.length === 0" class="px-4 py-3 text-sm text-gray-400 text-center">
          No results for "{{ query }}"
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number, null],
    default: null,
  },
  options: {
    type: Array,
    default: () => [],
  },
  valueKey: {
    type: String,
    default: 'value',
  },
  labelKey: {
    type: String,
    default: 'label',
  },
  placeholder: {
    type: String,
    default: 'Select an option',
  },
  searchPlaceholder: {
    type: String,
    default: 'Search...',
  },
  clearable: {
    type: Boolean,
    default: false,
  },
  clearLabel: {
    type: String,
    default: '— Clear selection —',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const query = ref('')
const highlightedIndex = ref(-1)
const containerRef = ref(null)
const searchInputRef = ref(null)
const listRef = ref(null)
const optionRefs = ref([])

const selectedLabel = computed(() => {
  if (!props.modelValue) return ''
  const found = props.options.find(o => o[props.valueKey] === props.modelValue)
  return found ? found[props.labelKey] : props.modelValue
})

const filteredOptions = computed(() => {
  if (!query.value.trim()) return props.options
  const q = query.value.toLowerCase()
  return props.options.filter(o =>
    String(o[props.labelKey]).toLowerCase().includes(q) ||
    String(o[props.valueKey]).toLowerCase().includes(q)
  )
})

const toggleDropdown = () => {
  if (props.disabled) return
  isOpen.value ? close() : open()
}

const open = () => {
  isOpen.value = true
  query.value = ''
  highlightedIndex.value = -1
  optionRefs.value = []
  nextTick(() => {
    searchInputRef.value?.focus()
    // Scroll selected option into view
    const selectedIdx = filteredOptions.value.findIndex(o => o[props.valueKey] === props.modelValue)
    if (selectedIdx >= 0) {
      highlightedIndex.value = selectedIdx
      nextTick(() => scrollToHighlighted())
    }
  })
}

const close = () => {
  isOpen.value = false
  query.value = ''
  highlightedIndex.value = -1
}

const selectOption = (option) => {
  emit('update:modelValue', option ? option[props.valueKey] : null)
  close()
}

const selectHighlighted = () => {
  if (highlightedIndex.value >= 0 && filteredOptions.value[highlightedIndex.value]) {
    selectOption(filteredOptions.value[highlightedIndex.value])
  }
}

const moveHighlight = (direction) => {
  const max = filteredOptions.value.length - 1
  if (max < 0) return
  if (highlightedIndex.value === -1) {
    highlightedIndex.value = direction > 0 ? 0 : max
  } else {
    highlightedIndex.value = Math.max(0, Math.min(max, highlightedIndex.value + direction))
  }
  nextTick(() => scrollToHighlighted())
}

const scrollToHighlighted = () => {
  const el = optionRefs.value[highlightedIndex.value]
  if (el) el.scrollIntoView({ block: 'nearest' })
}

// Reset highlight when search query changes
watch(query, () => {
  highlightedIndex.value = -1
  optionRefs.value = []
})

// Close on outside click
const handleOutsideClick = (e) => {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    close()
  }
}

onMounted(() => document.addEventListener('mousedown', handleOutsideClick))
onBeforeUnmount(() => document.removeEventListener('mousedown', handleOutsideClick))
</script>
