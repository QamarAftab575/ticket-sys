<template>
  <div class="space-y-4">
    <div class="text-center">
      <div
        class="inline-block px-4 py-2 rounded-full text-lg font-bold mb-2"
        :class="getStatusColor(project.status)"
      >
        {{ formatStatus(project.status) }}
      </div>
      <p class="text-sm text-gray-600">Last updated: {{ formatDate(project.updated_at) }}</p>
    </div>

    <div v-if="isEditing" class="space-y-3">
      <textarea
        v-model="editDescription"
        class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        rows="3"
        placeholder="Add status description..."
      />
      <div class="flex gap-2">
        <button
          @click="saveDescription"
          class="flex-1 px-3 py-2 bg-blue-600 text-white rounded text-sm font-medium hover:bg-blue-700 transition-colors"
        >
          Save
        </button>
        <button
          @click="cancelEdit"
          class="flex-1 px-3 py-2 border border-gray-300 text-gray-700 rounded text-sm font-medium hover:bg-gray-50 transition-colors"
        >
          Cancel
        </button>
      </div>
    </div>

    <div v-else class="space-y-3">
      <p v-if="project.description" class="text-sm text-gray-700">{{ project.description }}</p>
      <p v-else class="text-sm text-gray-500 italic">No description yet</p>
      <button
        @click="startEdit"
        class="w-full px-3 py-2 border border-gray-300 text-gray-700 rounded text-sm font-medium hover:bg-gray-50 transition-colors"
      >
        Edit status
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useToast } from '@/Composables/useToast'

const props = defineProps({
  project: Object,
})

const { success: showSuccess, error: showError } = useToast()

const isEditing = ref(false)
const editDescription = ref('')

const getStatusColor = (status) => {
  const colors = {
    on_track: 'bg-green-100 text-green-800',
    at_risk: 'bg-orange-100 text-orange-800',
    off_track: 'bg-red-100 text-red-800',
    on_hold: 'bg-gray-100 text-gray-800',
    complete: 'bg-blue-100 text-blue-800',
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatStatus = (status) => {
  const labels = {
    on_track: 'On Track',
    at_risk: 'At Risk',
    off_track: 'Off Track',
    on_hold: 'On Hold',
    complete: 'Complete',
  }
  return labels[status] || status
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

const startEdit = () => {
  editDescription.value = props.project.description || ''
  isEditing.value = true
}

const cancelEdit = () => {
  isEditing.value = false
  editDescription.value = ''
}

const saveDescription = async () => {
  try {
    const response = await fetch(`/projects/${props.project.id}`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      },
      body: JSON.stringify({
        description: editDescription.value,
      }),
    })

    if (!response.ok) {
      throw new Error('Failed to update project')
    }

    showSuccess('Project description updated')
    isEditing.value = false
  } catch (err) {
    showError('Failed to update project description')
    console.error('Error:', err)
  }
}
</script>
