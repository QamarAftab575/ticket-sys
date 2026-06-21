<template>
  <div class="space-y-4">
    <!-- Comment Form -->
    <div class="pb-4 border-b border-gray-200">
      <CommentForm :task="task" @comment-added="$emit('update')" />
    </div>

    <!-- Comments List -->
    <div v-if="task.comments?.length === 0" class="text-center py-8">
      <p class="text-gray-500 text-sm">No comments yet</p>
    </div>

    <div v-for="comment in task.comments" :key="comment.id" class="space-y-2">
      <CommentItem :comment="comment" :task="task" @update="$emit('update')" />
    </div>
  </div>
</template>

<script setup lang="ts">
import type { TaskDetail } from '@/Types/tasks';
import CommentForm from '../Comments/CommentForm.vue';
import CommentItem from '../Comments/CommentItem.vue';

interface Props {
  task: TaskDetail;
}

interface Emits {
  (e: 'update'): void;
}

defineProps<Props>();
defineEmits<Emits>();
</script>

