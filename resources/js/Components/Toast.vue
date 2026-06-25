<template>
  <div
    v-if="visible"
    :class="[
      'fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg text-white transition-all duration-300 z-50',
      typeClasses[type]
    ]"
  >
    <div class="flex items-center gap-2">
      <span>{{ message }}</span>
      <button
        @click="close"
        class="ml-2 text-white hover:opacity-75"
      >
        
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
  message: String,
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value),
  },
  duration: {
    type: Number,
    default: 3000,
  },
})

const emit = defineEmits(['close'])

const visible = ref(true)

const typeClasses = {
  success: 'bg-green-600',
  error: 'bg-red-600',
  warning: 'bg-yellow-600',
  info: 'bg-blue-600',
}

const close = () => {
  visible.value = false
  emit('close')
}

onMounted(() => {
  if (props.duration > 0) {
    setTimeout(close, props.duration)
  }
})
</script>

