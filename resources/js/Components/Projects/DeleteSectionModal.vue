<template>
  <Teleport to="body">
    <div
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
      @mousedown.self="$emit('cancel')"
    >
      <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">

        <!-- Header -->
        <div class="flex items-start justify-between mb-4">
          <div>
            <h3 class="text-base font-semibold text-gray-900">Delete "{{ section.name }}"</h3>
            <p class="text-sm text-gray-500 mt-0.5">
              This section contains <span class="font-medium text-gray-700">{{ taskCount }}</span> task{{ taskCount !== 1 ? 's' : '' }}.
            </p>
          </div>
          <button @click="$emit('cancel')" class="text-gray-400 hover:text-gray-600 ml-4 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Delete tasks checkbox -->
        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer mb-4">
          <input
            v-model="deleteTasks"
            type="checkbox"
            class="w-4 h-4 rounded border-gray-300 text-red-600 focus:ring-red-500 cursor-pointer"
          />
          <div>
            <span class="text-sm font-medium text-gray-800">Delete tasks also</span>
            <p class="text-xs text-gray-500 mt-0.5">Permanently removes all tasks in this section</p>
          </div>
        </label>

        <!-- Move tasks options (hidden when deleteTasks is checked) -->
        <Transition name="fade">
          <div v-if="!deleteTasks" class="mb-5">
            <template v-if="otherSections.length > 0">
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Move tasks to
              </label>
              <select
                v-model="targetSectionId"
                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
              >
                <option value="">Untitled Section (default)</option>
                <option
                  v-for="s in otherSections"
                  :key="s.id"
                  :value="s.id"
                >
                  {{ s.name }}
                </option>
              </select>
            </template>
            <p v-else class="text-sm text-gray-500 bg-gray-50 rounded-lg px-3 py-2">
              Tasks will be moved to <span class="font-medium text-gray-700">Untitled Section</span>.
            </p>
          </div>
        </Transition>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
          <button
            @click="$emit('cancel')"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>
          <button
            @click="confirm"
            :disabled="loading"
            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
          >
            <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            {{ deleteTasks ? 'Delete section & tasks' : 'Delete section' }}
          </button>
        </div>

      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  section: { type: Object, required: true },
  taskCount: { type: Number, required: true },
  allSections: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['confirm', 'cancel'])

const deleteTasks = ref(false)
const targetSectionId = ref('')

const otherSections = computed(() =>
  props.allSections.filter(s => s.id !== props.section.id)
)

function confirm() {
  emit('confirm', {
    deleteTasks: deleteTasks.value,
    targetSectionId: deleteTasks.value ? null : (targetSectionId.value || null),
  })
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s, transform 0.15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-4px); }
</style>

