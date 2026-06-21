<template>
  <div>
    <div
      class="min-h-[48px] w-full px-3 py-2 border border-gray-300 rounded-lg focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent bg-white flex flex-wrap gap-2 cursor-text"
      @click="focusInput"
    >
      <span
        v-for="(tag, index) in tags"
        :key="index"
        :class="[
          'inline-flex items-center gap-1 px-2 py-1 rounded text-sm font-medium transition-colors',
          tag.valid ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-600'
        ]"
      >
        {{ tag.email }}
        <button type="button" @click.stop="removeTag(index)" class="hover:opacity-70 leading-none">&times;</button>
      </span>
      <input
        ref="inputEl"
        v-model="inputValue"
        type="text"
        :placeholder="tags.length === 0 ? 'colleague@example.com' : ''"
        class="flex-1 min-w-[160px] outline-none text-sm bg-transparent py-0.5"
        @keydown="onKeydown"
        @blur="commit"
        @paste="onPaste"
      />
    </div>
    <div class="flex items-center justify-between mt-1">
      <p class="text-xs text-gray-500">Press Enter or comma to add. Invalid emails are shown in red.</p>
      <p v-if="invalidCount > 0" class="text-xs text-red-600 font-medium">
        {{ invalidCount }} invalid email{{ invalidCount > 1 ? 's' : '' }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const tags = defineModel({ default: () => [] })

const inputEl = ref(null)
const inputValue = ref('')

const isValidEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim())

const invalidCount = computed(() => tags.value.filter(t => !t.valid).length)

const focusInput = () => inputEl.value?.focus()

const addTag = (email) => {
  const trimmed = email.trim().toLowerCase()
  if (!trimmed) return
  if (tags.value.some(t => t.email === trimmed)) return
  tags.value.push({ email: trimmed, valid: isValidEmail(trimmed) })
}

const commit = () => {
  const val = inputValue.value.trim().replace(/,+$/, '')
  if (!val) return
  addTag(val)
  inputValue.value = ''
}

const removeTag = (index) => tags.value.splice(index, 1)

const onKeydown = (e) => {
  if (e.key === 'Enter' || e.key === ',') {
    e.preventDefault()
    commit()
  } else if (e.key === 'Backspace' && !inputValue.value && tags.value.length) {
    tags.value.pop()
  }
}

const onPaste = (e) => {
  e.preventDefault()
  e.clipboardData.getData('text').split(/[\s,;]+/).forEach(p => p && addTag(p))
}

// Expose commit so parents can flush the input before submitting
defineExpose({ commit, invalidCount })
</script>

