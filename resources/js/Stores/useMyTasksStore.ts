import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { api } from '@/Services/api';
import type { Task, TaskFilter, TaskSort, TaskGroupBy } from '@/Types/tasks';

export const useMyTasksStore = defineStore('myTasks', () => {
  // State
  const tasks = ref<Task[]>([]);
  const selectedTaskId = ref<string | null>(null);
  const selectedTask = ref<Task | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);

  // Filters and sorting
  const filters = ref<TaskFilter>({});
  const sort = ref<TaskSort[]>([]);
  const groupBy = ref<TaskGroupBy>('');

  // Pagination
  const currentPage = ref(1);
  const totalPages = ref(1);
  const totalTasks = ref(0);
  const perPage = ref(50);

  // Computed
  const groupedTasks = computed(() => {
    if (!groupBy.value || !Array.isArray(tasks.value)) {
      return {};
    }

    const grouped: Record<string, Task[]> = {};
    
    tasks.value.forEach(task => {
      let groupKey: string;
      
      switch (groupBy.value) {
        case 'project':
          groupKey = task.project?.name || 'No Project';
          break;
        case 'status':
          groupKey = formatStatus(task.status);
          break;
        case 'priority':
          groupKey = task.priority ? task.priority.charAt(0).toUpperCase() + task.priority.slice(1) : 'No Priority';
          break;
        case 'due_date':
          groupKey = getDueDateGroup(task.due_date);
          break;
        default:
          groupKey = 'All Tasks';
      }

      if (!grouped[groupKey]) {
        grouped[groupKey] = [];
      }
      grouped[groupKey].push(task);
    });

    return grouped;
  });

  // Actions
  async function fetchTasks() {
    loading.value = true;
    error.value = null;

    try {
      const params = new URLSearchParams({
        page: currentPage.value.toString(),
        per_page: perPage.value.toString(),
      });

      if (Object.keys(filters.value).length > 0) {
        params.append('filters', JSON.stringify(Object.values(filters.value)));
      }

      if (sort.value.length > 0) {
        params.append('sort', JSON.stringify(sort.value));
      }

      if (groupBy.value) {
        params.append('grouping', groupBy.value);
      }

      const response = await fetch(`/my-tasks/api/tasks?${params.toString()}`, {
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const data = await response.json();
      
      if (groupBy.value && typeof data.tasks === 'object' && !Array.isArray(data.tasks)) {
        // Tasks are grouped
        tasks.value = data.tasks;
      } else {
        // Tasks are flat array
        tasks.value = data.tasks || [];
      }
      
      totalTasks.value = data.total || 0;
      totalPages.value = data.pages || 1;
      currentPage.value = data.current_page || 1;
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch tasks';
      console.error('Error fetching my tasks:', err);
    } finally {
      loading.value = false;
    }
  }

  async function fetchTask(taskId: string) {
    try {
      const response = await fetch(`/api/tasks/${taskId}`, {
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const data = await response.json();
      selectedTask.value = data.data;
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch task';
      console.error('Error fetching task:', err);
    }
  }

  async function completeTask(taskId: string) {
    try {
      const task = tasks.value.find(t => t.id === taskId);
      if (!task) return;

      const newStatus = task.status === 'complete' ? 'to_do' : 'complete';
      
      // Optimistic update
      task.status = newStatus;
      if (newStatus === 'complete') {
        task.completed_at = new Date().toISOString();
      } else {
        task.completed_at = null;
      }

      const response = await fetch(`/api/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({
          status: newStatus,
        }),
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      // Update with server response
      Object.assign(task, data.data);
    } catch (err: any) {
      error.value = err.message || 'Failed to update task';
      console.error('Error completing task:', err);
      // Revert optimistic update
      await fetchTasks();
    }
  }

  async function deleteTask(taskId: string) {
    try {
      const response = await fetch(`/api/tasks/${taskId}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      // Remove from local state
      const index = tasks.value.findIndex(t => t.id === taskId);
      if (index > -1) {
        tasks.value.splice(index, 1);
      }

      // Close sidebar if this task was selected
      if (selectedTaskId.value === taskId) {
        deselectTask();
      }

      totalTasks.value = Math.max(0, totalTasks.value - 1);
    } catch (err: any) {
      error.value = err.message || 'Failed to delete task';
      console.error('Error deleting task:', err);
    }
  }

  function selectTask(taskId: string) {
    selectedTaskId.value = taskId;
  }

  function deselectTask() {
    selectedTaskId.value = null;
    selectedTask.value = null;
  }

  function setFilters(newFilters: TaskFilter) {
    filters.value = newFilters;
    currentPage.value = 1; // Reset to first page when filtering
  }

  function setSort(newSort: TaskSort[]) {
    sort.value = newSort;
    currentPage.value = 1; // Reset to first page when sorting
  }

  function setGroupBy(newGroupBy: TaskGroupBy) {
    groupBy.value = newGroupBy;
  }

  function setPage(page: number) {
    currentPage.value = page;
  }

  // Helper functions
  function formatStatus(status: string): string {
    const statusMap: Record<string, string> = {
      'to_do': 'To Do',
      'in_progress': 'In Progress',
      'blocked': 'Blocked',
      'in_review': 'In Review',
      'complete': 'Complete',
    };
    return statusMap[status] || status;
  }

  function getDueDateGroup(dueDate: string | null): string {
    if (!dueDate) return 'No Due Date';

    const today = new Date();
    const taskDate = new Date(dueDate);
    
    // Reset time to compare dates only
    today.setHours(0, 0, 0, 0);
    taskDate.setHours(0, 0, 0, 0);

    if (taskDate < today) {
      return 'Overdue';
    }

    if (taskDate.getTime() === today.getTime()) {
      return 'Today';
    }

    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);
    
    if (taskDate.getTime() === tomorrow.getTime()) {
      return 'Tomorrow';
    }

    const endOfWeek = new Date(today);
    endOfWeek.setDate(today.getDate() + (7 - today.getDay()));
    
    if (taskDate <= endOfWeek) {
      return 'This Week';
    }

    const endOfNextWeek = new Date(endOfWeek);
    endOfNextWeek.setDate(endOfWeek.getDate() + 7);
    
    if (taskDate <= endOfNextWeek) {
      return 'Next Week';
    }

    return 'Later';
  }

  return {
    // State
    tasks,
    selectedTaskId,
    selectedTask,
    loading,
    error,
    filters,
    sort,
    groupBy,
    currentPage,
    totalPages,
    totalTasks,
    perPage,
    
    // Computed
    groupedTasks,
    
    // Actions
    fetchTasks,
    fetchTask,
    completeTask,
    deleteTask,
    selectTask,
    deselectTask,
    setFilters,
    setSort,
    setGroupBy,
    setPage,
  };
});