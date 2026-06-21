<template>
  <Link
    :href="href"
    class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition"
  >
    {{ label }}
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  href: String,
  label: String,
})
</script>

