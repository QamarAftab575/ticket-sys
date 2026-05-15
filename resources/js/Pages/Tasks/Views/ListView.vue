<template>
  <div class="list-view">
    <div v-if="groupedTasks.length === 0" class="empty-state">
      <p>No tasks yet. Create one to get started!</p>
    </div>

    <div v-for="group in groupedTasks" :key="group.id" class="task-group">
      <div class="group-header" @click="toggleGroup(group.id)">
        <ChevronDownIcon v-if="!collapsedGroups.includes(group.id)" class="chevron" />
        <ChevronRightIcon v-else class="chevron" />
        <span class="group-title">{{ group.header }}</span>
        <span class="group-count">{{ group.tasks.length }}</span>
      </div>

      <div v-if="!collapsedGroups.includes(group.id)" class="tasks-list">
        <div
          v-for="task in group.tasks"
          :key="task.id"
          class="task-row"
          @click="selectTask(task)"
          :class="{ selected: selectedTaskId === task.id }"
        >
          <div class="task-checkbox">
            <input
              type="checkbox"
              :checked="task.status === 'complete'"
              @change="toggleTaskComplete(task)"
              @click.stop
            />
          </div>

          <div class="task-name">
            <span :class="['task-title', { completed: task.status === 'complete' }]">
              {{ task.name }}
            </span>
          </div>

          <div class="task-assignee">
            <img
              v-if="task.assignee"
              :src="task.assignee.avatar"
              :alt="task.assignee.name"
              :title="task.assignee.name"
              class="avatar"
            />
            <span v-else class="unassigned">Unassigned</span>
          </div>

          <div class="task-due-date" @click.stop>
            <DateRangePicker
              :start-date="task.start_date ?? null"
              :end-date="task.due_date ?? null"
              :completed="task.status === 'complete'"
              @update:start-date="val => updateTaskDates(task, { start_date: val })"
              @update:end-date="val => updateTaskDates(task, { due_date: val })"
            />
          </div>

          <div class="task-priority">
            <span :class="['priority-badge', `priority-${task.priority}`]">
              {{ task.priority }}
            </span>
          </div>

          <div class="task-status">
            <select
              :value="task.status"
              @change="updateTaskStatus(task, $event)"
              @click.stop
              class="status-select"
            >
              <option value="to_do">To Do</option>
              <option value="in_progress">In Progress</option>
              <option value="blocked">Blocked</option>
              <option value="in_review">In Review</option>
              <option value="complete">Complete</option>
            </select>
          </div>

          <div class="task-tags">
            <span v-for="tag in task.tags" :key="tag.id" class="tag" :style="{ backgroundColor: tag.color }">
              {{ tag.name }}
            </span>
          </div>

          <div class="task-actions">
            <button @click.stop="$emit('edit-task', task)" class="action-btn" title="Edit">
              <EditIcon />
            </button>
            <button @click.stop="$emit('delete-task', task.id)" class="action-btn delete" title="Delete">
              <TrashIcon />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Task Detail Panel -->
    <TaskDetailPanel
      v-if="selectedTask"
      :task="selectedTask"
      @close="selectedTaskId = null"
      @update="updateTask"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// Inline icon replacements — lucide-vue-next not installed
