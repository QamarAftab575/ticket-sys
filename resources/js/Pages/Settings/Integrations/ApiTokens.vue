<template>
  <AppLayout :user-workspaces="userWorkspaces" :current-workspace="currentWorkspace" :user-role="userRole">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <!-- Sidebar Navigation -->
          <SettingsSidebar :user-workspaces="userWorkspaces" />

          <!-- Main Content -->
          <div class="md:col-span-3 space-y-6">
            <!-- Error Messages -->
            <div v-if="errors.general" class="bg-red-50 border border-red-200 rounded-lg p-4">
              <p class="text-red-800 text-sm font-medium">{{ errors.general }}</p>
            </div>

            <!-- Success Message -->
            <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-4">
              <p class="text-green-800 text-sm font-medium">{{ successMessage }}</p>
            </div>

            <!-- API Documentation Banner -->
            <div class="bg-gradient-to-r from-indigo-50 to-blue-50 border border-indigo-200 rounded-lg p-5">
              <div class="flex items-start gap-4">
                <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                  <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <div class="flex-1">
                  <h4 class="text-base font-semibold text-gray-900 mb-1">API Documentation</h4>
                  <p class="text-sm text-gray-700 mb-3">Learn how to authenticate and use the API with your tokens. View endpoints, request examples, and response formats.</p>
                  <a 
                    href="/docs/api" 
                    target="_blank" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    View API Documentation
                  </a>
                </div>
              </div>
            </div>

            <!-- Create Token Section -->
            <div class="bg-white rounded-lg shadow p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Create New Token</h3>
              
              <form @submit.prevent="handleCreateToken" class="space-y-4">
                <div>
                  <label for="token_name" class="block text-sm font-medium text-gray-900">Token Name</label>
                  <input
                    id="token_name"
                    v-model="createForm.name"
                    type="text"
                    required
                    placeholder="e.g., My Integration, External App"
                    class="mt-2 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                  <p v-if="errors.name" class="text-red-600 text-sm mt-1">{{ errors.name[0] }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-900 mb-2">Expiration (Optional)</label>
                  <input
                    v-model="createForm.expires_at"
                    type="date"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                  <p class="text-gray-600 text-xs mt-1">Leave empty for no expiration</p>
                </div>

                <button
                  type="submit"
                  :disabled="isCreating"
                  class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  <span v-if="!isCreating">Create Token</span>
                  <span v-else class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Creating...
                  </span>
                </button>
              </form>
            </div>

            <!-- Token Created Modal -->
            <transition
              enter-active-class="ease-out duration-200"
              enter-from-class="opacity-0"
              enter-to-class="opacity-100"
              leave-active-class="ease-in duration-150"
              leave-from-class="opacity-100"
              leave-to-class="opacity-0"
            >
              <div v-if="showTokenModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeTokenModal">
                <transition
                  enter-active-class="ease-out duration-300"
                  enter-from-class="opacity-0 scale-95 translate-y-4"
                  enter-to-class="opacity-100 scale-100 translate-y-0"
                  leave-active-class="ease-in duration-200"
                  leave-from-class="opacity-100 scale-100 translate-y-0"
                  leave-to-class="opacity-0 scale-95 translate-y-4"
                >
                  <div v-if="showTokenModal" class="bg-white rounded-lg shadow-lg max-w-md w-full overflow-hidden">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                      <h3 class="text-base font-semibold text-gray-900">Token Created Successfully</h3>
                      <button @click="closeTokenModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-6 space-y-5">
                      <!-- Warning Banner -->
                      <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex gap-3">
                          <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0-10a4 4 0 100 8 4 4 0 000-8z" />
                          </svg>
                          <div>
                            <p class="text-sm font-medium text-amber-900">Save your token</p>
                            <p class="text-xs text-amber-800 mt-0.5">You won't be able to see it again. Store it somewhere secure.</p>
                          </div>
                        </div>
                      </div>

                      <!-- Token Display -->
                      <div>
                        <label class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2 block">Your API Token</label>
                        <div class="bg-gray-50 border border-gray-300 rounded-lg p-3 flex items-center justify-between gap-3">
                          <code class="font-mono text-sm text-gray-900 break-all flex-1">{{ newToken.plain_token }}</code>
                          <button
                            @click="copyTokenToClipboard"
                            class="flex-shrink-0 px-3 py-2 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 transition-colors"
                          >
                            {{ copyButtonText }}
                          </button>
                        </div>
                      </div>

                      <!-- Documentation Link -->
                      <div class="text-center">
                        <a href="/docs/api" target="_blank" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                          View API Documentation
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                          </svg>
                        </a>
                      </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex gap-3">
                      <button
                        @click="closeTokenModal"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-100 transition-colors"
                      >
                        Close
                      </button>
                    </div>
                  </div>
                </transition>
              </div>
            </transition>

            <!-- Tokens List -->
            <div class="bg-white rounded-lg shadow">
              <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">All Tokens</h3>
              </div>

              <div v-if="tokens.length === 0" class="p-6 text-center text-gray-500">
                <p>No API tokens yet. Create one to get started.</p>
              </div>

              <div v-else class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Token Name</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Token</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Created</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Last Used</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-for="token in tokens" :key="token.id" :class="!token.is_active ? 'bg-gray-50' : 'hover:bg-gray-50'">
                      <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ token.name }}</td>
                      <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ token.masked_token }}</td>
                      <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(token.created_at) }}</td>
                      <td class="px-6 py-4 text-sm text-gray-600">
                        {{ token.last_used_at ? formatDate(token.last_used_at) : 'Never' }}
                      </td>
                      <td class="px-6 py-4 text-sm">
                        <span v-if="!token.is_active" class="px-3 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded">Revoked</span>
                        <span v-else-if="token.is_expired" class="px-3 py-1 bg-red-100 text-red-800 text-xs font-medium rounded">Expired</span>
                        <span v-else-if="token.expires_at" class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded">
                          Expires {{ formatDate(token.expires_at) }}
                        </span>
                        <span v-else class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">Active</span>
                      </td>
                      <td class="px-6 py-4 text-sm space-x-2 flex">
                        <button
                          v-if="token.is_active"
                          @click="revokeToken(token.id)"
                          :disabled="isRevoking === token.id"
                          class="text-orange-600 hover:text-orange-800 font-medium text-sm disabled:opacity-50"
                        >
                          {{ isRevoking === token.id ? 'Revoking...' : 'Revoke' }}
                        </button>
                        <button
                          @click="deleteToken(token.id)"
                          :disabled="isDeleting === token.id"
                          class="flex items-center gap-1 text-red-600 hover:text-red-800 font-medium text-sm disabled:opacity-50"
                        >
                          <TrashIcon class="w-4 h-4" />
                          {{ isDeleting === token.id ? 'Deleting...' : 'Delete' }}
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { TrashIcon } from '@heroicons/vue/24/outline'
import AppLayout from '@/Layouts/AppLayout.vue'
import SettingsSidebar from '@/Components/Settings/SettingsSidebar.vue'

