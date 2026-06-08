import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type {
  Task,
  TaskDetail,
  TaskFilter,
  TaskSort,
  TaskGroupBy,
  TaskColumn,
  TaskFormData,
  TaskListState,
} from '@/Types/tasks';
import { api } from '@/Services/api';

export const useTasksStore = defineStore('tasks', () => {
  // State
  const tasks = ref<Task[]>([]);
  const selectedTaskId = ref<string | null>(null);
  const selectedTask = ref<TaskDetail | null>(null);
  const filters = ref<TaskFilter>({});
  const sort = ref<TaskSort[]>([
    { field: 'created_at', direction: 'desc' },
  ]);
  const groupBy = ref<TaskGroupBy>(null);
  const columns = ref<TaskColumn[]>([
    { id: 'name', label: 'Task Name', visible: true, sortable: true },
    { id: 'assignee', label: 'Assignee', visible: true, sortable: true },
    { id: 'due_date', label: 'Due Date', visible: true, sortable: true },
    { id: 'priority', label: 'Priority', visible: true, sortable: true },
    { id: 'tags', label: 'Tags', visible: true, sortable: false },
    { id: 'status', label: 'Status', visible: true, sortable: true },
  ]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  // Computed
  const visibleColumns = computed(() => columns.value.filter(col => col.visible));

  const groupedTasks = computed(() => {
    if (!groupBy.value) return { ungrouped: tasks.value };

    const grouped: Record<string, Task[]> = {};

    tasks.value.forEach(task => {
      let groupKey = 'Ungrouped';

      switch (groupBy.value) {
        case 'assignee':
          groupKey = task.assignee?.name || 'Unassigned';
          break;
        case 'due_date':
          groupKey = task.due_date || 'No Due Date';
          break;
        case 'priority':
          groupKey = task.priority || 'None';
          break;
        case 'section':
          groupKey = task.section_id || 'No Section';
          break;
        case 'project':
          groupKey = task.project_id;
          break;
        case 'status':
          groupKey = task.status || 'Todo';
          break;
      }

      if (!grouped[groupKey]) {
        grouped[groupKey] = [];
      }
      grouped[groupKey].push(task);
    });

    return grouped;
  });

  // Actions
  async function fetchTasks(projectId: string | null = null) {
    loading.value = true;
    error.value = null;

    try {
      const endpoint = projectId
        ? `/projects/${projectId}/tasks`
        : '/tasks';

      const params = new URLSearchParams();

      // Add filters
      if (filters.value.assigneeIds?.length) {
        params.append('filters[assignee_ids]', filters.value.assigneeIds.join(','));
      }
      if (filters.value.priorities?.length) {
        params.append('filters[priorities]', filters.value.priorities.join(','));
      }
      if (filters.value.statuses?.length) {
        params.append('filters[statuses]', filters.value.statuses.join(','));
      }
      if (filters.value.tagIds?.length) {
        params.append('filters[tag_ids]', filters.value.tagIds.join(','));
      }
      if (filters.value.dueDateOption) {
        params.append('filters[due_date]', filters.value.dueDateOption);
      }
      if (filters.value.searchQuery) {
        params.append('filters[search]', filters.value.searchQuery);
      }

      // Add sorting
      sort.value.forEach((s, index) => {
        params.append(`sort[${index}][field]`, s.field);
        params.append(`sort[${index}][direction]`, s.direction);
      });

      const response = await api.get(`${endpoint}?${params.toString()}`);
      tasks.value = response.data;
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch tasks';
      console.error('Error fetching tasks:', err);
    } finally {
      loading.value = false;
    }
  }

  async function fetchTask(taskId: string) {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.get(`/tasks/${taskId}`);
      selectedTask.value = response.data;
      return response.data;
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch task';
      console.error('Error fetching task:', err);
    } finally {
      loading.value = false;
    }
  }

  async function createTask(
    data: TaskFormData,
    projectId: string | null = null
  ) {
    loading.value = true;
    error.value = null;

    try {
      const endpoint = projectId
        ? `/projects/${projectId}/tasks`
        : '/tasks';

      const response = await api.post(endpoint, data);
      tasks.value.unshift(response.data);
      return response.data;
    } catch (err: any) {
      error.value = err.message || 'Failed to create task';
      console.error('Error creating task:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function updateTask(
    taskId: string,
    data: Partial<TaskFormData>,
    projectId: string | null = null
  ) {
    loading.value = true;
    error.value = null;

    try {
      const endpoint = projectId
        ? `/projects/${projectId}/tasks/${taskId}`
        : `/tasks/${taskId}`;

      const response = await api.put(endpoint, data);
      
      // Update in list
      const index = tasks.value.findIndex(t => t.id === taskId);
      if (index !== -1) {
        tasks.value[index] = response.data;
      }

      // Update selected task if it's the same
      if (selectedTask.value?.id === taskId) {
        selectedTask.value = response.data;
      }

      return response.data;
    } catch (err: any) {
      error.value = err.message || 'Failed to update task';
      console.error('Error updating task:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function deleteTask(
    taskId: string,
    projectId: string | null = null
  ) {
    loading.value = true;
    error.value = null;

    try {
      const endpoint = projectId
        ? `/projects/${projectId}/tasks/${taskId}`
        : `/tasks/${taskId}`;

      await api.delete(endpoint);
      
      tasks.value = tasks.value.filter(t => t.id !== taskId);

      if (selectedTask.value?.id === taskId) {
        selectedTask.value = null;
        selectedTaskId.value = null;
      }
    } catch (err: any) {
      error.value = err.message || 'Failed to delete task';
      console.error('Error deleting task:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function completeTask(
    taskId: string,
    projectId: string | null = null
  ) {
    loading.value = true;
    error.value = null;

    try {
      const endpoint = projectId
        ? `/projects/${projectId}/tasks/${taskId}/complete`
        : `/tasks/${taskId}/complete`;

      const response = await api.post(endpoint);

      // Update in list
      const index = tasks.value.findIndex(t => t.id === taskId);
      if (index !== -1) {
        tasks.value[index] = response.data;
      }

      // Update selected task
      if (selectedTask.value?.id === taskId) {
        selectedTask.value = response.data;
      }

      return response.data;
    } catch (err: any) {
      error.value = err.message || 'Failed to complete task';
      console.error('Error completing task:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function reopenTask(
    taskId: string,
    projectId: string | null = null
  ) {
    loading.value = true;
    error.value = null;

    try {
      const endpoint = projectId
        ? `/projects/${projectId}/tasks/${taskId}/reopen`
        : `/tasks/${taskId}/reopen`;

      const response = await api.post(endpoint);

      // Update in list
      const index = tasks.value.findIndex(t => t.id === taskId);
      if (index !== -1) {
        tasks.value[index] = response.data;
      }

      // Update selected task
      if (selectedTask.value?.id === taskId) {
        selectedTask.value = response.data;
      }

      return response.data;
    } catch (err: any) {
      error.value = err.message || 'Failed to reopen task';
      console.error('Error reopening task:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function duplicateTask(
    taskId: string,
    projectId: string | null = null
  ) {
    loading.value = true;
    error.value = null;

    try {
      const endpoint = projectId
        ? `/projects/${projectId}/tasks/${taskId}/duplicate`
        : `/tasks/${taskId}/duplicate`;

      const response = await api.post(endpoint);
      tasks.value.unshift(response.data);
      return response.data;
    } catch (err: any) {
      error.value = err.message || 'Failed to duplicate task';
      console.error('Error duplicating task:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  // Filter actions
  function setFilters(newFilters: TaskFilter) {
    filters.value = { ...filters.value, ...newFilters };
  }

  function clearFilters() {
    filters.value = {};
  }

  // Sort actions
  function setSort(newSort: TaskSort[]) {
    sort.value = newSort;
  }

  function addSort(sortRule: TaskSort) {
    sort.value.push(sortRule);
  }

  function removeSort(field: string) {
    sort.value = sort.value.filter(s => s.field !== field);
  }

  // Grouping actions
  function setGroupBy(group: TaskGroupBy) {
    groupBy.value = group;
  }

  // Column actions
  function toggleColumn(columnId: string) {
    const column = columns.value.find(c => c.id === columnId);
    if (column) {
      column.visible = !column.visible;
    }
  }

  function setColumnWidth(columnId: string, width: number) {
    const column = columns.value.find(c => c.id === columnId);
    if (column) {
      column.width = width;
    }
  }

  // Selection actions
  function selectTask(taskId: string) {
    selectedTaskId.value = taskId;
  }

  function deselectTask() {
    selectedTaskId.value = null;
    selectedTask.value = null;
  }

  // ── Real-time helpers (called by Echo listeners) ──────────────────────────

  /** Merge incoming task data into the list without a network round-trip. */
  function patchTask(incoming: Partial<Task> & { id: string }) {
    const idx = tasks.value.findIndex(t => t.id === incoming.id);
    if (idx !== -1) {
      tasks.value[idx] = { ...tasks.value[idx], ...incoming };
    }
    if (selectedTask.value?.id === incoming.id) {
      Object.assign(selectedTask.value, incoming);
    }
  }

  /** Insert a brand-new task from a broadcast event. */
  function addTaskFromEvent(task: Task) {
    const exists = tasks.value.some(t => t.id === task.id);
    if (!exists) {
      tasks.value.unshift(task);
    }
  }

  /** Update section/position after a move event. */
  function patchTaskMove(incoming: Partial<Task> & { id: string }) {
    patchTask(incoming);
  }

  return {
    // State
    tasks,
    selectedTaskId,
    selectedTask,
    filters,
    sort,
    groupBy,
    columns,
    loading,
    error,

    // Computed
    visibleColumns,
    groupedTasks,

    // Actions
    fetchTasks,
    fetchTask,
    createTask,
    updateTask,
    deleteTask,
    completeTask,
    reopenTask,
    duplicateTask,
    setFilters,
    clearFilters,
    setSort,
    addSort,
    removeSort,
    setGroupBy,
    toggleColumn,
    setColumnWidth,
    selectTask,
    deselectTask,

    // Real-time
    patchTask,
    addTaskFromEvent,
    patchTaskMove,
  };
});
