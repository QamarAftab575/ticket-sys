<template>
  <div class="bg-white border border-gray-200 rounded-xl p-4">
    <div class="flex flex-wrap items-end gap-3">

      <!-- 1. Date Range -->
      <div class="flex items-end gap-2 min-w-[260px]">
        <div class="flex-1">
          <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('from') }}</label>
          <input
            type="date"
            :value="localFilters.date_from"
            @change="update('date_from', $event.target.value)"
            class="w-full h-9 text-sm border border-gray-300 rounded-lg px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          />
        </div>
        <span class="text-gray-400 text-sm pb-2"></span>
        <div class="flex-1">
          <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('to') }}</label>
          <input
            type="date"
            :value="localFilters.date_to"
            @change="update('date_to', $event.target.value)"
            class="w-full h-9 text-sm border border-gray-300 rounded-lg px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          />
        </div>
      </div>

      <!-- 2. Project -->
      <div class="min-w-[160px]">
        <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('project') }}</label>
        <select
          :value="localFilters.project_id ?? ''"
          @change="onProjectChange($event.target.value)"
          class="w-full h-9 text-sm border border-gray-300 rounded-lg px-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
        >
          <option value="">{{ $t('all_projects') }}</option>
          <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
        </select>
      </div>

      <!-- 3. Assignee (cascading on project) -->
      <div class="min-w-[160px]">
        <label class="block text-xs font-medium text-gray-600 mb-1">
          {{ $t('assignee') }}
          <span v-if="loadingAssignees" class="ml-1 text-indigo-500">»</span>
        </label>
        <select
          :value="localFilters.assignee_id ?? ''"
          @change="update('assignee_id', $event.target.value || null)"
          :disabled="loadingAssignees"
          class="w-full h-9 text-sm border border-gray-300 rounded-lg px-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 disabled:opacity-50 disabled:cursor-wait"
        >
          <option value="">{{ $t('all_assignees') }}</option>
          <option v-for="m in assigneeOptions" :key="m.id" :value="m.id">{{ m.name }}</option>
        </select>
      </div>

      <!-- 4. Project Status -->
      <div class="min-w-[160px]">
        <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('project_status') }}</label>
        <select
          :value="localFilters.project_status ?? ''"
          @change="update('project_status', $event.target.value || null)"
          class="w-full h-9 text-sm border border-gray-300 rounded-lg px-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
        >
          <option value="">{{ $t('all_statuses') }}</option>
          <option v-for="opt in projectStatusOptions" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>
      </div>

      <!-- 5. Actions -->
      <div class="flex gap-2 ml-auto">
        <button
          type="button"
          @click="$emit('reset')"
          class="h-9 px-4 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
        >
          {{ $t('reset') }}
        </button>
        <button
          type="button"
          @click="$emit('apply')"
          class="h-9 px-5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 active:scale-95 transition-all shadow-sm"
        >
          {{ $t('apply_filters') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const props = defineProps({
  projects:   { type: Array,  default: () => [] },
  allMembers: { type: Array,  default: () => [] },
  filters:    { type: Object, required: true },
})

const emit = defineEmits(['update:filters', 'apply', 'reset', 'project-changed'])

const localFilters = reactive({ ...props.filters })

watch(() => props.filters, (v) => {
  Object.assign(localFilters, v)
}, { deep: true })

function update(key, value) {
  localFilters[key] = value
  emit('update:filters', { ...localFilters })
}

//  Project status options (matches projects.status enum) 
const projectStatusOptions = [
  { value: 'on_track',  label: page.props.translations?.on_track || 'On Track'  },
  { value: 'at_risk',   label: page.props.translations?.at_risk || 'At Risk'   },
  { value: 'off_track', label: page.props.translations?.off_track || 'Off Track' },
  { value: 'on_hold',   label: page.props.translations?.on_hold || 'On Hold'   },
  { value: 'complete',  label: page.props.translations?.complete || 'Complete'  },
]

//  Assignee cascade 
const assigneeOptions  = ref([...props.allMembers])
const loadingAssignees = ref(false)

watch(() => props.allMembers, (v) => {
  if (!localFilters.project_id) assigneeOptions.value = [...v]
})

async function onProjectChange(value) {
  const projectId = value || null
  update('project_id', projectId)
  update('assignee_id', null)

  if (!projectId) {
    assigneeOptions.value = [...props.allMembers]
    emit('project-changed', null)
    return
  }

  loadingAssignees.value = true
  try {
    const resp = await fetch(`/reports/assignees?project_id=${encodeURIComponent(projectId)}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
    })
    const data = await resp.json()
    assigneeOptions.value = Array.isArray(data) ? data : []
  } catch {
    assigneeOptions.value = [...props.allMembers]
  } finally {
    loadingAssignees.value = false
  }

  emit('project-changed', projectId)
}
</script>

