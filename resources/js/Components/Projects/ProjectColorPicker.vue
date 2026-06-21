<template>
  <div class="color-picker">
    <label class="block text-sm font-medium text-gray-700 mb-2">Project Color</label>
    
    <!-- Preset colors -->
    <div class="mb-4">
      <p class="text-xs text-gray-600 mb-2">Preset Colors</p>
      <div class="grid grid-cols-10 gap-2">
        <button
          v-for="color in presetColors"
          :key="color"
          type="button"
          @click="selectColor(color)"
          :style="{ backgroundColor: color }"
          :class="[
            'w-8 h-8 rounded border-2 transition-all',
            selectedColor === color ? 'border-gray-800 ring-2 ring-offset-2' : 'border-gray-300 hover:border-gray-500'
          ]"
          :title="color"
        />
      </div>
    </div>

    <!-- Custom color input -->
    <div class="mb-4">
      <label class="block text-xs text-gray-600 mb-2">Custom Hex Color</label>
      <div class="flex gap-2">
        <input
          v-model="customColor"
          type="text"
          placeholder="#FF5733"
          class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm"
          @input="validateCustomColor"
        />
        <button
          @click="selectColor(customColor)"
          type="button"
          :disabled="!isValidHex(customColor)"
          class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm disabled:bg-gray-400"
        >
          Apply
        </button>
      </div>
      <p v-if="customColorError" class="text-red-600 text-xs mt-1">{{ customColorError }}</p>
    </div>

    <!-- Preview -->
    <div class="flex items-center gap-3">
      <div
        :style="{ backgroundColor: selectedColor }"
        class="w-12 h-12 rounded border border-gray-300"
      />
      <div>
        <p class="text-sm font-medium text-gray-700">Preview</p>
        <p class="text-xs text-gray-600">{{ selectedColor }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: String,
})

const emit = defineEmits(['update:modelValue'])

const presetColors = [
  '#FF6B6B', '#FFA500', '#FFD93D', '#6BCB77', '#4D96FF',
  '#9D84B7', '#FF6B9D', '#C44569', '#00D4FF', '#FF1744',
  '#00E676', '#2196F3', '#FF9800', '#9C27B0', '#00BCD4',
  '#8BC34A', '#E91E63', '#3F51B5', '#009688', '#F44336',
]

const selectedColor = ref(props.modelValue || '#FF6B6B')
const customColor = ref('')
const customColorError = ref('')

const isValidHex = (color) => {
  return /^#[0-9A-F]{6}$/i.test(color)
}

const validateCustomColor = () => {
  if (customColor.value && !isValidHex(customColor.value)) {
    customColorError.value = 'Invalid hex color format (e.g., #FF5733)'
  } else {
    customColorError.value = ''
  }
}

const selectColor = (color) => {
  if (isValidHex(color)) {
    selectedColor.value = color
    emit('update:modelValue', color)
    customColor.value = ''
    customColorError.value = ''
  }
}

watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    selectedColor.value = newValue
  }
})
</script>

