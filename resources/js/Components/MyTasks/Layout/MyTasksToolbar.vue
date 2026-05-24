<template>
  <div class="bg-white border-b border-gray-200 px-6 py-3">
    <div class="flex items-center justify-between">
      <!-- Left side: View options -->
      <div class="flex items-center gap-4">
        <!-- Filter Button -->
        <div class="relative">
          <button
            @click="showFilters = !showFilters"
            :class="[
              'flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg border transition',
              hasActiveFilters 
                ? 'bg-blue-50 border-blue-200 text-blue-700' 
                : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'
            ]"
          >
            <FunnelIcon class="w-4 h-4" />
            Filter
            <span v-if="activeFilterCount > 0" class="ml-1 px-1.5 py-0.5 bg-blue-100 text-blue-700 text-xs rounded">
              {{ activeFilterCount }}
            </span>
          </button>

          <!-- Filter Dropdown -->
          <div
            v-if="showFilters"
            class="absolute top-full left-0 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg z-20"
          >
            <div class="p-4 space-y-4">
              <div class="flex items-center justify-between">
                <h3 class="font-medium text-gray-900">Filters</h3>
                <button
                  @click="clearAllFilters"
                  class="text-sm text-gray-500 hover:text-gray-700"
                >
                  Clear all
                </button>
              </div>

              <!-- Status Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select
                  v-model="localFilters.status"
                  @change="updateFilters"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                >
                  <option value="">All statuses</option>
                  <option value="to_do">To Do</option>
                  <option value="in_progress">In Progress</option>
                  <option value="blocked">Blocked</option>
                  <option value="in_review">In Review</option>
                  <option value="complete">Complete</option>
                </select>
              </div>

              <!-- Priority Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                <select
                  v-model="localFilters.priority"
                  @change="updateFilters"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                >
                  <option value="">All priorities</option>
                  <option value="urgent">Urgent</option>
                  <option value="high">High</option>
                  <option value="medium">Medium</option>
                  <option value="low">Low</option>
                </select>
              </div>

              <!-- Due Date Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                <select
                  v-model="localFilters.due_date"
                  @change="updateFilters"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                >
                  <option value="">All dates</option>
                  <option value="overdue">Overdue</option>
                  <option value="today">Today</option>
                  <option value="this_week">This Week</option>
                  <option value="no_due_date">No Due Date</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Sort Button -->
        <div class="relative">
          <button
            @click="showSort = !showSort"
            :class="[
              'flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg border transition',
              hasActiveSort 
                ? 'bg-blue-50 border-blue-200 text-blue-700' 
                : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'
            ]"
          >
            <ArrowsUpDownIcon class="w-4 h-4" />
            Sort
          </button>

          <!-- Sort Dropdown -->
          <div
            v-if="showSort"
            class="absolute top-full left-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg z-20"
          >
            <div class="p-4 space-y-3">
              <h3 class="font-medium text-gray-900">Sort by</h3>
              
              <div class="space-y-2">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="sort"
                    value="due_date"
                    v-model="localSort.field"
                    @change="updateSort"
                    class="text-blue-600"
                  />
                  <span class="text-sm">Due Date</span>
                </label>
                
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="sort"
                    value="priority"
                    v-model="localSort.field"
                    @change="updateSort"
                    class="text-blue-600"
                  />
                  <span class="text-sm">Priority</span>
                </label>
                
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="sort"
                    value="name"
                    v-model="localSort.field"
                    @change="updateSort"
                    class="text-blue-600"
                  />
                  <span class="text-sm">Task Name</span>
                </label>
                
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="sort"
                    value="project_name"
                    v-model="localSort.field"
                    @change="updateSort"
                    class="text-blue-600"
                  />
                  <span class="text-sm">Project</span>
                </label>
              </div>

              <div class="pt-2 border-t">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="localSort.descending"
                    @change="updateSort"
                    class="text-blue-600"
                  />
                  <span class="text-sm">Descending</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Group Button -->
        <div class="relative">
          <button
            @click="showGroup = !showGroup"
            :class="[
              'flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg border transition',
              hasActiveGroup 
                ? 'bg-blue-50 border-blue-200 text-blue-700' 
                : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'
            ]"
          >
            <Squares2X2Icon class="w-4 h-4" />
            Group
          </button>

          <!-- Group Dropdown -->
          <div
            v-if="showGroup"
            class="absolute top-full left-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-20"
          >
            <div class="p-4 space-y-3">
              <h3 class="font-medium text-gray-900">Group by</h3>
              
              <div class="space-y-2">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="group"
                    value=""
                    v-model="localGroup"
                    @change="updateGroup"
                    class="text-blue-600"
                  />
                  <span class="text-sm">None</span>
                </label>
                
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="group"
                    value="project"
                    v-model="localGroup"
                    @change="updateGroup"
                    class="text-blue-600"
                  />
                  <span class="text-sm">Project</span>
                </label>
                
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="group"
                    value="status"
                    v-model="localGroup"
                    @change="updateGroup"
                    class="text-blue-600"
                  />
                  <span class="text-sm">Status</span>
                </label>
                
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="group"
                    value="priority"
                    v-model="localGroup"
                    @change="updateGroup"
                    class="text-blue-600"
                  />
                  <span class="text-sm">Priority</span>
                </label>
                
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="group"
                    value="due_date"
                    v-model="localGroup"
                    @change="updateGroup"
                    class="text-blue-600"
                  />
                  <span class="text-sm">Due Date</span>
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right side: Empty for now (view controls moved to header) -->
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import {
  FunnelIcon,
  ArrowsUpDownIcon,
  Squares2X2Icon,
} from '@heroicons/vue/24/outline';
import type { TaskFilter, TaskSort, TaskGroupBy } from '@/Types/tasks';

