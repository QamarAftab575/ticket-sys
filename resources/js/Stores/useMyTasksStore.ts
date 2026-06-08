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

  // Section ordering (for list view virtual sections)
  const sectionOrder = ref<string[]>([]);
  const collapsedSections = ref<Set<string>>(new Set());

  // My Tasks real sections (from DB)
  interface MyTasksSection { id: string; name: string; position: number }
  const sections = ref<MyTasksSection[]>([]);

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
      
      console.log('Fetched tasks data:', data);
      
      if (groupBy.value && typeof data.tasks === 'object' && !Array.isArray(data.tasks)) {
        // Tasks are grouped
        tasks.value = data.tasks;
      } else {
        // Tasks are flat array
        tasks.value = data.tasks || [];
      }
      
      console.log('Tasks after assignment:', tasks.value);
      
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

  // ── Real-time helpers (called by Echo listeners) ──────────────────────────

  /** Merge incoming task data into the flat tasks array. */
  function patchTask(incoming: Partial<Task> & { id: string }) {
    const idx = tasks.value.findIndex((t: Task) => t.id === incoming.id);
    if (idx !== -1) {
      tasks.value[idx] = { ...tasks.value[idx], ...incoming };
    }
    if (selectedTask.value?.id === incoming.id) {
      Object.assign(selectedTask.value, incoming);
    }
  }

  /** Insert a brand-new task assigned to the current user. */
  function addTaskFromEvent(task: Task) {
    const exists = tasks.value.some((t: Task) => t.id === task.id);
    if (!exists) {
      tasks.value.push(task);
      totalTasks.value += 1;
    }
  }

  /** Update section/position after a move event. */
  function patchTaskMove(incoming: Partial<Task> & { id: string }) {
    patchTask(incoming);
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

  function setSectionOrder(order: string[]) {
    sectionOrder.value = order;
  }

  function toggleCollapseSection(sectionId: string) {
    if (collapsedSections.value.has(sectionId)) {
      collapsedSections.value.delete(sectionId);
    } else {
      collapsedSections.value.add(sectionId);
    }
  }

  function setSections(newSections: Array<{ id: string; name: string; position: number }>) {
    sections.value = newSections;
    // If no saved section_order, default to the sections' natural order
    if (sectionOrder.value.length === 0) {
      sectionOrder.value = newSections.map(s => s.id);
    }
  }

  async function createSection(name: string): Promise<void> {
    const response = await fetch('/my-tasks/api/sections', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({ name }),
    });
    if (!response.ok) throw new Error('Failed to create section');
    const data = await response.json();
    sections.value.push(data.data);
    sectionOrder.value.push(data.data.id);
  }

  async function renameSection(sectionId: string, name: string): Promise<void> {
    const response = await fetch(`/my-tasks/api/sections/${sectionId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({ name }),
    });
    if (!response.ok) throw new Error('Failed to rename section');
    const data = await response.json();
    const idx = sections.value.findIndex(s => s.id === sectionId);
    if (idx !== -1) sections.value[idx] = data.data;
  }

  async function deleteSection(sectionId: string): Promise<void> {
    const response = await fetch(`/my-tasks/api/sections/${sectionId}`, {
      method: 'DELETE',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    });
    if (!response.ok) throw new Error('Failed to delete section');
    sections.value = sections.value.filter(s => s.id !== sectionId);
    sectionOrder.value = sectionOrder.value.filter(id => id !== sectionId);
    await fetchTasks();
  }

  async function reorderSectionsRemote(orderedIds: string[]): Promise<void> {
    sectionOrder.value = orderedIds;
    // Persist to backend
    await fetch('/my-tasks/api/sections/reorder', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({ section_ids: orderedIds }),
    });
    savePreferences();
  }

  /** Persist current sort/grouping/section_order/collapsed_sections to the backend */
  const savePreferencesDebounced = debounce(async () => {
    try {
      const params: Record<string, string> = {
        view_type: 'list',
      };

      if (sort.value.length > 0) {
        params.sort = JSON.stringify(sort.value);
      }
      if (groupBy.value) {
        params.grouping = groupBy.value;
      }
      if (sectionOrder.value.length > 0) {
        params.section_order = JSON.stringify(sectionOrder.value);
      }
      if (collapsedSections.value.size > 0) {
        params.collapsed_sections = JSON.stringify([...collapsedSections.value]);
      }
      if (Object.keys(filters.value).length > 0) {
        params.filters = JSON.stringify(Object.values(filters.value));
      }

      await fetch('/my-tasks/api/preferences', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify(params),
      });
    } catch (err) {
      console.error('Failed to save my-tasks preferences:', err);
    }
  }, 500);

  function savePreferences() {
    savePreferencesDebounced();
  }

  // Debounce helper
  function debounce<T extends (...args: any[]) => any>(func: T, wait: number): (...args: Parameters<T>) => void {
    let timeout: ReturnType<typeof setTimeout> | null = null;
    return function(...args: Parameters<T>) {
      if (timeout) clearTimeout(timeout);
      timeout = setTimeout(() => func(...args), wait);
    };
  }

  /** Hydrate store from saved preferences (called on mount with Inertia page props) */
  function loadPreferences(prefs: {
    sort?: TaskSort[] | null;
    grouping?: string | null;
    section_order?: string[] | null;
    collapsed_sections?: string[] | null;
    filters?: any | null;
  }) {
    if (prefs.sort?.length) {
      sort.value = prefs.sort;
    }
    if (prefs.grouping) {
      groupBy.value = prefs.grouping as TaskGroupBy;
    }
    if (prefs.section_order?.length) {
      sectionOrder.value = prefs.section_order;
    }
    if (prefs.collapsed_sections?.length) {
      collapsedSections.value = new Set(prefs.collapsed_sections);
    }
    if (prefs.filters && Object.keys(prefs.filters).length > 0) {
      filters.value = prefs.filters;
    }
  }
  async function updateTask(taskId: string, updates: any) {
    try {
      const response = await fetch(`/api/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify(updates),
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      
      // Update the task in local state
      const taskIndex = tasks.value.findIndex(t => t.id === taskId);
      if (taskIndex > -1) {
        tasks.value[taskIndex] = { ...tasks.value[taskIndex], ...data.data };
      }

      // Update selected task if it's the one being updated
      if (selectedTaskId.value === taskId) {
        selectedTask.value = { ...selectedTask.value, ...data.data };
      }

      return data.data;
    } catch (err: any) {
      error.value = err.message || 'Failed to update task';
      console.error('Error updating task:', err);
      throw err;
    }
  }

  async function moveTask(taskId: string, toSectionId: string, position: number = 0) {
    const taskIndex = tasks.value.findIndex(t => t.id === taskId);
    if (taskIndex === -1) return;

    const task = tasks.value[taskIndex];
    const originalMyTasksSectionId = task.my_tasks_section_id;
    const originalMyTasksPosition = task.my_tasks_position;
    const originalTasksState = JSON.parse(JSON.stringify(tasks.value));

    try {
      // Optimistic update: remove task from current position
      tasks.value.splice(taskIndex, 1);

      // Update task properties (My Tasks specific)
      task.my_tasks_section_id = toSectionId;
      task.my_tasks_position = position;

      // Find insertion point in target section
      const targetSectionTasks = tasks.value.filter(t => t.my_tasks_section_id === toSectionId);
      const insertIndex = targetSectionTasks.findIndex(t => (t.my_tasks_position ?? 0) >= position);
      
      if (insertIndex === -1) {
        // Append to end of section
        const lastTaskIndex = tasks.value.findIndex(t => t.my_tasks_section_id === toSectionId && t === targetSectionTasks[targetSectionTasks.length - 1]);
        tasks.value.splice(lastTaskIndex + 1, 0, task);
      } else {
        // Insert before the found task
        const targetTask = targetSectionTasks[insertIndex];
        const targetIndex = tasks.value.findIndex(t => t === targetTask);
        tasks.value.splice(targetIndex, 0, task);
      }

      // Update positions of tasks in target section
      tasks.value
        .filter(t => t.my_tasks_section_id === toSectionId)
        .sort((a, b) => (a.my_tasks_position ?? 0) - (b.my_tasks_position ?? 0))
        .forEach((t, idx) => {
          t.my_tasks_position = idx;
        });

      // Make API call with My Tasks specific fields
      const response = await fetch(`/api/tasks/${taskId}/move`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({ 
          my_tasks_section_id: toSectionId, 
          my_tasks_position: position 
        }),
      });

      if (!response.ok) {
        throw new Error('Failed to move task');
      }

      const result = await response.json();
      if (result.data) {
        // Update task with server response
        Object.assign(task, result.data);
      }
    } catch (err: any) {
      // Rollback to original state
      tasks.value = originalTasksState;
      error.value = err.message || 'Failed to move task';
      console.error('Error moving task:', err);
      throw err;
    }
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

  async function createTask(data: { name: string; section_id?: string | null; [key: string]: any }) {
    try {
      const response = await fetch('/my-tasks/api/tasks', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify(data),
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const result = await response.json();
      
      // Add new task to local state optimistically
      if (result.data) {
        tasks.value.push(result.data);
        totalTasks.value += 1;
      }

      return result.data;
    } catch (err: any) {
      error.value = err.message || 'Failed to create task';
      console.error('Error creating task:', err);
      throw err;
    }
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
    sectionOrder,
    collapsedSections,
    sections,
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
    updateTask,
    moveTask,
    createTask,
    selectTask,
    deselectTask,
    setFilters,
    setSort,
    setGroupBy,
    setPage,
    setSectionOrder,
    toggleCollapseSection,
    setSections,
    createSection,
    renameSection,
    deleteSection,
    reorderSectionsRemote,
    savePreferences,
    loadPreferences,

    // Real-time
    patchTask,
    addTaskFromEvent,
    patchTaskMove,
  };
});