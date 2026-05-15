<template>
  <div class="relative w-full" ref="rootRef">
    <!-- Trigger -->
    <button
      type="button"
      @click.stop="toggleOpen"
      class="flex items-center gap-1 flex-wrap min-w-0 w-full rounded px-1.5 py-0.5 text-xs hover:bg-gray-100 transition min-h-[24px]"
    >
      <!-- Single select: one chip -->
      <template v-if="field.field_type === 'single_select'">
        <span
          v-if="selectedOption"
          class="px-2 py-0.5 rounded-full text-white text-xs font-medium truncate max-w-full"
          :style="{ backgroundColor: selectedOption.color }"
        >
          {{ selectedOption.name }}
        </span>
        <span v-else class="text-gray-400">—</span>
      </template>

      <!-- Multi select: chips -->
      <template v-else-if="field.field_type === 'multi_select'">
        <template v-if="selectedOptions.length">
          <span
            v-for="opt in selectedOptions"
            :key="opt.id"
            class="px-2 py-0.5 rounded-full text-white text-xs font-medium"
            :style="{ backgroundColor: opt.color }"
          >
            {{ opt.name }}
          </span>
        </template>
        <span v-else class="text-gray-400">—</span>
      </template>
    </button>

    <!-- Dropdown (teleported so it escapes overflow:hidden parents) -->
    <Teleport to="body">
      <div
        v-if="open"
        class="fixed bg-white border border-gray-200 rounded-lg shadow-xl z-[9999] min-w-[200px] py-1"
        :style="dropdownStyle"
        @mousedown.stop
      >
        <!-- Options list -->
        <div
          v-for="opt in field.options"
          :key="opt.id"
          @mousedown.prevent="toggle(opt.id)"
          class="flex items-center gap-2 px-3 py-1.5 cursor-pointer hover:bg-gray-50 transition select-none"
        >
          <svg
            v-if="isSelected(opt.id)"
            class="w-3.5 h-3.5 text-indigo-600 flex-shrink-0"
            fill="currentColor"
            viewBox="0 0 24 24"
          >
            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
          </svg>
          <span v-else class="w-3.5 h-3.5 flex-shrink-0" />

          <span
            class="px-2 py-0.5 rounded-full text-white text-xs font-medium"
            :style="{ backgroundColor: opt.color }"
          >
            {{ opt.name }}
          </span>
        </div>

        <!-- Empty state -->
        <div v-if="!field.options?.length" class="px-3 py-2 text-xs text-gray-400">
          No options yet
        </div>

        <!-- Footer actions -->
        <div class="border-t mt-1 pt-1">
          <!-- Clear -->
          <div
            v-if="hasValue"
            @mousedown.prevent="clear"
            class="flex items-center gap-2 px-3 py-1.5 cursor-pointer hover:bg-red-50 text-red-500 text-xs transition select-none"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Clear
          </div>

          <!-- Edit options -->
          <div
            @mousedown.prevent="onEditOptions"
            class="flex items-center gap-2 px-3 py-1.5 cursor-pointer hover:bg-gray-50 text-gray-600 text-xs transition select-none"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit options
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue'

const props = defineProps({
  field:       { type: Object, required: true },
  modelValue:  { type: [String, Array], default: null },
})

const emit = defineEmits(['update:modelValue', 'edit-field'])

const open    = ref(false)
const rootRef = ref(null)
const dropdownStyle = ref({})

const isMulti = computed(() => props.field.field_type === 'multi_select')

const selectedOption = computed(() =>
  props.field.options?.find(o => o.id === props.modelValue) ?? null
)

const selectedOptions = computed(() => {
  const val = Array.isArray(props.modelValue) ? props.modelValue : []
  return props.field.options?.filter(o => val.includes(o.id)) ?? []
})

const hasValue = computed(() =>
  isMulti.value
    ? Array.isArray(props.modelValue) && props.modelValue.length > 0
    : !!props.modelValue
)

function isSelected(id) {
  return isMulti.value
    ? Array.isArray(props.modelValue) && props.modelValue.includes(id)
    : props.modelValue === id
}

function toggle(id) {
  if (isMulti.value) {
    const current = Array.isArray(props.modelValue) ? [...props.modelValue] : []
    const idx = current.indexOf(id)
    idx === -1 ? current.push(id) : current.splice(idx, 1)
    emit('update:modelValue', current)
  } else {
    emit('update:modelValue', props.modelValue === id ? null : id)
    open.value = false
  }
}

function clear() {
  emit('update:modelValue', isMulti.value ? [] : null)
  open.value = false
}

function onEditOptions() {
  open.value = false
  emit('edit-field', props.field)
}

function toggleOpen() {
  open.value = !open.value
  if (open.value) positionDropdown()
}

function positionDropdown() {
  nextTick(() => {
    if (!rootRef.value) return
    const rect = rootRef.value.getBoundingClientRect()
    const spaceBelow = window.innerHeight - rect.bottom
    const dropdownH  = Math.min(300, (props.field.options?.length ?? 0) * 36 + 80)

    dropdownStyle.value = {
      left:     `${rect.left + window.scrollX}px`,
      minWidth: `${Math.max(rect.width, 200)}px`,
      ...(spaceBelow >= dropdownH
        ? { top: `${rect.bottom + window.scrollY + 2}px` }
        : { bottom: `${window.innerHeight - rect.top + window.scrollY + 2}px` }),
    }
  })
}

function onDocClick(e) {
  if (rootRef.value && !rootRef.value.contains(e.target)) {
    open.value = false
  }
}

onMounted(() => document.addEventListener('mousedown', onDocClick))
onBeforeUnmount(() => document.removeEventListener('mousedown', onDocClick))
</script>
