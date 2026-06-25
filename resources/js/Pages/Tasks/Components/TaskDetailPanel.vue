<template>
  <div class="detail-panel-overlay" @click="closePanel">
    <div class="detail-panel" @click.stop>
      <div class="panel-header">
        <h2>Task Details</h2>
        <button @click="closePanel" class="btn-close">
          <XIcon class="icon" />
        </button>
      </div>

      <div class="panel-content">
        <!-- Task Name -->
        <div class="field-group">
          <label>Task Name</label>
          <input
            v-model="editedTask.name"
            type="text"
            class="input-field"
            @blur="saveField('name')"
          />
        </div>

        <!-- Description -->
        <div class="field-group">
          <label>Description</label>
          <textarea
            v-model="editedTask.description"
            class="textarea-field"
            rows="4"
            @blur="saveField('description')"
          ></textarea>
        </div>

        <!-- Status -->
        <div class="field-group">
          <label>Status</label>
          <select
            v-model="editedTask.status"
            class="select-field"
            @change="saveField('status')"
          >
            <option value="to_do">To Do</option>
            <option value="in_progress">In Progress</option>
            <option value="blocked">Blocked</option>
            <option value="in_review">In Review</option>
            <option value="complete">Complete</option>
          </select>
        </div>

        <!-- Priority -->
        <div class="field-group">
          <label>Priority</label>
          <select
            v-model="editedTask.priority"
            class="select-field"
            @change="saveField('priority')"
          >
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="urgent">Urgent</option>
          </select>
        </div>

        <!-- Assignee -->
        <div class="field-group">
          <label>Assignee</label>
          <div class="assignee-display">
            <img
              v-if="editedTask.assignee"
              :src="editedTask.assignee.avatar"
              :alt="editedTask.assignee.name"
              class="avatar"
            />
            <span>{{ editedTask.assignee?.name || 'Unassigned' }}</span>
          </div>
        </div>

        <!-- Due Date -->
        <div class="field-group">
          <label>Due Date</label>
          <input
            v-model="editedTask.due_date"
            type="date"
            class="input-field"
            @change="saveField('due_date')"
          />
        </div>

        <!-- Section -->
        <div class="field-group">
          <label>Section</label>
          <select
            v-model="editedTask.section_id"
            class="select-field"
            @change="saveField('section_id')"
          >
            <option :value="null">No Section</option>
            <option v-for="section in sections" :key="section.id" :value="section.id">
              {{ section.name }}
            </option>
          </select>
        </div>

        <!-- Tags -->
        <div class="field-group">
          <label>Tags</label>
          <div class="tags-display">
            <span v-for="tag in editedTask.tags" :key="tag.id" class="tag" :style="{ backgroundColor: tag.color }">
              {{ tag.name }}
            </span>
          </div>
        </div>

        <!-- Activity Log -->
        <div class="field-group">
          <label>Activity</label>
          <div class="activity-log">
            <div v-if="activities.length === 0" class="empty-activity">
              No activity yet
            </div>
            <div v-for="activity in activities" :key="activity.id" class="activity-item">
              <div class="activity-header">
                <span class="activity-user">{{ activity.user?.name }}</span>
                <span class="activity-time">{{ formatTime(activity.created_at) }}</span>
              </div>
              <div class="activity-description">{{ activity.description }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

// Inline X icon   no external dependency needed
const XIcon = { template: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' }

const props = defineProps({
  task: Object,
  sections: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'update'])

const editedTask = ref({ ...props.task })
const activities = ref([])

watch(() => props.task, (newTask) => {
  editedTask.value = { ...newTask }
  loadActivities()
}, { deep: true })

const closePanel = () => {
  emit('close')
}

const saveField = (field) => {
  const changes = {}
  changes[field] = editedTask.value[field]
  emit('update', changes)
}

const loadActivities = async () => {
  try {
    const response = await fetch(`/api/tasks/${props.task.id}/activities`)
    if (response.ok) {
      const data = await response.json()
      activities.value = data.data || []
    }
  } catch (error) {
    console.error('Error loading activities:', error)
  }
}

const formatTime = (timestamp) => {
  const date = new Date(timestamp)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

loadActivities()
</script>

<style scoped>
.detail-panel-overlay {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  background: rgba(0, 0, 0, 0.3);
  display: flex;
  justify-content: flex-end;
  z-index: 1000;
}

.detail-panel {
  width: 400px;
  background: white;
  box-shadow: -2px 0 8px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
  }
  to {
    transform: translateX(0);
  }
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e0e0e0;
}

.panel-header h2 {
  margin: 0;
  font-size: 18px;
}

.btn-close {
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

.btn-close:hover {
  background: #f0f0f0;
}

.icon {
  width: 18px;
  height: 18px;
}

.panel-content {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.field-group label {
  font-size: 12px;
  font-weight: 600;
  color: #666;
  text-transform: uppercase;
}

.input-field,
.textarea-field,
.select-field {
  padding: 10px;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  font-size: 14px;
  font-family: inherit;
}

.input-field:focus,
.textarea-field:focus,
.select-field:focus {
  outline: none;
  border-color: #2e7d32;
  box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
}

.textarea-field {
  resize: vertical;
}

.assignee-display {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  background: #f5f5f5;
  border-radius: 6px;
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
}

.tags-display {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.tag {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  color: white;
}

.activity-log {
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-height: 300px;
  overflow-y: auto;
}

.empty-activity {
  text-align: center;
  color: #999;
  padding: 20px;
  font-size: 13px;
}

.activity-item {
  padding: 10px;
  background: #f9f9f9;
  border-radius: 6px;
  border-left: 3px solid #2e7d32;
}

.activity-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 6px;
}

.activity-user {
  font-weight: 600;
  font-size: 13px;
}

.activity-time {
  font-size: 11px;
  color: #999;
}

.activity-description {
  font-size: 12px;
  color: #666;
  line-height: 1.4;
}
</style>

