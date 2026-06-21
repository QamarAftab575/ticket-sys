<template>
  <div
    @click="$emit('click')"
    class="flex-1 h-8 bg-blue-500 rounded cursor-pointer hover:bg-blue-600 flex items-center px-2 text-white text-xs font-medium truncate"
    :style="{ marginLeft: getLeftOffset() + '%', width: getWidth() + '%' }"
    :title="task.name"
  >
    {{ task.name }}
  </div>
</template>

<script setup>
defineProps({
  task: Object,
  dateRange: Array,
})

defineEmits(['click'])

const getLeftOffset = () => {
  if (!props.task.start_date || !props.dateRange.length) return 0
  const startIdx = props.dateRange.indexOf(props.task.start_date)
  return startIdx >= 0 ? (startIdx / props.dateRange.length) * 100 : 0
}

const getWidth = () => {
  if (!props.task.start_date || !props.task.due_date || !props.dateRange.length) {
    return 5
  }
  const startIdx = props.dateRange.indexOf(props.task.start_date)
  const endIdx = props.dateRange.indexOf(props.task.due_date)
  if (startIdx < 0 || endIdx < 0) return 5
  return ((endIdx - startIdx + 1) / props.dateRange.length) * 100
}
</script>

