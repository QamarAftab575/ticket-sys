<template>
  <div
    class="fixed inset-0 bg-black/40 flex items-center justify-center z-[100]"
    @mousedown.self="$emit('close')"
  >
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 flex flex-col max-h-[90vh]">

      <!-- Header -->
      <div class="flex items-center justify-between px-6 py-4 border-b">
        <h2 class="text-lg font-semibold text-gray-900">
          {{ isEditMode ? 'Edit field' : 'Add field' }}
        </h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Body -->
      <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

        <!-- Field title + type row -->
        <div class="flex gap-4">
          <div class="flex-1">
            <label class="block text-xs font-medium text-gray-600 mb-1">
              Field title <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Priority, Stage, Status..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              @keydown.enter.prevent
            />
          </div>

          <!-- Type picker (disabled in edit mode to avoid data loss) -->
          <div class="w-52 relative" ref="typeDropdownRef">
            <label class="block text-xs font-medium text-gray-600 mb-1">Field type</label>
            <button
              type="button"
              :disabled="isEditMode"
              @click="!isEditMode && (showTypeDropdown = !showTypeDropdown)"
              class="w-full flex items-center justify-between px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed"
            >
              <span class="flex items-center gap-2">
                <span v-html="selectedType.icon" class="w-4 h-4 text-gray-500 flex-shrink-0" />
                {{ selectedType.label }}
              </span>
              <svg v-if="!isEditMode" class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>

            <div
              v-if="showTypeDropdown"
              class="absolute top-full left-0 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg z-20 py-1 max-h-80 overflow-y-auto"
            >
              <!-- Custom field types -->
              <button
                v-for="t in fieldTypes"
                :key="t.value"
                type="button"
                @click="selectType(t)"
                class="w-full flex items-center gap-3 px-3 py-2 text-sm hover:bg-gray-50 transition"
                :class="form.field_type === t.value ? 'text-indigo-600 font-medium' : 'text-gray-700'"
              >
                <svg v-if="form.field_type === t.value" class="w-3.5 h-3.5 text-indigo-600 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                </svg>
                <span v-else class="w-3.5 h-3.5 flex-shrink-0" />
                <span v-html="t.icon" class="w-4 h-4 text-gray-500 flex-shrink-0" />
                {{ t.label }}
              </button>

              <!-- System fields separator -->
              <div class="flex items-center gap-2 px-3 py-1.5 mt-1">
                <div class="flex-1 h-px bg-gray-200" />
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide whitespace-nowrap">System Fields</span>
                <div class="flex-1 h-px bg-gray-200" />
              </div>

              <!-- System field types -->
              <button
                v-for="t in systemFieldTypes"
                :key="t.value"
                type="button"
                @click="selectType({ ...t, system: true })"
                :disabled="isSystemFieldUsed(t.value)"
                class="w-full flex items-center gap-3 px-3 py-2 text-sm transition"
                :class="[
                  isSystemFieldUsed(t.value)
                    ? 'opacity-40 cursor-not-allowed text-gray-400'
                    : form.field_type === t.value
                      ? 'text-indigo-600 font-medium hover:bg-gray-50'
                      : 'text-gray-700 hover:bg-gray-50',
                ]"
              >
                <svg v-if="form.field_type === t.value" class="w-3.5 h-3.5 text-indigo-600 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                </svg>
                <span v-else class="w-3.5 h-3.5 flex-shrink-0" />
                <span v-html="t.icon" class="w-4 h-4 flex-shrink-0" :class="isSystemFieldUsed(t.value) ? 'text-gray-300' : 'text-gray-500'" />
                <span class="flex-1 text-left">{{ t.label }}</span>
                <span v-if="isSystemFieldUsed(t.value)" class="text-xs text-gray-400 ml-auto">Added</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Options (for select types) -->
        <div v-if="isSelectType">
          <label class="block text-xs font-medium text-gray-600 mb-2">
            Options <span class="text-red-500">*</span>
          </label>

          <div class="space-y-1.5">
            <div
              v-for="(opt, idx) in form.options"
              :key="opt._key"
              class="flex items-center gap-2 group"
              draggable="true"
              @dragstart="dragStart(idx)"
              @dragover.prevent="dragOver(idx)"
              @dragend="dragEnd"
            >
              <!-- Drag handle -->
              <div class="cursor-grab text-gray-300 hover:text-gray-500 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M9 3h2v2H9V3zm0 4h2v2H9V7zm0 4h2v2H9v-2zm4-8h2v2h-2V3zm0 4h2v2h-2V7zm0 4h2v2h-2v-2z"/>
                </svg>
              </div>

              <!-- Color dot -->
              <div class="relative flex-shrink-0">
                <button
                  type="button"
                  @click="toggleColorPicker(idx)"
                  class="w-6 h-6 rounded-full border-2 border-white shadow-sm flex items-center justify-center transition hover:scale-110"
                  :style="{ backgroundColor: opt.color }"
                >
                  <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M7 10l5 5 5-5z"/>
                  </svg>
                </button>

                <div
                  v-if="colorPickerIdx === idx"
                  class="absolute left-0 top-8 bg-white border rounded-lg shadow-lg p-2 z-30 grid grid-cols-5 gap-1.5"
                  @mousedown.stop
                >
                  <button
                    v-for="c in colorPalette"
                    :key="c"
                    type="button"
                    @click="setOptionColor(idx, c)"
                    class="w-6 h-6 rounded-full border-2 transition hover:scale-110"
                    :style="{ backgroundColor: c }"
                    :class="opt.color === c ? 'border-gray-800' : 'border-transparent'"
                  />
                </div>
              </div>

              <!-- Option name -->
              <input
                v-model="opt.name"
                type="text"
                placeholder="Type an option name"
                class="flex-1 px-2 py-1.5 text-sm border border-transparent rounded focus:outline-none focus:border-indigo-400 focus:bg-indigo-50 bg-gray-50"
                @keydown.enter.prevent="addOption"
              />

              <!-- Remove -->
              <button
                type="button"
                @click="removeOption(idx)"
                class="opacity-0 group-hover:opacity-100 text-gray-300 hover:text-red-500 transition flex-shrink-0"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>

          <button
            type="button"
            @click="addOption"
            class="mt-2 flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 transition"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add an option
          </button>
        </div>

        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-between px-6 py-4 border-t bg-gray-50 rounded-b-xl">
        <!-- Delete (edit mode only) -->
        <button
          v-if="isEditMode"
          type="button"
          @click="deleteField"
          :disabled="saving"
          class="px-4 py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition disabled:opacity-50"
        >
          Delete field
        </button>
        <div v-else />

        <div class="flex items-center gap-3">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-100 transition"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="submit"
            :disabled="saving"
            class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition"
          >
            {{ saving ? 'Saving...' : (isEditMode ? 'Save changes' : 'Create field') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useCustomFields } from '@/Composables/useCustomFields'

const props = defineProps({
  projectId:   { type: [String, null], default: null },
  /** Pass an existing field object to open in edit mode */
  editField:   { type: Object, default: null },
})

const emit = defineEmits(['close', 'created', 'updated', 'deleted'])

const isEditMode = computed(() => !!props.editField)

// ── custom field types ────────────────────────────────────────────────────────
const fieldTypes = [
  { value: 'single_select', label: 'Single-select', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>' },
  { value: 'multi_select',  label: 'Multi-select',  icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>' },
  { value: 'text',          label: 'Text',          icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h6"/></svg>' },
  { value: 'number',        label: 'Number',        icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>' },
  { value: 'date',          label: 'Date',          icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/></svg>' },
  { value: 'people',        label: 'People',        icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>' },
]

// ── system field definitions (read-only, no options builder) ──────────────────
const systemFieldTypes = [
  { value: 'system_assignee',         label: 'Assignee',         icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>' },
  { value: 'system_blocked_by',       label: 'Blocked by',       icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.93 4.93l14.14 14.14"/></svg>' },
  { value: 'system_blocking',         label: 'Blocking',         icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>' },
  { value: 'system_completed_on',     label: 'Completed on',     icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
  { value: 'system_last_modified_on', label: 'Last modified on', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>' },
  { value: 'system_created_on',       label: 'Created on',       icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/></svg>' },
  { value: 'system_created_by',       label: 'Created by',       icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>' },
  { value: 'system_collaborators',    label: 'Collaborators',    icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>' },
]

// ── shared fields cache (to detect already-added system fields) ───────────────
const { fields: projectFields } = useCustomFields(props.projectId)

/** System field values already active in this project */
const usedSystemFieldTypes = computed(() =>
  new Set(
    projectFields.value
      .filter(f => f.field_type.startsWith('system_') && f.is_active)
      .map(f => f.field_type)
  )
)

/** Is a system field type already used? */
const isSystemFieldUsed = (value) => usedSystemFieldTypes.value.has(value)

// All types combined for selectedType lookup
const allTypes = computed(() => [...fieldTypes, ...systemFieldTypes])

const colorPalette = [
  '#ef4444','#f97316','#eab308','#22c55e','#10b981',
  '#06b6d4','#3b82f6','#6366f1','#8b5cf6','#ec4899',
  '#64748b','#78716c','#84cc16','#14b8a6','#f43f5e',
]

// ── form state ────────────────────────────────────────────────────────────────
const form = ref({ name: '', field_type: 'single_select', options: [] })
const saving           = ref(false)
const error            = ref('')
const showTypeDropdown = ref(false)
const colorPickerIdx   = ref(null)
const typeDropdownRef  = ref(null)
const dragIdx          = ref(null)
let _keyCounter = 0

const selectedType = computed(() =>
  allTypes.value.find(t => t.value === form.value.field_type) ?? fieldTypes[0]
)

const isSelectType = computed(() =>
  ['single_select', 'multi_select', 'dropdown'].includes(form.value.field_type)
)

const isSystemType = computed(() =>
  form.value.field_type.startsWith('system_')
)

// ── populate form (create vs edit) ────────────────────────────────────────────
function initForm() {
  if (props.editField) {
    form.value.name       = props.editField.name
    form.value.field_type = props.editField.field_type
    form.value.options    = (props.editField.options ?? []).map(o => ({
      _key:     ++_keyCounter,
      id:       o.id,
      name:     o.name,
      color:    o.color ?? '#6366f1',
      position: o.position ?? 0,
    }))
  } else {
    form.value = { name: '', field_type: 'single_select', options: [] }
    addOption(); addOption()
  }
}

// ── type selection ────────────────────────────────────────────────────────────
function selectType(t) {
  if (t.system && isSystemFieldUsed(t.value)) return  // blocked — already added
  form.value.field_type = t.value
  // Auto-fill name for system fields
  if (t.system) form.value.name = t.label
  showTypeDropdown.value = false
  // Reset options when switching to a select type with none yet
  if (['single_select', 'multi_select', 'dropdown'].includes(t.value) && form.value.options.length === 0) {
    addOption(); addOption()
  }
  // Clear options when switching away from select types
  if (!['single_select', 'multi_select', 'dropdown'].includes(t.value)) {
    form.value.options = []
  }
}

// ── options ───────────────────────────────────────────────────────────────────
function makeOption() {
  return { _key: ++_keyCounter, id: null, name: '', color: colorPalette[_keyCounter % colorPalette.length], position: 0 }
}
function addOption()          { form.value.options.push(makeOption()) }
function removeOption(idx)    { form.value.options.splice(idx, 1) }
function toggleColorPicker(i) { colorPickerIdx.value = colorPickerIdx.value === i ? null : i }
function setOptionColor(i, c) { form.value.options[i].color = c; colorPickerIdx.value = null }

// ── drag sort ─────────────────────────────────────────────────────────────────
function dragStart(idx) { dragIdx.value = idx }
function dragOver(idx) {
  if (dragIdx.value === null || dragIdx.value === idx) return
  const opts = [...form.value.options]
  const [moved] = opts.splice(dragIdx.value, 1)
  opts.splice(idx, 0, moved)
  form.value.options = opts
  dragIdx.value = idx
}
function dragEnd() { dragIdx.value = null }

// ── submit ────────────────────────────────────────────────────────────────────
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content

async function submit() {
  error.value = ''
  if (!form.value.name.trim()) { error.value = 'Field title is required.'; return }
  if (isSelectType.value && form.value.options.some(o => !o.name.trim())) {
    error.value = 'All options must have a name.'; return
  }

  saving.value = true
  try {
    const payload = {
      name:       form.value.name.trim(),
      field_type: form.value.field_type,
      options:    isSelectType.value
        ? form.value.options.map((o, i) => ({ id: o.id, name: o.name.trim(), color: o.color, position: i }))
        : null,
    }

    if (isEditMode.value) {
      const res = await fetch(`/api/custom-fields/${props.editField.id}`, {
        method:  'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
        body:    JSON.stringify(payload),
      })
      if (!res.ok) {
        const data = await res.json()
        error.value = Object.values(data.errors ?? {}).flat().join(' ') || 'Failed to update field.'
        return
      }
      const data = await res.json()
      emit('updated', data.data)
    } else {
      // Determine endpoint based on whether projectId is provided
      const endpoint = props.projectId
        ? `/api/projects/${props.projectId}/custom-fields`
        : `/api/my-tasks/custom-fields`
      
      const res = await fetch(endpoint, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
        body:    JSON.stringify(payload),
      })
      if (!res.ok) {
        const data = await res.json()
        error.value = Object.values(data.errors ?? {}).flat().join(' ') || 'Failed to create field.'
        return
      }
      const data = await res.json()
      emit('created', data.data)
    }
    emit('close')
  } catch {
    error.value = 'Something went wrong.'
  } finally {
    saving.value = false
  }
}

async function deleteField() {
  if (!confirm(`Delete field "${form.value.name}"? This will remove all values from tasks.`)) return
  saving.value = true
  try {
    const res = await fetch(`/api/custom-fields/${props.editField.id}`, {
      method:  'DELETE',
      headers: { 'X-CSRF-TOKEN': csrf() },
    })
    if (!res.ok) throw new Error()
    emit('deleted', props.editField.id)
    emit('close')
  } catch {
    error.value = 'Failed to delete field.'
  } finally {
    saving.value = false
  }
}

// ── outside click ─────────────────────────────────────────────────────────────
function onDocClick(e) {
  if (typeDropdownRef.value && !typeDropdownRef.value.contains(e.target)) showTypeDropdown.value = false
  if (colorPickerIdx.value !== null) colorPickerIdx.value = null
}

onMounted(() => { document.addEventListener('mousedown', onDocClick); initForm() })
onBeforeUnmount(() => document.removeEventListener('mousedown', onDocClick))
</script>
