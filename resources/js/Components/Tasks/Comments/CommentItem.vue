<template>
  <div class="flex gap-3 group">
    <Avatar
      :name="comment.user?.name"
      :src="comment.user?.avatar"
      size="sm"
      class="flex-shrink-0 mt-0.5"
    />

    <div class="flex-1 min-w-0">
      <!-- Header -->
      <div class="flex items-baseline gap-2 mb-1">
        <span class="text-sm font-semibold text-gray-800">{{ comment.user?.name }}</span>
        <span class="text-xs text-gray-400">{{ formatDate(comment.created_at) }}</span>
        <span
          v-if="comment.edited_at"
          class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-400 border border-gray-200"
        >
          edited
        </span>
      </div>

      <!-- View mode -->
      <template v-if="!isEditing">
        <!-- Rich text content -->
        <div
          class="text-sm text-gray-700 prose prose-sm max-w-none rich-content"
          v-html="comment.content"
        />

        <!-- Reactions -->
        <div v-if="comment.reactions?.length" class="flex gap-1 mt-2 flex-wrap">
          <span
            v-for="reaction in comment.reactions"
            :key="reaction.id"
            class="text-xs bg-white border border-gray-200 rounded px-2 py-1"
          >
            {{ reaction.emoji }}
          </span>
        </div>

        <!-- Edit / Delete actions (own comments only) -->
        <div
          v-if="isOwnComment"
          class="mt-1 flex items-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity"
        >
          <button
            @click="startEdit"
            class="text-xs text-gray-400 hover:text-indigo-600 transition-colors"
          >
            Edit
          </button>
          <button
            @click="handleDelete"
            :disabled="deleting"
            class="text-xs text-gray-400 hover:text-red-500 transition-colors disabled:opacity-50"
          >
            {{ deleting ? 'Deleting ¦' : 'Delete' }}
          </button>
        </div>
      </template>

      <!-- Edit mode -->
      <div v-else class="border border-indigo-300 rounded-lg overflow-hidden">
        <RichEditor
          v-model="editContent"
          :show-toolbar="true"
          :task-id="task.id"
          :project-id="task.project_id"
          placeholder="Edit comment ¦"
        />
        <div class="flex gap-2 px-3 py-2 bg-gray-50 border-t border-gray-100">
          <button
            @click="saveEdit"
            :disabled="!hasEditContent || saving"
            class="px-3 py-1 text-xs font-medium bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-40 transition-colors flex items-center gap-1.5"
          >
            <svg v-if="saving" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            {{ saving ? 'Saving ¦' : 'Save' }}
          </button>
          <button
            @click="cancelEdit"
            class="px-3 py-1 text-xs font-medium text-gray-600 hover:text-gray-800 transition-colors"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { api } from '@/Services/api';
import type { Comment, TaskDetail } from '@/Types/tasks';
import Avatar from '@/Components/Avatar.vue';
import RichEditor from '@/Components/Projects/RichEditor.vue';

interface Props {
  comment: Comment;
  task: TaskDetail;
}

interface Emits {
  (e: 'update'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const page = usePage();
const currentUserId = computed(() => (page.props as any).auth?.user?.id ?? null);
const isOwnComment = computed(() => props.comment.user_id === currentUserId.value);

//  Edit state 
const isEditing = ref(false);
const editContent = ref('');
const saving = ref(false);
const deleting = ref(false);

const hasEditContent = computed(() => {
  const val = editContent.value?.trim();
  return !!val && val !== '<p></p>' && val !== '<p><br></p>';
});

function startEdit() {
  editContent.value = props.comment.content;
  isEditing.value = true;
}

function cancelEdit() {
  isEditing.value = false;
  editContent.value = '';
}

async function saveEdit() {
  if (!hasEditContent.value) return;

  saving.value = true;
  try {
    await api.put(`/comments/${props.comment.id}`, {
      content: editContent.value,
    });
    isEditing.value = false;
    emit('update');
  } catch (err) {
    console.error('Error updating comment:', err);
  } finally {
    saving.value = false;
  }
}

async function handleDelete() {
  if (!confirm('Delete this comment?')) return;

  deleting.value = true;
  try {
    await api.delete(`/comments/${props.comment.id}`);
    emit('update');
  } catch (err) {
    console.error('Error deleting comment:', err);
  } finally {
    deleting.value = false;
  }
}

//  Date formatting 
function formatDate(date: string): string {
  const now = new Date();
  const d = new Date(date);
  const diffMs = now.getTime() - d.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;

  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}
</script>

<style scoped>
/* Mention styles for rendered content */
:deep(.rich-content .mention),
:deep(.rich-content span[data-type="mention"]) {
  background-color: #e0e7ff;
  color: #4f46e5;
  border-radius: 0.25rem;
  padding: 0.125rem 0.25rem;
  font-weight: 500;
  white-space: nowrap;
  cursor: pointer;
}

:deep(.rich-content .mention:hover),
:deep(.rich-content span[data-type="mention"]:hover) {
  background-color: #c7d2fe;
}
</style>

