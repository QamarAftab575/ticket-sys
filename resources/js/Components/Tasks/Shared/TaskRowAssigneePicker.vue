<template>
  <div ref="containerRef" class="relative inline-flex items-center">
    <!-- Trigger -->
    <button
      @click.stop="toggle"
      class="flex items-center gap-1.5 rounded px-1 py-0.5 hover:bg-gray-100 transition-colors group"
      :title="localAssignee ? localAssignee.name : 'Assign'"
    >
      <Avatar
        v-if="localAssignee"
        :name="localAssignee.name"
        :src="localAssignee.avatar"
        size="xs"
      />
      <span
        v-else
        class="w-6 h-6 rounded-full border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-400 group-hover:border-gray-400 transition-colors"
      >
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
      </span>
    </button>

    <!-- Popover (teleported to body for correct z-index) -->
    <Teleport to="body">
      <div
        v-if="open"
        ref="popoverRef"
        class="fixed z-50 w-64 bg-white rounded-lg shadow-lg border border-gray-200 py-2"
        :style="popoverStyle"
        @click.stop
      >
        <!-- Search input -->
        <div class="px-3 pb-2">
          <input
            ref="searchInputRef"
            v-model="search"
            type="text"
            placeholder="Name or email"
            class="w-full text-sm border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        <!-- Remove assignee option (only when assigned) -->
        <button
          v-if="localAssignee"
          @click="select(null)"
          class="w-full flex items-center gap-2.5 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors"
        >
          <span class="w-6 h-6 rounded-full border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-400 flex-shrink-0">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </span>
          <span>No assignee</span>
        </button>

        <!-- Members list -->
        <div class="max-h-52 overflow-y-auto">
          <div v-if="filteredMembers.length === 0" class="px-3 py-4 text-center text-sm text-gray-400">
            No members found
          </div>
          <button
            v-for="member in filteredMembers"
            :key="member.id"
            @click="select(member)"
            class="w-full flex items-center gap-2.5 px-3 py-1.5 text-sm hover:bg-gray-50 transition-colors"
            :class="localAssignee?.id === member.id ? 'bg-blue-50' : ''"
          >
            <Avatar :name="member.name" :src="member.avatar" size="xs" class="flex-shrink-0" />
            <div class="flex flex-col items-start min-w-0 flex-1">
              <span class="font-medium text-gray-800 truncate w-full text-left">{{ member.name }}</span>
              <span class="text-xs text-gray-400 truncate w-full text-left">{{ member.email }}</span>
            </div>
            <svg
              v-if="localAssignee?.id === member.id"
              class="w-4 h-4 text-blue-500 flex-shrink-0"
              fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import Avatar from '@/Components/Avatar.vue';

const props = defineProps({
  task: { type: Object, required: true },
  members: { type: Array, default: () => [] },
});

const emit = defineEmits(['update-assignee']);

// Optimistic local state
const localAssignee = ref(props.task.assignee ?? null);
watch(() => props.task.assignee, (v) => { localAssignee.value = v ?? null; });

const open = ref(false);
const search = ref('');
const containerRef = ref(null);
const popoverRef = ref(null);
const searchInputRef = ref(null);
const popoverStyle = ref({});

const filteredMembers = computed(() => {
  const q = search.value.trim().toLowerCase();
  if (!q) return props.members;
  return props.members.filter(
    m => m.name.toLowerCase().includes(q) || (m.email || '').toLowerCase().includes(q)
  );
});

async function toggle() {
  open.value = !open.value;
  if (open.value) {
    search.value = '';
    await nextTick();
    positionPopover();
    searchInputRef.value?.focus();
  }
}

function positionPopover() {
  if (!containerRef.value) return;
  const rect = containerRef.value.getBoundingClientRect();
  const spaceBelow = window.innerHeight - rect.bottom;
  const top = spaceBelow > 280 ? rect.bottom + 4 : rect.top - 284;
  let left = rect.left;
  if (left + 256 > window.innerWidth - 8) left = window.innerWidth - 264;
  popoverStyle.value = { top: `${top}px`, left: `${left}px` };
}

function select(member) {
  open.value = false;
  // Optimistic update
  const previous = localAssignee.value;
  localAssignee.value = member;
  emit('update-assignee', {
    taskId: props.task.id,
    assignee_id: member?.id ?? null,
    // Pass full member so parent can update its task list optimistically
    assignee: member ?? null,
    rollback: () => { localAssignee.value = previous; },
  });
}

function handleOutsideClick(e) {
  if (open.value && !containerRef.value?.contains(e.target) && !popoverRef.value?.contains(e.target)) {
    open.value = false;
  }
}

onMounted(() => document.addEventListener('mousedown', handleOutsideClick));
onBeforeUnmount(() => document.removeEventListener('mousedown', handleOutsideClick));
</script>

