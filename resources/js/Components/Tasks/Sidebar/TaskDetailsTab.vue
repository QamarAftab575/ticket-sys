<template>
  <div class="space-y-4">
    <!-- Assignee -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Assignee</label>
      <TaskAssigneeField :task="task" :project-id="projectId" @update="$emit('update')" />
    </div>

    <!-- Due Date -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
      <TaskDueDateField :task="task" @update="$emit('update')" />
    </div>

    <!-- Priority -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
      <TaskPriorityField :task="task" @update="$emit('update')" />
    </div>

    <!-- Tags -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
      <TaskTagsField :task="task" :project-id="projectId" @update="$emit('update')" />
    </div>

    <!-- Description -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
      <TaskDescriptionField :task="task" @update="$emit('update')" />
    </div>

    <!-- Custom Fields -->
    <div v-if="task.customFieldValues?.length">
      <label class="block text-sm font-medium text-gray-700 mb-2">Custom Fields</label>
      <TaskCustomFieldsForm :task="task" @update="$emit('update')" />
    </div>

    <!-- Subtasks -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Subtasks</label>
      <SubtaskList :task="task" @update="$emit('update')" />
    </div>

    <!-- Dependencies -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Dependencies</label>
      <TaskDependenciesForm :task="task" :project-id="projectId" @update="$emit('update')" />
    </div>
  </div>
</template>

<script setup lang="ts">
import type { TaskDetail } from '@/Types/tasks';
import TaskAssigneeField from '../Forms/TaskAssigneeField.vue';
import TaskDueDateField from '../Forms/TaskDueDateField.vue';
import TaskPriorityField from '../Forms/TaskPriorityField.vue';
import TaskTagsField from '../Forms/TaskTagsField.vue';
import TaskDescriptionField from '../Forms/TaskDescriptionField.vue';
import TaskCustomFieldsForm from '../Forms/TaskCustomFieldsForm.vue';
import TaskDependenciesForm from '../Forms/TaskDependenciesForm.vue';
import SubtaskList from '../Subtasks/SubtaskList.vue';

interface Props {
  task: TaskDetail;
  projectId?: string | null;
}

interface Emits {
  (e: 'update'): void;
}

const props = withDefaults(defineProps<Props>(), {
  projectId: null,
});

defineEmits<Emits>();
</script>
