<template>
  <div class="bg-white border rounded-lg p-4 mb-4 space-y-3">
    <div class="flex items-center justify-between mb-3">
      <h3 class="font-semibold">Filters</h3>
      <button
        @click="$emit('close')"
        class="text-gray-500 hover:text-gray-700"
      >
        Ã—
      </button>
    </div>

    <!-- Filter rules -->
    <div class="space-y-2">
      <div
        v-for="(filter, idx) in localFilters"
        :key="idx"
        class="flex gap-2"
      >
        <select
          v-model="filter.field"
          class="flex-1 px-2 py-1 border rounded text-sm"
        >
          <option value="">Select field</option>
          <option value="name">Name</option>
          <option value="status">Status</option>
          <option value="priority">Priority</option>
          <option value="assignee_id">Assignee</option>
        </select>
        <select
          v-model="filter.operator"
          class="px-2 py-1 border rounded text-sm"
        >
          <option value="equals">Equals</option>
          <option value="contains">Contains</option>
          <option value="is_empty">Is empty</option>
        </select>
        <input
          v-model="filter.value"
          type="text"
          placeholder="Value"
          class="flex-1 px-2 py-1 border rounded text-sm"
        />
        <button
          @click="removeFilter(idx)"
          class="px-2 py-1 text-red-600 hover:bg-red-50 rounded"
        >
          Remove
        </button>
      </div>
    </div>

    <!-- Add filter button -->
    <button
      @click="addFilter"
      class="w-full px-3 py-2 text-sm border rounded hover:bg-gray-50"
    >
      + Add filter
    </button>

    <!-- Clear all button -->
    <button
      @click="clearFilters"
      class="w-full px-3 py-2 text-sm text-gray-600 border rounded hover:bg-gray-50"
    >
      Clear all
    </button>

    <!-- Apply button -->
    <button
      @click="applyFilters"
      class="w-full px-3 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
    >
      Apply filters
    </button>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  filters: Array,
})

defineEmits(['update', 'close'])

const localFilters = ref([...props.filters])

watch(
  () => props.filters,
  (newFilters) => {
    localFilters.value = [...newFilters]
  }
)

const addFilter = () => {
  localFilters.value.push({
    field: '',
    operator: 'equals',
    value: '',
  })
}

const removeFilter = (idx) => {
  localFilters.value.splice(idx, 1)
}

const clearFilters = () => {
  localFilters.value = []
}

const applyFilters = () => {
  emit('update', localFilters.value)
}
</script>

