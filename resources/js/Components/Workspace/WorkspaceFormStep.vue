<template>
  <form @submit.prevent="$emit('next')" class="space-y-6">
    <!-- Workspace Name -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ nameLabel }}
      </label>
      <input
        :value="workspace.name"
        @input="$emit('update:workspace', { ...workspace, name: $event.target.value })"
        type="text"
        :placeholder="namePlaceholder"
        maxlength="255"
        required
        :class="[
          'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition',
          (errors?.name || isNameDuplicate) ? 'border-red-500' : 'border-gray-300'
        ]"
      />
      <p v-if="errors?.name" class="text-red-600 text-sm mt-1">{{ errors.name[0] }}</p>
      <p v-if="isNameDuplicate" class="text-red-600 text-sm mt-1">
        {{ duplicateErrorMessage }}
      </p>
    </div>

    <!-- Organization Type -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ typeLabel }}
      </label>
      <div class="relative">
        <button
          type="button"
          @click="typeDropdownOpen = !typeDropdownOpen"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg text-left bg-white hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent flex items-center justify-between"
        >
          <span v-if="workspace.types.length === 0" class="text-gray-500">{{ typeDropdownPlaceholder }}</span>
          <span v-else class="text-gray-900">{{ workspace.types.join(', ') }}</span>
          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
          </svg>
        </button>

        <div v-if="typeDropdownOpen" class="absolute z-10 w-full mt-2 bg-white border border-gray-300 rounded-lg shadow-lg">
          <div class="p-2 space-y-1">
            <label v-for="type in organizationTypes" :key="type" class="flex items-center px-3 py-2 hover:bg-gray-100 rounded cursor-pointer">
              <input
                type="checkbox"
                :checked="workspace.types.includes(type)"
                @change="toggleType(type)"
                class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
              />
              <span class="ml-3 text-sm text-gray-700">{{ type }}</span>
            </label>
          </div>
        </div>
      </div>
    </div>

    <!-- Workspace Description -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ descriptionLabel }}
      </label>
      <textarea
        :value="workspace.description"
        @input="$emit('update:workspace', { ...workspace, description: $event.target.value })"
        :placeholder="descriptionPlaceholder"
        maxlength="1000"
        rows="4"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
      />
      <p class="text-xs text-gray-500 mt-1">{{ workspace.description.length }}/1000 characters</p>
    </div>

    <!-- Navigation Buttons -->
    <div class="flex gap-3 pt-6 border-t">
      <slot name="cancel" />
      <button
        type="button"
        @click="$emit('skip')"
        v-if="showSkip"
        class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition"
      >
        {{ skipButtonLabel }}
      </button>
      <button
        type="submit"
        :disabled="!workspace.name || isNameDuplicate"
        class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg font-medium transition"
      >
        {{ nextButtonLabel }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  workspace: {
    type: Object,
    required: true
  },
  organizationTypes: {
    type: Array,
    default: () => [
      'Design', 'HR', 'Engineering', 'Education / Teacher',
      'IT Company', 'Marketing', 'Finance', 'Sales', 'Other'
    ]
  },
  isNameDuplicate: {
    type: Boolean,
    default: false
  },
  errors: {
    type: Object,
    default: () => ({})
  },
  showSkip: {
    type: Boolean,
    default: true
  },
  nameLabel: {
    type: String,
    default: 'Workspace'
  },
  namePlaceholder: {
    type: String,
    default: 'Acme Studio'
  },
  typeLabel: {
    type: String,
    default: 'What type of work do you do?'
  },
  typeDropdownPlaceholder: {
    type: String,
    default: 'Select one or more'
  },
  descriptionLabel: {
    type: String,
    default: 'Tell us about your workspace'
  },
  descriptionPlaceholder: {
    type: String,
    default: 'What will you use this workspace for...'
  },
  skipButtonLabel: {
    type: String,
    default: 'Skip'
  },
  nextButtonLabel: {
    type: String,
    default: 'Next'
  },
  duplicateErrorMessage: {
    type: String,
    default: 'You already have a workspace with this name'
  }
})

const emit = defineEmits(['update:workspace', 'next', 'skip'])

const typeDropdownOpen = ref(false)

const toggleType = (type) => {
  const types = [...props.workspace.types]
  const index = types.indexOf(type)
  index > -1 ? types.splice(index, 1) : types.push(type)
  emit('update:workspace', { ...props.workspace, types })
}
</script>
