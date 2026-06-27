<template>
  <div class="space-y-8">
    <!-- SMTP Configuration Section -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">SMTP Configuration</h2>
        <p class="mt-1 text-sm text-gray-500">
          Configure your email server settings to allow the application to send emails.
        </p>
      </div>

      <div class="p-6">
        <form @submit.prevent="saveSettings" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Mailer -->
            <div>
              <label for="mail_mailer" class="block text-sm font-medium text-gray-700">Mailer</label>
              <input
                type="text"
                id="mail_mailer"
                v-model="form.mail_mailer"
                placeholder="e.g. smtp"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              />
              <p v-if="form.errors.mail_mailer" class="mt-1 text-sm text-red-600">{{ form.errors.mail_mailer }}</p>
            </div>

            <!-- Host -->
            <div>
              <label for="mail_host" class="block text-sm font-medium text-gray-700">Host</label>
              <input
                type="text"
                id="mail_host"
                v-model="form.mail_host"
                placeholder="e.g. smtp.mailgun.org"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              />
              <p v-if="form.errors.mail_host" class="mt-1 text-sm text-red-600">{{ form.errors.mail_host }}</p>
            </div>

            <!-- Port -->
            <div>
              <label for="mail_port" class="block text-sm font-medium text-gray-700">Port</label>
              <input
                type="number"
                id="mail_port"
                v-model="form.mail_port"
                placeholder="e.g. 587"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              />
              <p v-if="form.errors.mail_port" class="mt-1 text-sm text-red-600">{{ form.errors.mail_port }}</p>
            </div>

            <!-- Encryption -->
            <div>
              <label for="mail_encryption" class="block text-sm font-medium text-gray-700">Encryption</label>
              <select
                id="mail_encryption"
                v-model="form.mail_encryption"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              >
                <option value="">None</option>
                <option value="tls">TLS</option>
                <option value="ssl">SSL</option>
              </select>
              <p v-if="form.errors.mail_encryption" class="mt-1 text-sm text-red-600">{{ form.errors.mail_encryption }}</p>
            </div>

            <!-- Username -->
            <div>
              <label for="mail_username" class="block text-sm font-medium text-gray-700">Username</label>
              <input
                type="text"
                id="mail_username"
                v-model="form.mail_username"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              />
              <p v-if="form.errors.mail_username" class="mt-1 text-sm text-red-600">{{ form.errors.mail_username }}</p>
            </div>

            <!-- Password -->
            <div>
              <label for="mail_password" class="block text-sm font-medium text-gray-700">Password</label>
              <div class="mt-1 relative rounded-md shadow-sm">
                <input
                  :type="showPassword ? 'text' : 'password'"
                  id="mail_password"
                  v-model="form.mail_password"
                  class="block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm pr-10"
                />
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" @click="showPassword = !showPassword">
                  <!-- Eye Icon -->
                  <svg v-if="!showPassword" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <!-- Eye Slash Icon -->
                  <svg v-else class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                </div>
              </div>
              <p v-if="form.errors.mail_password" class="mt-1 text-sm text-red-600">{{ form.errors.mail_password }}</p>
            </div>

            <!-- From Address -->
            <div>
              <label for="mail_from_address" class="block text-sm font-medium text-gray-700">From Address</label>
              <input
                type="email"
                id="mail_from_address"
                v-model="form.mail_from_address"
                placeholder="noreply@example.com"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              />
              <p v-if="form.errors.mail_from_address" class="mt-1 text-sm text-red-600">{{ form.errors.mail_from_address }}</p>
            </div>

            <!-- From Name -->
            <div>
              <label for="mail_from_name" class="block text-sm font-medium text-gray-700">From Name</label>
              <input
                type="text"
                id="mail_from_name"
                v-model="form.mail_from_name"
                placeholder="My Application"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              />
              <p v-if="form.errors.mail_from_name" class="mt-1 text-sm text-red-600">{{ form.errors.mail_from_name }}</p>
            </div>
          </div>

          <div class="flex justify-end pt-4">
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
            >
              {{ form.processing ? 'Saving...' : 'Save Settings' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Connection Testing Section -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">Connection Testing</h2>
        <p class="mt-1 text-sm text-gray-500">
          Verify your SMTP configuration by testing the connection to the server.
        </p>
      </div>

      <div class="p-6">
        <div class="space-y-4">
          <button
            @click="testConnection"
            :disabled="testingConnection"
            class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
          >
            {{ testingConnection ? 'Testing...' : 'Test Connection' }}
          </button>

          <!-- Test Connection Result -->
          <div v-if="testConnectionResult" :class="['p-4 rounded-md', testConnectionResult.success ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800']">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg v-if="testConnectionResult.success" class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <svg v-else class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium">{{ testConnectionResult.success ? 'Connection successful' : 'Unable to connect to the SMTP server.' }}</h3>
                <div class="mt-2 text-sm">
                  <p>{{ testConnectionResult.message }}</p>
                  
                  <div v-if="!testConnectionResult.success" class="mt-3 text-sm">
                    <p class="font-medium mb-1">Please verify:</p>
                    <ul class="list-disc pl-5 space-y-1">
                      <li>Host</li>
                      <li>Port</li>
                      <li>Username</li>
                      <li>Password</li>
                      <li>Encryption</li>
                    </ul>
                  </div>
                </div>

                <!-- Technical Details Toggle -->
                <div v-if="!testConnectionResult.success && testConnectionResult.technical_details" class="mt-4">
                  <button @click="showTechnicalDetails = !showTechnicalDetails" class="text-sm font-medium underline focus:outline-none hover:text-red-700">
                    {{ showTechnicalDetails ? 'Hide Technical Details' : 'Show Technical Details' }}
                  </button>
                  
                  <div v-if="showTechnicalDetails" class="mt-3 p-3 bg-white rounded border border-red-200 text-xs font-mono overflow-auto whitespace-pre-wrap">
                    <div class="mb-2 font-bold text-gray-700">Exception</div>
                    <div class="mb-4">{{ testConnectionResult.technical_details.exception }}</div>
                    
                    <div class="mb-2 font-bold text-gray-700">Error Message</div>
                    <div>{{ testConnectionResult.technical_details.error_message }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Send Test Email Section -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">Send Test Email</h2>
        <p class="mt-1 text-sm text-gray-500">
          Send a test email to verify that emails can be successfully delivered.
        </p>
      </div>

      <div class="p-6">
        <div class="max-w-xl space-y-4">
          <div>
            <label for="test_email" class="block text-sm font-medium text-gray-700">Test Email Address</label>
            <input
              type="email"
              id="test_email"
              v-model="testEmailForm.to"
              placeholder="admin@example.com"
              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            />
            <p v-if="testEmailErrors.to" class="mt-1 text-sm text-red-600">{{ testEmailErrors.to }}</p>
          </div>

          <div>
            <label for="test_message" class="block text-sm font-medium text-gray-700">Message (optional)</label>
            <textarea
              id="test_message"
              v-model="testEmailForm.message"
              rows="3"
              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            ></textarea>
          </div>

          <div>
            <button
              @click="sendTestEmail"
              :disabled="sendingTestEmail || !testEmailForm.to"
              class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
            >
              {{ sendingTestEmail ? 'Sending...' : 'Send Test Email' }}
            </button>
          </div>

          <!-- Test Email Result -->
          <div v-if="testEmailResult" :class="['p-4 rounded-md', testEmailResult.success ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800']">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg v-if="testEmailResult.success" class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <svg v-else class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                   <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium">{{ testEmailResult.success ? 'Email sent successfully' : 'Failed to send email' }}</h3>
                <div class="mt-2 text-sm">
                  <p>{{ testEmailResult.message }}</p>
                </div>
                 <!-- Technical Details Toggle -->
                <div v-if="!testEmailResult.success && testEmailResult.technical_details" class="mt-4">
                  <button @click="showEmailTechnicalDetails = !showEmailTechnicalDetails" class="text-sm font-medium underline focus:outline-none hover:text-red-700">
                    {{ showEmailTechnicalDetails ? 'Hide Technical Details' : 'Show Technical Details' }}
                  </button>
                  
                  <div v-if="showEmailTechnicalDetails" class="mt-3 p-3 bg-white rounded border border-red-200 text-xs font-mono overflow-auto whitespace-pre-wrap">
                    <div class="mb-2 font-bold text-gray-700">Exception</div>
                    <div class="mb-4">{{ testEmailResult.technical_details.exception }}</div>
                    
                    <div class="mb-2 font-bold text-gray-700">Error Message</div>
                    <div>{{ testEmailResult.technical_details.error_message }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  mailSettings: {
    type: Object,
    required: true
  }
})

const showPassword = ref(false)
const showTechnicalDetails = ref(false)
const showEmailTechnicalDetails = ref(false)

const form = useForm({
  mail_mailer: props.mailSettings.mail_mailer || 'smtp',
  mail_host: props.mailSettings.mail_host || '',
  mail_port: props.mailSettings.mail_port || '',
  mail_encryption: props.mailSettings.mail_encryption || 'tls',
  mail_username: props.mailSettings.mail_username || '',
  mail_password: props.mailSettings.mail_password || '',
  mail_from_address: props.mailSettings.mail_from_address || '',
  mail_from_name: props.mailSettings.mail_from_name || '',
})

const saveSettings = () => {
  form.post('/admin/settings', {
    preserveScroll: true,
    onSuccess: () => {
      // Show success notification if needed
    },
  })
}

// Connection Testing
const testingConnection = ref(false)
const testConnectionResult = ref(null)

const testConnection = async () => {
  testingConnection.value = true
  testConnectionResult.value = null
  showTechnicalDetails.value = false
  
  try {
    const response = await axios.post('/admin/settings/mail/test-connection', {
      host: form.mail_host,
      port: form.mail_port,
      username: form.mail_username,
      password: form.mail_password,
      encryption: form.mail_encryption,
    })
    
    testConnectionResult.value = response.data
  } catch (error) {
    if (error.response && error.response.data) {
      testConnectionResult.value = error.response.data
    } else {
      testConnectionResult.value = {
        success: false,
        message: 'An unexpected error occurred while testing the connection.'
      }
    }
  } finally {
    testingConnection.value = false
  }
}

// Send Test Email
const sendingTestEmail = ref(false)
const testEmailResult = ref(null)
const testEmailErrors = ref({})
const testEmailForm = ref({
  to: '',
  message: ''
})

const sendTestEmail = async () => {
  testEmailErrors.value = {}
  
  if (!testEmailForm.value.to) {
    testEmailErrors.value.to = 'The email address is required.'
    return
  }
  
  sendingTestEmail.value = true
  testEmailResult.value = null
  showEmailTechnicalDetails.value = false
  
  try {
    const response = await axios.post('/admin/settings/mail/test-email', {
      to: testEmailForm.value.to,
      message: testEmailForm.value.message,
      // Pass the current config to test without needing to save first
      config: {
        host: form.mail_host,
        port: form.mail_port,
        username: form.mail_username,
        password: form.mail_password,
        encryption: form.mail_encryption,
        from_address: form.mail_from_address,
        from_name: form.mail_from_name,
      }
    })
    
    testEmailResult.value = response.data
  } catch (error) {
    if (error.response && error.response.status === 422) {
      testEmailErrors.value = error.response.data.errors || {}
    } else if (error.response && error.response.data) {
      testEmailResult.value = error.response.data
    } else {
      testEmailResult.value = {
        success: false,
        message: 'An unexpected error occurred while sending the test email.'
      }
    }
  } finally {
    sendingTestEmail.value = false
  }
}
</script>
