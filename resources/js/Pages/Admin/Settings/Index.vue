<template>
  <AdminLayout>
    <template #header>
      <h1 class="text-3xl font-bold text-gray-900">Admin Settings</h1>
    </template>

    <div class="space-y-6">
      <!-- Tabs Navigation -->
      <div class="bg-white rounded-lg shadow border-b border-gray-200">
        <div class="flex">
          <button
            @click="activeTab = 'billing'"
            :class="[
              'px-6 py-4 font-medium text-sm border-b-2 transition',
              activeTab === 'billing'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-600 hover:text-gray-900'
            ]"
          >
            💳 Billing & Trial
          </button>
          <button
            @click="activeTab = 'plans'"
            :class="[
              'px-6 py-4 font-medium text-sm border-b-2 transition',
              activeTab === 'plans'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-600 hover:text-gray-900'
            ]"
          >
            📦 Plans
          </button>
          <button
            @click="activeTab = 'social-login'"
            :class="[
              'px-6 py-4 font-medium text-sm border-b-2 transition',
              activeTab === 'social-login'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-600 hover:text-gray-900'
            ]"
          >
            🔐 Social Login
          </button>
        </div>
      </div>

      <!-- Tab Content -->
      <BillingSettings v-if="activeTab === 'billing'" :settings="settings" />
      <PlansSettings v-if="activeTab === 'plans'" :plans="plans" />
      <SocialLoginSettings v-if="activeTab === 'social-login'" :google-settings="googleSettings" :google-status="googleStatus" />
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import BillingSettings from './Billing.vue'
import PlansSettings from './Plans.vue'
import SocialLoginSettings from './SocialLogin.vue'

defineProps({
  settings: Object,
  plans: Array,
  googleSettings: Object,
  googleStatus: Object,
})

const activeTab = ref('billing')
</script>
