<template>
  <div class="space-y-4">
    <div v-if="!priorityStats || Object.keys(priorityStats).length === 0" class="text-center py-6 text-gray-500">
      <p class="text-sm">No tasks with priorities yet</p>
    </div>

    <div v-else class="flex gap-6">
      <!-- Donut chart -->
      <div class="flex-1 flex items-center justify-center">
        <svg class="w-32 h-32" viewBox="0 0 100 100">
          <circle
            v-for="(segment, index) in chartSegments"
            :key="index"
            cx="50"
            cy="50"
            r="40"
            :fill="'none'"
            :stroke="segment.color"
            :stroke-width="20"
            :stroke-dasharray="`${segment.dasharray} 251.2`"
            :stroke-dashoffset="`${segment.offset}`"
            :style="{ cursor: 'pointer' }"
            @click="$emit('filter', { field: 'priority', value: segment.priority })"
          />
          <text x="50" y="50" text-anchor="middle" dy="0.3em" class="text-sm font-bold fill-gray-900">
            {{ totalTasks }}
          </text>
          <text x="50" y="60" text-anchor="middle" dy="0.3em" class="text-xs fill-gray-600">
            tasks
          </text>
        </svg>
      </div>

      <!-- Legend -->
      <div class="flex-1 space-y-2">
        <div
          v-for="(count, priority) in priorityStats"
          :key="priority"
          class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded transition-colors"
          @click="$emit('filter', { field: 'priority', value: priority })"
        >
          <div
            class="w-3 h-3 rounded-full flex-shrink-0"
            :style="{ backgroundColor: getPriorityColor(priority) }"
          />
          <span class="text-sm text-gray-900 flex-1">{{ formatPriority(priority) }}</span>
          <span class="text-sm font-bold text-gray-700">{{ count }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  priorityStats: Object,
})

defineEmits(['filter'])

const totalTasks = computed(() => {
  if (!props.priorityStats) return 0
  return Object.values(props.priorityStats).reduce((sum, count) => sum + count, 0)
})

const chartSegments = computed(() => {
  if (!props.priorityStats || totalTasks.value === 0) return []

  const priorities = ['urgent', 'high', 'medium', 'low', 'none']
  const segments = []
  let offset = 0

  priorities.forEach((priority) => {
    const count = props.priorityStats[priority] || 0
    if (count > 0) {
      const percentage = (count / totalTasks.value) * 100
      const dasharray = (percentage / 100) * 251.2
      segments.push({
        priority,
        color: getPriorityColor(priority),
        dasharray,
        offset,
      })
      offset -= dasharray
    }
  })

  return segments
})

const getPriorityColor = (priority) => {
  const colors = {
    urgent: '#dc2626',
    high: '#f97316',
    medium: '#eab308',
    low: '#3b82f6',
    none: '#d1d5db',
  }
  return colors[priority] || '#d1d5db'
}

const formatPriority = (priority) => {
  const labels = {
    urgent: 'Urgent',
    high: 'High',
    medium: 'Medium',
    low: 'Low',
    none: 'None',
  }
  return labels[priority] || priority
}
</script>