const ChevronDownIcon  = { template: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>' }
const ChevronRightIcon = { template: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>' }
const EditIcon         = { template: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>' }
const TrashIcon        = { template: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>' }
import TaskDetailPanel from '../Components/TaskDetailPanel.vue'
import DateRangePicker from '@/Components/Tasks/Shared/DateRangePicker.vue'

const props = defineProps({
  tasks: Array,
  groupBy: String,
  sections: Array
})

const emit = defineEmits(['create-task', 'edit-task', 'delete-task'])

const selectedTaskId = ref(null)
const collapsedGroups = ref([])

const selectedTask = computed(() => {
  return props.tasks.find(t => t.id === selectedTaskId.value)
})

const groupedTasks = computed(() => {
  if (!props.groupBy) {
    return [{
      id: 'all',
      header: 'All Tasks',
      tasks: props.tasks
    }]
  }

  const groups = {}

  props.tasks.forEach(task => {
    let groupKey, groupHeader

    switch (props.groupBy) {
      case 'assignee':
        groupKey = task.assignee_id || 'unassigned'
        groupHeader = task.assignee?.name || 'Unassigned'
        break
      case 'status':
        groupKey = task.status
        groupHeader = formatStatus(task.status)
        break
      case 'priority':
        groupKey = task.priority
        groupHeader = task.priority.charAt(0).toUpperCase() + task.priority.slice(1)
        break
      case 'due_date':
        groupKey = getDueDateGroup(task.due_date)
        groupHeader = getDueDateGroupLabel(groupKey)
        break
      case 'section':
        groupKey = task.section_id || 'no_section'
        groupHeader = task.section?.name || 'No Section'
        break
      default:
        groupKey = 'all'
        groupHeader = 'All Tasks'
    }

    if (!groups[groupKey]) {
      groups[groupKey] = {
        id: groupKey,
        header: groupHeader,
        tasks: []
      }
    }

    groups[groupKey].tasks.push(task)
  })

  return Object.values(groups)
})

const formatDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const formatStatus = (status) => {
  const map = {
    'to_do': 'To Do',
    'in_progress': 'In Progress',
    'blocked': 'Blocked',
    'in_review': 'In Review',
    'complete': 'Complete'
  }
  return map[status] || status
}

const getDueDateClass = (date) => {
  if (!date) return ''
  const today = new Date()
  const dueDate = new Date(date)
  if (dueDate < today) return 'overdue'
  if (dueDate.toDateString() === today.toDateString()) return 'today'
  return ''
}

const getDueDateGroup = (date) => {
  if (!date) return 'no_due_date'
  const today = new Date()
  const dueDate = new Date(date)
  
  if (dueDate < today) return 'overdue'
  if (dueDate.toDateString() === today.toDateString()) return 'today'
  if (dueDate <= new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000)) return 'this_week'
  if (dueDate.getMonth() === today.getMonth()) return 'this_month'
  return 'later'
}

const getDueDateGroupLabel = (group) => {
  const map = {
    'overdue': 'Overdue',
    'today': 'Today',
    'this_week': 'This Week',
    'this_month': 'This Month',
    'later': 'Later',
    'no_due_date': 'No Due Date'
  }
  return map[group] || group
}

const toggleGroup = (groupId) => {
  const index = collapsedGroups.value.indexOf(groupId)
  if (index > -1) {
    collapsedGroups.value.splice(index, 1)
  } else {
    collapsedGroups.value.push(groupId)
  }
}

const selectTask = (task) => {
  selectedTaskId.value = task.id
}

const toggleTaskComplete = (task) => {
  updateTaskStatus(task, { target: { value: task.status === 'complete' ? 'to_do' : 'complete' } })
}

const updateTaskStatus = async (task, event) => {
  const newStatus = event.target.value
  try {
    const response = await fetch(`/api/tasks/${task.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: newStatus })
    })
    const result = await response.json()
    if (result.data) {
      Object.assign(task, result.data)
    }
  } catch (error) {
    console.error('Error updating task:', error)
  }
}

const updateTask = async (updates) => {
  try {
    const response = await fetch(`/api/tasks/${selectedTask.value.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(updates)
    })
    const result = await response.json()
    if (result.data) {
      Object.assign(selectedTask.value, result.data)
    }
  } catch (error) {
    console.error('Error updating task:', error)
  }
}

const updateTaskDates = async (task, updates) => {
  // Optimistic update
  Object.assign(task, updates)
  try {
    const response = await fetch(`/api/tasks/${task.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(updates)
    })
    const result = await response.json()
    if (result.data) {
      Object.assign(task, result.data)
    }
  } catch (error) {
    console.error('Error updating task dates:', error)
    // revert on failure
    Object.assign(task, updates)
  }
}
</script>

<style scoped>
.list-view {
  display: flex;
  gap: 20px;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #999;
}

.task-group {
  flex: 1;
  background: white;
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 20px;
}

.group-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  background: #f9f9f9;
  border-bottom: 1px solid #e0e0e0;
  cursor: pointer;
  font-weight: 600;
  user-select: none;
}

.group-header:hover {
  background: #f0f0f0;
}

.chevron {
  width: 18px;
  height: 18px;
  color: #666;
}

.group-title {
  flex: 1;
}

.group-count {
  background: #e0e0e0;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 12px;
  color: #666;
}

.tasks-list {
  display: flex;
  flex-direction: column;
}

.task-row {
  display: grid;
  grid-template-columns: 40px 1fr 100px 100px 100px 120px 150px 80px;
  gap: 12px;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid #f0f0f0;
  cursor: pointer;
  transition: background 0.2s;
}

.task-row:hover {
  background: #fafafa;
}

.task-row.selected {
  background: #e8f5e9;
}

.task-checkbox {
  display: flex;
  align-items: center;
}

.task-checkbox input {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.task-name {
  display: flex;
  align-items: center;
}

.task-title {
  font-size: 14px;
  color: #333;
}

.task-title.completed {
  text-decoration: line-through;
  color: #999;
}

.task-assignee {
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
}

.unassigned {
  font-size: 12px;
  color: #999;
}

.task-due-date {
  font-size: 13px;
  text-align: center;
}

.due-date {
  padding: 4px 8px;
  border-radius: 4px;
  background: #f0f0f0;
}

.due-date.overdue {
  background: #ffebee;
  color: #c62828;
}

.due-date.today {
  background: #fff3e0;
  color: #e65100;
}

.task-priority {
  text-align: center;
}

.priority-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  text-transform: capitalize;
}

.priority-urgent {
  background: #ffcdd2;
  color: #c62828;
}

.priority-high {
  background: #ffe0b2;
  color: #e65100;
}

.priority-medium {
  background: #fff9c4;
  color: #f57f17;
}

.priority-low {
  background: #e8f5e9;
  color: #2e7d32;
}

.task-status {
  text-align: center;
}

.status-select {
  padding: 4px 8px;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  font-size: 12px;
  background: white;
  cursor: pointer;
}

.task-tags {
  display: flex;
  gap: 4px;
  flex-wrap: wrap;
}

.tag {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 11px;
  color: white;
}

.task-actions {
  display: flex;
  gap: 4px;
  justify-content: flex-end;
}

.action-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  cursor: pointer;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.action-btn:hover {
  background: #f0f0f0;
}

.action-btn.delete:hover {
  background: #ffebee;
  color: #c62828;
}

.action-btn svg {
  width: 16px;
  height: 16px;
}
</style>
