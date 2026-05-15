<template>
  <div class="space-y-4">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Assignee</label>
      <div class="space-y-2">
        <label v-for="member in projectMembers" :key="member.id" class="flex items-center">
          <input
            type="checkbox"
            :checked="selectedAssignees.includes(member.id)"
            @change="toggleAssignee(member.id)"
            class="rounded border-gray-300"
          />
          <span class="ml-2 text-sm text-gray-700">{{ member.name }}</span>
        </label>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
      <div class="space-y-2">
        <label v-for="priority in priorities" :key="priority" class="flex items-center">
          <input
            type="checkbox"
            :checked="selectedPriorities.includes(priority)"
            @change="togglePriority(priority)"
            class="rounded border-gray-300"
          />
          <span class="ml-2 text-sm text-gray-700">{{ formatPriority(priority) }}</span>
        </label>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
      <select
        v-model="selectedDueDate"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option value="">Any time</option>
        <option value="today">Today</option>
        <option value="tomorrow">Tomorrow</option>
        <option value="this_week">This week</option>
        <option value="overdue">Overdue</option>
        <option value="no_date">No due date</option>
      </select>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
      <div class="space-y-2">
        <label v-for="status in statuses" :key="status" class="flex items-center">
          <input
            type="checkbox"
            :checked="selectedStatuses.includes(status)"
            @change="toggleStatus(status)"
            class="rounded border-gray-300"
          />
          <span class="ml-2 text-sm text-gray-700">{{ formatStatus(status) }}</span>
        </label>
      </div>
    </div>

    <div class="flex gap-2 pt-4 border-t border-gray-200">
      <button
        @click="handleApply"
        class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition"
      >
        Apply
      </button>
      <button
        @click="handleClear"
        class="flex-1 px-3 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition"
      >
        Clear
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import type { TaskFilter, TaskPriority, TaskStatus } from '@/Types/tasks';
import { api } from '@/Services/api';

interface Props {
  projectId?: string | null;
}

interface Emits {
  (e: 'apply', filters: TaskFilter): void;
  (e: 'close'): void;
}

const props = withDefaults(defineProps<Props>(), {
  projectId: null,
});

defineEmits<Emits>();

const projectMembers = ref<any[]>([]);
const selectedAssignees = ref<string[]>([]);
const selectedPriorities = ref<TaskPriority[]>([]);
const selectedDueDate = ref('');
const selectedStatuses = ref<TaskStatus[]>([]);

const priorities: TaskPriority[] = ['none', 'low', 'medium', 'high', 'urgent'];
const statuses: TaskStatus[] = ['todo', 'in_progress', 'done'];

onMounted(async () => {
  if (props.projectId) {
    try {
      const response = await api.get(`/projects/${props.projectId}/members`);
      projectMembers.value = response.data;
    } catch (err) {
      console.error('Error fetching project members:', err);
    }
  }
});

function toggleAssignee(memberId: string) {
  const index = selectedAssignees.value.indexOf(memberId);
  if (index > -1) {
    selectedAssignees.value.splice(index, 1);
  } else {
    selectedAssignees.value.push(memberId);
  }
}

function togglePriority(priority: TaskPriority) {
  const index = selectedPriorities.value.indexOf(priority);
  if (index > -1) {
    selectedPriorities.value.splice(index, 1);
  } else {
    selectedPriorities.value.push(priority);
  }
}

function toggleStatus(status: TaskStatus) {
  const index = selectedStatuses.value.indexOf(status);
  if (index > -1) {
    selectedStatuses.value.splice(index, 1);
  } else {
    selectedStatuses.value.push(status);
  }
}

function formatPriority(priority: TaskPriority): string {
  const labels: Record<TaskPriority, string> = {
    none: 'None',
    low: 'Low',
    medium: 'Medium',
    high: 'High',
    urgent: 'Urgent',
  };
  return labels[priority];
}

function formatStatus(status: TaskStatus): string {
  const labels: Record<TaskStatus, string> = {
    todo: 'To Do',
    in_progress: 'In Progress',
    done: 'Done',
  };
  return labels[status];
}

function handleApply() {
  const filters: TaskFilter = {};
  if (selectedAssignees.value.length) filters.assigneeIds = selectedAssignees.value;
  if (selectedPriorities.value.length) filters.priorities = selectedPriorities.value;
  if (selectedDueDate.value) filters.dueDateOption = selectedDueDate.value as any;
  if (selectedStatuses.value.length) filters.statuses = selectedStatuses.value;
  
  // Emit apply event
}

function handleClear() {
  selectedAssignees.value = [];
  selectedPriorities.value = [];
  selectedDueDate.value = '';
  selectedStatuses.value = [];
}
</script>
