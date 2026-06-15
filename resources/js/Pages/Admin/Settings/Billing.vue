<template>
  <div class="max-w-2xl space-y-6">
    <form @submit.prevent="save" class="space-y-6">
      <!-- Stripe Configuration -->
      <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Stripe Configuration</h2>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Stripe Public Key
          </label>
          <input
            v-model="form.stripe_public_key"
            type="text"
            placeholder="pk_live_..."
            autocomplete="off"
            spellcheck="false"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
          />
          <p class="text-xs text-gray-500 mt-1">Found in your Stripe Dashboard settings</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Stripe Secret Key
          </label>
          <input
            v-model="form.stripe_secret_key"
            type="password"
            placeholder="sk_live_..."
            autocomplete="off"
            spellcheck="false"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
          />
          <p class="text-xs text-gray-500 mt-1">Keep this secret - never share publicly</p>
        </div>
      </div>

      <!-- Trial Configuration -->
      <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Free Trial Settings</h2>

        <label class="flex items-center gap-3 cursor-pointer">
          <input
            v-model="form.trial_enabled"
            type="checkbox"
            class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
          />
          <span class="text-sm font-medium text-gray-900">Enable free trial for new users</span>
        </label>

        <transition name="fade">
          <div v-if="form.trial_enabled" class="pt-4 border-t border-gray-200">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Trial Duration (days)
              </label>
              <div class="flex items-center gap-3">
                <input
                  v-model.number="form.trial_days"
                  type="number"
                  min="1"
                  class="w-24 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                />
                <span class="text-sm text-gray-600">days</span>
              </div>
              <p class="text-xs text-gray-500 mt-1">New users will get {{ form.trial_days }} days free access</p>
            </div>
          </div>
        </transition>
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
  stripe_public_key: props.settings?.stripe_public_key ?? '',
  stripe_secret_key: props.settings?.stripe_secret_key ?? '',
  trial_enabled: props.settings?.trial_enabled === '1',
  trial_days: parseInt(props.settings?.trial_days) || 14,
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
