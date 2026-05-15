<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal" @click.stop>
      <div class="modal-header">
        <h2>{{ task ? 'Edit Task' : 'Create New Task' }}</h2>
        <button @click="closeModal" class="btn-close">
          <XIcon class="icon" />
        </button>
      </div>

      <form @submit.prevent="submitForm" class="modal-content">
        <!-- Task Name -->
        <div class="form-group">
          <label for="name">Task Name *</label>
          <input
            id="name"
            v-model="formData.name"
            type="text"
            class="input-field"
            placeholder="Enter task name"
            required
          />
        </div>

        <!-- Description -->
        <div class="form-group">
          <label for="description">Description</label>
          <textarea
            id="description"
            v-model="formData.description"
            class="textarea-field"
            placeholder="Add task description"
            rows="4"
          ></textarea>
        </div>

        <!-- Status -->
        <div class="form-row">
          <div class="form-group">
            <label for="status">Status</label>
            <select id="status" v-model="formData.status" class="select-field">
              <option value="to_do">To Do</option>
              <option value="in_progress">In Progress</option>
              <option value="blocked">Blocked</option>
              <option value="in_review">In Review</option>
              <option value="complete">Complete</option>
            </select>
          </div>

          <div class="form-group">
            <label for="priority">Priority</label>
            <select id="priority" v-model="formData.priority" class="select-field">
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>
        </div>

        <!-- Due Date -->
        <div class="form-row">
          <div class="form-group">
            <label for="start_date">Start Date</label>
            <input
              id="start_date"
              v-model="formData.start_date"
              type="date"
              class="input-field"
            />
          </div>

          <div class="form-group">
            <label for="due_date">Due Date</label>
            <input
              id="due_date"
              v-model="formData.due_date"
              type="date"
              class="input-field"
            />
          </div>
        </div>

        <!-- Section -->
        <div class="form-group">
          <label for="section">Section</label>
          <select id="section" v-model="formData.section_id" class="select-field">
            <option :value="null">No Section</option>
            <option v-for="section in sections" :key="section.id" :value="section.id">
              {{ section.name }}
            </option>
          </select>
        </div>

        <!-- Visibility -->
        <div class="form-group">
          <label for="visibility">Visibility</label>
          <select id="visibility" v-model="formData.visibility" class="select-field">
            <option value="everyone">Everyone</option>
            <option value="private">Private</option>
          </select>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
          <button type="button" @click="closeModal" class="btn-cancel">
            Cancel
          </button>
          <button type="submit" class="btn-submit">
            {{ task ? 'Update Task' : 'Create Task' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const XIcon = { template: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' }

const props = defineProps({
  task: Object,
  sections: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['save', 'close'])

const formData = ref({
  name: '',
  description: '',
  status: 'to_do',
  priority: 'medium',
  start_date: '',
  due_date: '',
  section_id: null,
  visibility: 'everyone'
})

watch(() => props.task, (newTask) => {
  if (newTask) {
    formData.value = {
      name: newTask.name || '',
      description: newTask.description || '',
      status: newTask.status || 'to_do',
      priority: newTask.priority || 'medium',
      start_date: newTask.start_date || '',
      due_date: newTask.due_date || '',
      section_id: newTask.section_id || null,
      visibility: newTask.visibility || 'everyone'
    }
  }
}, { immediate: true })

const closeModal = () => {
  emit('close')
}

const submitForm = () => {
  emit('save', formData.value)
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1001;
}

.modal {
  background: white;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e0e0e0;
}

.modal-header h2 {
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

.modal-content {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #333;
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

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  padding-top: 12px;
  border-top: 1px solid #e0e0e0;
  margin-top: 12px;
}

.btn-cancel,
.btn-submit {
  padding: 10px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-cancel {
  background: #f0f0f0;
  color: #333;
}

.btn-cancel:hover {
  background: #e0e0e0;
}

.btn-submit {
  background: #2e7d32;
  color: white;
}

.btn-submit:hover {
  background: #1b5e20;
}
</style>
