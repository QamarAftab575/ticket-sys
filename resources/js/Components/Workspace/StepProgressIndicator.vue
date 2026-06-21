<template>
  <div class="mb-8">
    <div class="flex items-center justify-between">
      <div v-for="step in totalSteps" :key="step" class="flex items-center flex-1">
        <div
          :class="[
            'w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all',
            step < currentStep
              ? 'bg-green-500 text-white'
              : step === currentStep
              ? 'bg-blue-600 text-white'
              : 'bg-gray-300 text-gray-600'
          ]"
        >
          {{ step }}
        </div>
        <div
          v-if="step < totalSteps"
          :class="[
            'flex-1 h-1 mx-2 transition-all',
            step < currentStep ? 'bg-green-500' : 'bg-gray-300'
          ]"
        />
      </div>
    </div>
    <div class="flex justify-between mt-4 text-xs font-medium text-gray-600">
      <span v-for="label in stepLabels" :key="label">{{ label }}</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  currentStep: {
    type: Number,
    required: true
  },
  totalSteps: {
    type: Number,
    default: 2
  },
  stepLabels: {
    type: Array,
    default: () => ['Step 1', 'Step 2']
  }
})
</script>