interface Props {
  initialSort?: TaskSort[];
  initialGroup?: string;
  initialFilters?: any;
}

const props = withDefaults(defineProps<Props>(), {
  initialSort: () => [],
  initialGroup: '',
  initialFilters: () => ({}),
});

const emit = defineEmits<{
  'filter-change': [filters: TaskFilter];
  'sort-change': [sort: TaskSort[]];
  'group-change': [groupBy: TaskGroupBy];
}>();

// Local state
const showFilters = ref(false);
const showSort = ref(false);
const showGroup = ref(false);

const localFilters = ref({
  status: '',
  priority: '',
  due_date: '',
});

const localSort = ref({
  field: 'due_date',
  descending: false,
});

const localGroup = ref('');

// Initialize from saved preferences on mount
onMounted(() => {
  // Restore sort
  if (props.initialSort?.length) {
    const first = props.initialSort[0];
    localSort.value.field = first.field || 'due_date';
    localSort.value.descending = first.direction === 'desc';
  }

  // Restore grouping
  if (props.initialGroup) {
    localGroup.value = props.initialGroup;
  }

  // Restore filters
  if (props.initialFilters && typeof props.initialFilters === 'object') {
    const filtersArr = Array.isArray(props.initialFilters)
      ? props.initialFilters
      : Object.values(props.initialFilters);

    for (const f of filtersArr as any[]) {
      if (f?.field === 'status') localFilters.value.status = f.value || '';
      if (f?.field === 'priority') localFilters.value.priority = f.value || '';
      if (f?.field === 'due_date') {
        // Reverse-map operator back to select value
        if (f.operator === 'is_empty') localFilters.value.due_date = 'no_due_date';
        else if (f.operator === 'equals') localFilters.value.due_date = 'today';
        else if (f.operator === 'less_than') {
          const today = new Date().toISOString().split('T')[0];
          localFilters.value.due_date = f.value === today ? 'overdue' : 'this_week';
        }
      }
    }
  }
});

// Computed
const hasActiveFilters = computed(() => {
  return Object.values(localFilters.value).some(value => value !== '');
});

const activeFilterCount = computed(() => {
  return Object.values(localFilters.value).filter(value => value !== '').length;
});

const hasActiveSort = computed(() => {
  return localSort.value.field !== '';
});

const hasActiveGroup = computed(() => {
  return localGroup.value !== '';
});

// Methods
function updateFilters() {
  const filters: any = {};
  
  if (localFilters.value.status) {
    filters.status = {
      field: 'status',
      operator: 'equals',
      value: localFilters.value.status,
    };
  }
  
  if (localFilters.value.priority) {
    filters.priority = {
      field: 'priority',
      operator: 'equals',
      value: localFilters.value.priority,
    };
  }
  
  if (localFilters.value.due_date) {
    // Handle special due date filters
    const dueDateFilter = localFilters.value.due_date;
    if (dueDateFilter === 'overdue') {
      filters.due_date = {
        field: 'due_date',
        operator: 'less_than',
        value: new Date().toISOString().split('T')[0],
      };
    } else if (dueDateFilter === 'today') {
      const today = new Date().toISOString().split('T')[0];
      filters.due_date = {
        field: 'due_date',
        operator: 'equals',
        value: today,
      };
    } else if (dueDateFilter === 'this_week') {
      const today = new Date();
      const endOfWeek = new Date(today);
      endOfWeek.setDate(today.getDate() + (7 - today.getDay()));
      filters.due_date = {
        field: 'due_date',
        operator: 'less_than',
        value: endOfWeek.toISOString().split('T')[0],
      };
    } else if (dueDateFilter === 'no_due_date') {
      filters.due_date = {
        field: 'due_date',
        operator: 'is_empty',
        value: null,
      };
    }
  }
  
  emit('filter-change', filters);
}

function updateSort() {
  const sortRules = [];
  
  if (localSort.value.field) {
    sortRules.push({
      field: localSort.value.field,
      direction: localSort.value.descending ? 'desc' : 'asc',
    });
  }
  
  console.log('Emitting sort-change:', sortRules);
  emit('sort-change', sortRules);
}

function updateGroup() {
  emit('group-change', localGroup.value as TaskGroupBy);
}

function clearAllFilters() {
  localFilters.value = {
    status: '',
    priority: '',
    due_date: '',
  };
  updateFilters();
}

// Close dropdowns when clicking outside
function handleClickOutside(event: Event) {
  const target = event.target as Element;
  if (!target.closest('.relative')) {
    showFilters.value = false;
    showSort.value = false;
    showGroup.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>