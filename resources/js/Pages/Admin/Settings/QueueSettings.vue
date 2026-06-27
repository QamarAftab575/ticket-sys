<template>
  <div class="space-y-6">

    <!-- Queue Mode Card -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">Email Queue Settings</h2>
        <p class="mt-1 text-sm text-gray-500">
          Choose how emails and background jobs should be processed based on your hosting environment.
        </p>
      </div>

      <div class="p-6">
        <form @submit.prevent="saveSettings" class="space-y-6">

          <!-- Queue Mode Dropdown -->
          <div class="max-w-md">
            <label for="queue_connection" class="block text-sm font-medium text-gray-700">
              Queue Mode
            </label>
            <select
              id="queue_connection"
              v-model="form.queue_connection"
              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            >
              <option value="database">Database Queue (Recommended)</option>
              <option value="sync">Sync (Immediate Processing)</option>
            </select>
            <p v-if="form.errors.queue_connection" class="mt-1 text-sm text-red-600">
              {{ form.errors.queue_connection }}
            </p>
          </div>

          <!-- Description box – changes based on selection -->
          <div v-if="form.queue_connection === 'database'" class="rounded-md border border-green-200 bg-green-50 p-4">
            <div class="flex">
              <span class="text-xl mr-3 leading-none">✅</span>
              <div class="text-sm text-green-800">
                <p class="font-semibold mb-2">Recommended for production servers and businesses sending a higher volume of emails.</p>
                <ul class="list-disc pl-5 space-y-1">
                  <li>Emails are placed into a queue and processed in the background.</li>
                  <li>Improves application performance because users do not wait for emails to be sent.</li>
                  <li>Best for VPS, dedicated servers, or cloud hosting.</li>
                  <li>Requires a Queue Worker (Supervisor/systemd) or a scheduled queue processor to handle queued jobs.</li>
                </ul>
              </div>
            </div>
          </div>

          <div v-else class="rounded-md border border-blue-200 bg-blue-50 p-4">
            <div class="flex">
              <span class="text-xl mr-3 leading-none">ℹ️</span>
              <div class="text-sm text-blue-800">
                <p class="font-semibold mb-2">Recommended for shared hosting and small businesses.</p>
                <ul class="list-disc pl-5 space-y-1">
                  <li>Emails are sent immediately during the request.</li>
                  <li>No Queue Worker or Supervisor is required.</li>
                  <li>Easier to configure on cPanel/shared hosting.</li>
                  <li>Suitable for low to moderate email volume.</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Next Step: Queue Worker instructions (database only) -->
          <div v-if="form.queue_connection === 'database'" class="rounded-lg border border-gray-200 bg-gray-50 p-5 space-y-4">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z" />
              </svg>
              <h3 class="text-sm font-semibold text-gray-800">Next Step — Start Your Queue Worker</h3>
            </div>

            <p class="text-sm text-gray-600">
              After saving this setting, you must start a Laravel Queue Worker on your server.
            </p>

            <!-- Basic command -->
            <div>
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Basic command</p>
              <div class="flex items-center justify-between bg-gray-900 text-green-400 rounded-md px-4 py-3 font-mono text-sm">
                <span>php artisan queue:work</span>
                <button
                  type="button"
                  @click="copyToClipboard('php artisan queue:work')"
                  class="ml-4 text-gray-400 hover:text-white transition text-xs flex items-center gap-1"
                  title="Copy"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                  {{ copied === 'basic' ? 'Copied!' : 'Copy' }}
                </button>
              </div>
            </div>

            <!-- Production / Supervisor command -->
            <div>
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">
                Production — Supervisor / systemd (Recommended)
              </p>
              <p class="text-xs text-gray-500 mb-2">
                For production environments, keep the queue worker running continuously using Supervisor or systemd.
              </p>
              <div class="flex items-center justify-between bg-gray-900 text-green-400 rounded-md px-4 py-3 font-mono text-sm">
                <span>php artisan queue:work --queue=emails,default --sleep=3 --tries=3</span>
                <button
                  type="button"
                  @click="copyToClipboard('php artisan queue:work --queue=emails,default --sleep=3 --tries=3')"
                  class="ml-4 shrink-0 text-gray-400 hover:text-white transition text-xs flex items-center gap-1"
                  title="Copy"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                  {{ copied === 'supervisor' ? 'Copied!' : 'Copy' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Save button -->
          <div class="flex justify-end pt-2">
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

    <!-- Help Card -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">Which option should I choose?</h2>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

          <!-- Database -->
          <div class="rounded-lg border border-gray-200 p-4">
            <div class="flex items-center gap-2 mb-3">
              <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Recommended</span>
              <span class="text-sm font-semibold text-gray-900">Database Queue</span>
            </div>
            <ul class="text-sm text-gray-600 space-y-1 list-disc pl-5">
              <li>Best performance.</li>
              <li>Ideal for production environments.</li>
              <li>Recommended when your server supports background workers.</li>
            </ul>
          </div>

          <!-- Sync -->
          <div class="rounded-lg border border-gray-200 p-4">
            <div class="flex items-center gap-2 mb-3">
              <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">Shared Hosting</span>
              <span class="text-sm font-semibold text-gray-900">Sync</span>
            </div>
            <ul class="text-sm text-gray-600 space-y-1 list-disc pl-5">
              <li>Best for shared hosting.</li>
              <li>No additional server configuration required.</li>
              <li>Recommended if you cannot run a Queue Worker.</li>
            </ul>
          </div>

        </div>
      </div>
    </div>

    <!-- Friendly Support Note -->
    <div class="rounded-lg border border-amber-200 bg-amber-50 p-5">
      <div class="flex gap-3">
        <span class="text-xl leading-none">💡</span>
        <div class="text-sm text-amber-800">
          <p class="font-semibold mb-1">Not sure which option is right for your hosting?</p>
          <p>
            If your hosting provider does not support Queue Workers or Supervisor, choose
            <strong>Sync</strong>.
          </p>
          <p class="mt-2">
            If you continue to experience issues with email delivery or queue processing, please contact the
            script seller for assistance with your server configuration.
          </p>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  queueSettings: {
    type: Object,
    required: true,
  },
})

const form = useForm({
  queue_connection: props.queueSettings.queue_connection || 'database',
})

const saveSettings = () => {
  form.post('/admin/settings', {
    preserveScroll: true,
  })
}

// Copy-to-clipboard helper
const copied = ref(null)

const copyToClipboard = (text) => {
  const key = text.includes('--queue') ? 'supervisor' : 'basic'
  navigator.clipboard.writeText(text).then(() => {
    copied.value = key
    setTimeout(() => { copied.value = null }, 2000)
  })
}
</script>
