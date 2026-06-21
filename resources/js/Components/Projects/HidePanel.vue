<template>
  <div class="bg-white border rounded-lg p-4 mb-4 space-y-3">
    <div class="flex items-center justify-between mb-3">
      <h3 class="font-semibold">Hide columns</h3>
      <button
        @click="$emit('close')"
        class="text-gray-500 hover:text-gray-700"
      >
        Ã—
      </button>
    </div>

    <!-- Column visibility toggles -->
    <div class="space-y-2">
      <label class="flex items-center gap-2">
        <input type="checkbox" class="w-4 h-4" checked />
        <span class="text-sm">Name</span>
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" class="w-4 h-4" checked />
        <span class="text-sm">Assignee</span>
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" class="w-4 h-4" checked />
        <span class="text-sm">Due Date</span>
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" class="w-4 h-4" checked />
        <span class="text-sm">Priority</span>
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" class="w-4 h-4" checked />
        <span class="text-sm">Status</span>
      </label>
    </div>

    <!-- Apply button -->
    <button
      @click="$emit('close')"
      class="w-full px-3 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
    >
      Done
    </button>
  </div>
</template>

<script setup>
defineEmits(['close'])
</script>

