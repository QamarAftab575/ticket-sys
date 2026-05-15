<template>
  <div class="bg-white border rounded-lg p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-semibold text-lg">{{ title }}</h3>
      <button class="p-1 hover:bg-gray-100 rounded">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 8c1.1 0 2-0.9 2-2s-0.9-2-2-2-2 0.9-2 2 0.9 2 2 2zm0 2c-1.1 0-2 0.9-2 2s0.9 2 2 2 2-0.9 2-2-0.9-2-2-2zm0 6c-1.1 0-2 0.9-2 2s0.9 2 2 2 2-0.9 2-2-0.9-2-2-2z" />
        </svg>
      </button>
    </div>
    <slot />
  </div>
</template>

<script setup>
defineProps({
  title: String,
})
</script>
