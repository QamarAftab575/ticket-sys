<template>
  <div class="space-y-2">
    <div class="flex space-x-1">
      <div
        v-for="i in 4"
        :key="i"
        class="h-2 flex-1 rounded-full transition-colors"
        :class="getStrengthColor(i)"
      />
    </div>
    <p class="text-xs text-gray-600">
      Strength: <span :class="getStrengthTextClass">{{ getStrengthText }}</span>
    </p>
    <ul class="text-xs text-gray-600 space-y-1">
      <li :class="{ 'text-green-600': hasMinLength, 'text-gray-400': !hasMinLength }">
        ✓ At least 8 characters
      </li>
      <li :class="{ 'text-green-600': hasMixedCase, 'text-gray-400': !hasMixedCase }">
        ✓ Mix of uppercase and lowercase
      </li>
      <li :class="{ 'text-green-600': hasNumbers, 'text-gray-400': !hasNumbers }">
        ✓ Contains numbers
      </li>
    </ul>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  password: {
    type: String,
    default: ''
  }
})

const hasMinLength = computed(() => props.password.length >= 8)
const hasMixedCase = computed(() => /[a-z]/.test(props.password) && /[A-Z]/.test(props.password))
const hasNumbers = computed(() => /\d/.test(props.password))

const strength = computed(() => {
  let score = 0
  if (hasMinLength.value) score++
  if (hasMixedCase.value) score++
  if (hasNumbers.value) score++
  return score
})

const getStrengthColor = (level) => {
  if (strength.value >= level) {
    if (strength.value === 1) return 'bg-red-500'
    if (strength.value === 2) return 'bg-yellow-500'
    if (strength.value === 3) return 'bg-green-500'
  }
  return 'bg-gray-200'
}

const getStrengthText = computed(() => {
  if (strength.value === 0) return 'Weak'
  if (strength.value === 1) return 'Weak'
  if (strength.value === 2) return 'Fair'
  if (strength.value === 3) return 'Strong'
  return 'Strong'
})

const getStrengthTextClass = computed(() => {
  if (strength.value === 0 || strength.value === 1) return 'text-red-600'
  if (strength.value === 2) return 'text-yellow-600'
  return 'text-green-600'
})
</script>
