<template>
  <!-- Guard: if field definition not loaded yet, show placeholder -->
  <div v-if="!field || !field.field_type" class="w-full px-1 text-gray-300 text-xs">â€”</div>

  <div v-else class="w-full h-full flex items-center" @click.stop>

    <!-- System fields: read directly from task properties, no stored value -->
    <SystemFieldCell
      v-if="isSystemType"
      :field="field"
      :task="task"
      :members="members"
    />

    <!-- Single / Multi select -->
    <CustomFieldOptionPicker
      v-else-if="isSelectType"
      :field="field"
      :model-value="parsedValue"
      @update:model-value="onUpdate"
      @edit-field="$emit('edit-field', $event)"
    />

    <!-- People: multi-member picker -->
    <CustomFieldPeoplePicker
      v-else-if="field.field_type === 'people'"
      :model-value="parsedPeopleValue"
      :members="members"
      @update:model-value="onUpdate"
    />

    <!-- Text -->
    <template v-else-if="field.field_type === 'text'">
      <input
        v-if="editing"
        ref="inputRef"
        v-model="localText"
        type="text"
        class="w-full px-1 py-0.5 text-xs border border-indigo-400 rounded focus:outline-none bg-white"
        @blur="commitText"
        @keydown.enter="commitText"
        @keydown.escape="cancelEdit"
      />
      <span
        v-else
        @click="startEdit"
        class="text-xs text-gray-700 cursor-text truncate w-full px-1 hover:bg-gray-50 rounded py-0.5"
        :class="{ 'text-gray-400 italic': !displayValue }"
      >
        {{ displayValue || 'â€”' }}
      </span>
    </template>

    <!-- Number -->
    <template v-else-if="field.field_type === 'number'">
      <input
        v-if="editing"
        ref="inputRef"
        v-model="localText"
        type="number"
        class="w-full px-1 py-0.5 text-xs border border-indigo-400 rounded focus:outline-none bg-white"
        @blur="commitText"
        @keydown.enter="commitText"
        @keydown.escape="cancelEdit"
      />
      <span
        v-else
        @click="startEdit"
        class="text-xs text-gray-700 cursor-text truncate w-full px-1 hover:bg-gray-50 rounded py-0.5"
        :class="{ 'text-gray-400 italic': !displayValue }"
      >
        {{ displayValue || 'â€”' }}
      </span>
    </template>

    <!-- Date -->
    <template v-else-if="field.field_type === 'date'">
      <input
        type="date"
        :value="displayValue || ''"
        class="text-xs border-0 bg-transparent focus:outline-none focus:ring-1 focus:ring-indigo-400 rounded px-1 cursor-pointer text-gray-700 w-full"
        @change="e => onUpdate(e.target.value || null)"
      />
    </template>

    <!-- Fallback -->
    <template v-else>
      <span class="text-xs text-gray-400 px-1">{{ displayValue || 'â€”' }}</span>
    </template>

  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import CustomFieldOptionPicker from './CustomFieldOptionPicker.vue'
import CustomFieldPeoplePicker from './CustomFieldPeoplePicker.vue'
import SystemFieldCell from './SystemFieldCell.vue'

const props = defineProps({
  field:    { type: Object, default: null },
  task:     { type: Object, required: true },
  rawValue: { type: String, default: null },
  members:  { type: Array,  default: () => [] },
})

const emit = defineEmits(['update', 'edit-field'])

const editing   = ref(false)
const localText = ref('')
const inputRef  = ref(null)

const isSystemType = computed(() =>
  props.field?.field_type?.startsWith('system_') ?? false
)

const isSelectType = computed(() =>
  props.field && ['single_select', 'multi_select', 'dropdown'].includes(props.field.field_type)
)

const parsedValue = computed(() => {
  if (!props.rawValue) return props.field?.field_type === 'multi_select' ? [] : null
  if (props.field?.field_type === 'multi_select') {
    try { return JSON.parse(props.rawValue) } catch { return [] }
  }
  return props.rawValue
})

const parsedPeopleValue = computed(() => {
  if (!props.rawValue) return []
  try { return JSON.parse(props.rawValue) } catch { return [] }
})

const displayValue = computed(() => {
  if (!props.rawValue) return null
  if (isSystemType.value || isSelectType.value || props.field?.field_type === 'people') return null
  return props.rawValue
})

function onUpdate(value) {
  emit('update', { taskId: props.task.id, fieldId: props.field.id, value })
}

function startEdit() {
  localText.value = props.rawValue ?? ''
  editing.value = true
  nextTick(() => inputRef.value?.focus())
}

function commitText() {
  editing.value = false
  const val = localText.value.trim()
  if (val !== (props.rawValue ?? '')) {
    onUpdate(val || null)
  }
}

function cancelEdit() {
  editing.value = false
}
</script>

