<template>
  <div class="space-y-2">
    <!-- Blocked By section -->
    <DependencySection
      v-if="blockedBy.length"
      label="Blocked by"
      :tasks="blockedBy"
      type="blocked_by"
      @remove="removeDependency"
      @open-task="$emit('open-task', $event)"
    />

    <!-- Blocking section -->
    <DependencySection
      v-if="blocking.length"
      label="Blocking"
      :tasks="blocking"
      type="blocks"
      @remove="removeDependency"
      @open-task="$emit('open-task', $event)"
    />

    <!-- Add dependency row -->
    <div v-if="addingType" class="flex items-center gap-2">
      <span class="text-xs font-medium w-20 flex-shrink-0" :class="addingType === 'blocked_by' ? 'text-orange-600' : 'text-red-500'">
        {{ addingType === 'blocked_by' ? 'Blocked by' : 'Blocking' }}
      </span>
      <div class="relative flex-1" ref="dropdownRef">
        <input
          ref="searchInputRef"
          v-model="searchQuery"
          type="text"
          placeholder="Find a task..."
          class="w-full text-sm border border-blue-400 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500"
          @focus="onFocus"
          @input="onSearch"
          @keydown.escape="cancelAdding"
        />
        <Teleport to="body">
        <div
          v-if="dropdownOpen"
          ref="dropdownListRef"
          :style="dropdownStyle"
          class="fixed z-[9999] bg-white border border-gray-200 rounded-lg shadow-lg w-72 max-h-60 overflow-y-auto"
        >
          <div v-if="searching" class="px-3 py-2 text-xs text-gray-400">Loading...</div>
          <template v-else-if="searchResults.length > 0">
            <p v-if="!searchQuery" class="px-3 pt-2 pb-1 text-xs text-gray-400 font-medium">Recent tasks</p>
            <button
              v-for="result in searchResults"
              :key="result.id"
              class="flex items-center gap-3 w-full px-3 py-2 text-left hover:bg-gray-50 transition-colors"
              @click="selectTask(result)"
            >
              <svg class="w-4 h-4 flex-shrink-0" :class="result.completed_at ? 'text-green-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div class="min-w-0">
                <p class="text-sm text-gray-900 truncate" :class="{ 'line-through text-gray-400': result.completed_at }">{{ result.name }}</p>
                <p v-if="result.due_date" class="text-xs text-gray-500">{{ formatDate(result.due_date) }}</p>
              </div>
            </button>
          </template>
          <div v-else-if="searchQuery || !searching" class="px-3 py-2 text-xs text-gray-400">No tasks found</div>
        </div>
        </Teleport>
      </div>
      <button @click="cancelAdding" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <!-- Add dependencies button -->
    <div v-if="!addingType" class="flex items-center gap-2 flex-wrap">
      <button
        @click="startAdding('blocked_by')"
        class="text-xs text-gray-500 hover:text-gray-700 flex items-center gap-1"
      >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add blocked by
      </button>
      <span class="text-gray-300 text-xs"></span>
      <button
        @click="startAdding('blocks')"
        class="text-xs text-gray-500 hover:text-gray-700 flex items-center gap-1"
      >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add blocking
      </button>
    </div>

    <p v-if="error" class="text-xs text-red-500">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue';
import type { TaskDetail, TaskDependency } from '@/Types/tasks';
import DependencySection from './DependencySection.vue';

interface Props {
  task: TaskDetail;
  projectId?: string | null;
}

interface Emits {
  (e: 'update'): void;
  (e: 'open-task', task: any): void;
}

const props = withDefaults(defineProps<Props>(), { projectId: null });
const emit = defineEmits<Emits>();

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
const apiHeaders = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken };

const addingType = ref<'blocked_by' | 'blocks' | null>(null);
const searchQuery = ref('');
const searchResults = ref<any[]>([]);
const searching = ref(false);
const error = ref('');
const searchInputRef = ref<HTMLInputElement | null>(null);
const dropdownRef = ref<HTMLElement | null>(null);
const dropdownListRef = ref<HTMLElement | null>(null);
const dropdownOpen = ref(false);

