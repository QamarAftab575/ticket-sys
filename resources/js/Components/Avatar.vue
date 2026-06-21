<template>
  <div 
    :class="[
      'flex items-center justify-center rounded-full font-semibold text-white',
      sizeClasses,
      bgColor || defaultBgColor
    ]"
    :style="customStyle"
  >
    <img 
      v-if="src && !imageError" 
      :src="src" 
      :alt="name"
      class="w-full h-full rounded-full object-cover"
      @error="handleImageError"
    />
    <span v-else :class="textSizeClasses">{{ initials }}</span>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  name: {
    type: String,
    default: null,
  },
  src: {
    type: String,
    default: null
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl'].includes(value)
  },
  bgColor: {
    type: String,
    default: null
  }
})

const imageError = ref(false)

// Reset error state when src changes (e.g. after new upload)
watch(() => props.src, () => {
  imageError.value = false
})

const sizeClasses = computed(() => {
  const sizes = {
    xs: 'w-6 h-6',
    sm: 'w-8 h-8',
    md: 'w-10 h-10',
    lg: 'w-12 h-12',
    xl: 'w-16 h-16',
    '2xl': 'w-20 h-20'
  }
  return sizes[props.size] || sizes.md
})

const textSizeClasses = computed(() => {
  const sizes = {
    xs: 'text-xs',
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg',
    xl: 'text-xl',
    '2xl': 'text-2xl'
  }
  return sizes[props.size] || sizes.md
})

const initials = computed(() => {
  if (!props.name) return '?'
  
  const words = props.name.trim().split(/\s+/)
  if (words.length === 1) {
    return words[0].charAt(0).toUpperCase()
  }
  return (words[0].charAt(0) + words[words.length - 1].charAt(0)).toUpperCase()
})

const defaultBgColor = computed(() => {
  const colors = [
    'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500',
    'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-teal-500',
    'bg-orange-500', 'bg-cyan-500',
  ]
  if (!props.name) return colors[0]
  const hash = props.name.split('').reduce((acc, char) => char.charCodeAt(0) + ((acc << 5) - acc), 0)
  return colors[Math.abs(hash) % colors.length]
})

const customStyle = computed(() => {
  if (props.bgColor && props.bgColor.startsWith('#')) {
    return { backgroundColor: props.bgColor }
  }
  return {}
})

const handleImageError = () => {
  imageError.value = true
}
</script>

