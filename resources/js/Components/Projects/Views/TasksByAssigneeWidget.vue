<template>
  <div class="space-y-4">
    <div v-if="!assigneeStats || assigneeStats.length === 0" class="text-center py-6 text-gray-500">
      <p class="text-sm">No tasks assigned yet</p>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="assignee in assigneeStats"
        :key="assignee.id"
        class="cursor-pointer hover:bg-gray-50 p-2 rounded transition-colors"
        @click="$emit('filter', { field: 'assignee', value: assignee.id })"
      >
        <div class="flex items-center justify-between mb-1">
          <div class="flex items-center gap-2 flex-1 min-w-0">
            <img
              v-if="assignee.avatar"
              :src="assignee.avatar"
              :alt="assignee.name"
              class="w-6 h-6 rounded-full"
            />
            <div v-else class="w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center text-xs font-bold text-white">
              {{ assignee.name.charAt(0).toUpperCase() }}
            </div>
            <span class="text-sm font-medium text-gray-900 truncate">{{ assignee.name }}</span>
          </div>
          <span class="text-sm font-bold text-gray-900 ml-2">{{ assignee.total }}</span>
        </div>

        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
          <div class="flex h-full">
            <div
              class="bg-green-600"
              :style="{ width: (assignee.completed / assignee.total) * 100 + '%' }"
            />
            <div
              class="bg-blue-600"
              :style="{ width: ((assignee.total - assignee.completed) / assignee.total) * 100 + '%' }"
            />
          </div>
        </div>

        <div class="flex justify-between text-xs text-gray-600 mt-1">
          <span>{{ assignee.completed }} completed</span>
          <span>{{ assignee.total - assignee.completed }} remaining</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  assigneeStats: Array,
})

defineEmits(['filter'])
</script>
