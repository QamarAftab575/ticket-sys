<template>
  <div class="space-y-4">
    <div class="text-center">
      <div class="text-3xl font-bold text-gray-900 mb-1">
        {{ stats.completed_tasks }} of {{ stats.total_tasks }}
      </div>
      <p class="text-sm text-gray-600">tasks complete</p>
    </div>

    <div class="w-full bg-gray-200 rounded-full h-3">
      <div
        class="bg-green-600 h-3 rounded-full transition-all"
        :style="{ width: stats.completion_percentage + '%' }"
      />
    </div>

    <p class="text-center text-sm font-medium text-gray-700">
      {{ stats.completion_percentage }}% complete
    </p>

    <div class="grid grid-cols-3 gap-3 pt-2">
      <div class="text-center">
        <p class="text-2xl font-bold text-blue-600">{{ stats.incomplete_tasks }}</p>
        <p class="text-xs text-gray-600">Incomplete</p>
      </div>
      <div class="text-center">
        <p class="text-2xl font-bold text-green-600">{{ stats.completed_tasks }}</p>
        <p class="text-xs text-gray-600">Completed</p>
      </div>
      <div class="text-center">
        <p class="text-2xl font-bold text-red-600">{{ stats.overdue_count }}</p>
        <p class="text-xs text-gray-600">Overdue</p>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  stats: Object,
})
</script>

