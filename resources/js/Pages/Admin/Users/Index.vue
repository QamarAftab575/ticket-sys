<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
          <p class="text-gray-600 mt-1">View and manage all platform users</p>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Filters & Search -->
      <div class="bg-white rounded-2xl shadow-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Name or email..."
              @input="updateSearch"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
            />
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="statusFilter"
              @change="updateFilters"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
            >
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>

          <!-- Type Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
            <select
              v-model="typeFilter"
              @change="updateFilters"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
            >
              <option value="">All Types</option>
              <option value="trial">Trial Users</option>
              <option value="paid">Paid Users</option>
              <option value="expired">Expired Trial</option>
            </select>
          </div>

          <!-- Join Date From -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
            <input
              v-model="joinedFrom"
              type="date"
              @change="updateFilters"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
            />
          </div>

          <!-- Join Date To -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
            <input
              v-model="joinedTo"
              type="date"
              @change="updateFilters"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
            />
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">User</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Email</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Joined</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Type</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Workspaces</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Status</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users.data" :key="user.id" class="border-b border-gray-100 hover:bg-gray-50 transition" @click="viewUserDetails(user.id)" style="cursor: pointer;">
                <td class="px-6 py-4 text-sm">
                  <div class="flex items-center gap-3">
                    <div v-if="user.avatar" class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden">
                      <img :src="user.avatar" :alt="user.name" class="w-full h-full object-cover" />
                    </div>
                    <div v-else class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-semibold">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">{{ user.name }}</p>
                      <p v-if="user.is_super_admin" class="text-xs text-purple-600 font-semibold">ðŸ‘‘ Super Admin</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ user.email }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(user.created_at) }}</td>
                <td class="px-6 py-4 text-sm">
                  <span v-if="getUserType(user) === 'paid'" class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    ðŸ’³ Paid
                  </span>
                  <span v-else-if="getUserType(user) === 'trial'" class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    â±ï¸ Trial
                  </span>
                  <span v-else class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    â° Expired
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ user.organizations_count }}</td>
                <td class="px-6 py-4 text-sm">
                  <span v-if="user.is_suspended" class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    ðŸ”’ Suspended
                  </span>
                  <span v-else class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    âœ… Active
                  </span>
                </td>
                <td class="px-6 py-4 text-sm" @click.stop>
                  <div class="flex items-center space-x-2">
                    <button
                      @click="viewUserDetails(user.id)"
                      class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition text-xs font-medium"
                      title="View details"
                    >
                      View
                    </button>
                    <button
                      @click="confirmImpersonate(user)"
                      class="px-3 py-1 bg-purple-100 text-purple-700 rounded hover:bg-purple-200 transition text-xs font-medium"
                      title="Login as this user"
                    >
                      Login
                    </button>
                    <div class="relative group">
                      <button
                        class="px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition text-xs font-medium"
                      >
                        â‹®
                      </button>
                      <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-10 hidden group-hover:block">
                        <button
                          v-if="!user.is_suspended"
                          @click.prevent="confirmSuspend(user)"
                          class="block w-full text-left px-4 py-2 text-red-700 hover:bg-red-50 text-sm"
                        >
                          Suspend User
                        </button>
                        <button
                          v-else
                          @click.prevent="confirmActivate(user)"
                          class="block w-full text-left px-4 py-2 text-green-700 hover:bg-green-50 text-sm"
                        >
                          Activate User
                        </button>
                        <button
                          @click.prevent="confirmDelete(user)"
                          class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-50 text-sm border-t border-gray-200"
                        >
                          Delete User
                        </button>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <div class="text-sm text-gray-600">
            Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
          </div>
          <div class="flex items-center space-x-2">
            <Link
              v-if="users.prev_page_url"
              :href="users.prev_page_url"
              class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium transition"
            >
              â† Previous
            </Link>
            <Link
              v-if="users.next_page_url"
              :href="users.next_page_url"
              class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium transition"
            >
              Next â†’
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
  users: Object,
  filters: Object
})

const searchQuery = ref('')
const statusFilter = ref('')
const typeFilter = ref('')
const joinedFrom = ref('')
const joinedTo = ref('')

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
  if (typeFilter.value) params.append('type', typeFilter.value)
  if (joinedFrom.value) params.append('joined_from', joinedFrom.value)
  if (joinedTo.value) params.append('joined_to', joinedTo.value)
  router.get('/admin/users', Object.fromEntries(params))
}

const updateFilters = () => {
  updateSearch()
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getUserType = (user) => {
  if (user.active_plan_id) return 'paid'
  if (user.trial_ends_at) {
    return new Date(user.trial_ends_at) > new Date() ? 'trial' : 'expired'
  }
  return 'trial'
}

const viewUserDetails = (userId) => {
  router.visit(`/admin/users/${userId}`)
}

const confirmImpersonate = (user) => {
  confirmTitle.value = 'Impersonate User'
  confirmMessage.value = `Login as ${user.email}? You'll be able to see exactly what they see. Use /admin/stop-impersonating to stop.`
  confirmButtonText.value = 'Login as this user'
  confirmActionClass.value = 'bg-purple-600'
  pendingAction.value = {
    type: 'impersonate',
    userId: user.id
  }
  showConfirmModal.value = true
}

const confirmSuspend = (user) => {
  confirmTitle.value = 'Suspend User'
  confirmMessage.value = `Suspend ${user.email}? They will no longer be able to access the platform.`
  confirmButtonText.value = 'Suspend'
  confirmActionClass.value = 'bg-red-600'
  pendingAction.value = {
    type: 'suspend',
    userId: user.id
  }
  showConfirmModal.value = true
}

const confirmActivate = (user) => {
  confirmTitle.value = 'Activate User'
  confirmMessage.value = `Activate ${user.email}? They will regain access to the platform.`
  confirmButtonText.value = 'Activate'
  confirmActionClass.value = 'bg-green-600'
  pendingAction.value = {
    type: 'activate',
    userId: user.id
  }
  showConfirmModal.value = true
}

const confirmDelete = (user) => {
  confirmTitle.value = 'Delete User'
  confirmMessage.value = `Permanently delete ${user.email}? This action cannot be undone and all their data will be removed.`
  confirmButtonText.value = 'Delete'
  confirmActionClass.value = 'bg-gray-600'
  pendingAction.value = {
    type: 'delete',
    userId: user.id
  }
  showConfirmModal.value = true
}

const confirmAction = () => {
  const action = pendingAction.value
  showConfirmModal.value = false

  if (action.type === 'impersonate') {
    router.post(`/admin/users/${action.userId}/impersonate`)
  } else if (action.type === 'suspend') {
    router.patch(`/admin/users/${action.userId}/suspend`)
  } else if (action.type === 'activate') {
    router.patch(`/admin/users/${action.userId}/activate`)
  } else if (action.type === 'delete') {
    router.delete(`/admin/users/${action.userId}`)
  }
}
</script>

