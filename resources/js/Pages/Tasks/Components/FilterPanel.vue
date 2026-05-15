<template>
  <div class="filter-panel">
    <div class="filter-section">
      <h3>Status</h3>
      <div class="filter-options">
        <label v-for="status in statuses" :key="status.value" class="filter-checkbox">
          <input
            type="checkbox"
            :value="status.value"
            :checked="filters.status.includes(status.value)"
            @change="toggleFilter('status', status.value)"
          />
          <span>{{ status.label }}</span>
        </label>
      </div>
    </div>

    <div class="filter-section">
      <h3>Priority</h3>
      <div class="filter-options">
        <label v-for="priority in priorities" :key="priority.value" class="filter-checkbox">
          <input
            type="checkbox"
            :value="priority.value"
            :checked="filters.priority.includes(priority.value)"
            @change="toggleFilter('priority', priority.value)"
          />
          <span>{{ priority.label }}</span>
        </label>
      </div>
    </div>

    <div class="filter-section">
      <h3>Due Date</h3>
      <div class="filter-options">
        <label v-for="option in dueDateOptions" :key="option.value" class="filter-checkbox">
          <input
            type="radio"
            name="due_date"
            :value="option.value"
            :checked="filters.due_date === option.value"
            @change="filters.due_date = option.value; emit('update', filters)"
          />
          <span>{{ option.label }}</span>
        </label>
      </div>
    </div>

    <div class="filter-actions">
      <button @click="clearFilters" class="btn-clear">Clear All</button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  filters: Object
})

const emit = defineEmits(['update'])

const statuses = [
  { value: 'to_do', label: 'To Do' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'blocked', label: 'Blocked' },
  { value: 'in_review', label: 'In Review' },
  { value: 'complete', label: 'Complete' }
]

const priorities = [
  { value: 'low', label: 'Low' },
  { value: 'medium', label: 'Medium' },
  { value: 'high', label: 'High' },
  { value: 'urgent', label: 'Urgent' }
]

const dueDateOptions = [
  { value: null, label: 'All' },
  { value: 'overdue', label: 'Overdue' },
  { value: 'today', label: 'Today' },
  { value: 'this_week', label: 'This Week' },
  { value: 'this_month', label: 'This Month' }
]

const filters = ref({ ...props.filters })

watch(() => props.filters, (newFilters) => {
  filters.value = { ...newFilters }
}, { deep: true })

const toggleFilter = (filterType, value) => {
  const index = filters.value[filterType].indexOf(value)
  if (index > -1) {
    filters.value[filterType].splice(index, 1)
  } else {
    filters.value[filterType].push(value)
  }
  emit('update', filters.value)
}

const clearFilters = () => {
  filters.value = {
    assignee: [],
    status: [],
    priority: [],
    due_date: null,
    tags: []
  }
  emit('update', filters.value)
}
</script>

<style scoped>
.filter-panel {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.filter-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.filter-section h3 {
  margin: 0;
  font-size: 13px;
  font-weight: 600;
  color: #333;
  text-transform: uppercase;
}

.filter-options {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.filter-checkbox {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 13px;
}

.filter-checkbox input {
  cursor: pointer;
  width: 16px;
  height: 16px;
}

.filter-checkbox span {
  user-select: none;
}

.filter-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 12px;
  border-top: 1px solid #e0e0e0;
  grid-column: 1 / -1;
}

.btn-clear {
  padding: 8px 12px;
  border: 1px solid #e0e0e0;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  transition: all 0.2s;
}

.btn-clear:hover {
  border-color: #2e7d32;
  color: #2e7d32;
}
</style>
