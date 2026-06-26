<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-4xl font-bold text-gray-900">{{ $t('language_management') }}</h1>
          <p class="text-gray-600 mt-1">{{ $t('manage_languages_desc') }}</p>
        </div>
        <Link
          href="/admin/languages/create"
          class="px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200 font-medium cursor-pointer"
        >
          + {{ $t('add_language') }}
        </Link>
      </div>
    </template>

    <!-- Site Language Selection Card -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow-md p-6 mb-6 border border-blue-200">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-1">🌍 {{ $t('site_wide_language') }}</h2>
          <p class="text-sm text-gray-600">{{ $t('choose_default_language') }}</p>
        </div>
        <div class="flex items-center gap-4">
          <select
            v-model="selectedSiteLanguage"
            @change="setSiteLanguage"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white text-gray-900 font-medium cursor-pointer"
          >
            <option v-for="lang in activeLanguages" :key="lang.id" :value="lang.code">
              {{ lang.name }} ({{ lang.code }})
            </option>
          </select>
          <div class="flex items-center gap-2 px-3 py-2 bg-green-100 text-green-800 rounded-lg text-sm font-medium">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>{{ $t('current') }}: {{ currentSiteLanguageName }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
      <div v-if="languages.length === 0" class="p-8 text-center">
        <p class="text-gray-600 text-lg">{{ $t('no_languages_found') }}</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                {{ $t('language_table_header') }}
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                {{ $t('code_table_header') }}
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                {{ $t('default_table_header') }}
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                {{ $t('status_table_header') }}
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                {{ $t('actions_table_header') }}
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="language in languages" :key="language.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ language.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                  {{ language.code }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                <span v-if="language.is_default" class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                  ✓ {{ $t('default_badge') }}
                </span>
                <span v-else class="text-gray-400">—</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <button
                  @click="toggleLanguage(language)"
                  :disabled="language.is_default && language.is_active"
                  :class="[
                    'inline-flex px-3 py-1 text-xs font-semibold rounded-full transition-colors cursor-pointer',
                    language.is_active
                      ? 'bg-green-100 text-green-800 hover:bg-green-200'
                      : 'bg-gray-100 text-gray-800 hover:bg-gray-200',
                    language.is_default && language.is_active ? 'opacity-50 cursor-not-allowed' : ''
                  ]"
                >
                  {{ language.is_active ? '✓ ' + $t('active_status') : '○ ' + $t('inactive_status') }}
                </button>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <Link
                  :href="`/admin/languages/${language.id}/translations`"
                  class="text-indigo-600 hover:text-indigo-900 transition-colors"
                  :title="$t('manage_translations')"
                >
                  {{ $t('translate_link') }}
                </Link>

                <Link
                  v-if="!language.is_default"
                  :href="`/admin/languages/${language.id}/edit`"
                  class="text-blue-600 hover:text-blue-900 transition-colors"
                  :title="$t('edit_language_link')"
                >
                  {{ $t('edit_link') }}
                </Link>

                <button
                  v-if="!language.is_default"
                  @click="deleteLanguage(language)"
                  class="text-red-600 hover:text-red-900 transition-colors cursor-pointer"
                  :title="$t('delete_language_link')"
                >
                  {{ $t('delete_link') }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'

const props = defineProps({
  languages: Array,
  siteLanguage: String
})

const page = usePage()

// Site language management
const selectedSiteLanguage = ref(props.siteLanguage || 'en')

const activeLanguages = computed(() => {
  return props.languages.filter(lang => lang.is_active)
})

const currentSiteLanguageName = computed(() => {
  const lang = props.languages.find(l => l.code === selectedSiteLanguage.value)
  return lang ? lang.name : 'English'
})

const setSiteLanguage = () => {
  router.post('/admin/languages/set-site-language', {
    language_code: selectedSiteLanguage.value
  }, {
    onSuccess: () => {
      const message = (page.props.translations?.site_language_changed || 'Site language changed to {name}! 🌍').replace('{name}', currentSiteLanguageName.value)
      toast.success(message, {
        autoClose: 3000
      })
    },
    onError: (errors) => {
      toast.error(errors.error || page.props.translations?.failed_set_site_language || 'Failed to set site language', {
        autoClose: 3000
      })
    }
  })
}

const toggleLanguage = (language) => {
  if (language.is_default && language.is_active) {
    toast.error(page.props.translations?.default_english_must_active || 'Default English language must remain active', {
      autoClose: 3000
    })
    return
  }

  // Show warning when activating a language
  if (!language.is_active) {
    const confirmed = confirm(
      `⚠️ IMPORTANT: Activating "${language.name}" will:\n\n` +
      `✓ Set it as the site-wide default language\n` +
      `✓ Deactivate all other languages (except English)\n` +
      `✓ All users will see the site in ${language.name}\n\n` +
      `Are you sure you want to continue?`
    )
    
    if (!confirmed) return
  }

  router.post(`/admin/languages/${language.id}/toggle`, {}, {
    onSuccess: () => {
      const status = language.is_active ? 'deactivated' : 'activated'
      toast.success(`Language ${status}!`, {
        autoClose: 3000
      })
    },
    onError: (errors) => {
      toast.error(errors.error || 'Failed to toggle language', {
        autoClose: 3000
      })
    }
  })
}

const deleteLanguage = (language) => {
  if (!confirm(page.props.translations?.sure_delete_language?.replace('{name}', language.name) || `Are you sure you want to delete the "${language.name}" language? This action cannot be undone.`)) {
    return
  }

  router.delete(`/admin/languages/${language.id}`, {
    onSuccess: () => {
      const message = (page.props.translations?.language_deleted || 'Language "{name}" deleted successfully!').replace('{name}', language.name)
      toast.success(message, {
        autoClose: 3000
      })
    },
    onError: (errors) => {
      toast.error(errors.error || page.props.translations?.failed_delete_language || 'Failed to delete language', {
        autoClose: 3000
      })
    }
  })
}
</script>
