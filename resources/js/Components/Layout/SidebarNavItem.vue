<template>
  <Link
    :href="href"
    :class="[
      'group relative flex items-center gap-3 rounded-xl text-sm font-medium transition-all duration-150',
      collapsed ? 'justify-center p-2.5' : 'px-3 py-2.5',
      active
        ? 'bg-blue-50 text-blue-700'
        : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
    ]"
  >
    <!-- Icon -->
    <svg
      class="shrink-0 w-5 h-5"
      :class="active ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600'"
      fill="none" stroke="currentColor" viewBox="0 0 24 24"
    >
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" :d="icon" />
    </svg>

    <!-- Label -->
    <span v-if="!collapsed" class="truncate">{{ label }}</span>

    <!-- Badge (unread count) -->
    <span
      v-if="badge && unreadCount > 0 && !collapsed"
      class="ml-auto px-1.5 py-0.5 text-[10px] font-bold bg-indigo-600 text-white rounded-full min-w-[18px] text-center"
    >
      {{ unreadCount > 99 ? '99+' : unreadCount }}
    </span>

    <!-- Active indicator -->
    <span v-else-if="active && !collapsed" class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-600" />

    <!-- Badge dot when collapsed -->
    <span
      v-if="badge && unreadCount > 0 && collapsed"
      class="absolute top-1.5 right-1.5 w-2 h-2 bg-indigo-600 rounded-full border-2 border-white"
    />

    <!-- Tooltip when collapsed -->
    <span
      v-if="collapsed"
      class="pointer-events-none absolute left-full ml-3 px-2.5 py-1.5 rounded-lg bg-gray-900 text-white text-xs font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-150 z-50 shadow-lg"
    >
      {{ label }}
      <span v-if="badge && unreadCount > 0" class="ml-1.5 px-1.5 py-0.5 text-[10px] font-bold bg-indigo-600 rounded-full">
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
      <!-- Arrow -->
      <span class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900" />
    </span>
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  href: String,
  label: String,
  icon: String,
  active: Boolean,
  collapsed: Boolean,
  badge: { type: Boolean, default: false },
  unreadCount: { type: Number, default: 0 },
})
</script>
