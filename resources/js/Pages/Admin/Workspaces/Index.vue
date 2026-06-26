<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">{{ $t('manage_workspaces') }}</h1>
          <p class="text-gray-600 mt-1">{{ $t('view_manage_all_workspaces') }}</p>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Filters & Search -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('workspace_search') }}</label>
            <input
              v-model="searchQuery"
              type="text"
              :placeholder="$t('workspace_search_placeholder')"
              @input="updateSearch"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('workspace_status') }}</label>
            <select
              v-model="statusFilter"
              @change="updateFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">{{ $t('workspace_all_statuses') }}</option>
              <option value="active">{{ $t('workspace_active') }}</option>
              <option value="inactive">{{ $t('workspace_inactive') }}</option>
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
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ $t('workspace_table_header') }}</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ $t('workspace_owner') }}</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ $t('workspace_members') }}</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ $t('workspace_projects') }}</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ $t('workspace_status') }}</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ $t('workspace_created') }}</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ $t('workspace_actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="workspace in workspaces.data" :key="workspace.id" class="border-b border-gray-200 hover:bg-gray-50">
                <td class="px-6 py-4 text-sm">
                  <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-sm font-medium" :style="{ backgroundColor: workspace.avatar_color || '#3B82F6' }">
                      {{ workspace.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="font-medium text-gray-900">{{ workspace.name }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm">
                  <div v-if="workspace.creator" class="text-gray-900">{{ workspace.creator.name }}</div>
                  <div v-else class="text-gray-500 italic">{{ $t('workspace_no_owner') }}</div>
                  <div v-if="workspace.creator" class="text-gray-500 text-xs">{{ workspace.creator.email }}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ workspace.members_count }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ workspace.projects_count }}</td>
                <td class="px-6 py-4 text-sm">
                  <span v-if="workspace.is_active" class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    {{ $t('workspace_active') }}
                  </span>
                  <span v-else class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    {{ $t('workspace_inactive') }}
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
                      {{ $t('workspace_deactivate_button') }}
                    </button>
                    <button
                      v-else
                      @click="confirmActivate(workspace)"
                      class="px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 transition text-xs font-medium"
                    >
                      {{ $t('workspace_activate_button') }}
                    </button>
                    <button
                      @click="confirmDelete(workspace)"
                      class="px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition text-xs font-medium"
                    >
                      {{ $t('workspace_delete_button') }}
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="workspaces.data.length === 0">
                <td colspan="7" class="px-6 py-8 text-center text-gray-600">
                  {{ $t('no_workspaces_found') }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="workspaces.data.length > 0" class="bg-white px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <div class="text-sm text-gray-600">
            {{ $t('workspace_showing_results').replace('{from}', workspaces.from).replace('{to}', workspaces.to).replace('{total}', workspaces.total) }}
          </div>
          <div class="flex items-center space-x-2">
            <Link
              v-if="workspaces.prev_page_url"
              :href="workspaces.prev_page_url"
              class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 text-sm"
            >
              {{ $t('workspace_previous') }}
            </Link>
            <Link
              v-if="workspaces.next_page_url"
              :href="workspaces.next_page_url"
              class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 text-sm"
            >
              {{ $t('workspace_next') }}
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
              {{ $t('workspace_cancel') }}
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
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  workspaces: Object,
  filters: Object
})

const page = usePage()

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
  confirmTitle.value = page.props.translations?.workspace_deactivate_title || 'Deactivate Workspace'
  confirmMessage.value = (page.props.translations?.workspace_deactivate_message || 'Are you sure you want to deactivate "{name}"? Members will not be able to access it.').replace('{name}', workspace.name)
  confirmButtonText.value = page.props.translations?.workspace_deactivate_button || 'Deactivate'
  confirmActionClass.value = 'bg-yellow-600'
  pendingAction.value = {
    type: 'deactivate',
    workspaceId: workspace.id
  }
  showConfirmModal.value = true
}

const confirmActivate = (workspace) => {
  confirmTitle.value = page.props.translations?.workspace_activate_title || 'Activate Workspace'
  confirmMessage.value = (page.props.translations?.workspace_activate_message || 'Activate "{name}"? Members will regain access.').replace('{name}', workspace.name)
  confirmButtonText.value = page.props.translations?.workspace_activate_button || 'Activate'
  confirmActionClass.value = 'bg-green-600'
  pendingAction.value = {
    type: 'activate',
    workspaceId: workspace.id
  }
  showConfirmModal.value = true
}

const confirmDelete = (workspace) => {
  confirmTitle.value = page.props.translations?.workspace_delete_title || 'Delete Workspace'
  confirmMessage.value = (page.props.translations?.workspace_delete_message || 'Are you sure you want to permanently delete "{name}"? This action cannot be undone and all data will be lost.').replace('{name}', workspace.name)
  confirmButtonText.value = page.props.translations?.workspace_delete_button || 'Delete'
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

