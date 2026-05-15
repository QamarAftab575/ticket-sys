<template>
  <div class="fixed top-4 right-4 z-50 space-y-2">
    <transition-group name="toast">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="[
          'px-4 py-3 rounded-lg shadow-lg text-white font-medium',
          getToastClass(toast.type),
        ]"
      >
        <div class="flex items-center justify-between gap-4">
          <span>{{ toast.message }}</span>
          <button
            @click="removeToast(toast.id)"
            class="hover:opacity-75"
          >
            ×
          </button>
        </div>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useToast } from '@/Composables/useToast'

const { toasts, removeToast } = useToast()

const getToastClass = (type) => {
  const classes = {
    success: 'bg-green-600',
    error: 'bg-red-600',
    warning: 'bg-orange-600',
    info: 'bg-blue-600',
  }
  return classes[type] || 'bg-gray-600'
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
</style>
