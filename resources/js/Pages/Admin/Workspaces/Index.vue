<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Manage Workspaces</h1>
          <p class="text-gray-600 mt-1">View and manage all platform workspaces</p>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Filters & Search -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by workspace name..."
              @input="updateSearch"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="statusFilter"
              @change="updateFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Statuses</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Workspaces Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Workspace</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Owner</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Members</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Projects</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Created</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="workspace in workspaces.data" :key="workspace.id" class="border-b border-gray-200 hover:bg-gray-50">
                <td class="px-6 py-4 text-sm">
                  <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-sm font-medium" :style="{ backgroundColor: workspace.creator.avatar_color || '#3B82F6' }">
                      {{ workspace.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="font-medium text-gray-900">{{ workspace.name }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm">
                  <div class="text-gray-900">{{ workspace.creator.name }}</div>
                  <div class="text-gray-500 text-xs">{{ workspace.creator.email }}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ workspace.members_count }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ workspace.projects_count }}</td>
                <td class="px-6 py-4 text-sm">
                  <span v-if="workspace.is_active" class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Active
                  </span>
                  <span v-else class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    Inactive
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(workspace.created_at) }}</td>
                <td class="px-6 py-4 text-sm">
                  <div class="flex items-center space-x-2">
                    <button
                      v-if="workspace.is_active"
                      @click="confirmDeactivate(workspace)"
                      class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200 transition text-xs font-medium"
                    >
                      Deactivate
                    </button>
                    <button
                      v-else
                      @click="confirmActivate(workspace)"
                      class="px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 transition text-xs font-medium"
                    >
                      Activate
                    </button>
                    <button
                      @click="confirmDelete(workspace)"
                      class="px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition text-xs font-medium"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="workspaces.data.length === 0">
                <td colspan="7" class="px-6 py-8 text-center text-gray-600">
                  No workspaces found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="workspaces.data.length > 0" class="bg-white px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <div class="text-sm text-gray-600">
            Showing {{ workspaces.from }} to {{ workspaces.to }} of {{ workspaces.total }} results
          </div>
          <div class="flex items-center space-x-2">
            <Link
              v-if="workspaces.prev_page_url"
              :href="workspaces.prev_page_url"
              class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 text-sm"
            >
              Previous
            </Link>
            <Link
              v-if="workspaces.next_page_url"
              :href="workspaces.next_page_url"
              class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 text-sm"
            >
              Next
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <div v-if="showConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-lg max-w-sm w-full mx-4">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-2">{{ confirmTitle }}</h2>
          <p class="text-gray-600 mb-6">{{ confirmMessage }}</p>
          <div class="flex items-center justify-end space-x-3">
            <button
              @click="showConfirmModal = false"
              class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50 font-medium"
            >
              Cancel
            </button>
            <button
              @click="confirmAction"
              :class="confirmActionClass"
              class="px-4 py-2 rounded text-white font-medium hover:opacity-90 transition"
            >
              {{ confirmButtonText }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
  workspaces: Object,
  filters: Object
})

const searchQuery = ref('')
const statusFilter = ref('')

const showConfirmModal = ref(false)
const confirmTitle = ref('')
const confirmMessage = ref('')
const confirmButtonText = ref('')
const confirmActionClass = ref('')
const pendingAction = ref(null)

const updateSearch = () => {
  const params = new URLSearchParams()
  if (searchQuery.value) params.append('search', searchQuery.value)
  if (statusFilter.value) params.append('status', statusFilter.value)
  router.get('/admin/workspaces', Object.fromEntries(params))
}

const updateFilters = () => {
  updateSearch()
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const confirmDeactivate = (workspace) => {
  confirmTitle.value = 'Deactivate Workspace'
  confirmMessage.value = `Are you sure you want to deactivate "${workspace.name}"? Members will not be able to access it.`
  confirmButtonText.value = 'Deactivate'
  confirmActionClass.value = 'bg-yellow-600'
  pendingAction.value = {
    type: 'deactivate',
    workspaceId: workspace.id
  }
  showConfirmModal.value = true
}

const confirmActivate = (workspace) => {
  confirmTitle.value = 'Activate Workspace'
  confirmMessage.value = `Activate "${workspace.name}"? Members will regain access.`
  confirmButtonText.value = 'Activate'
  confirmActionClass.value = 'bg-green-600'
  pendingAction.value = {
    type: 'activate',
    workspaceId: workspace.id
  }
  showConfirmModal.value = true
}

const confirmDelete = (workspace) => {
  confirmTitle.value = 'Delete Workspace'
  confirmMessage.value = `Are you sure you want to permanently delete "${workspace.name}"? This action cannot be undone and all data will be lost.`
  confirmButtonText.value = 'Delete'
  confirmActionClass.value = 'bg-gray-600'
  pendingAction.value = {
    type: 'delete',
    workspaceId: workspace.id
  }
  showConfirmModal.value = true
}

const confirmAction = () => {
  const action = pendingAction.value
  showConfirmModal.value = false

  if (action.type === 'deactivate') {
    router.patch(`/admin/workspaces/${action.workspaceId}/deactivate`)
  } else if (action.type === 'activate') {
    router.patch(`/admin/workspaces/${action.workspaceId}/activate`)
  } else if (action.type === 'delete') {
    router.delete(`/admin/workspaces/${action.workspaceId}`)
  }
}
</script>
