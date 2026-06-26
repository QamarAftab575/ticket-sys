<template>
  <!-- Toolbar wrapper with sticky positioning on desktop only -->
  <div class="flex items-center gap-3">
    <!-- Filter Button -->
    <button
      @click="showFilters = !showFilters"
      :title="$t('filter')"
      :class="[
        'flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium rounded-md transition-colors relative cursor-pointer',
        hasActiveFilters 
          ? 'text-blue-600 hover:bg-blue-50' 
          : 'text-gray-700 hover:bg-gray-100'
      ]"
    >
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
      </svg>
      <span class="hidden sm:inline">{{ $t('filter') }}</span>
      <span v-if="activeFilterCount > 0" class="px-1.5 min-w-[20px] h-5 flex items-center justify-center bg-blue-600 text-white text-xs font-semibold rounded-full">
        {{ activeFilterCount }}
      </span>
    </button>

    <!-- Sort Button -->
    <button
      @click="showSort = !showSort"
      :title="$t('sort')"
      :class="[
        'flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium rounded-md transition-colors cursor-pointer',
        hasActiveSort 
          ? 'text-blue-600 hover:bg-blue-50' 
          : 'text-gray-700 hover:bg-gray-100'
      ]"
    >
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
      </svg>
      <span class="hidden sm:inline">{{ $t('sort') }}</span>
    </button>

    <!-- Filter Dropdown Panel -->
    <Teleport to="body">
      <div
        v-if="showFilters"
        class="fixed inset-0 z-40"
        @click="showFilters = false"
      />
      <div
        v-if="showFilters"
        class="fixed z-50 w-80 bg-white border border-gray-200 rounded-lg shadow-xl"
        :style="filterDropdownStyle"
      >
        <div class="p-4">
          <!-- Header -->
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-900">{{ $t('filter') }}</h3>
            <button
              @click="clearAllFilters"
              class="text-xs text-gray-500 hover:text-gray-700 font-medium"
            >
              {{ $t('clear_all') }}
            </button>
          </div>

          <!-- Filters -->
          <div class="space-y-4">
            <!-- Due Date Filter -->
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1.5">{{ $t('due_date') }}</label>
              <select
                v-model="localFilters.due_date"
                @change="updateFilters"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">{{ $t('any_date') }}</option>
                <option value="overdue">{{ $t('overdue') }}</option>
                <option value="today">{{ $t('today') }}</option>
                <option value="this_week">{{ $t('this_week') }}</option>
                <option value="next_week">{{ $t('next_week') }}</option>
                <option value="no_due_date">{{ $t('no_due_date') }}</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Sort Dropdown Panel -->
    <Teleport to="body">
      <div
        v-if="showSort"
        class="fixed inset-0 z-40"
        @click="showSort = false"
      />
      <div
        v-if="showSort"
        class="fixed z-50 w-56 bg-white border border-gray-200 rounded-lg shadow-xl"
        :style="sortDropdownStyle"
      >
        <div class="p-3">
          <h3 class="text-xs font-semibold text-gray-900 mb-2 px-2">{{ $t('sort_by') }}</h3>
          
          <div class="space-y-0.5">
            <button
              v-for="option in sortOptions"
              :key="option.value"
              @click="selectSort(option.value)"
              class="w-full flex items-center justify-between px-2 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition-colors"
            >
              <span>{{ option.label }}</span>
              <svg v-if="localSort.field === option.value" class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>

          <div class="border-t border-gray-200 mt-2 pt-2">
            <button
              @click="toggleSortDirection"
              class="w-full flex items-center justify-between px-2 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition-colors"
            >
              <span>{{ localSort.descending ? $t('descending') : $t('ascending') }}</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="localSort.descending ? 'M19 9l-7 7-7-7' : 'M5 15l7-7 7 7'" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick, Fragment } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { TaskFilter, TaskSort, TaskGroupBy } from '@/Types/tasks';

interface Props {
  initialSort?: TaskSort[];
  initialGroup?: string;
  initialFilters?: any;
  workspaceMembers?: any[];
}

