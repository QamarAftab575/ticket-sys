<template>
  <div class="privacy-selector">
    <label class="block text-sm font-medium text-gray-700 mb-4">Privacy Level</label>
    
    <!-- Privacy options -->
    <div class="space-y-3 mb-6">
      <label
        v-for="option in privacyOptions"
        :key="option.value"
        class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all"
        :class="[
          selectedPrivacy === option.value
            ? 'border-blue-600 bg-blue-50'
            : 'border-gray-300 hover:border-gray-400'
        ]"
      >
        <input
          type="radio"
          :value="option.value"
          v-model="selectedPrivacy"
          class="mt-1 mr-3"
          @change="updatePrivacy"
        />
        <div>
          <p class="font-medium text-gray-900">{{ option.label }}</p>
          <p class="text-sm text-gray-600">{{ option.description }}</p>
        </div>
      </label>
    </div>

    <!-- Member selector for specific members -->
    <div v-if="selectedPrivacy === 'specific_members'" class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">Select Members</label>
      <div class="border border-gray-300 rounded-md p-3 max-h-48 overflow-y-auto">
        <label
          v-for="member in availableMembers"
          :key="member.id"
          class="flex items-center mb-2"
        >
          <input
            type="checkbox"
            :value="member.id"
            v-model="selectedMembers"
            class="mr-2"
          />
          <span class="text-sm text-gray-700">{{ member.name }}</span>
        </label>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: String,
  members: Array,
  selectedMembers: Array,
})

const emit = defineEmits(['update:modelValue', 'update:selectedMembers'])

const privacyOptions = [
  {
    value: 'public_to_team',
    label: 'Public to Workspace',
    description: 'All workspace members can view and join this project',
  },
  {
    value: 'private',
    label: 'Private',
    description: 'Only the owner and invited members can access this project',
  },
  {
    value: 'specific_members',
    label: 'Specific Members',
    description: 'Only selected workspace members can access this project',
  },
]

const selectedPrivacy = ref(props.modelValue || 'public_to_team')
const selectedMembers = ref(props.selectedMembers || [])
const availableMembers = ref(props.members || [])

const updatePrivacy = () => {
  emit('update:modelValue', selectedPrivacy.value)
}

watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    selectedPrivacy.value = newValue
  }
})

watch(() => props.selectedMembers, (newValue) => {
  if (newValue) {
    selectedMembers.value = newValue
  }
})

watch(selectedMembers, (newValue) => {
  emit('update:selectedMembers', newValue)
})
</script>

