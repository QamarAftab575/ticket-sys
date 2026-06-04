<template>
  <AppLayout :user-workspaces="userWorkspaces" :current-workspace="currentWorkspace" :user-role="userRole">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <!-- Sidebar Navigation -->
          <SettingsSidebar />

          <!-- Main Content -->
          <div class="md:col-span-3">
            <!-- Workspace Settings -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Workspace Settings</h3>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Workspace Name</label>
                  <input
                    v-model="settings.workspaceName"
                    type="text"
                    class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Default View</label>
                  <select
                    v-model="settings.defaultView"
                    class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  >
                    <option value="kanban">Kanban Board</option>
                    <option value="list">List View</option>
                    <option value="timeline">Timeline</option>
                    <option value="calendar">Calendar</option>
                  </select>
                </div>

                <button
                  @click="saveSettings"
                  :disabled="isSaving"
                  class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition-colors"
                >
                  {{ isSaving ? 'Saving...' : 'Save Settings' }}
                </button>
              </div>
            </div>

            <!-- Notification Settings -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Notifications</h3>
              
              <div class="space-y-4">
                <div class="flex items-center">
                  <input
                    v-model="settings.emailNotifications"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label class="ml-2 block text-sm text-gray-700">
                    Email notifications for task updates
                  </label>
                </div>

                <div class="flex items-center">
                  <input
                    v-model="settings.mentionNotifications"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label class="ml-2 block text-sm text-gray-700">
                    Notify me when mentioned
                  </label>
                </div>

                <div class="flex items-center">
                  <input
                    v-model="settings.commentNotifications"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label class="ml-2 block text-sm text-gray-700">
                    Notify me on new comments
                  </label>
                </div>
              </div>
            </div>

            <!-- Success Message -->
            <div v-if="successMessage" class="p-4 bg-green-50 border border-green-200 rounded-lg">
              <p class="text-green-800">{{ successMessage }}</p>
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
import AppLayout from '@/Layouts/AppLayout.vue'
import SettingsSidebar from '@/Components/Settings/SettingsSidebar.vue'
import { Link } from '@inertiajs/vue3'

const page = usePage()

const props = defineProps({
  userWorkspaces: Array,
  currentWorkspace: Object,
  userRole: String
})

const settings = reactive({
  workspaceName: 'My Workspace',
  defaultView: 'list',
  emailNotifications: true,
  mentionNotifications: true,
  commentNotifications: true
})

const isSaving = ref(false)
const successMessage = ref('')

const isActive = (path) => {
  return page.url === path || page.url.startsWith(path + '/')
}

const saveSettings = () => {
  isSaving.value = true
  router.post('/settings/save', settings, {
    onSuccess: () => {
      successMessage.value = 'Settings saved successfully!'
      setTimeout(() => {
        successMessage.value = ''
      }, 3000)
    },
    onFinish: () => {
      isSaving.value = false
    }
  })
}
</script>
