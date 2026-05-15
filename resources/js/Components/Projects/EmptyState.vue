<template>
  <div class="text-center py-12">
    <div class="text-6xl mb-4">{{ icon }}</div>
    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ title }}</h3>
    <p class="text-gray-600 mb-6">{{ message }}</p>
    <button
      v-if="ctaText"
      @click="$emit('cta')"
      class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
    >
      {{ ctaText }}
    </button>
  </div>
</template>

<script setup>
defineProps({
  icon: String,
  title: String,
  message: String,
  ctaText: String,
})

defineEmits(['cta'])
</script>
