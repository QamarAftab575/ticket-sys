<template>
  <span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
    :style="{
      backgroundColor: tag.color + '20',
      color: tag.color,
      borderLeft: `3px solid ${tag.color}`,
    }"
  >
    {{ tag.name }}
  </span>
</template>

<script setup lang="ts">
import type { Tag } from '@/Types/tasks';

interface Props {
  tag: Tag;
}

defineProps<Props>();
</script>
