<template>
  <div class="bg-white border rounded-lg p-4 mb-4 space-y-3">
    <div class="flex items-center justify-between mb-3">
      <h3 class="font-semibold">Group by</h3>
      <button
        @click="$emit('close')"
        class="text-gray-500 hover:text-gray-700"
      >
        Ã—
      </button>
    </div>

    <!-- Grouping options -->
    <div class="space-y-2">
      <label class="flex items-center gap-2">
        <input
          type="radio"
          :value="null"
          v-model="localGrouping"
          class="w-4 h-4"
        />
        <span class="text-sm">None</span>
      </label>
      <label class="flex items-center gap-2">
        <input
          type="radio"
          value="status"
          v-model="localGrouping"
          class="w-4 h-4"
        />
        <span class="text-sm">Status</span>
      </label>
      <label class="flex items-center gap-2">
        <input
          type="radio"
          value="priority"
          v-model="localGrouping"
          class="w-4 h-4"
        />
        <span class="text-sm">Priority</span>
      </label>
      <label class="flex items-center gap-2">
        <input
          type="radio"
          value="assignee_id"
          v-model="localGrouping"
          class="w-4 h-4"
        />
        <span class="text-sm">Assignee</span>
      </label>
    </div>

    <!-- Apply button -->
    <button
      @click="applyGrouping"
      class="w-full px-3 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
    >
      Apply grouping
    </button>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  grouping: String,
})

defineEmits(['update', 'close'])

const localGrouping = ref(props.grouping)

watch(
  () => props.grouping,
  (newGrouping) => {
    localGrouping.value = newGrouping
  }
)

const applyGrouping = () => {
  emit('update', localGrouping.value)
}
</script>

