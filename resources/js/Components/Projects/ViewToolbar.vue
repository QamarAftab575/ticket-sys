<template>
  <div class="flex items-center gap-1">
    <!-- Filter button -->
    <button
      @click="showFilterPanel = !showFilterPanel"
      class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 border border-gray-200 rounded hover:bg-gray-50 transition"
      :aria-expanded="showFilterPanel"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
      </svg>
      Filter
      <span v-if="filters && filters.length" class="bg-indigo-600 text-white text-xs px-1.5 py-0.5 rounded-full leading-none">
        {{ filters.length }}
      </span>
    </button>

    <!-- Sort button -->
    <button
      @click="showSortPanel = !showSortPanel"
      class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 border border-gray-200 rounded hover:bg-gray-50 transition"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
      </svg>
      Sort
    </button>

    <!-- Group button -->
    <button
      @click="showGroupPanel = !showGroupPanel"
      class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 border border-gray-200 rounded hover:bg-gray-50 transition"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h9" />
      </svg>
      Group
    </button>
  </div>

  <!-- Panels -->
  <FilterPanel v-if="showFilterPanel" :filters="filters" @update="$emit('filter-changed', $event)" @close="showFilterPanel = false" />
  <SortPanel v-if="showSortPanel" :sort="sort" @update="$emit('sort-changed', $event)" @close="showSortPanel = false" />
  <GroupPanel v-if="showGroupPanel" :grouping="grouping" @update="$emit('grouping-changed', $event)" @close="showGroupPanel = false" />
</template>

<script setup>
import { ref } from 'vue'
import FilterPanel from './FilterPanel.vue'
import SortPanel from './SortPanel.vue'
import GroupPanel from './GroupPanel.vue'
import HidePanel from './HidePanel.vue'

defineProps({
  filters: Array,
  sort: Array,
  grouping: String,
})

defineEmits(['filter-changed', 'sort-changed', 'grouping-changed', 'hide-changed'])

const showFilterPanel = ref(false)
const showSortPanel = ref(false)
const showGroupPanel = ref(false)
const showHidePanel = ref(false)

const removeFilter = (idx) => {
  const newFilters = props.filters.filter((_, i) => i !== idx)
  emit('filter-changed', newFilters)
}
</script>
