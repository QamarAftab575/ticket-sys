<template>
  <AppLayout :user-workspaces="userWorkspaces" :current-workspace="currentWorkspace" :user-role="userRole">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <!-- Sidebar Navigation -->
          <div class="md:col-span-1">
            <div class="bg-white rounded-lg shadow p-4 space-y-2">
              <Link
                href="/settings"
                :class="[
                  'block px-4 py-2 rounded-lg font-medium transition-colors',
                  isActive('/settings') 
                    ? 'bg-blue-50 text-blue-600' 
                    : 'text-gray-700 hover:bg-gray-50'
                ]"
              >
                Workspace Settings
              </Link>

              <div class="mt-4 pt-4 border-t">
                <h3 class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Integrations</h3>
                <Link
                  href="/settings/integrations/google"
                  :class="[
                    'block px-4 py-2 rounded-lg font-medium transition-colors ml-2',
                    isActive('/settings/integrations/google') 
                      ? 'bg-blue-50 text-blue-600' 
                      : 'text-gray-700 hover:bg-gray-50'
                  ]"
                >
                  Google Social Login
                </Link>
                <Link
                  href="/settings/integrations/tokens"
                  :class="[
                    'block px-4 py-2 rounded-lg font-medium transition-colors ml-2',
                    isActive('/settings/integrations/tokens') 
                      ? 'bg-blue-50 text-blue-600' 
                      : 'text-gray-700 hover:bg-gray-50'
                  ]"
                >
                  Access Tokens
                </Link>
              </div>
            </div>
          </div>

          <!-- Main Content -->
          <div class="md:col-span-3 space-y-6">
            <!-- Status Card -->
            <div v-if="status" class="bg-white rounded-lg shadow p-6">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Configuration Status</h3>
                  <p class="text-gray-600 mt-1">
                    <span v-if="status.enabled" class="text-green-600 font-medium">âœ“ Enabled</span>
                    <span v-else class="text-red-600 font-medium">âœ— Disabled</span>
                  </p>
                  <p v-if="status.last_updated" class="text-sm text-gray-500 mt-2">
                    Last updated: {{ formatDate(status.last_updated) }}
                  </p>
                </div>
                <div class="text-right">
                  <div v-if="status.configured" class="text-green-600 text-sm font-medium">
                    âœ“ Credentials Configured
                  </div>
                  <div v-else class="text-amber-600 text-sm font-medium">
                    âš  Credentials Missing
                  </div>
                </div>
              </div>
            </div>

            <!-- Error Messages -->
            <div v-if="errors.general" class="bg-red-50 border border-red-200 rounded-lg p-4">
              <p class="text-red-800 text-sm font-medium">{{ errors.general }}</p>
            </div>

            <!-- Success Message -->
            <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-4">
              <p class="text-green-800 text-sm font-medium">{{ successMessage }}</p>
            </div>

            <!-- Configuration Form -->
            <form @submit.prevent="handleSubmit" class="bg-white rounded-lg shadow p-6 space-y-6">
              <!-- Enable Toggle -->
              <div class="flex items-center justify-between">
                <div>
                  <label class="text-sm font-medium text-gray-900">Enable Google Login</label>
                  <p class="text-sm text-gray-600 mt-1">Allow users to authenticate with Google</p>
                </div>
                <button
                  type="button"
                  @click="form.enabled = !form.enabled"
                  :class="[
                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                    form.enabled ? 'bg-green-600' : 'bg-gray-300'
                  ]"
                >
                  <span
                    :class="[
                      'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                      form.enabled ? 'translate-x-6' : 'translate-x-1'
                    ]"
                  ></span>
                </button>
              </div>

              <!-- Client ID Field -->
              <div>
                <label for="client_id" class="block text-sm font-medium text-gray-900">Google Client ID</label>
                <input
                  id="client_id"
                  v-model="form.client_id"
                  type="text"
                  :required="form.enabled"
                  :disabled="!form.enabled"
                  class="mt-2 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500"
                  placeholder="Your Google Client ID"
                />
                <p v-if="errors.client_id" class="text-red-600 text-sm mt-1">{{ errors.client_id[0] }}</p>
              </div>

              <!-- Client Secret Field -->
              <div>
                <label for="client_secret" class="block text-sm font-medium text-gray-900">Google Client Secret</label>
                <input
                  id="client_secret"
                  v-model="form.client_secret"
                  type="text"
                  :required="form.enabled"
                  :disabled="!form.enabled"
                  class="mt-2 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500"
                  placeholder="Your Google Client Secret"
                />
                <p v-if="errors.client_secret" class="text-red-600 text-sm mt-1">{{ errors.client_secret[0] }}</p>
              </div>

              <!-- Redirect URI Field (Read-only) -->
              <div>
                <label for="redirect_uri" class="block text-sm font-medium text-gray-900">Redirect URI (Required in Google Console)</label>
                <div class="mt-2 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                  <p class="text-sm font-mono text-gray-900 break-all">{{ callbackUrl }}</p>
                   
                </div>
                <p class="text-sm text-gray-600 mt-2">
                  <strong>Important:</strong> You must enter this exact callback URL in your Google Console, or authentication will fail.
                </p>
              </div>

              <!-- Action Buttons -->
              <div class="flex gap-4 pt-4">
                <button
                  type="button"
                  @click="handleTestConnection"
                  :disabled="!form.enabled || !form.client_id || !form.client_secret || isTestingConnection"
                  class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  <span v-if="!isTestingConnection">Test Connection</span>
                  <span v-else class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Testing...
                  </span>
                </button>

                <button
                  type="submit"
                  :disabled="isSubmitting"
                  class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  <span v-if="!isSubmitting">Save Settings</span>
                  <span v-else class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Saving...
                  </span>
                </button>
              </div>
            </form>

            <!-- Test Connection Result -->
            <div v-if="testResult" :class="[
              'rounded-lg p-4',
              testResult.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'
            ]">
              <p :class="[
                'text-sm font-medium',
                testResult.success ? 'text-green-800' : 'text-red-800'
              ]">
                {{ testResult.message }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const page = usePage()

const props = defineProps({
  status: Object,
  userWorkspaces: Array,
  currentWorkspace: Object,
  userRole: String,
})

const callbackUrl = computed(() => {
  const url = new URL(window.location.origin)
  return `${url.origin}/auth/google/callback`
})

const form = reactive({
  enabled: props.status?.enabled || false,
  client_id: '',
  client_secret: '',
  redirect_uri: callbackUrl.value
})

const errors = reactive({
  general: null,
  client_id: null,
  client_secret: null,
  redirect_uri: null
})

const isSubmitting = ref(false)
const isTestingConnection = ref(false)
const successMessage = ref(null)
const testResult = ref(null)

const isActive = (path) => {
  return page.url === path || page.url.startsWith(path + '/')
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const handleTestConnection = async () => {
  isTestingConnection.value = true
  testResult.value = null

  try {
    const response = await fetch('/settings/integrations/google/test', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        client_id: form.client_id,
        client_secret: form.client_secret,
        redirect_uri: form.redirect_uri
      })
    })

    const data = await response.json()
    testResult.value = data
  } catch (error) {
    testResult.value = {
      success: false,
      message: 'Failed to test connection: ' + error.message
    }
  } finally {
    isTestingConnection.value = false
  }
}

const handleSubmit = async () => {
  isSubmitting.value = true
  errors.general = null
  errors.client_id = null
  errors.client_secret = null
  errors.redirect_uri = null
  successMessage.value = null

  router.post('/settings/integrations/google', form, {
    onError: (pageErrors) => {
      if (pageErrors.client_id) errors.client_id = pageErrors.client_id
      if (pageErrors.client_secret) errors.client_secret = pageErrors.client_secret
      if (pageErrors.redirect_uri) errors.redirect_uri = pageErrors.redirect_uri
      if (pageErrors.message) errors.general = pageErrors.message
    },
    onSuccess: () => {
      successMessage.value = 'Google login settings updated successfully'
    },
    onFinish: () => {
      isSubmitting.value = false
    }
  })
}
</script>

