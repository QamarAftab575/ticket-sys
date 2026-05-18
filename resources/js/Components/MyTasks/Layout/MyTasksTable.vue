<template>
  <div class="flex-1 overflow-auto bg-white flex flex-col">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center h-64">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!loading && displayedTasks.length === 0" class="flex flex-col items-center justify-center h-64 text-gray-500">
      <CheckCircleIcon class="w-12 h-12 text-gray-300 mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">No tasks found</h3>
      <p class="text-sm text-gray-500">You don't have any tasks assigned to you yet.</p>
    </div>

    <!-- Tasks Table -->
    <div v-else class="overflow-hidden flex flex-col flex-1">
      <!-- Advanced Header with drag/drop, hide/show, resize -->
      <ListViewHeader
        :project-id="null"
        :visible-columns="visibleColumns"
        :column-widths="columnWidths"
        :sort-rules="[]"
        :hidden-columns="hiddenColumns"
        :available-fields="availableFields"
        @sort="handleSort"
        @resize-column="handleResizeColumn"
        @hide-column="handleHideColumn"
        @show-column="handleShowColumn"
        @add-column="handleAddColumn"
        @create-field="onFieldCreated"
        @reorder-column="handleReorderColumn"
      />

      <!-- Scrollable Task List -->
      <div class="flex-1 overflow-x-auto">
        <!-- Task Groups or Flat List -->
        <div v-if="isGrouped">
          <div v-for="(group, groupName) in groupedTasks" :key="groupName" class="border-b border-gray-100">
            <!-- Group Header -->
            <div 
              class="bg-gray-25 px-6 py-3 border-b border-gray-100 cursor-pointer hover:bg-gray-50 sticky top-0 z-5"
              @click="toggleGroup(groupName)"
            >
              <div class="flex items-center gap-2">
                <ChevronRightIcon 
                  :class="[
                    'w-4 h-4 text-gray-400 transition-transform',
                    collapsedGroups.includes(groupName) ? '' : 'rotate-90'
                  ]" 
                />
                <span class="font-medium text-gray-900">{{ groupName }}</span>
                <span class="text-sm text-gray-500">({{ group.length }})</span>
              </div>
            </div>

            <!-- Group Tasks -->
            <div v-if="!collapsedGroups.includes(groupName)">
              <TaskRow
                v-for="task in group"
                :key="task.id"
                :task="task"
                :columns="visibleColumns"
                :column-widths="columnWidths"
                :members="[]"
                :custom-fields="customFields"
                @click="$emit('task-select', task.id)"
                @toggle-complete="$emit('task-complete', task.id)"
                @update-dates="handleUpdateDates"
                @update-assignee="handleUpdateAssignee"
                @update-custom-field="handleUpdateCustomField"
              />
            </div>
          </div>
        </div>

        <!-- Flat Task List -->
        <div v-else>
          <TaskRow
            v-for="task in tasks"
            :key="task.id"
            :task="task"
            :columns="visibleColumns"
            :column-widths="columnWidths"
            :members="[]"
            :custom-fields="customFields"
            @click="$emit('task-select', task.id)"
            @toggle-complete="$emit('task-complete', task.id)"
            @update-dates="handleUpdateDates"
            @update-assignee="handleUpdateAssignee"
            @update-custom-field="handleUpdateCustomField"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { CheckCircleIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';
import TaskRow from '@/Components/Projects/Views/TaskRow.vue';
import ListViewHeader from '@/Components/Projects/Views/ListViewHeader.vue';
import { useCustomFields } from '@/Composables/useCustomFields';
import type { Task } from '@/Types/tasks';

interface Props {
  tasks: Task[];
  loading: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  'task-select': [taskId: string];
  'task-complete': [taskId: string];
  'task-delete': [taskId: string];
}>();

// Local state
const collapsedGroups = ref<string[]>([]);

// Initialize personal custom fields composable (projectId = null for My Tasks)
const { fields: customFieldsList, fetchFields } = useCustomFields(null)

// Custom fields for My Tasks
const customFields = computed(() => customFieldsList.value);

// My Tasks default columns
const defaultColumns = [
  { id: 'name', label: 'Task name', visible: true, sortable: true, required: true },
  { id: 'due_date', label: 'Due date', visible: true, sortable: true, required: false },
  { id: 'project_name', label: 'Project', visible: true, sortable: true, required: false },
  { id: 'assignee', label: 'Assignee', visible: true, sortable: false, required: false },
];

// Track visible and hidden columns
const hiddenColumns = ref<string[]>([]);
const allColumns = ref([...defaultColumns]);

// Column widths for My Tasks layout
const columnWidths = ref({
  name: '300px',
  due_date: '150px', 
  project_name: '200px',
  assignee: '150px',
});

// Visible columns (excluding hidden ones)
const visibleColumns = computed(() => {
  return allColumns.value.filter(col => !hiddenColumns.value.includes(col.id));
});

// Available fields for showing/hiding
const availableFields = computed(() => {
  return allColumns.value.map(col => ({
    id: col.id,
    name: col.label,
    visible: visibleColumns.value.some(vc => vc.id === col.id),
  }));
});

// Computed
const isGrouped = computed(() => {
  return Array.isArray(props.tasks) && props.tasks.length > 0 && 
         typeof props.tasks[0] === 'object' && 
         !('id' in props.tasks[0]);
});

const groupedTasks = computed(() => {
  if (isGrouped.value) {
    return props.tasks as any;
  }
  return {};
});

const displayedTasks = computed(() => {
  if (isGrouped.value) {
    return Object.values(groupedTasks.value).flat();
  }
  return props.tasks;
});

// Methods
function toggleGroup(groupName: string) {
  const index = collapsedGroups.value.indexOf(groupName);
  if (index > -1) {
    collapsedGroups.value.splice(index, 1);
  } else {
    collapsedGroups.value.push(groupName);
  }
}

function handleSort(data: any) {
  console.log('Sort:', data);
  // Implement sort logic
}

function handleResizeColumn(data: any) {
  const { columnId, delta } = data;
  const currentWidth = parseInt(columnWidths.value[columnId] || '200px');
  columnWidths.value[columnId] = (currentWidth + delta) + 'px';
}

function handleHideColumn(columnId: string) {
  if (!hiddenColumns.value.includes(columnId)) {
    hiddenColumns.value.push(columnId);
  }
}

function handleShowColumn(columnId: string) {
  const index = hiddenColumns.value.indexOf(columnId);
  if (index > -1) {
    hiddenColumns.value.splice(index, 1);
  }
}

function handleAddColumn(column: any) {
  if (!allColumns.value.find(c => c.id === column.id)) {
    allColumns.value.push(column);
    columnWidths.value[column.id] = '150px';
  }
}

function handleReorderColumn(data: any) {
  const { fromIndex, toIndex } = data;
  const visible = visibleColumns.value;
  const [removed] = visible.splice(fromIndex, 1);
  visible.splice(toIndex, 0, removed);
  
  // Update allColumns order
  allColumns.value = visible.concat(
    allColumns.value.filter(col => hiddenColumns.value.includes(col.id))
  );
}

function handleUpdateDates(data: any) {
  console.log('Update dates:', data);
}

function handleUpdateAssignee(data: any) {
  console.log('Update assignee:', data);
}

function handleUpdateCustomField(data: any) {
  console.log('Update custom field:', data);
}

function onFieldCreated(field: any) {
  // Add the new field to our columns
  const newColumn = {
    id: field.id,
    label: field.name,
    visible: true,
    sortable: false,
    isCustom: true,
  };
  
  allColumns.value.push(newColumn);
  columnWidths.value[field.id] = '150px';
}

// Fetch personal custom fields on mount
onMounted(async () => {
  await fetchFields()
  // Add fetched custom fields as columns if they're not already there
  customFieldsList.value.forEach(field => {
    if (!allColumns.value.find(c => c.id === field.id)) {
      allColumns.value.push({
        id: field.id,
        label: field.name,
        visible: true,
        sortable: false,
        isCustom: true,
      })
      columnWidths.value[field.id] = '150px'
    }
  })
})
</script>