<template>
  <div class="max-w-2xl space-y-6">
    <form @submit.prevent="save" autocomplete="off" class="space-y-6">
      <!-- Stripe Keys from ENV -->
      <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Stripe API Keys</h2>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Stripe Key
          </label>
          <input
            v-model="form.stripe_key"
            type="text"
            name="stripe_publishable_key"
            placeholder="pk_live_..."
            autocomplete="off"
            spellcheck="false"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
          />
          <p v-if="settings?.stripe_key" class="text-xs text-gray-500 mt-1">Current value: {{ settings?.stripe_key }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Stripe Secret
          </label>
          <input
            v-model="form.stripe_secret"
            type="password"
            name="stripe_secret_key"
            placeholder="sk_live_..."
            autocomplete="new-password"
            spellcheck="false"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
          />
          <p v-if="settings?.stripe_secret" class="text-xs text-gray-500 mt-1">Current value: ••••••••</p>
        </div>
      </div>

      <!-- Trial Period from ENV -->
      <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Trial Period Settings</h2>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Free Trial Days
          </label>
          <div class="flex items-center gap-3">
            <input
              v-model.number="form.free_trial_days"
              type="number"
              name="trial_days_count"
              min="0"
              max="999"
              autocomplete="off"
              class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            />
            <span class="text-sm text-gray-600">days (0 = no trial)</span>
          </div>
          <p v-if="settings?.free_trial_days !== undefined && settings?.free_trial_days !== null" class="text-xs text-gray-500 mt-1">Current value: {{ settings?.free_trial_days }} days</p>
        </div>
      </div>

      <!-- Trial Users Limits -->
      <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Trial Users Limits</h2>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Workspaces per Trial User
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
            <span class="text-sm text-gray-600">workspace(s)</span>
          </div>
          <p v-if="settings?.workspace_for_trial_users !== undefined && settings?.workspace_for_trial_users !== null" class="text-xs text-gray-500 mt-1">Current value: {{ settings?.workspace_for_trial_users }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Projects per Workspace (Trial Users)
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
            <span class="text-sm text-gray-600">project(s)</span>
          </div>
          <p v-if="settings?.project_per_workspace_for_trial_users !== undefined && settings?.project_per_workspace_for_trial_users !== null" class="text-xs text-gray-500 mt-1">Current value: {{ settings?.project_per_workspace_for_trial_users }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Members per Project (Trial Users)
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
            <span class="text-sm text-gray-600">member(s)</span>
          </div>
          <p v-if="settings?.members_per_project_for_trial_users !== undefined && settings?.members_per_project_for_trial_users !== null" class="text-xs text-gray-500 mt-1">Current value: {{ settings?.members_per_project_for_trial_users }}</p>
        </div>
      </div>



      <!-- Save Button -->
      <div class="flex justify-end gap-3">
        <button
          type="submit"
          class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition text-sm"
        >
          Save Settings
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