const page = usePage()

const props = defineProps({
  tokens: Array,
  stats: Object,
  pagination: Object,
  userWorkspaces: Array,
  currentWorkspace: Object,
  userRole: String,
})

const tokens = ref(props.tokens || [])
const stats = ref(props.stats || {})

const isActive = (route) => {
  return page.props.auth?.current_route === route || route === page.url
}

const createForm = reactive({
  name: '',
  expires_at: null,
})

const errors = reactive({
  general: null,
  name: null,
})

const isCreating = ref(false)
const isRevoking = ref(null)
const isDeleting = ref(null)
const showTokenModal = ref(false)
const newToken = ref(null)
const successMessage = ref('')
const copyButtonText = ref('Copy Token')

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const handleCreateToken = async () => {
  isCreating.value = true
  errors.general = null
  errors.name = null

  try {
    const response = await fetch('/settings/integrations/tokens', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify(createForm),
    })

    const data = await response.json()

    if (!response.ok) {
      if (data.errors) {
        if (data.errors.name) errors.name = data.errors.name
        if (data.errors.general) errors.general = data.errors.general
      }
      return
    }

    newToken.value = data
    showTokenModal.value = true
    createForm.name = ''
    createForm.expires_at = null
    
    // Add new token to list (without plain_token)
    const formattedToken = data.token
    tokens.value.unshift(formattedToken)
    
    // Update stats
    if (stats.value) {
      stats.value.total = (stats.value.total || 0) + 1
      stats.value.active = (stats.value.active || 0) + 1
    }
  } catch (error) {
    errors.general = 'Failed to create token: ' + error.message
  } finally {
    isCreating.value = false
  }
}

const copyTokenToClipboard = async () => {
  try {
    await navigator.clipboard.writeText(newToken.value.plain_token)
    copyButtonText.value = 'âœ“ Copied!'
    setTimeout(() => {
      copyButtonText.value = 'Copy Token'
    }, 2000)
  } catch (err) {
    console.error('Failed to copy:', err)
  }
}

const closeTokenModal = () => {
  showTokenModal.value = false
}

const revokeToken = async (tokenId) => {
  if (!confirm('Are you sure you want to revoke this token?')) {
    return
  }

  isRevoking.value = tokenId

  try {
    const response = await fetch(`/settings/integrations/tokens/${tokenId}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
    })

    if (response.ok) {
      // Update token status locally
      const tokenIndex = tokens.value.findIndex(t => t.id === tokenId)
      if (tokenIndex !== -1) {
        tokens.value[tokenIndex].is_active = false
      }
      
      // Update stats
      if (stats.value) {
        stats.value.active = Math.max(0, stats.value.active - 1)
      }
      
      successMessage.value = 'Token revoked successfully'
    } else {
      errors.general = 'Failed to revoke token'
    }
  } catch (error) {
    errors.general = 'Error revoking token: ' + error.message
  } finally {
    isRevoking.value = null
  }
}

const deleteToken = async (tokenId) => {
  if (!confirm('Are you sure you want to permanently delete this token?')) {
    return
  }

  isDeleting.value = tokenId

  try {
    const response = await fetch(`/settings/integrations/tokens/${tokenId}/delete`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
    })

    if (response.ok) {
      // Remove token from list locally
      tokens.value = tokens.value.filter(t => t.id !== tokenId)
      
      // Update stats
      if (stats.value) {
        stats.value.total = Math.max(0, stats.value.total - 1)
      }
      
      successMessage.value = 'Token deleted successfully'
    } else {
      errors.general = 'Failed to delete token'
    }
  } catch (error) {
    errors.general = 'Error deleting token: ' + error.message
  } finally {
    isDeleting.value = null
  }
}

const revokeAllTokens = async () => {
  if (!confirm('Are you sure you want to revoke ALL tokens? This cannot be undone.')) {
    return
  }

  isRevokingAll.value = true

  try {
    const response = await fetch('/settings/integrations/tokens/revoke-all', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
    })

    const data = await response.json()

    if (response.ok) {
      // Revoke all tokens locally
      tokens.value.forEach(t => t.is_active = false)
      
      // Update stats
      if (stats.value) {
        stats.value.active = 0
      }
      
      successMessage.value = data.message
    } else {
      errors.general = 'Failed to revoke all tokens'
    }
  } catch (error) {
    errors.general = 'Error revoking tokens: ' + error.message
  } finally {
    isRevokingAll.value = false
  }
}</script>