const dropdownStyle = ref<{ top: string; left: string }>({ top: '0px', left: '0px' });

function updateDropdownPosition() {
  const el = searchInputRef.value;
  if (!el) return;
  const rect = el.getBoundingClientRect();
  dropdownStyle.value = {
    top: `${rect.bottom + 4}px`,
    left: `${rect.left}px`,
  };
}

// "blocked_by" = task.dependencies (tasks this task is waiting on)
// "blocks"     = task.dependents   (tasks waiting on this task)
const blockedBy = computed(() => props.task.dependencies ?? []);
const blocking = computed(() => props.task.dependents ?? []);

const alreadySelectedIds = computed(() => {
  const ids = new Set<string>([props.task.id]);
  blockedBy.value.forEach(t => ids.add(t.id));
  blocking.value.forEach(t => ids.add(t.id));
  return ids;
});

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

async function startAdding(type: 'blocked_by' | 'blocks') {
  addingType.value = type;
  searchQuery.value = '';
  error.value = '';
  dropdownOpen.value = true;
  await nextTick();
  searchInputRef.value?.focus();
  updateDropdownPosition();
  await doSearch();
}

function cancelAdding() {
  addingType.value = null;
  searchQuery.value = '';
  searchResults.value = [];
  dropdownOpen.value = false;
}

function onFocus() {
  updateDropdownPosition();
  dropdownOpen.value = true;
  if (!searchResults.value.length) {
    doSearch();
  }
}

function onSearch() {
  if (searchTimer) clearTimeout(searchTimer);
  dropdownOpen.value = true;
  searchTimer = setTimeout(doSearch, 250);
}

async function doSearch() {
  const projectId = props.projectId ?? (props.task as any).project_id;
  if (!projectId) return;

  searching.value = true;
  try {
    const url = searchQuery.value.length
      ? `/api/projects/${projectId}/tasks?filters[search]=${encodeURIComponent(searchQuery.value)}`
      : `/api/projects/${projectId}/tasks?sort[0][field]=created_at&sort[0][direction]=desc`;

    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) return;
    const { data } = await res.json();
    searchResults.value = (data as any[])
      .filter(t => !alreadySelectedIds.value.has(t.id))
      .slice(0, 5);
  } finally {
    searching.value = false;
  }
}

async function selectTask(selected: any) {
  if (!addingType.value) return;
  error.value = '';

  try {
    const res = await fetch(`/api/tasks/${props.task.id}/dependencies`, {
      method: 'POST',
      headers: { ...apiHeaders },
      body: JSON.stringify({ depends_on_task_id: selected.id, type: addingType.value }),
    });

    if (!res.ok) {
      const body = await res.json();
      error.value = Object.values(body.errors ?? {}).flat().join(' ') || 'Failed to add dependency';
      return;
    }

    cancelAdding();
    emit('update');
  } catch {
    error.value = 'Failed to add dependency';
  }
}

async function removeDependency(taskId: string) {
  error.value = '';
  try {
    const res = await fetch(`/api/tasks/${props.task.id}/dependencies/${taskId}`, {
      method: 'DELETE',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
    });
    if (!res.ok) throw new Error();
    emit('update');
  } catch {
    error.value = 'Failed to remove dependency';
  }
}

// Close dropdown on outside click
function handleOutsideClick(e: MouseEvent) {
  const target = e.target as Node;
  const insideInput = dropdownRef.value?.contains(target);
  const insideList = dropdownListRef.value?.contains(target);
  if (!insideInput && !insideList) {
    dropdownOpen.value = false;
    searchResults.value = [];
  }
}

onMounted(() => document.addEventListener('mousedown', handleOutsideClick));
onUnmounted(() => document.removeEventListener('mousedown', handleOutsideClick));
</script>

