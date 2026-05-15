<template>
  <div class="flex gap-3">
    <Avatar :name="currentUser?.name" :src="currentUser?.avatar" size="sm" class="flex-shrink-0 mt-0.5" />

    <div
      class="flex-1 border border-gray-200 rounded-lg overflow-hidden transition-all duration-150"
      :class="isFocused ? 'border-indigo-300 ring-1 ring-indigo-200' : ''"
    >
      <RichEditor
        v-model="commentContent"
        :show-toolbar="'auto'"
        :task-id="task.id"
        :project-id="task.project_id"
        placeholder="Add a comment…"
        @focus="isFocused = true"
        @blur="onEditorBlur"
      />

      <!-- Action bar — visible when focused or has content -->
      <Transition
        enter-active-class="transition-all duration-150 ease-out overflow-hidden"
        enter-from-class="max-h-0 opacity-0"
        enter-to-class="max-h-12 opacity-100"
        leave-active-class="transition-all duration-100 ease-in overflow-hidden"
        leave-from-class="max-h-12 opacity-100"
        leave-to-class="max-h-0 opacity-0"
      >
        <div
          v-if="isFocused || hasContent"
          class="flex items-center justify-between px-3 py-2 bg-gray-50 border-t border-gray-100"
        >
          <span class="text-xs text-gray-400 hidden sm:block">Supports rich text</span>
          <div class="flex items-center gap-2 ml-auto">
            <button
              v-if="hasContent"
              @click="cancel"
              class="px-3 py-1 text-xs font-medium text-gray-600 hover:text-gray-800 transition-colors"
            >
              Cancel
            </button>
            <button
              @click="handleSubmit"
              :disabled="!hasContent || loading"
              class="px-3 py-1 text-xs font-medium bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-40 transition-colors flex items-center gap-1.5"
            >
              <svg v-if="loading" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              {{ loading ? 'Posting…' : 'Comment' }}
            </button>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { api } from '@/Services/api';
import type { TaskDetail } from '@/Types/tasks';
import RichEditor from '@/Components/Projects/RichEditor.vue';
import Avatar from '@/Components/Avatar.vue';
import { usePage } from '@inertiajs/vue3';

interface Props {
  task: TaskDetail;
}

interface Emits {
  (e: 'comment-added'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const page = usePage();
const currentUser = computed(() => (page.props as any).auth?.user ?? null);

const commentContent = ref('');
const loading = ref(false);
const isFocused = ref(false);

// Consider content empty if it's blank or just an empty paragraph from Tiptap
const hasContent = computed(() => {
  const val = commentContent.value?.trim();
  return !!val && val !== '<p></p>' && val !== '<p><br></p>';
});

function onEditorBlur() {
  // Keep action bar visible if there's content; hide if empty
  if (!hasContent.value) {
    isFocused.value = false;
  }
}

async function handleSubmit() {
  if (!hasContent.value) return;

  loading.value = true;
  try {
    await api.post(`/tasks/${props.task.id}/comments`, {
      content: commentContent.value,
    });
    commentContent.value = '';
    isFocused.value = false;
    emit('comment-added');
  } catch (err) {
    console.error('Error posting comment:', err);
  } finally {
    loading.value = false;
  }
}

function cancel() {
  commentContent.value = '';
  isFocused.value = false;
}
</script>