const props = withDefaults(defineProps<Props>(), {
  initialSort: () => [],
  initialGroup: '',
  initialFilters: () => ({}),
  workspaceMembers: () => [],
});

const emit = defineEmits<{
  'filter-change': [filters: TaskFilter];
  'sort-change': [sort: TaskSort[]];
  'group-change': [groupBy: TaskGroupBy];
}>();

const page = usePage();

// Local state
const showFilters = ref(false);
const showSort = ref(false);

const filterDropdownStyle = ref({});
const sortDropdownStyle = ref({});

const localFilters = ref({
  due_date: '',
});

const localSort = ref({
  field: 'due_date',
  descending: false,
});

const sortOptions = computed(() => [
  { label: page.props.translations?.due_date || 'Due date', value: 'due_date' },
  { label: page.props.translations?.alphabetically || 'Alphabetically', value: 'name' },
  { label: page.props.translations?.project || 'Project', value: 'project_name' },
  { label: page.props.translations?.created_on || 'Created on', value: 'created_at' },
]);

// Position dropdowns
watch(showFilters, (show) => {
  if (show) {
    nextTick(() => {
      const button = document.querySelector('button:has(svg path[d*="M3 4a1"])') as HTMLElement;
      if (button) {
        const rect = button.getBoundingClientRect();
        filterDropdownStyle.value = {
          top: `${rect.bottom + 8}px`,
          right: `${window.innerWidth - rect.right}px`,
        };
      }
    });
  }
});

watch(showSort, (show) => {
  if (show) {
    nextTick(() => {
      const buttons = Array.from(document.querySelectorAll('button'));
      const button = buttons.find(b => b.textContent?.includes('Sort')) as HTMLElement;
      if (button) {
        const rect = button.getBoundingClientRect();
        sortDropdownStyle.value = {
          top: `${rect.bottom + 8}px`,
          right: `${window.innerWidth - rect.right}px`,
        };
      }
    });
  }
});

// Initialize from saved preferences on mount
onMounted(() => {
  if (props.initialSort?.length) {
    const first = props.initialSort[0];
    localSort.value.field = first.field || 'due_date';
    localSort.value.descending = first.direction === 'desc';
  }

  if (props.initialFilters && typeof props.initialFilters === 'object') {
    const filtersArr = Array.isArray(props.initialFilters)
      ? props.initialFilters
      : Object.values(props.initialFilters);

    for (const f of filtersArr as any[]) {
      if (f?.field === 'due_date') {
        if (f.operator === 'is_empty') localFilters.value.due_date = 'no_due_date';
        else if (f.operator === 'equals') localFilters.value.due_date = 'today';
        else if (f.operator === 'less_than') localFilters.value.due_date = 'overdue';
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
  return localSort.value.field !== 'due_date' || localSort.value.descending;
});

// Methods
function updateFilters() {
  const filters: any = {};
  
  if (localFilters.value.due_date) {
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
        operator: 'less_than_or_equal',
        value: endOfWeek.toISOString().split('T')[0],
      };
    } else if (dueDateFilter === 'next_week') {
      const today = new Date();
      const nextWeekStart = new Date(today);
      nextWeekStart.setDate(today.getDate() + (7 - today.getDay() + 1));
      const nextWeekEnd = new Date(nextWeekStart);
      nextWeekEnd.setDate(nextWeekStart.getDate() + 6);
      filters.due_date = {
        field: 'due_date',
        operator: 'between',
        value: [nextWeekStart.toISOString().split('T')[0], nextWeekEnd.toISOString().split('T')[0]],
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

function selectSort(field: string) {
  localSort.value.field = field;
  updateSort();
  showSort.value = false;
}

function toggleSortDirection() {
  localSort.value.descending = !localSort.value.descending;
  updateSort();
}

function updateSort() {
  const sortRules = [];
  
  if (localSort.value.field) {
    sortRules.push({
      field: localSort.value.field,
      direction: localSort.value.descending ? 'desc' : 'asc',
    });
  }
  
  emit('sort-change', sortRules);
}

function clearAllFilters() {
  localFilters.value = {
    due_date: '',
  };
  updateFilters();
}
</script>
