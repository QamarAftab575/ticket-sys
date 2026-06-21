<template>
  <div class="board-view">
    <div class="board-container">
      <div
        v-for="column in columns"
        :key="column.id"
        class="board-column"
        @dragover.prevent
        @drop="dropTask($event, column.id)"
      >
        <div class="column-header">
          <h3 class="column-title">{{ column.title }}</h3>
          <span class="column-count">{{ column.tasks.length }}</span>
        </div>

        <div class="tasks-container">
          <div
            v-for="task in column.tasks"
            :key="task.id"
            class="task-card"
            draggable="true"
            @dragstart="dragStart($event, task)"
            @click="selectTask(task)"
          >
            <div class="card-header">
              <h4 class="card-title">{{ task.name }}</h4>
              <button @click.stop="$emit('delete-task', task.id)" class="btn-delete">Ã—</button>
            </div>

            <p v-if="task.description" class="card-description">{{ task.description }}</p>

            <div class="card-tags">
              <span v-for="tag in task.tags" :key="tag.id" class="tag" :style="{ backgroundColor: tag.color }">
                {{ tag.name }}
              </span>
            </div>

            <div class="card-footer">
              <div class="card-assignee">
                <img
                  v-if="task.assignee"
                  :src="task.assignee.avatar"
                  :alt="task.assignee.name"
                  :title="task.assignee.name"
                  class="avatar"
                />
              </div>
              <div v-if="task.due_date" class="card-due-date" :class="getDueDateClass(task.due_date)">
                {{ formatDate(task.due_date) }}
              </div>
              <div class="card-priority" :class="`priority-${task.priority}`">
                {{ task.priority.charAt(0).toUpperCase() }}
              </div>
            </div>
          </div>

          <button class="btn-add-task" @click="$emit('create-task')">
            + Add task
          </button>
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
import TaskDetailPanel from '../Components/TaskDetailPanel.vue'

const props = defineProps({
  tasks: Array
})

const emit = defineEmits(['create-task', 'edit-task', 'delete-task'])

const selectedTaskId = ref(null)
const draggedTask = ref(null)

const selectedTask = computed(() => {
  return props.tasks.find(t => t.id === selectedTaskId.value)
})

const columns = computed(() => {
  const statuses = ['to_do', 'in_progress', 'blocked', 'in_review', 'complete']
  const statusLabels = {
    'to_do': 'To Do',
    'in_progress': 'In Progress',
    'blocked': 'Blocked',
    'in_review': 'In Review',
    'complete': 'Complete'
  }

  return statuses.map(status => ({
    id: status,
    title: statusLabels[status],
    tasks: props.tasks.filter(t => t.status === status)
  }))
})

const formatDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const getDueDateClass = (date) => {
  if (!date) return ''
  const today = new Date()
  const dueDate = new Date(date)
  if (dueDate < today) return 'overdue'
  if (dueDate.toDateString() === today.toDateString()) return 'today'
  return ''
}

const dragStart = (event, task) => {
  draggedTask.value = task
  event.dataTransfer.effectAllowed = 'move'
}

const dropTask = async (event, columnId) => {
  event.preventDefault()
  if (!draggedTask.value) return

  try {
    const response = await fetch(`/api/tasks/${draggedTask.value.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: columnId })
    })
    const result = await response.json()
    if (result.data) {
      Object.assign(draggedTask.value, result.data)
    }
    draggedTask.value = null
  } catch (error) {
    console.error('Error updating task:', error)
  }
}

const selectTask = (task) => {
  selectedTaskId.value = task.id
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
</script>

<style scoped>
.board-view {
  display: flex;
  gap: 20px;
}

.board-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  flex: 1;
}

.board-column {
  background: #f5f5f5;
  border-radius: 8px;
  padding: 12px;
  min-height: 500px;
  display: flex;
  flex-direction: column;
}

.column-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  padding-bottom: 12px;
  border-bottom: 2px solid #e0e0e0;
}

.column-title {
  font-size: 14px;
  font-weight: 600;
  margin: 0;
}

.column-count {
  background: #e0e0e0;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 12px;
  color: #666;
}

.tasks-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 12px;
  overflow-y: auto;
}

.task-card {
  background: white;
  border-radius: 8px;
  padding: 12px;
  cursor: move;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: box-shadow 0.2s, transform 0.2s;
}

.task-card:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 8px;
}

.card-title {
  font-size: 14px;
  font-weight: 600;
  margin: 0;
  flex: 1;
}

.btn-delete {
  width: 24px;
  height: 24px;
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 18px;
  color: #999;
  border-radius: 4px;
  transition: background 0.2s;
}

.btn-delete:hover {
  background: #ffebee;
  color: #c62828;
}

.card-description {
  font-size: 12px;
  color: #666;
  margin: 8px 0;
  line-height: 1.4;
}

.card-tags {
  display: flex;
  gap: 4px;
  flex-wrap: wrap;
  margin-bottom: 8px;
}

.tag {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 11px;
  color: white;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 8px;
  border-top: 1px solid #f0f0f0;
}

.card-assignee {
  display: flex;
  align-items: center;
}

.avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  object-fit: cover;
}

.card-due-date {
  font-size: 11px;
  padding: 2px 6px;
  border-radius: 4px;
  background: #f0f0f0;
  color: #666;
}

.card-due-date.overdue {
  background: #ffebee;
  color: #c62828;
}

.card-due-date.today {
  background: #fff3e0;
  color: #e65100;
}

.card-priority {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 600;
  color: white;
}

.priority-urgent {
  background: #c62828;
}

.priority-high {
  background: #e65100;
}

.priority-medium {
  background: #f57f17;
}

.priority-low {
  background: #2e7d32;
}

.btn-add-task {
  padding: 12px;
  border: 2px dashed #e0e0e0;
  background: transparent;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  color: #666;
  transition: all 0.2s;
}

.btn-add-task:hover {
  border-color: #2e7d32;
  color: #2e7d32;
  background: #f0f8f0;
}
</style>

