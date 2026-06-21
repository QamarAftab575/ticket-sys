<template>
  <div class="max-w-2xl space-y-6">
    <!-- Google OAuth Settings -->
    <div class="bg-white rounded-lg shadow p-6 space-y-6">
      <div>
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Google OAuth</h2>
        <p class="text-sm text-gray-600">Configure Google OAuth to allow users to sign in with their Google accounts</p>
      </div>

      <!-- Status Card -->
      <div v-if="googleStatus" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-900">Configuration Status</p>
            <p class="text-sm text-gray-600 mt-1">
              <span v-if="googleStatus.enabled" class="text-green-600 font-medium">âœ“ Enabled</span>
              <span v-else class="text-red-600 font-medium">âœ— Disabled</span>
            </p>
            <p v-if="googleStatus.last_updated" class="text-xs text-gray-500 mt-2">
              Last updated: {{ formatDate(googleStatus.last_updated) }}
            </p>
          </div>
          <div class="text-right">
            <div v-if="googleStatus.configured" class="text-green-600 text-sm font-medium">
              âœ“ Credentials Configured
            </div>
            <div v-else class="text-amber-600 text-sm font-medium">
              âš  Credentials Missing
            </div>
          </div>
        </div>
      </div>

      <!-- Error Messages -->
      <div v-if="errors.google_oauth" class="bg-red-50 border border-red-200 rounded-lg p-4">
        <p class="text-red-800 text-sm font-medium">{{ errors.google_oauth }}</p>
      </div>

      <!-- Success Message -->
      <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-4">
        <p class="text-green-800 text-sm font-medium">{{ successMessage }}</p>
      </div>

      <!-- Configuration Form -->
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Enable Toggle -->
        <div class="flex items-center justify-between">
          <div>
            <label class="text-sm font-medium text-gray-900">Enable Google Login</label>
            <p class="text-sm text-gray-600 mt-1">Allow users to authenticate with Google</p>
          </div>
          <button
            type="button"
            @click="form.google_oauth.enabled = !form.google_oauth.enabled"
            :class="[
              'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
              form.google_oauth.enabled ? 'bg-green-600' : 'bg-gray-300'
            ]"
          >
            <span
              :class="[
                'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                form.google_oauth.enabled ? 'translate-x-6' : 'translate-x-1'
              ]"
            ></span>
          </button>
        </div>

        <!-- Client ID Field -->
        <div>
          <label for="google_client_id" class="block text-sm font-medium text-gray-900">Google Client ID</label>
          <input
            id="google_client_id"
            v-model="form.google_oauth.client_id"
            type="text"
            :required="form.google_oauth.enabled"
            :disabled="!form.google_oauth.enabled"
            class="mt-2 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500"
            placeholder="Your Google Client ID"
          />
          <p v-if="errors.client_id" class="text-red-600 text-sm mt-1">{{ errors.client_id }}</p>
          <p class="text-xs text-gray-500 mt-1">Found in Google Cloud Console</p>
        </div>

        <!-- Client Secret Field -->
        <div>
          <label for="google_client_secret" class="block text-sm font-medium text-gray-900">Google Client Secret</label>
          <input
            id="google_client_secret"
            v-model="form.google_oauth.client_secret"
            type="text"
            :required="form.google_oauth.enabled"
            :disabled="!form.google_oauth.enabled"
            class="mt-2 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500"
            placeholder="Your Google Client Secret"
          />
          <p v-if="errors.client_secret" class="text-red-600 text-sm mt-1">{{ errors.client_secret }}</p>
          <p class="text-xs text-gray-500 mt-1">Keep this secret - never share publicly</p>
        </div>

        <!-- Redirect URI Field (Read-only) -->
        <div>
          <label for="google_redirect_uri" class="block text-sm font-medium text-gray-900">Redirect URI (Required in Google Console)</label>
          <div class="mt-2 p-4 bg-gray-50 border border-gray-200 rounded-lg">
            <p class="text-sm font-mono text-gray-900 break-all">{{ callbackUrl }}</p>
          </div>
          <p v-if="errors.redirect_uri" class="text-red-600 text-sm mt-1">{{ errors.redirect_uri }}</p>
          <p class="text-sm text-gray-600 mt-2">
            <strong>Important:</strong> You must enter this exact callback URL in your Google Cloud Console under Authorized redirect URIs, or authentication will fail.
          </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-4 pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="handleTestConnection"
            :disabled="!form.google_oauth.enabled || !form.google_oauth.client_id || !form.google_oauth.client_secret || isTestingConnection"
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
            class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors ml-auto"
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
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  googleSettings: Object,
  googleStatus: Object,
})

const callbackUrl = computed(() => {
  const url = new URL(window.location.origin)
  return `${url.origin}/auth/google/callback`
})

const form = reactive({
  google_oauth: {
    enabled: props.googleSettings?.enabled || false,
    client_id: props.googleSettings?.client_id || '',
    client_secret: props.googleSettings?.client_secret || '',
    redirect_uri: callbackUrl.value
  }
})

const errors = reactive({
  client_id: null,
  client_secret: null,
  redirect_uri: null,
  google_oauth: null
})

const isSubmitting = ref(false)
const isTestingConnection = ref(false)
const successMessage = ref(null)
const testResult = ref(null)

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
    const response = await fetch('/admin/settings/google/test', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        client_id: form.google_oauth.client_id,
        client_secret: form.google_oauth.client_secret,
        redirect_uri: form.google_oauth.redirect_uri
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
  errors.client_id = null
  errors.client_secret = null
  errors.redirect_uri = null
  errors.google_oauth = null
  successMessage.value = null

  router.post('/admin/settings', form, {
    onError: (pageErrors) => {
      if (pageErrors['google_oauth.client_id']) {
        errors.client_id = Array.isArray(pageErrors['google_oauth.client_id']) 
          ? pageErrors['google_oauth.client_id'][0] 
          : pageErrors['google_oauth.client_id']
      }
      if (pageErrors['google_oauth.client_secret']) {
        errors.client_secret = Array.isArray(pageErrors['google_oauth.client_secret']) 
          ? pageErrors['google_oauth.client_secret'][0] 
          : pageErrors['google_oauth.client_secret']
      }
      if (pageErrors['google_oauth.redirect_uri']) {
        errors.redirect_uri = Array.isArray(pageErrors['google_oauth.redirect_uri']) 
          ? pageErrors['google_oauth.redirect_uri'][0] 
          : pageErrors['google_oauth.redirect_uri']
      }
      if (pageErrors.message) errors.google_oauth = pageErrors.message
    },
    onSuccess: () => {
      successMessage.value = 'Google login settings updated successfully'
      testResult.value = null
    },
    onFinish: () => {
      isSubmitting.value = false
    }
  })
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>

