<template>
  <div
    :class="[
      'bg-white border rounded-xl p-4 flex flex-col gap-3 transition-shadow hover:shadow-md',
      accent === 'red' ? 'border-red-200' : 'border-gray-200',
    ]"
  >
    <!-- Icon + label row -->
    <div class="flex items-start justify-between gap-2">
      <p class="text-xs font-medium text-gray-500 leading-tight">{{ label }}</p>
      <span
        :class="[
          'flex items-center justify-center w-8 h-8 rounded-lg shrink-0',
          accent === 'red' ? 'bg-red-100 text-red-600' : 'bg-indigo-50 text-indigo-600',
        ]"
      >
        <!-- Clipboard List -->
        <svg v-if="icon === 'clipboard-list'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
        </svg>
        <!-- Circle Check -->
        <svg v-else-if="icon === 'circle-check'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <!-- Clock -->
        <svg v-else-if="icon === 'clock'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <!-- Alert Triangle -->
        <svg v-else-if="icon === 'alert-triangle'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <!-- Folder Open -->
        <svg v-else-if="icon === 'folder-open'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"/>
        </svg>
        <!-- Users -->
        <svg v-else-if="icon === 'users'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
      </span>
    </div>

    <!-- Value -->
    <div>
      <p
        :class="[
          'text-3xl font-bold tracking-tight',
          accent === 'red' ? 'text-red-600' : 'text-gray-900',
        ]"
      >{{ formattedValue }}</p>
      <p v-if="subtitle" class="text-xs text-gray-400 mt-0.5">{{ subtitle }}</p>
    </div>

    <!-- Trend indicator -->
    <div class="flex items-center gap-1.5">
      <span
        :class="[
          'inline-flex items-center gap-0.5 text-xs font-semibold px-1.5 py-0.5 rounded-full',
          trend > 0 ? 'bg-green-100 text-green-700' : trend < 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500',
        ]"
      >
        <svg v-if="trend > 0" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
        </svg>
        <svg v-else-if="trend < 0" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
        </svg>
        <span v-else class="w-3 h-3 flex items-center justify-center">â€”</span>
        {{ Math.abs(trend) }}%
      </span>
      <span class="text-xs text-gray-400">vs last period</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label:    { type: String, default: '' },
  value:    { type: Number, default: 0 },
  trend:    { type: Number, default: 0 },
  icon:     { type: String, default: 'clipboard-list' },
  accent:   { type: String, default: null },   // 'red' for overdue
  subtitle: { type: String, default: null },
})

const formattedValue = computed(() =>
  props.value >= 1000 ? (props.value / 1000).toFixed(1) + 'k' : String(props.value)
)
</script>

