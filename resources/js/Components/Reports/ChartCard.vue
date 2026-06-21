<template>
  <div class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col">
    <!-- Header -->
    <div class="mb-4">
      <h3 class="text-sm font-semibold text-gray-900">{{ title }}</h3>
      <p v-if="subtitle" class="text-xs text-gray-400 mt-0.5">{{ subtitle }}</p>
    </div>
    <!-- Chart area â€” fixed height so charts don't grow unbounded -->
    <div class="flex-1 relative" style="height: 240px">
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title:    { type: String, required: true },
  subtitle: { type: String, default: '' },
})
</script>

