<template>
  <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-8 text-center space-y-6 animate-bounce">
      <div class="text-6xl"></div>

      <div class="space-y-2">
        <h2 class="text-2xl font-bold text-gray-900">Your free trial has ended</h2>
        <p class="text-gray-600">
          Upgrade your plan to continue using your workspace and keep your team productive.
        </p>
      </div>

      <div class="space-y-3 pt-2">
        <Link
          :href="route('billing.plans')"
          class="block w-full bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition text-center"
        >
          View Plans
        </Link>
        <button
          @click="$emit('dismiss')"
          class="w-full px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium"
        >
          Dismiss
        </button>
      </div>

      <p class="text-xs text-gray-500">
        Only workspace owners can upgrade. Ask your workspace owner to view plans.
      </p>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineEmits(['dismiss'])
</script>

<style scoped>
@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.animate-bounce {
  animation: bounce 2s infinite;
}
</style>

