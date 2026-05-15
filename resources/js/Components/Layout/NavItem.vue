<template>
  <Link
    :href="href"
    :class="[
      'block px-3 py-2 text-sm rounded-lg transition',
      active
        ? 'bg-blue-100 text-blue-700 font-medium'
        : 'text-gray-700 hover:bg-gray-100'
    ]"
  >
    {{ label }}
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  href: String,
  label: String,
  active: Boolean,
})
</script>
