<template>
  <div class="space-y-4">
    <div
      v-for="i in 5"
      :key="i"
      class="h-12 bg-gray-200 rounded animate-pulse"
    />
  </div>
</template>

<script setup>
defineProps({
  count: {
    type: Number,
    default: 5,
  },
})
</script>
