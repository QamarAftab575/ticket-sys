<template>
  <div class="space-y-1">
    <p class="text-xs font-medium mb-1" :class="type === 'blocked_by' ? 'text-orange-600' : 'text-red-500'">
      {{ label }}
    </p>
    <div
      v-for="dep in tasks"
      :key="dep.id"
      class="flex items-center gap-2 group"
    >
      <!-- Task chip with hover tooltip -->
      <div class="relative flex items-center gap-1.5 min-w-0 flex-1"
        @mouseenter="(e) => showTooltip(dep.id, e)"
        @mouseleave="hoveredId = null"
      >
        <!-- Clickable task name -->
        <button
          class="flex items-center gap-1.5 min-w-0 flex-1 text-left hover:underline"
          @click="$emit('open-task', dep)"
        >
          <svg
            class="w-3.5 h-3.5 flex-shrink-0"
            :class="dep.completed_at ? 'text-green-500' : 'text-gray-400'"
            fill="none" stroke="currentColor" viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span
            class="text-sm text-gray-800 truncate"
            :class="{ 'line-through text-gray-400': dep.completed_at }"
          >{{ dep.name }}</span>
          <span v-if="dep.start_date || dep.due_date" class="text-xs text-gray-400 flex-shrink-0">
           {{ formatDateRange(dep.start_date, dep.due_date) }}
          </span>
        </button>

        <!-- Hover tooltip -->
        <Teleport to="body">
          <div
            v-if="hoveredId === dep.id"
            :style="tooltipStyle"
            class="fixed z-[9999] bg-white border border-gray-200 rounded-lg shadow-lg px-3 py-2.5 w-56 pointer-events-none"
          >
            <div class="flex items-start gap-2">
              <svg
                class="w-4 h-4 flex-shrink-0 mt-0.5"
                :class="dep.completed_at ? 'text-green-500' : 'text-gray-400'"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div class="min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ dep.name }}</p>
                <p class="text-xs text-gray-500 mt-0.5">
                  <span v-if="dep.assignee">{{ dep.assignee.name }}</span>
                  <span v-if="dep.assignee && (dep.start_date || dep.due_date)"> </span>
                  <span v-if="dep.start_date || dep.due_date">{{ formatDateRange(dep.start_date, dep.due_date) }}</span>
                  <span v-if="!dep.assignee && !dep.start_date && !dep.due_date" class="italic">No assignee or date</span>
                </p>
              </div>
            </div>
          </div>
        </Teleport>
      </div>

      <!-- Remove button -->
      <button
        @click="$emit('remove', dep.id)"
        class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-gray-600 flex-shrink-0 transition-opacity"
        title="Remove dependency"
      >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import type { TaskDependency } from '@/Types/tasks';

defineProps<{
  label: string;
  tasks: TaskDependency[];
  type: 'blocked_by' | 'blocks';
}>();

defineEmits<{
  (e: 'remove', taskId: string): void;
  (e: 'open-task', task: TaskDependency): void;
}>();

const hoveredId = ref<string | null>(null);
const tooltipStyle = reactive({ top: '0px', left: '0px' });

function showTooltip(id: string, e: MouseEvent) {
  const rect = (e.currentTarget as HTMLElement).getBoundingClientRect();
  tooltipStyle.top = `${rect.bottom + 6}px`;
  tooltipStyle.left = `${rect.left}px`;
  hoveredId.value = id;
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function formatDateRange(start?: string | null, end?: string | null): string {
  if (start && end) return `${formatDate(start)}  “ ${formatDate(end)}`;
  if (end) return formatDate(end);
  if (start) return formatDate(start);
  return '';
}
</script>

