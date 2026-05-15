<template>
  <div class="bg-white border rounded-lg p-4 mb-4 space-y-3">
    <div class="flex items-center justify-between mb-3">
      <h3 class="font-semibold">Sort</h3>
      <button
        @click="$emit('close')"
        class="text-gray-500 hover:text-gray-700"
      >
        ×
      </button>
    </div>

    <!-- Sort rules -->
    <div class="space-y-2">
      <div
        v-for="(rule, idx) in localSort"
        :key="idx"
        class="flex gap-2"
      >
        <select
          v-model="rule.field"
          class="flex-1 px-2 py-1 border rounded text-sm"
        >
          <option value="">Select field</option>
          <option value="name">Name</option>
          <option value="due_date">Due Date</option>
          <option value="priority">Priority</option>
          <option value="status">Status</option>
        </select>
        <select
          v-model="rule.direction"
          class="px-2 py-1 border rounded text-sm"
        >
          <option value="asc">Ascending</option>
          <option value="desc">Descending</option>
        </select>
        <button
          @click="removeSort(idx)"
          class="px-2 py-1 text-red-600 hover:bg-red-50 rounded"
        >
          Remove
        </button>
      </div>
    </div>

    <!-- Add sort button -->
    <button
      @click="addSort"
      class="w-full px-3 py-2 text-sm border rounded hover:bg-gray-50"
    >
      + Add sort
    </button>

    <!-- Clear all button -->
    <button
      @click="clearSort"
      class="w-full px-3 py-2 text-sm text-gray-600 border rounded hover:bg-gray-50"
    >
      Clear all
    </button>

    <!-- Apply button -->
    <button
      @click="applySort"
      class="w-full px-3 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
    >
      Apply sort
    </button>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  sort: Array,
})

defineEmits(['update', 'close'])

const localSort = ref([...props.sort])

watch(
  () => props.sort,
  (newSort) => {
    localSort.value = [...newSort]
  }
)

const addSort = () => {
  localSort.value.push({
    field: '',
    direction: 'asc',
  })
}

const removeSort = (idx) => {
  localSort.value.splice(idx, 1)
}

const clearSort = () => {
  localSort.value = []
}

const applySort = () => {
  emit('update', localSort.value)
}
</script>
