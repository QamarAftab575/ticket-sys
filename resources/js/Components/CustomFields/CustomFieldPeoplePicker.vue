<template>
  <div class="relative w-full" ref="rootRef">

    <!-- Trigger: shows selected member chips + search input inline -->
    <div
      @click.stop="openPicker"
      class="flex items-center flex-wrap gap-1 min-h-[28px] w-full rounded px-1 py-0.5 cursor-text hover:bg-gray-50 transition"
      :class="open ? 'ring-1 ring-indigo-400 bg-white' : ''"
    >
      <!-- Selected chips -->
      <span
        v-for="member in selectedMembers"
        :key="member.id"
        class="flex items-center gap-1 bg-gray-100 rounded-full pl-0.5 pr-2 py-0.5 text-xs font-medium text-gray-700"
      >
        <Avatar :name="member.name" :src="member.avatar" size="xs" class="flex-shrink-0" />
        {{ member.name }}
        <button
          type="button"
          @click.stop="remove(member.id)"
          class="ml-0.5 text-gray-400 hover:text-red-500 transition leading-none"
        >
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </span>

      <!-- Placeholder -->
      <span v-if="!selectedMembers.length && !open" class="text-gray-400 text-xs px-1">—</span>
    </div>

    <!-- Dropdown -->
    <Teleport to="body">
      <div
        v-if="open"
        class="fixed bg-white border border-gray-200 rounded-lg shadow-xl z-[9999] w-64 py-1"
        :style="dropdownStyle"
        @mousedown.stop
      >
        <!-- Search -->
        <div class="px-3 pb-2 pt-1">
          <input
            ref="searchRef"
            v-model="search"
            type="text"
            placeholder="Search members..."
            class="w-full text-sm border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>

        <!-- Members list -->
        <div class="max-h-52 overflow-y-auto">
          <div
            v-if="filteredMembers.length === 0"
            class="px-3 py-3 text-center text-xs text-gray-400"
          >
            No members found
          </div>

          <button
            v-for="member in filteredMembers"
            :key="member.id"
            type="button"
            @mousedown.prevent="toggle(member)"
            class="w-full flex items-center gap-2.5 px-3 py-1.5 text-sm hover:bg-gray-50 transition text-left"
            :class="isSelected(member.id) ? 'bg-indigo-50' : ''"
          >
            <Avatar :name="member.name" :src="member.avatar" size="xs" class="flex-shrink-0" />
            <div class="flex flex-col min-w-0 flex-1">
              <span class="font-medium text-gray-800 truncate">{{ member.name }}</span>
              <span class="text-xs text-gray-400 truncate">{{ member.email }}</span>
            </div>
            <svg
              v-if="isSelected(member.id)"
              class="w-4 h-4 text-indigo-500 flex-shrink-0"
              fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </button>
        </div>

        <!-- Clear all -->
        <div
          v-if="selectedIds.length"
          class="border-t mt-1 pt-1"
        >
          <button
            type="button"
            @mousedown.prevent="clearAll"
            class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-red-500 hover:bg-red-50 transition"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Clear all
          </button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onBeforeUnmount } from 'vue'
import Avatar from '@/Components/Avatar.vue'

const props = defineProps({
  /** Array of user IDs currently selected */
  modelValue: { type: Array, default: () => [] },
  /** Project members array: [{ id, name, email, avatar }] */
  members:    { type: Array, default: () => [] },
})

const emit = defineEmits(['update:modelValue'])

const open      = ref(false)
const search    = ref('')
const rootRef   = ref(null)
const searchRef = ref(null)
const dropdownStyle = ref({})

// Normalise: always an array of IDs
const selectedIds = computed(() =>
  Array.isArray(props.modelValue) ? props.modelValue : []
)

const selectedMembers = computed(() =>
  selectedIds.value
    .map(id => props.members.find(m => m.id === id))
    .filter(Boolean)
)

const filteredMembers = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return props.members
  return props.members.filter(
    m => m.name.toLowerCase().includes(q) || (m.email ?? '').toLowerCase().includes(q)
  )
})

function isSelected(id) {
  return selectedIds.value.includes(id)
}

function toggle(member) {
  const current = [...selectedIds.value]
  const idx = current.indexOf(member.id)
  idx === -1 ? current.push(member.id) : current.splice(idx, 1)
  emit('update:modelValue', current)
}

function remove(id) {
  emit('update:modelValue', selectedIds.value.filter(i => i !== id))
}

function clearAll() {
  emit('update:modelValue', [])
  open.value = false
}

function openPicker() {
  open.value = true
  search.value = ''
  nextTick(() => {
    positionDropdown()
    searchRef.value?.focus()
  })
}

function positionDropdown() {
  if (!rootRef.value) return
  const rect = rootRef.value.getBoundingClientRect()
  const spaceBelow = window.innerHeight - rect.bottom
  dropdownStyle.value = {
    left:     `${rect.left + window.scrollX}px`,
    minWidth: `${Math.max(rect.width, 256)}px`,
    ...(spaceBelow >= 280
      ? { top: `${rect.bottom + window.scrollY + 2}px` }
      : { bottom: `${window.innerHeight - rect.top + window.scrollY + 2}px` }),
  }
}

function onDocClick(e) {
  if (rootRef.value && !rootRef.value.contains(e.target)) {
    open.value = false
  }
}

onMounted(() => document.addEventListener('mousedown', onDocClick))
onBeforeUnmount(() => document.removeEventListener('mousedown', onDocClick))
</script>
