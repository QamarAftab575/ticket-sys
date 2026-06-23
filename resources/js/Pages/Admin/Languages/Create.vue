<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center gap-4">
        <Link
          href="/admin/languages"
          class="text-indigo-600 hover:text-indigo-900 transition-colors"
        >
          ← Back to Languages
        </Link>
        <div>
          <h1 class="text-4xl font-bold text-gray-900">Add New Language</h1>
          <p class="text-gray-600 mt-1">Create a new language for your system</p>
        </div>
      </div>
    </template>

    <div class="max-w-2xl mx-auto">
      <div class="bg-white rounded-lg shadow-md p-8">
        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Language Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Language Name <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.name"
              type="text"
              placeholder="e.g., French, German, Russian"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition"
              :class="{ 'border-red-500': errors.name }"
            />
            <p v-if="errors.name" class="text-red-600 text-sm mt-1">{{ errors.name }}</p>
          </div>

          <!-- Language Code -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Language Code <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.code"
              type="text"
              placeholder="e.g., fr, de, ru"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition uppercase"
              :class="{ 'border-red-500': errors.code }"
              maxlength="10"
            />
            <p class="text-gray-500 text-xs mt-1">ISO 639-1 code (e.g., en, fr, de, ru)</p>
            <p v-if="errors.code" class="text-red-600 text-sm mt-1">{{ errors.code }}</p>
          </div>

          <!-- Info Box -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex gap-3">
              <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
              <div class="text-sm text-blue-800">
                <p class="font-semibold mb-1">What happens when you create a language?</p>
                <ul class="list-disc list-inside space-y-1 text-blue-700">
                  <li>A folder will be created in resources/lang/{code}</li>
                  <li>A messages.php file will be created with all translation keys</li>
                  <li>You can then add translation values for each key</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Buttons -->
          <div class="flex gap-3 justify-end pt-4">
            <Link
              href="/admin/languages"
              class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium cursor-pointer"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="processing"
              class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ processing ? 'Creating...' : 'Create Language' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const page = usePage()

const form = ref({
  name: '',
  code: '',
})

const processing = ref(false)
const errors = ref({})

const submitForm = () => {
  if (!form.value.name.trim()) {
    errors.value.name = 'Language name is required'
    return
  }

  if (!form.value.code.trim()) {
    errors.value.code = 'Language code is required'
    return
  }

  processing.value = true
  errors.value = {}

  router.post('/admin/languages', form.value, {
    onSuccess: () => {
      processing.value = false
    },
    onError: (newErrors) => {
      errors.value = newErrors
      processing.value = false
    }
  })
}
</script>
