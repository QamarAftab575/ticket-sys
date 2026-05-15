<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40" @click.self="$emit('cancel')">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">

        <!-- Header -->
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
          <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-gray-900">Incomplete blocking tasks</h3>
            <p class="text-xs text-gray-500 mt-0.5">This task is blocked by {{ tasks.length }} incomplete {{ tasks.length === 1 ? 'task' : 'tasks' }}</p>
          </div>
        </div>

        <!-- Blocked task list -->
        <ul class="px-5 py-3 space-y-2 max-h-56 overflow-y-auto">
          <li
            v-for="t in tasks"
            :key="t.id"
            class="flex items-center gap-2.5 py-1.5"
          >
            <span class="w-4 h-4 rounded-full border-2 border-gray-300 flex-shrink-0"/>
            <span class="text-sm text-gray-700 truncate">{{ t.name }}</span>
            <span class="ml-auto text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 flex-shrink-0 capitalize">
              {{ t.status?.replace('_', ' ') }}
            </span>
          </li>
        </ul>

        <!-- Override checkbox -->
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
          <label class="flex items-center gap-2.5 cursor-pointer select-none">
            <input
              type="checkbox"
              v-model="override"
              class="w-4 h-4 rounded border-gray-300 text-indigo-600 cursor-pointer"
            />
            <span class="text-sm text-gray-700">Mark complete anyway and remove these blocking dependencies</span>
          </label>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-2 px-5 py-3 border-t border-gray-100">
          <button
            @click="$emit('cancel')"
            class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-md transition-colors"
          >
            Cancel
          </button>
          <button
            :disabled="!override"
            @click="$emit('confirm')"
            class="px-4 py-1.5 text-sm font-medium rounded-md transition-colors"
            :class="override
              ? 'bg-indigo-600 text-white hover:bg-indigo-700'
              : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
          >
            Mark complete
          </button>
        </div>

      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  tasks: { type: Array, required: true },
})

defineEmits(['confirm', 'cancel'])

const override = ref(false)
</script>
