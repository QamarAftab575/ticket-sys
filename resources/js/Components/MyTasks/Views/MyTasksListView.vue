<template>
  <div>
    <div v-if="processedTasks.length === 0 && !isLoading" class="p-4 text-center text-gray-500">
      No tasks found
    </div>
    <ListView
      v-else
      :project="null"
      :tasks="processedTasks"
      :sections="orderedSections"
      :filters="filtersArray"
      :sort="sort"
      :grouping="grouping"
      :is-loading="isLoading"
      :initial-collapsed="collapsedSectionsArray"
      :is-my-tasks="true"
      :members="allMembers"
      @select-task="$emit('select-task', $event)"
      @task-completed="$emit('task-completed', $event)"
      @task-created="$emit('task-created', $event)"
      @task-move="$emit('task-move', $event)"
      @update-dates="$emit('update-dates', $event)"
      @update-assignee="$emit('update-assignee', $event)"
      @update-custom-field="$emit('update-custom-field', $event)"
      @sections-reordered="handleSectionsReordered"
      @section-collapsed="handleSectionCollapsed"
      @add-section="$emit('add-section', $event)"
      @rename-section="(id, name) => $emit('rename-section', id, name)"
      @delete-section="(payload) => $emit('delete-section', payload.sectionId)"
    />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import ListView from '@/Components/Projects/Views/ListView.vue';
import type { Task } from '@/Types/tasks';

interface Section {
  id: string;
  name: string;
  position: number;
}

interface Props {
  tasks: Task[];
  filters: any;
  sort: any[];
  grouping: string;
  isLoading: boolean;
  sections?: Section[];          // real DB sections from the store
  sectionOrder?: string[];       // saved order of section IDs
  collapsedSections?: Set<string>;
  workspaceMembers?: any[];      // workspace members for tasks without project
}

const props = withDefaults(defineProps<Props>(), {
  sections: () => [],
  sectionOrder: () => [],
  collapsedSections: () => new Set(),
  workspaceMembers: () => [],
});

const emit = defineEmits<{
  'select-task': [task: Task];
  'task-completed': [taskId: string];
  'task-created': [data: any];
  'task-move': [data: any];
  'update-dates': [data: any];
  'update-assignee': [data: any];
  'update-custom-field': [data: any];
  'section-order-change': [order: string[]];
  'section-collapse-change': [sectionId: string];
  'add-section': [name: string];
  'rename-section': [sectionId: string, name: string];
  'delete-section': [sectionId: string];
}>();

// Build a map for quick lookup
const sectionMap = computed(() =>
  Object.fromEntries(props.sections.map(s => [s.id, s]))
);

// Sections ordered by the saved section_order preference
const orderedSections = computed<Section[]>(() => {
  const order = props.sectionOrder?.length
    ? props.sectionOrder
    : props.sections.map(s => s.id);

  return order
    .filter(id => sectionMap.value[id])
    .map((id, index) => ({ ...sectionMap.value[id], order: index + 1 }));
});

// Convert filters object to array for ListView
const filtersArray = computed(() => {
  if (Array.isArray(props.filters)) return props.filters;
  if (typeof props.filters === 'object' && props.filters !== null) return Object.values(props.filters);
  return [];
});

// Convert Set to array for ListView's initialCollapsed prop
const collapsedSectionsArray = computed(() => {
  if (props.collapsedSections instanceof Set) return [...props.collapsedSections];
  if (Array.isArray(props.collapsedSections)) return props.collapsedSections;
  return [];
});

// Tasks already have a real section_id from the DB â€” no mapping needed
const processedTasks = computed(() => props.tasks);

// Compute members map: task.id -> members array
// Logic: If task has project, use project.members; otherwise use workspace members
const taskMembersMap = computed(() => {
  const map: Record<string, any[]> = {};
  
  props.tasks.forEach(task => {
    if (task.project?.members && task.project.members.length > 0) {
      // Task belongs to a project - use project members
      map[task.id] = task.project.members;
    } else {
      // Task doesn't belong to a project - use workspace members
      map[task.id] = props.workspaceMembers;
    }
  });
  
  return map;
});

// Get all unique members across all tasks for the ListView
const allMembers = computed(() => {
  const membersSet = new Map();
  
  // Add all workspace members first
  props.workspaceMembers.forEach(member => {
    membersSet.set(member.id, member);
  });
  
  // Add project members from all tasks
  props.tasks.forEach(task => {
    if (task.project?.members) {
      task.project.members.forEach((member: any) => {
        membersSet.set(member.id, member);
      });
    }
  });
  
  return Array.from(membersSet.values());
});

function handleSectionsReordered(sectionIds: string[]) {
  emit('section-order-change', sectionIds);
}

function handleSectionCollapsed(sectionId: string) {
  emit('section-collapse-change', sectionId);
}
</script>

