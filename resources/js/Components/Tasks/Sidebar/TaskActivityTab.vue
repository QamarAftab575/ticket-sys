<template>
  <div class="space-y-4">
    <div v-if="task.activities?.length === 0" class="text-center py-8">
      <p class="text-gray-500 text-sm">No activity yet</p>
    </div>

    <div v-for="activity in task.activities" :key="activity.id" class="flex gap-3">
      <img
        :src="activity.user?.avatar"
        :alt="activity.user?.name"
        class="w-8 h-8 rounded-full flex-shrink-0"
      />
      <div class="flex-1 min-w-0">
        <p class="text-sm text-gray-900">
          <span class="font-medium">{{ activity.user?.name }}</span>
          {{ formatAction(activity.action) }}
        </p>
        <p v-if="activity.old_value || activity.new_value" class="text-xs text-gray-500 mt-1">
          <span v-if="activity.old_value" class="line-through">{{ activity.old_value }}</span>
          <span v-if="activity.new_value" class="font-medium">{{ activity.new_value }}</span>
        </p>
        <p class="text-xs text-gray-400 mt-1">{{ formatDate(activity.created_at) }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { TaskDetail } from '@/Types/tasks';

interface Props {
  task: TaskDetail;
}

defineProps<Props>();

function formatAction(action: string): string {
  const actions: Record<string, string> = {
    created: 'created this task',
    updated: 'updated this task',
    completed: 'completed this task',
    reopened: 'reopened this task',
    assigned: 'assigned this task',
    commented: 'commented on this task',
  };
  return actions[action] || action;
}

function formatDate(date: string): string {
  const now = new Date();
  const activityDate = new Date(date);
  const diffMs = now.getTime() - activityDate.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;

  return activityDate.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
  });
}
</script>

