<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <!-- Sidebar Navigation -->
          <SettingsSidebar :user-workspaces="userWorkspaces" />

          <!-- Main Content -->
          <div class="md:col-span-3">
            <!-- Workspaces List -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">My Workspaces</h3>
              
              <div v-if="userWorkspaces.length === 0" class="text-center py-8">
                <p class="text-gray-500">You are not part of any workspaces yet.</p>
              </div>

              <div v-else class="space-y-4">
                <Link
                  v-for="workspace in userWorkspaces"
                  :key="workspace.id"
                  :href="`/workspace/${workspace.id}/dashboard`"
                  class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:shadow-md transition cursor-pointer"
                >
                  <div class="flex items-center space-x-4">
                    <div
                      :style="{ backgroundColor: workspace.avatar_color }"
                      class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-semibold"
                    >
                      {{ workspace.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <h4 class="font-medium text-gray-900">{{ workspace.name }}</h4>
                      <p v-if="workspace.description" class="text-sm text-gray-600">
                        {{ workspace.description }}
                      </p>
                    </div>
                  </div>

                  <div class="flex items-center space-x-4">
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                      {{ getRoleLabel(workspace.role) }}
                    </span>
                  </div>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SettingsSidebar from '@/Components/Settings/SettingsSidebar.vue'

const props = defineProps({
  userWorkspaces: {
    type: Array,
    default: () => []
  }
})

const getRoleLabel = (role) => {
  const roles = {
    'owner': 'Owner',
    'admin': 'Admin',
    'member': 'Member',
    'guest': 'Guest'
  }
  return roles[role] || role
}
</script>

