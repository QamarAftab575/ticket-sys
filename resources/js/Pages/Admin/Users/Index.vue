<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center gap-4">
        <div class="min-w-0">
          <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">User Management</h1>
          <p class="text-slate-600 text-sm sm:text-base mt-1">View and manage all platform users</p>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Mobile Filter Toggle Button -->
      <div class="md:hidden">
        <button
          @click="showFilters = !showFilters"
          class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-3 flex items-center justify-between hover:bg-slate-50 transition-colors"
        >
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <span class="text-sm font-medium text-slate-700">Filters</span>
          </div>
          <svg 
            :class="['w-5 h-5 text-slate-600 transition-transform', showFilters ? 'rotate-180' : '']" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
          </svg>
        </button>
      </div>

      <!-- Filters & Search -->
      <transition name="fade">
        <div 
          v-if="showFilters || isDesktop"
          class="bg-white rounded-2xl shadow-sm p-4 sm:p-6 border border-slate-200"
        >
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
          <!-- Search -->
          <div>
            <label class="block text-xs sm:text-sm font-medium text-slate-700 mb-1 sm:mb-2">Search</label>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Name or email..."
              @input="updateSearch"
              class="w-full px-3 sm:px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
            />
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-xs sm:text-sm font-medium text-slate-700 mb-1 sm:mb-2">Status</label>
            <select
              v-model="statusFilter"
              @change="updateFilters"
              class="w-full px-3 sm:px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
            >
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>

          <!-- Type Filter -->
          <div>
            <label class="block text-xs sm:text-sm font-medium text-slate-700 mb-1 sm:mb-2">Type</label>
            <select
              v-model="typeFilter"
              @change="updateFilters"
              class="w-full px-3 sm:px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
            >
              <option value="">All Types</option>
              <option value="trial">Trial Users</option>
              <option value="paid">Paid Users</option>
              <option value="expired">Expired Trial</option>
            </select>
          </div>

          <!-- Join Date From -->
          <div>
            <label class="block text-xs sm:text-sm font-medium text-slate-700 mb-1 sm:mb-2">From Date</label>
            <input
              v-model="joinedFrom"
              type="date"
              @change="updateFilters"
              class="w-full px-3 sm:px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
            />
          </div>

          <!-- Join Date To -->
          <div>
            <label class="block text-xs sm:text-sm font-medium text-slate-700 mb-1 sm:mb-2">To Date</label>
            <input
              v-model="joinedTo"
              type="date"
              @change="updateFilters"
              class="w-full px-3 sm:px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
            />
          </div>
          </div>
        </div>
      </transition>

      <!-- Users Table -->
      <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-200">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-semibold text-slate-900">User</th>
                <th class="hidden sm:table-cell px-3 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-semibold text-slate-900">Email</th>
                <th class="hidden md:table-cell px-3 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-semibold text-slate-900">Joined</th>
                <th class="hidden lg:table-cell px-3 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-semibold text-slate-900">Type</th>
                <th class="hidden lg:table-cell px-3 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-semibold text-slate-900">Workspaces</th>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-semibold text-slate-900">Status</th>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-semibold text-slate-900">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users.data" :key="user.id" class="border-b border-slate-100 hover:bg-slate-50 transition" @click="viewUserDetails(user.id)" style="cursor: pointer;">
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm">
                  <div class="flex items-center gap-2 sm:gap-3">
                    <div v-if="user.avatar" class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg overflow-hidden flex-shrink-0">
                      <img :src="user.avatar" :alt="user.name" class="w-full h-full object-cover" />
                    </div>
                    <div v-else class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-semibold text-xs flex-shrink-0">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0">
                      <p class="font-medium text-slate-900 truncate text-xs sm:text-sm">{{ user.name }}</p>
                      <p v-if="user.is_super_admin" class="text-xs text-purple-600 font-semibold">Super Admin</p>
                    </div>
                  </div>
                </td>
                <td class="hidden sm:table-cell px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-600">{{ user.email }}</td>
                <td class="hidden md:table-cell px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-600">{{ formatDate(user.created_at) }}</td>
                <td class="hidden lg:table-cell px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm">
                  <span v-if="getUserType(user) === 'paid'" class="inline-flex px-2 py-1 rounded text-xs font-medium bg-emerald-50 text-emerald-700">
                    Paid
                  </span>
                  <span v-else-if="getUserType(user) === 'trial'" class="inline-flex px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700">
                    Trial
                  </span>
                  <span v-else class="inline-flex px-2 py-1 rounded text-xs font-medium bg-red-50 text-red-700">
                    Expired
                  </span>
                </td>
                <td class="hidden lg:table-cell px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-600">{{ user.organizations_count }}</td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm">
                  <span v-if="user.is_suspended" class="inline-flex px-2 py-1 rounded text-xs font-medium bg-red-50 text-red-700">
                    Suspended
                  </span>
                  <span v-else class="inline-flex px-2 py-1 rounded text-xs font-medium bg-emerald-50 text-emerald-700">
                    Active
                  </span>
                </td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs" @click.stop>
                  <div class="flex items-center space-x-1">
                    <button
                      @click="viewUserDetails(user.id)"
                      class="px-2 py-1 bg-blue-50 text-blue-700 rounded hover:bg-blue-100 transition text-xs font-medium"
                      title="View details"
                    >
                      View
                    </button>
                    <button
                      @click="confirmImpersonate(user)"
                      class="hidden sm:inline-block px-2 py-1 bg-purple-50 text-purple-700 rounded hover:bg-purple-100 transition text-xs font-medium"
                      title="Login as this user"
                    >
                      Login
                    </button>
                    <div class="relative group">
                      <button
                        class="px-2 py-1 bg-slate-100 text-slate-700 rounded hover:bg-slate-200 transition text-xs font-medium"
                      >
                        ⋮
                      </button>
                      <div class="absolute right-0 mt-1 w-40 bg-white rounded-lg shadow-lg z-10 hidden group-hover:block">
                        <button
                          v-if="!user.is_suspended"
                          @click.prevent="confirmSuspend(user)"
                          class="block w-full text-left px-3 py-2 text-red-700 hover:bg-red-50 text-xs"
                        >
                          Suspend
                        </button>
                        <button
                          v-else
                          @click.prevent="confirmActivate(user)"
                          class="block w-full text-left px-3 py-2 text-emerald-700 hover:bg-emerald-50 text-xs"
                        >
                          Activate
                        </button>
                        <button
                          @click.prevent="confirmDelete(user)"
                          class="block w-full text-left px-3 py-2 text-slate-700 hover:bg-slate-50 text-xs border-t border-slate-200"
                        >
                          Delete
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
        <div class="bg-white px-3 sm:px-6 py-3 sm:py-4 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="text-xs sm:text-sm text-slate-600">
            Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
          </div>
          <div class="flex items-center space-x-2 flex-wrap">
            <Link
              v-if="users.prev_page_url"
              :href="users.prev_page_url"
              class="px-3 py-2 border border-slate-300 rounded-lg hover:bg-slate-50 text-xs sm:text-sm font-medium transition"
            >
              ← Previous
            </Link>
            <Link
              v-if="users.next_page_url"
              :href="users.next_page_url"
              class="px-3 py-2 border border-slate-300 rounded-lg hover:bg-slate-50 text-xs sm:text-sm font-medium transition"
            >
              Next →
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <div v-if="showConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-lg max-w-sm w-full mx-4">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-slate-900 mb-2">{{ confirmTitle }}</h2>
          <p class="text-slate-600 mb-6 text-sm">{{ confirmMessage }}</p>
          <div class="flex items-center justify-end space-x-3">
            <button
              @click="showConfirmModal = false"
              class="px-4 py-2 border border-slate-300 rounded hover:bg-slate-50 font-medium text-sm"
            >
              Cancel
            </button>
            <button
              @click="confirmAction"
              :class="confirmActionClass"
              class="px-4 py-2 rounded text-white font-medium hover:opacity-90 transition text-sm"
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
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
  users: Object,
  filters: Object
})

const showFilters = ref(false)
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
const isDesktop = ref(true)

const handleResize = () => {
  if (typeof window !== 'undefined') {
    isDesktop.value = window.innerWidth >= 768
    if (isDesktop.value) {
      showFilters.value = false
    }
  }
}

onMounted(() => {
  if (typeof window !== 'undefined') {
    isDesktop.value = window.innerWidth >= 768
    window.addEventListener('resize', handleResize)
  }
})

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('resize', handleResize)
  }
})

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
  confirmActionClass.value = 'bg-emerald-600'
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
  confirmActionClass.value = 'bg-slate-600'
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


<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
