<template>
  <AdminLayout>
    <template #header>
      <h1 class="text-3xl font-bold text-gray-900">Admin Settings</h1>
    </template>

    <div class="w-full">
      <!-- Tabs Navigation -->
      <div class="bg-white rounded-lg shadow border-b border-gray-200">
        <div class="flex">
          <button
            @click="activeTab = 'billing'"
            :class="[
              'flex items-center gap-2 px-6 py-4 font-medium text-sm border-b-2 transition cursor-pointer',
              activeTab === 'billing'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-600 hover:text-gray-900'
            ]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h10m4 0a1 1 0 11-2 0m2 0a1 1 0 10-2 0m-4 0a1 1 0 11-2 0m2 0a1 1 0 10-2 0M3 5a2 2 0 012-2h14a2 2 0 012 2v2H3V5z" />
            </svg>
            Billing & Trial
          </button>
          <button
            @click="activeTab = 'plans'"
            :class="[
              'flex items-center gap-2 px-6 py-4 font-medium text-sm border-b-2 transition cursor-pointer',
              activeTab === 'plans'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-600 hover:text-gray-900'
            ]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            Plans
          </button>
          <button
            @click="activeTab = 'social-login'"
            :class="[
              'flex items-center gap-2 px-6 py-4 font-medium text-sm border-b-2 transition cursor-pointer',
              activeTab === 'social-login'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-600 hover:text-gray-900'
            ]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            Social Login
          </button>
        </div>
      </div>

      <!-- Tab Content -->
      <div class="mt-6">
        <BillingSettings v-if="activeTab === 'billing'" :settings="settings" />
        <PlansSettings v-if="activeTab === 'plans'" :plans="plans" />
        <SocialLoginSettings v-if="activeTab === 'social-login'" :google-settings="googleSettings" :google-status="googleStatus" />
      </div>
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
