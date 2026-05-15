<template>
  <div ref="containerRef" class="relative inline-flex">
    <!-- Trigger: the colored square with icon/letter -->
    <button
      @click.stop="toggle"
      :title="canEdit ? 'Change color & icon' : null"
      :class="[
        'flex-shrink-0 rounded-lg flex items-center justify-center transition-all select-none',
        sizeClass,
        canEdit ? 'cursor-pointer hover:opacity-80 hover:ring-2 hover:ring-offset-1 hover:ring-indigo-400' : 'cursor-default',
      ]"
      :style="{ backgroundColor: localColor }"
    >
      <!-- emoji / text icon -->
      <span v-if="localIcon && !isSvgIcon(localIcon)" :class="iconTextSize">{{ localIcon }}</span>
      <!-- svg icon -->
      <span v-else-if="localIcon && isSvgIcon(localIcon)" class="text-white" v-html="getSvgIcon(localIcon)" />
      <!-- fallback: first letter -->
      <span v-else class="font-bold text-white" :class="iconTextSize">
        {{ (name || '?').charAt(0).toUpperCase() }}
      </span>
    </button>

    <!-- Picker popover -->
    <Teleport to="body">
      <div
        v-if="open && canEdit"
        ref="popoverRef"
        class="fixed z-[200] bg-white rounded-xl shadow-2xl border border-gray-200 w-72"
        :style="popoverStyle"
        @click.stop
      >
        <!-- Color palette -->
        <div class="p-3 border-b border-gray-100">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Color</p>
          <div class="grid grid-cols-9 gap-1.5">
            <button
              v-for="c in COLORS"
              :key="c"
              @click="selectColor(c)"
              class="w-6 h-6 rounded-md transition-transform hover:scale-110 focus:outline-none"
              :style="{ backgroundColor: c }"
              :class="localColor === c ? 'ring-2 ring-offset-1 ring-gray-700 scale-110' : ''"
              :title="c"
            />
          </div>
        </div>

        <!-- Icon grid -->
        <div class="p-3">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Icon</p>
          <div class="grid grid-cols-8 gap-1 max-h-48 overflow-y-auto pr-0.5">
            <!-- No icon option -->
            <button
              @click="selectIcon(null)"
              :class="[
                'w-8 h-8 rounded-lg flex items-center justify-center text-xs border transition-colors',
                !localIcon ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-400 hover:bg-gray-50',
              ]"
              title="No icon"
            >
              <span class="text-gray-400 font-bold text-sm">A</span>
            </button>

            <!-- Emoji icons -->
            <button
              v-for="icon in EMOJI_ICONS"
              :key="icon"
              @click="selectIcon(icon)"
              :class="[
                'w-8 h-8 rounded-lg flex items-center justify-center text-base border transition-colors',
                localIcon === icon ? 'border-indigo-500 bg-indigo-50' : 'border-transparent hover:border-gray-300 hover:bg-gray-50',
              ]"
              :title="icon"
            >{{ icon }}</button>

            <!-- SVG icons -->
            <button
              v-for="icon in SVG_ICONS"
              :key="icon.id"
              @click="selectIcon(icon.id)"
              :class="[
                'w-8 h-8 rounded-lg flex items-center justify-center border transition-colors',
                localIcon === icon.id ? 'border-indigo-500 bg-indigo-50' : 'border-transparent hover:border-gray-300 hover:bg-gray-50',
              ]"
              :title="icon.label"
            >
              <span
                class="w-4 h-4 text-gray-600"
                v-html="icon.svg"
              />
            </button>
          </div>
        </div>

        <!-- Footer -->
        <div class="px-3 pb-3 flex justify-end gap-2">
          <button
            @click="open = false"
            class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-100 transition-colors"
          >Cancel</button>
          <button
            @click="save"
            :disabled="saving"
            class="px-3 py-1.5 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition-colors"
          >{{ saving ? 'Saving…' : 'Save' }}</button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  projectId: { type: String, required: true },
  name:      { type: String, default: '' },
  color:     { type: String, default: '#6366f1' },
  icon:      { type: String, default: null },
  size:      { type: String, default: 'md' }, // sm | md | lg
  canEdit:   { type: Boolean, default: false },
})

const emit = defineEmits(['update'])

// ── Local state ────────────────────────────────────────────────────────────
const open    = ref(false)
const saving  = ref(false)
const localColor = ref(props.color || '#6366f1')
const localIcon  = ref(props.icon  || null)

watch(() => props.color, v => { localColor.value = v || '#6366f1' })
watch(() => props.icon,  v => { localIcon.value  = v || null })

// ── Size helpers ───────────────────────────────────────────────────────────
const sizeClass = computed(() => ({
  sm: 'w-5 h-5',
  md: 'w-7 h-7',
  lg: 'w-14 h-14',
}[props.size] ?? 'w-7 h-7'))

