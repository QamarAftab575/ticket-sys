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
          <h1 class="text-4xl font-bold text-gray-900">Edit Language</h1>
          <p class="text-gray-600 mt-1">Update language details</p>
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
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition"
              :class="{ 'border-red-500': errors.name }"
            />
            <p v-if="errors.name" class="text-red-600 text-sm mt-1">{{ errors.name }}</p>
          </div>

          <!-- Language Code (Read-only) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Language Code <span class="text-gray-500 text-xs">(Cannot be changed)</span>
            </label>
            <input
              type="text"
              :value="language.code"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed"
              disabled
            />
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
              {{ processing ? 'Saving...' : 'Save Changes' }}
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
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  language: Object
})

const form = ref({
  name: props.language.name,
})

const processing = ref(false)
const errors = ref({})

const submitForm = () => {
  if (!form.value.name.trim()) {
    errors.value.name = 'Language name is required'
    return
  }

  processing.value = true
  errors.value = {}

  router.put(`/admin/languages/${props.language.id}`, form.value, {
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
