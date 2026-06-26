<template>
  <div class="w-full space-y-6">
    <form @submit.prevent="save" autocomplete="off" class="space-y-6">
      <!-- Stripe Keys Section -->
      <div class="bg-white rounded-lg shadow p-8 space-y-6">
        <div class="flex items-center gap-3 mb-6">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h10m4 0a1 1 0 11-2 0m2 0a1 1 0 10-2 0m-4 0a1 1 0 11-2 0m2 0a1 1 0 10-2 0M3 5a2 2 0 012-2h14a2 2 0 012 2v2H3V5z" />
          </svg>
          <h2 class="text-xl font-semibold text-gray-900">{{ $t('stripe_api_keys') }}</h2>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ $t('stripe_publishable_key') }}
            </label>
            <input
              v-model="form.stripe_key"
              type="text"
              name="stripe_publishable_key"
              :placeholder="$t('stripe_publishable_key_placeholder')"
              autocomplete="off"
              spellcheck="false"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ $t('stripe_secret_key') }}
            </label>
            <input
              v-model="form.stripe_secret"
              type="password"
              name="stripe_secret_key"
              :placeholder="$t('stripe_secret_key_placeholder')"
              autocomplete="new-password"
              spellcheck="false"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            />
          </div>
        </div>
      </div>

      <!-- Trial Period Settings Section -->
      <div class="bg-white rounded-lg shadow p-8 space-y-6">
        <div class="flex items-center gap-3 mb-6">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h2 class="text-xl font-semibold text-gray-900">{{ $t('trial_period_settings') }}</h2>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ $t('free_trial_days') }}
          </label>
          <div class="flex items-center gap-3 max-w-xs">
            <input
              v-model.number="form.free_trial_days"
              type="number"
              name="trial_days_count"
              min="0"
              max="999"
              autocomplete="off"
              class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            />
            <span class="text-sm text-gray-600">{{ $t('days_no_trial') }}</span>
          </div>
        </div>
      </div>

      <!-- Trial Users Limits Section -->
      <div class="bg-white rounded-lg shadow p-8 space-y-6">
        <div class="flex items-center gap-3 mb-6">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3.654a1 1 0 01-.894-1.447l5.394-7.72A6 6 0 1113.16 21z" />
          </svg>
          <h2 class="text-xl font-semibold text-gray-900">{{ $t('trial_users_limits') }}</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ $t('workspaces_per_trial_user') }}
            </label>
            <div class="flex items-center gap-3">
              <input
                v-model.number="form.workspace_for_trial_users"
                type="number"
                name="workspace_trial_limit"
                min="1"
                max="999"
                autocomplete="off"
                class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
              />
              <span class="text-sm text-gray-600">{{ $t('workspace_trial_limit_unit') }}</span>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ $t('projects_per_workspace_trial') }}
            </label>
            <div class="flex items-center gap-3">
              <input
                v-model.number="form.project_per_workspace_for_trial_users"
                type="number"
                name="project_workspace_trial_limit"
                min="1"
                max="999"
                autocomplete="off"
                class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
              />
              <span class="text-sm text-gray-600">{{ $t('projects_trial_limit_unit') }}</span>
            </div>
          </div>

          <div class="lg:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ $t('members_per_project_trial') }}
            </label>
            <div class="flex items-center gap-3">
              <input
                v-model.number="form.members_per_project_for_trial_users"
                type="number"
                name="members_project_trial_limit"
                min="1"
                max="999"
                autocomplete="off"
                class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
              />
              <span class="text-sm text-gray-600">{{ $t('members_trial_limit_unit') }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="flex justify-end">
        <button
          type="submit"
          class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition text-sm flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          {{ $t('save_settings') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  settings: Object,
})

const form = reactive({
  stripe_key: props.settings?.stripe_key ?? '',
  stripe_secret: props.settings?.stripe_secret ?? '',
  free_trial_days: parseInt(props.settings?.free_trial_days) || 10,
  workspace_for_trial_users: parseInt(props.settings?.workspace_for_trial_users) || 1,
  project_per_workspace_for_trial_users: parseInt(props.settings?.project_per_workspace_for_trial_users) || 5,
  members_per_project_for_trial_users: parseInt(props.settings?.members_per_project_for_trial_users) || 20,
})

const save = () => {
  router.post('/admin/settings', form)
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
