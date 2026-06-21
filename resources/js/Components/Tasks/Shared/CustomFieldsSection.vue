<template>
  <div class="border border-gray-200 rounded-lg overflow-hidden">
    <!-- Header with collapse toggle -->
    <button
      @click="isCollapsed = !isCollapsed"
      class="w-full flex items-center justify-between px-4 py-2.5 bg-gray-50 hover:bg-gray-100 transition-colors"
    >
      <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Custom Fields</span>
      <svg
        class="w-4 h-4 text-gray-400 transition-transform"
        :class="{ 'rotate-180': !isCollapsed }"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>

    <!-- Custom fields list -->
    <div v-if="!isCollapsed" class="divide-y divide-gray-100">
      <div
        v-for="field in activeCustomFields"
        :key="field.id"
        class="flex items-start gap-3 px-4 py-2.5 hover:bg-gray-50/50 transition-colors"
      >
        <span class="w-24 flex-shrink-0 text-xs font-medium text-gray-500 pt-1.5">{{ field.name }}</span>
        <div class="flex-1 min-w-0">
          <CustomFieldCell
            :field="field"
            :task="task"
            :raw-value="getCustomFieldValue(field.id)"
            :members="members"
            @update="handleUpdate"
            @edit-field="$emit('edit-field', $event)"
          />
        </div>
      </div>

      <div v-if="activeCustomFields.length === 0" class="px-4 py-3 text-xs text-gray-400 text-center">
        No custom fields added yet
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import CustomFieldCell from '@/Components/CustomFields/CustomFieldCell.vue'
import { useCustomFields } from '@/Composables/useCustomFields'

const props = defineProps({
  task: { type: Object, required: true },
  projectId: { type: [String, Number], default: null },
  members: { type: Array, default: () => [] },
})

const emit = defineEmits(['update', 'edit-field'])

const isCollapsed = ref(false)

// Fetch custom fields for the project
const { fields, fetchFields, setTaskFieldValue } = useCustomFields(props.projectId)

onMounted(() => {
  if (props.projectId) {
    fetchFields()
  }
})

// Get only active custom fields
const activeCustomFields = computed(() => {
  return fields.value.filter(f => f.is_active)
})

// Get the value for a specific custom field from the task
function getCustomFieldValue(fieldId) {
  const cfv = props.task.custom_field_values?.find(v => v.custom_field_id === fieldId)
  return cfv?.value ?? null
}

// Handle custom field update
async function handleUpdate({ taskId, fieldId, value }) {
  try {
    await setTaskFieldValue(taskId, fieldId, value)
    emit('update', taskId)
  } catch (e) {
    console.error('Failed to update custom field:', e)
  }
}
</script>