const iconTextSize = computed(() => ({
  sm: 'text-xs',
  md: 'text-sm',
  lg: 'text-2xl',
}[props.size] ?? 'text-sm'))

// ── Color palette ──────────────────────────────────────────────────────────
const COLORS = [
  '#ef4444','#f97316','#eab308','#22c55e','#10b981',
  '#14b8a6','#06b6d4','#3b82f6','#6366f1','#8b5cf6',
  '#a855f7','#ec4899','#f43f5e','#64748b','#374151',
  '#78716c','#84cc16','#0ea5e9','#d946ef','#f59e0b',
  '#ffffff','#e5e7eb','#9ca3af','#4b5563','#111827',
  '#7c3aed','#1d4ed8','#0369a1','#065f46','#92400e',
]

// ── Emoji icons ────────────────────────────────────────────────────────────
const EMOJI_ICONS = [
  '🚀','📋','📌','🎯','💡','⚡','🔥','🌟','✅','📊',
  '🛠️','🎨','📱','💻','🌐','📦','🔒','📝','🗂️','📅',
  '🏆','💬','🔔','📣','🤝','🧩','🔍','📈','🎪','🌈',
  '🦋','🐛','🌱','🌿','🍀','🎵','🎸','🎮','🏋️','🚗',
]

// ── SVG icons ──────────────────────────────────────────────────────────────
const SVG_ICONS = [
  {
    id: 'svg:list', label: 'List',
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>`,
  },
  {
    id: 'svg:board', label: 'Board',
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="18" rx="1"/><rect x="14" y="3" width="7" height="10" rx="1"/></svg>`,
  },
  {
    id: 'svg:chart', label: 'Chart',
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>`,
  },
  {
    id: 'svg:star', label: 'Star',
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>`,
  },
  {
    id: 'svg:settings', label: 'Settings',
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>`,
  },
  {
    id: 'svg:globe', label: 'Globe',
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>`,
  },
  {
    id: 'svg:check', label: 'Check',
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>`,
  },
  {
    id: 'svg:users', label: 'Team',
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>`,
  },
  {
    id: 'svg:lightning', label: 'Lightning',
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>`,
  },
  {
    id: 'svg:flag', label: 'Flag',
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>`,
  },
  {
    id: 'svg:lock', label: 'Lock',
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>`,
  },
  {
    id: 'svg:target', label: 'Target',
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>`,
  },
  {
    id: 'svg:box', label: 'Box',
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>`,
  },
  {
    id: 'svg:trending', label: 'Trending',
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>`,
  },
  {
    id: 'svg:calendar', label: 'Calendar',
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`,
  },
  {
    id: 'svg:message', label: 'Message',
    svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>`,
  },
]

// ── Icon helpers ───────────────────────────────────────────────────────────
const isSvgIcon = (icon) => icon?.startsWith('svg:')

const getSvgIcon = (iconId) => {
  const found = SVG_ICONS.find(i => i.id === iconId)
  return found?.svg ?? ''
}

// ── Popover positioning ────────────────────────────────────────────────────
const containerRef = ref(null)
const popoverRef   = ref(null)
const popoverStyle = ref({})

const positionPopover = () => {
  if (!containerRef.value) return
  const rect = containerRef.value.getBoundingClientRect()
  const spaceBelow = window.innerHeight - rect.bottom
  const top = spaceBelow > 380 ? rect.bottom + 6 : rect.top - 386
  let left = rect.left
  if (left + 288 > window.innerWidth - 8) left = window.innerWidth - 296
  popoverStyle.value = { top: `${top}px`, left: `${left}px` }
}

const toggle = async () => {
  if (!props.canEdit) return
  open.value = !open.value
  if (open.value) {
    await nextTick()
    positionPopover()
  }
}

const handleOutsideClick = (e) => {
  if (!open.value) return
  if (!containerRef.value?.contains(e.target) && !popoverRef.value?.contains(e.target)) {
    open.value = false
  }
}

onMounted(() => document.addEventListener('mousedown', handleOutsideClick))
onBeforeUnmount(() => document.removeEventListener('mousedown', handleOutsideClick))

// ── Selection ──────────────────────────────────────────────────────────────
const selectColor = (c) => { localColor.value = c }
const selectIcon  = (i) => { localIcon.value  = i }

// ── Save ───────────────────────────────────────────────────────────────────
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content

const save = async () => {
  saving.value = true
  try {
    const res = await fetch(`/api/projects/${props.projectId}/appearance`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ color: localColor.value, icon: localIcon.value }),
    })
    if (!res.ok) throw new Error('Failed to save')
    emit('update', { color: localColor.value, icon: localIcon.value })
    open.value = false
  } catch {
    // silently fail — parent can add toast if desired
  } finally {
    saving.value = false
  }
}
</script>
