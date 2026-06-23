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
          <h1 class="text-4xl font-bold text-gray-900">Manage Translations</h1>
          <p class="text-gray-600 mt-1">{{ language.name }} ({{ language.code }})</p>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Search Bar -->
      <div class="relative">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search translation keys..."
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition"
        />
        <svg class="absolute right-3 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>

      <!-- Translations Table -->
      <form @submit.prevent="saveTranslations" class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider w-1/4">
                  Key
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider w-1/3">
                  English Value
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider w-1/3">
                  {{ language.name }} Value
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(item, index) in filteredTranslations" :key="item.key" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <code class="text-sm bg-gray-100 px-2 py-1 rounded text-gray-800 font-mono">
                    {{ item.key }}
                  </code>
                </td>
                <td class="px-6 py-4">
                  <p class="text-sm text-gray-700 line-clamp-2">
                    {{ item.english_value }}
                  </p>
                </td>
                <td class="px-6 py-4">
                  <textarea
                    v-model="translations[index].value"
                    :placeholder="`Enter ${language.name} translation...`"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition text-sm"
                    rows="2"
                  ></textarea>
                </td>
              </tr>
              <tr v-if="filteredTranslations.length === 0">
                <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                  No translations found matching "{{ searchQuery }}"
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Save Button -->
        <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t border-gray-200">
          <Link
            href="/admin/languages"
            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors font-medium cursor-pointer"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="processing"
            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ processing ? 'Saving...' : 'Save Translations' }}
          </button>
        </div>
      </form>

      <!-- Summary -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex gap-3">
          <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div class="text-sm text-blue-800">
            <p class="font-semibold mb-1">Translation Progress</p>
            <p class="text-blue-700">
              {{ completedCount }} of {{ translations.length }} translations completed
              <span class="font-semibold">({{ completedPercentage }}%)</span>
            </p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'

const props = defineProps({
  language: Object,
  translations: Array
})

const translations = ref(props.translations)
const searchQuery = ref('')
const processing = ref(false)

const filteredTranslations = computed(() => {
  if (!searchQuery.value) return translations.value

  const query = searchQuery.value.toLowerCase()
  return translations.value.filter(item =>
    item.key.toLowerCase().includes(query) ||
    item.english_value.toLowerCase().includes(query) ||
    item.value.toLowerCase().includes(query)
  )
})

const completedCount = computed(() => {
  return translations.value.filter(item => item.value.trim()).length
})

const completedPercentage = computed(() => {
  if (translations.value.length === 0) return 0
  return Math.round((completedCount.value / translations.value.length) * 100)
})

const saveTranslations = () => {
  processing.value = true

  const translationsObj = {}
  translations.value.forEach(item => {
    translationsObj[item.key] = item.value
  })

  router.post(
    `/admin/languages/${props.language.id}/translations`,
    { translations: translationsObj },
    {
      onSuccess: () => {
        processing.value = false
        toast.success('Translations saved successfully!', {
          autoClose: 3000
        })
      },
      onError: (errors) => {
        processing.value = false
        toast.error(errors.error || 'Failed to save translations', {
          autoClose: 3000
        })
      }
    }
  )
}
</script>
