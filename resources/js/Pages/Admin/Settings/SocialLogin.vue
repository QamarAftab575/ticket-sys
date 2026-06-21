<template>
  <div class="w-full space-y-6">
    <!-- Google OAuth Settings -->
    <div class="bg-white rounded-lg shadow p-8 space-y-6">
      <div class="flex items-center gap-3 mb-6">
        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
        </svg>
        <div>
          <h2 class="text-xl font-semibold text-gray-900">Google OAuth</h2>
          <p class="text-sm text-gray-600 mt-1">Configure Google OAuth to allow users to sign in with their Google accounts</p>
        </div>
      </div>

      <!-- Status Card -->
      <div v-if="googleStatus" class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900 mb-3">Configuration Status</p>
            <div class="flex flex-col gap-2">
              <div class="flex items-center gap-2">
                <svg v-if="googleStatus.enabled" class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <svg v-else class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span :class="googleStatus.enabled ? 'text-green-700 font-medium' : 'text-red-700 font-medium'">
                  {{ googleStatus.enabled ? 'Enabled' : 'Disabled' }}
                </span>
              </div>
              <div class="flex items-center gap-2">
                <svg v-if="googleStatus.configured" class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <svg v-else class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span :class="googleStatus.configured ? 'text-green-700 font-medium' : 'text-amber-700 font-medium'">
                  {{ googleStatus.configured ? 'Credentials Configured' : 'Credentials Missing' }}
                </span>
              </div>
            </div>
            <p v-if="googleStatus.last_updated" class="text-xs text-gray-600 mt-4">
              Last updated: {{ formatDate(googleStatus.last_updated) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Error Messages -->
      <div v-if="errors.google_oauth" class="bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <p class="text-red-800 text-sm font-medium">{{ errors.google_oauth }}</p>
      </div>

      <!-- Success Message -->
      <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        <p class="text-green-800 text-sm font-medium">{{ successMessage }}</p>
      </div>

      <!-- Configuration Form -->
      <form @submit.prevent="handleSubmit" class="space-y-6 border-t border-gray-200 pt-6">
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
              'relative inline-flex h-7 w-12 items-center rounded-full transition-colors cursor-pointer flex-shrink-0',
              form.google_oauth.enabled ? 'bg-green-600' : 'bg-gray-300'
            ]"
          >
            <span
              :class="[
                'inline-block h-5 w-5 transform rounded-full bg-white transition-transform',
                form.google_oauth.enabled ? 'translate-x-6' : 'translate-x-1'
              ]"
            ></span>
          </button>
        </div>

        <!-- Client ID Field -->
        <div>
          <label for="google_client_id" class="block text-sm font-medium text-gray-900 mb-2">Google Client ID</label>
          <input
            id="google_client_id"
            v-model="form.google_oauth.client_id"
            type="text"
            :required="form.google_oauth.enabled"
            :disabled="!form.google_oauth.enabled"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 text-sm"
            placeholder="Your Google Client ID"
          />
          <p v-if="errors.client_id" class="text-red-600 text-sm mt-2">{{ errors.client_id }}</p>
          <p class="text-xs text-gray-500 mt-2">Found in Google Cloud Console</p>
        </div>

        <!-- Client Secret Field -->
        <div>
          <label for="google_client_secret" class="block text-sm font-medium text-gray-900 mb-2">Google Client Secret</label>
          <input
            id="google_client_secret"
            v-model="form.google_oauth.client_secret"
            type="text"
            :required="form.google_oauth.enabled"
            :disabled="!form.google_oauth.enabled"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 text-sm"
            placeholder="Your Google Client Secret"
          />
          <p v-if="errors.client_secret" class="text-red-600 text-sm mt-2">{{ errors.client_secret }}</p>
          <p class="text-xs text-gray-500 mt-2">Keep this secret - never share publicly</p>
        </div>

        <!-- Redirect URI Field (Read-only) -->
        <div>
          <label for="google_redirect_uri" class="block text-sm font-medium text-gray-900 mb-2">Redirect URI (Required in Google Console)</label>
          <div class="mt-2 p-4 bg-gray-50 border border-gray-200 rounded-lg">
            <p class="text-sm font-mono text-gray-900 break-all select-all">{{ callbackUrl }}</p>
          </div>
          <p v-if="errors.redirect_uri" class="text-red-600 text-sm mt-2">{{ errors.redirect_uri }}</p>
          <p class="text-sm text-gray-600 mt-3">
            <strong>Important:</strong> You must enter this exact callback URL in your Google Cloud Console under <strong>Authorized redirect URIs</strong>, or authentication will fail.
          </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200">
          <button
            type="button"
            @click="handleTestConnection"
            :disabled="!form.google_oauth.enabled || !form.google_oauth.client_id || !form.google_oauth.client_secret || isTestingConnection"
            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2 text-sm"
          >
            <svg v-if="!isTestingConnection" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ isTestingConnection ? 'Testing...' : 'Test Connection' }}</span>
          </button>

          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors ml-auto flex items-center justify-center gap-2 text-sm"
          >
            <svg v-if="!isSubmitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ isSubmitting ? 'Saving...' : 'Save Settings' }}</span>
          </button>
        </div>
      </form>

      <!-- Test Connection Result -->
      <div v-if="testResult" :class="[
        'rounded-lg p-4 flex items-start gap-3',
        testResult.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'
      ]">
        <svg :class="[
          'w-5 h-5 flex-shrink-0 mt-0.5',
          testResult.success ? 'text-green-600' : 'text-red-600'
        ]" fill="currentColor" viewBox="0 0 20 20">
          <path v-if="testResult.success" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          <path v-else fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
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
