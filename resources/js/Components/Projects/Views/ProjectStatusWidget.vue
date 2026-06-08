<template>
  <div class="space-y-4">

    <!-- View mode -->
    <template v-if="!isEditing">
      <!-- Current status badge -->
      <div class="flex items-center justify-between">
        <span
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-semibold"
          :class="STATUS_COLORS[localStatus]?.badge"
        >
          <span class="w-2 h-2 rounded-full" :class="STATUS_COLORS[localStatus]?.dot" />
          {{ STATUS_LABELS[localStatus] }}
        </span>
        <span class="text-xs text-gray-400">{{ formatDate(localUpdatedAt) }}</span>
      </div>

      <!-- Status update / note -->
      <p v-if="localStatusUpdate" class="text-sm text-gray-700 leading-relaxed">
        {{ localStatusUpdate }}
      </p>
      <p v-else class="text-sm text-gray-400 italic">No status update yet</p>

      <!-- Edit button -->
      <button
        @click="startEdit"
        class="w-full flex items-center justify-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        Update status
      </button>
    </template>

    <!-- Edit mode -->
    <template v-else>
      <!-- Status picker -->
      <div class="space-y-2">
        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Status</label>
        <div class="grid grid-cols-1 gap-1.5">
          <button
            v-for="(cfg, key) in STATUS_OPTIONS"
            :key="key"
            @click="editStatus = key"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg border text-sm font-medium transition-all text-left"
            :class="editStatus === key
              ? `${cfg.activeBg} ${cfg.activeText} ${cfg.activeBorder} ring-2 ${cfg.ring}`
              : 'border-gray-200 text-gray-700 hover:bg-gray-50'"
          >
            <!-- Colored dot -->
            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" :class="cfg.dot" />
            <!-- Label + description -->
            <span class="flex-1">
              <span class="block font-semibold">{{ cfg.label }}</span>
              <span class="text-xs font-normal opacity-70">{{ cfg.hint }}</span>
            </span>
            <!-- Check mark when selected -->
            <svg v-if="editStatus === key" class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Status update textarea -->
      <div class="space-y-1.5">
        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Status update</label>
        <textarea
          v-model="editStatusUpdate"
          rows="3"
          placeholder="Describe what's happening, blockers, next steps…"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
        />
      </div>

      <!-- Actions -->
      <div class="flex gap-2">
        <button
          @click="save"
          :disabled="isSaving"
          class="flex-1 px-3 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          {{ isSaving ? 'Saving…' : 'Save' }}
        </button>
        <button
          @click="cancel"
          :disabled="isSaving"
          class="flex-1 px-3 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors"
        >
          Cancel
        </button>
      </div>
    </template>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useToast } from '@/Composables/useToast'

const props = defineProps({
  project: Object,
})

const { success: showSuccess, error: showError } = useToast()

// ─── Status config ────────────────────────────────────────────────────────
const STATUS_OPTIONS = {
  on_track: {
    label: 'On Track',
    hint: 'Project is progressing as planned',
    dot: 'bg-green-500',
    activeBg: 'bg-green-50',
    activeText: 'text-green-800',
    activeBorder: 'border-green-300',
    ring: 'ring-green-200',
  },
  at_risk: {
    label: 'At Risk',
    hint: 'Some concerns that need attention',
    dot: 'bg-yellow-400',
    activeBg: 'bg-yellow-50',
    activeText: 'text-yellow-800',
    activeBorder: 'border-yellow-300',
    ring: 'ring-yellow-200',
  },
  off_track: {
    label: 'Off Track',
    hint: 'Significant issues blocking progress',
    dot: 'bg-red-500',
    activeBg: 'bg-red-50',
    activeText: 'text-red-800',
    activeBorder: 'border-red-300',
    ring: 'ring-red-200',
  },
  on_hold: {
    label: 'On Hold',
    hint: 'Work paused, waiting on something',
    dot: 'bg-gray-400',
    activeBg: 'bg-gray-100',
    activeText: 'text-gray-800',
    activeBorder: 'border-gray-400',
    ring: 'ring-gray-200',
  },
  complete: {
    label: 'Complete',
    hint: 'All goals achieved',
    dot: 'bg-blue-500',
    activeBg: 'bg-blue-50',
    activeText: 'text-blue-800',
    activeBorder: 'border-blue-300',
    ring: 'ring-blue-200',
  },
}

const STATUS_LABELS = Object.fromEntries(
  Object.entries(STATUS_OPTIONS).map(([k, v]) => [k, v.label])
)

const STATUS_COLORS = {
  on_track:  { badge: 'bg-green-100 text-green-800',  dot: 'bg-green-500' },
  at_risk:   { badge: 'bg-yellow-100 text-yellow-800', dot: 'bg-yellow-400' },
  off_track: { badge: 'bg-red-100 text-red-800',      dot: 'bg-red-500' },
  on_hold:   { badge: 'bg-gray-100 text-gray-700',    dot: 'bg-gray-400' },
  complete:  { badge: 'bg-blue-100 text-blue-800',    dot: 'bg-blue-500' },
}

// ─── Local reactive state (so UI updates immediately on save) ─────────────
const localStatus       = ref(props.project.status)
const localStatusUpdate = ref(props.project.status_update || '')
const localUpdatedAt    = ref(props.project.updated_at)

// ─── Edit state ───────────────────────────────────────────────────────────
const isEditing      = ref(false)
const isSaving       = ref(false)
const editStatus     = ref(props.project.status)
const editStatusUpdate = ref('')

const startEdit = () => {
  editStatus.value       = localStatus.value
  editStatusUpdate.value = localStatusUpdate.value
  isEditing.value        = true
}

const cancel = () => {
  isEditing.value = false
}

const save = async () => {
  isSaving.value = true
  try {
    const response = await fetch(`/projects/${props.project.id}/status`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      },
      body: JSON.stringify({
        status:        editStatus.value,
        status_update: editStatusUpdate.value,
      }),
    })

    if (!response.ok) throw new Error('Failed to update status')

    const data = await response.json()
    localStatus.value       = data.status
    localStatusUpdate.value = data.status_update || ''
    localUpdatedAt.value    = data.updated_at

    isEditing.value = false
    showSuccess('Project status updated')
  } catch (err) {
    showError('Failed to update project status')
    console.error(err)
  } finally {
    isSaving.value = false
  }
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}
</script>
