<template>
  <div class="w-96 bg-white border-l border-gray-200 flex flex-col overflow-hidden">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
      <h2 class="text-lg font-semibold text-gray-900">Task Details</h2>
      <button
        @click="$emit('close')"
        class="p-1 hover:bg-gray-100 rounded transition"
      >
        <XMarkIcon class="w-5 h-5 text-gray-500" />
      </button>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto">
      <!-- Task Title -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-start gap-3">
          <button
            @click="handleToggleComplete"
            class="flex-shrink-0 w-6 h-6 rounded border-2 transition mt-1"
            :class="
              task.completed
                ? 'bg-green-500 border-green-500'
                : 'border-gray-300 hover:border-gray-400'
            "
          >
            <CheckIcon v-if="task.completed" class="w-5 h-5 text-white" />
          </button>
          <div class="flex-1 min-w-0">
            <h3
              class="text-lg font-semibold text-gray-900"
              :class="{ 'line-through text-gray-500': task.completed }"
            >
              {{ task.name }}
            </h3>
            <p class="text-sm text-gray-500 mt-1">
              Created {{ formatDate(task.created_at) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200">
        <div class="flex">
          <button
            v-for="tab in tabs"
            :key="tab"
            @click="activeTab = tab"
            class="flex-1 px-4 py-3 text-sm font-medium text-center border-b-2 transition"
            :class="
              activeTab === tab
                ? 'text-blue-600 border-blue-600'
                : 'text-gray-600 border-transparent hover:text-gray-900'
            "
          >
            {{ formatTabLabel(tab) }}
          </button>
        </div>
      </div>

      <!-- Tab Content -->
      <div class="px-6 py-4">
        <!-- Details Tab -->
        <div v-if="activeTab === 'details'" class="space-y-4">
          <TaskDetailsTab :task="task" :project-id="projectId" @update="$emit('task-update')" />
        </div>

        <!-- Activity Tab -->
        <div v-if="activeTab === 'activity'" class="space-y-4">
          <TaskActivityTab :task="task" />
        </div>

        <!-- Comments Tab -->
        <div v-if="activeTab === 'comments'" class="space-y-4">
          <TaskCommentsTab :task="task" @update="$emit('task-update')" />
        </div>

        <!-- Attachments Tab -->
        <div v-if="activeTab === 'attachments'" class="space-y-4">
          <TaskAttachmentsTab :task="task" @update="$emit('task-update')" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { XMarkIcon, CheckIcon } from '@heroicons/vue/24/outline';
import { useTasksStore } from '@/Stores/useTasksStore';
import type { TaskDetail } from '@/Types/tasks';
import TaskDetailsTab from './TaskDetailsTab.vue';
import TaskActivityTab from './TaskActivityTab.vue';
import TaskCommentsTab from './TaskCommentsTab.vue';
import TaskAttachmentsTab from './TaskAttachmentsTab.vue';

interface Props {
  task: TaskDetail;
  projectId?: string | null;
}

interface Emits {
  (e: 'close'): void;
  (e: 'task-update'): void;
}

const props = withDefaults(defineProps<Props>(), {
  projectId: null,
});

defineEmits<Emits>();

const tasksStore = useTasksStore();
const activeTab = ref<'details' | 'activity' | 'comments' | 'attachments'>('details');
const tabs = ['details', 'activity', 'comments', 'attachments'] as const;

function formatTabLabel(tab: string): string {
  const labels: Record<string, string> = {
    details: 'Details',
    activity: 'Activity',
    comments: 'Comments',
    attachments: 'Attachments',
  };
  return labels[tab] || tab;
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
}

async function handleToggleComplete() {
  try {
    if (props.task.completed) {
      await tasksStore.reopenTask(props.task.id, props.projectId || null);
    } else {
      await tasksStore.completeTask(props.task.id, props.projectId || null);
    }
    // Emit update event
  } catch (err) {
    console.error('Error toggling task completion:', err);
  }
}
</script>

