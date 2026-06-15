<template>
  <AppLayout :user-workspaces="userWorkspaces" :current-workspace="currentWorkspace" :user-role="userRole">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <!-- Sidebar Navigation -->
          <SettingsSidebar />

          <!-- Main Content -->
          <div class="md:col-span-3 space-y-6">
            <!-- Header -->
            <div class="mb-4">
              <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Plans & Subscriptions</h1>
              <p class="text-slate-600 dark:text-slate-400 mt-1">Manage your subscription and billing</p>
            </div>

            <!-- Current Subscription -->
            <div v-if="currentSubscription" class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl p-8 mb-8 border border-slate-200/50 dark:border-slate-700/50">
          <div class="flex items-start justify-between mb-6">
            <div>
              <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Current Plan</h2>
              <p class="text-slate-600 dark:text-slate-400 mt-1">Your active subscription</p>
            </div>
            <div class="flex items-center gap-3">
              <!-- Tier Status Badge -->
              <span v-if="showTrial" class="px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 font-bold rounded-lg text-sm">
                Trial
              </span>
              <span v-else class="px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 font-bold rounded-lg text-sm">
                {{ tierStatus }}
              </span>
              <span class="px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 font-bold rounded-lg text-sm">
                ✓ Active
              </span>
            </div>
          </div>

          <div v-if="showTrial" class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700/50 rounded-xl p-4">
            <div class="flex items-start gap-3">
              <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
              <div>
                <p class="text-sm font-semibold text-blue-900 dark:text-blue-200">Your trial period ends on {{ formatDate(trialEndsAt) }}</p>
                <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">After this, your subscription will continue with the {{ currentSubscription?.plan.name }} plan unless you cancel.</p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Plan Details -->
            <div class="md:col-span-2">
              <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-6 border border-blue-200 dark:border-blue-700/50">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">{{ currentSubscription.plan.name }}</h3>
                
                <div class="grid grid-cols-2 gap-4 mb-6">
                  <div>
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-1">Price</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">${{ currentSubscription.plan.price }}</p>
                    <p class="text-sm text-slate-600 dark:text-slate-400">per {{ currentSubscription.plan.billing_cycle }}</p>
                  </div>
                  <div>
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-1">Started</p>
                    <p class="text-lg font-bold text-slate-900 dark:text-white">{{ formatDate(currentSubscription.started_at) }}</p>
                  </div>
                </div>

                <!-- Expiration -->
                <div v-if="currentSubscription.expires_at" class="mb-4">
                  <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-2">Expires</p>
                  <div class="flex items-baseline gap-2">
                    <p class="text-lg font-bold text-slate-900 dark:text-white">{{ formatDate(currentSubscription.expires_at) }}</p>
                    <span class="text-sm font-semibold px-3 py-1 rounded-full" :class="currentSubscription.days_remaining > 30 ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : currentSubscription.days_remaining > 0 ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300'">
                      {{ currentSubscription.days_remaining }} days
                    </span>
                  </div>
                </div>

                <!-- Source Badge -->
                <div class="inline-flex gap-1 px-3 py-1 rounded-full text-xs font-semibold" :class="currentSubscription.source === 'purchase' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300' : 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300'">
                  {{ currentSubscription.source === 'purchase' ? '💳 Purchased' : '👤 Admin Assigned' }}
                </div>
              </div>
            </div>

            <!-- Features -->
            <div>
              <h4 class="font-bold text-slate-900 dark:text-white mb-4">Features</h4>
              <ul class="space-y-3">
                <li v-for="feature in currentSubscription.plan.features" :key="feature" class="flex items-start gap-3">
                  <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                  <span class="text-sm text-slate-700 dark:text-slate-300">{{ feature }}</span>
                </li>
              </ul>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-wrap gap-3">
            <button
              @click="showChangePlanModal = true"
              class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition"
            >
              Change Plan
            </button>
            <button
              @click="showRebuyModal = true"
              class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition"
            >
              Renew Subscription
            </button>
            <button
              @click="showCancelModal = true"
              class="px-6 py-2 border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg font-semibold transition"
            >
              Cancel Subscription
            </button>
          </div>
        </div>

            <!-- No Subscription Message -->
            <div v-else class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl p-8 mb-8 border border-slate-200/50 dark:border-slate-700/50">
          <div class="text-center py-8">
            <svg class="w-16 h-16 text-slate-300 dark:text-slate-600 mx-auto mb-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/></svg>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">No Active Subscription</h3>
            <p class="text-slate-600 dark:text-slate-400 mb-6">You don't have an active subscription. Choose a plan below to get started.</p>
          </div>
        </div>

            <!-- Available Plans -->
            <div class="mb-8">
              <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">Available Plans</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
              v-for="plan in availablePlans"
              :key="plan.id"
              class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-lg p-8 border-2 border-slate-200/50 dark:border-slate-700/50 hover:border-blue-500/50 dark:hover:border-blue-500/50 transition-all"
              :class="plan.is_current ? 'ring-2 ring-green-500' : ''"
            >
              <!-- Current Badge -->
              <div v-if="plan.is_current" class="absolute top-4 right-4 px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 font-bold rounded-lg text-xs">
                Current Plan
              </div>

              <!-- Plan Name and Price -->
              <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">{{ plan.name }}</h3>
              <div class="mb-6">
                <p class="text-4xl font-bold text-slate-900 dark:text-white">${{ plan.price }}</p>
                <p class="text-slate-600 dark:text-slate-400 text-sm">{{ plan.billing_cycle }}</p>
              </div>

              <!-- Features -->
              <ul class="space-y-3 mb-8">
                <li v-for="feature in plan.features" :key="feature" class="flex items-start gap-3">
                  <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                  <span class="text-sm text-slate-700 dark:text-slate-300">{{ feature }}</span>
                </li>
              </ul>

              <!-- Action Button -->
              <button
                v-if="!plan.is_current"
                @click="selectPlanForChange(plan)"
                class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition"
              >
                Choose Plan
              </button>
              <button
                v-else
                disabled
                class="w-full px-4 py-3 bg-gray-300 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-semibold cursor-not-allowed"
              >
                Current Plan
              </button>
            </div>
          </div>
        </div>

            <!-- Subscription History -->
            <div v-if="subscriptionHistory.length" class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl p-8 border border-slate-200/50 dark:border-slate-700/50">
              <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">Subscription History</h2>
          
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b-2 border-slate-200 dark:border-slate-700">
                  <th class="text-left py-4 px-4 text-sm font-bold text-slate-900 dark:text-white uppercase">Plan</th>
                  <th class="text-left py-4 px-4 text-sm font-bold text-slate-900 dark:text-white uppercase">Started</th>
                  <th class="text-left py-4 px-4 text-sm font-bold text-slate-900 dark:text-white uppercase">Expired</th>
                  <th class="text-left py-4 px-4 text-sm font-bold text-slate-900 dark:text-white uppercase">Status</th>
                  <th class="text-left py-4 px-4 text-sm font-bold text-slate-900 dark:text-white uppercase">Source</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="subscription in subscriptionHistory" :key="subscription.id" class="border-b border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                  <td class="py-4 px-4 text-sm font-semibold text-slate-900 dark:text-white">{{ subscription.plan_name }}</td>
                  <td class="py-4 px-4 text-sm text-slate-600 dark:text-slate-400">{{ formatDate(subscription.started_at) }}</td>
                  <td class="py-4 px-4 text-sm text-slate-600 dark:text-slate-400">{{ subscription.expires_at ? formatDate(subscription.expires_at) : '—' }}</td>
                  <td class="py-4 px-4 text-sm">
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold" :class="subscription.is_current ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : subscription.status === 'expired' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' : 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300'">
                      {{ subscription.is_current ? 'Active' : subscription.status }}
                    </span>
                  </td>
                  <td class="py-4 px-4 text-sm">
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold" :class="subscription.source === 'purchase' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300' : 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300'">
                      {{ subscription.source === 'purchase' ? '💳 Purchased' : '👤 Admin' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Change Plan Modal -->
    <div v-if="showChangePlanModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-sm w-full">
        <div class="p-8">
          <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Change Plan</h2>
          <p class="text-slate-600 dark:text-slate-400 text-sm mb-6">Are you sure you want to change to the <strong>{{ selectedPlanForChange?.name }}</strong> plan?</p>
          
          <div class="flex items-center justify-end gap-3">
            <button
              @click="showChangePlanModal = false"
              class="px-5 py-2 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition"
            >
              Cancel
            </button>
            <button
              @click="confirmChangePlan"
              class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition"
            >
              Change Plan
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Renew Modal -->
    <div v-if="showRebuyModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-sm w-full">
        <div class="p-8">
          <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Renew Subscription</h2>
          <p class="text-slate-600 dark:text-slate-400 text-sm mb-6">Renew your subscription to {{ currentSubscription?.plan.name }}? This will extend your subscription for another {{ currentSubscription?.plan.billing_cycle }}.</p>
          
          <div class="flex items-center justify-end gap-3">
            <button
              @click="showRebuyModal = false"
              class="px-5 py-2 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition"
            >
              Cancel
            </button>
            <button
              @click="confirmRebuy"
              class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition"
            >
              Renew Now
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Cancel Modal -->
    <div v-if="showCancelModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-sm w-full">
        <div class="p-8">
          <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 mx-auto mb-4">
            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
          </div>
          <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-2 text-center">Cancel Subscription</h2>
          <p class="text-slate-600 dark:text-slate-400 text-sm mb-6 text-center">Are you sure you want to cancel your subscription? You will lose access to premium features.</p>
          
          <div class="flex items-center justify-end gap-3">
            <button
              @click="showCancelModal = false"
              class="px-5 py-2 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition"
            >
              Keep Subscription
            </button>
            <button
              @click="confirmCancel"
              class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition"
            >
              Cancel Subscription
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SettingsSidebar from '@/Components/Settings/SettingsSidebar.vue'

const page = usePage()

const props = defineProps({
  currentSubscription: Object,
  subscriptionHistory: Array,
  availablePlans: Array,
  userWorkspaces: Array,
  currentWorkspace: Object,
  userRole: String,
})

const showChangePlanModal = ref(false)
const showRebuyModal = ref(false)
const showCancelModal = ref(false)
const selectedPlanForChange = ref(null)

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const selectPlanForChange = (plan) => {
  selectedPlanForChange.value = plan
  showChangePlanModal.value = true
}

const confirmChangePlan = () => {
  router.post('/settings/subscriptions/change-plan', {
    plan_id: selectedPlanForChange.value.id,
  })
  showChangePlanModal.value = false
}

const confirmRebuy = () => {
  router.post('/settings/subscriptions/rebuy')
  showRebuyModal.value = false
}

const confirmCancel = () => {
  router.post('/settings/subscriptions/cancel')
  showCancelModal.value = false
}
</script>
