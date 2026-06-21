<template>
  <div
    v-if="items.length > 0"
    class="bg-white rounded-lg shadow-lg border border-gray-200 py-1 max-h-64 overflow-y-auto"
  >
    <button
      v-for="(item, index) in items"
      :key="item.id"
      :class="[
        'w-full flex items-center gap-2 px-3 py-2 text-left transition-colors',
        index === selectedIndex ? 'bg-indigo-50' : 'hover:bg-gray-50'
      ]"
      @click="selectItem(index)"
    >
      <div
        v-if="item.avatar"
        class="w-6 h-6 rounded-full bg-gray-200 flex-shrink-0 overflow-hidden"
      >
        <img :src="item.avatar" :alt="item.name" class="w-full h-full object-cover" />
      </div>
      <div
        v-else
        class="w-6 h-6 rounded-full bg-indigo-500 text-white text-xs font-semibold flex items-center justify-center flex-shrink-0"
      >
        {{ getInitials(item.name) }}
      </div>
      <div class="flex-1 min-w-0">
        <div class="text-sm font-medium text-gray-900 truncate">{{ item.name }}</div>
        <div v-if="item.email" class="text-xs text-gray-500 truncate">{{ item.email }}</div>
      </div>
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';

interface MentionItem {
  id: string;
  name: string;
  email?: string;
  avatar?: string;
}

interface Props {
  items: MentionItem[];
  command: (item: { id: string; label: string }) => void;
}

const props = defineProps<Props>();

const selectedIndex = ref(0);

watch(() => props.items, () => {
  selectedIndex.value = 0;
});

function onKeyDown(event: KeyboardEvent) {
  if (event.key === 'ArrowUp') {
    event.preventDefault();
    selectedIndex.value = (selectedIndex.value + props.items.length - 1) % props.items.length;
    return true;
  }

  if (event.key === 'ArrowDown') {
    event.preventDefault();
    selectedIndex.value = (selectedIndex.value + 1) % props.items.length;
    return true;
  }

  if (event.key === 'Enter') {
    event.preventDefault();
    selectItem(selectedIndex.value);
    return true;
  }

  return false;
}

function selectItem(index: number) {
  const item = props.items[index];
  if (item) {
    props.command({ id: item.id, label: item.name });
  }
}

function getInitials(name: string): string {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
}

onMounted(() => {
  document.addEventListener('keydown', onKeyDown);
});

onBeforeUnmount(() => {
  document.removeEventListener('keydown', onKeyDown);
});

defineExpose({
  onKeyDown,
});
</script>

