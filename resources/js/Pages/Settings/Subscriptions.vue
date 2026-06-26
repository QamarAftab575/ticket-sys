<template>
  <AppLayout :user-workspaces="userWorkspaces" :current-workspace="currentWorkspace" :user-role="userRole">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <!-- Sidebar Navigation -->
          <SettingsSidebar :user-workspaces="userWorkspaces" />

          <!-- Main Content -->
          <div class="md:col-span-3 space-y-6">
            <!-- Header -->
            <div class="mb-4">
              <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $t('plans_subscriptions') }}</h1>
              <p class="text-slate-600 dark:text-slate-400 mt-1">{{ $t('manage_subscription_billing') }}</p>
            </div>

            <!-- Super Admin Banner -->
            <div v-if="isSuperAdmin" class="bg-gradient-to-r from-purple-600 via-purple-700 to-indigo-700 rounded-2xl shadow-2xl p-8 border border-purple-500/50">
              <div class="flex items-start gap-6">
                <div class="flex-shrink-0">
                  <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                  </div>
                </div>
                <div class="flex-1">
                  <div class="flex items-center gap-3 mb-2">
                    <h3 class="text-2xl font-bold text-white">{{ $t('super_admin_access') }}</h3>
                    <span class="px-3 py-1 bg-yellow-400 text-purple-900 text-xs font-bold rounded-full uppercase tracking-wide">{{ $t('unlimited') }}</span>
                  </div>
                  <p class="text-purple-100 text-lg mb-4">
                    {{ $t('unrestricted_access') }}
                  </p>
                  <div class="flex items-center gap-6 text-sm text-purple-200">
                    <div class="flex items-center gap-2">
                      <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                      </svg>
                      <span>{{ $t('unlimited_projects') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                      </svg>
                      <span>{{ $t('all_features') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                      </svg>
                      <span>{{ $t('priority_support') }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Trial Banner for Regular Users -->
            <div v-if="!isSuperAdmin && isOnTrial && !currentSubscription" class="bg-gradient-to-r from-blue-600 via-blue-700 to-cyan-600 rounded-2xl shadow-2xl p-8 border border-blue-500/50">
              <div class="flex items-start gap-6">
                <div class="flex-shrink-0">
                  <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                <div class="flex-1">
                  <div class="flex items-center gap-3 mb-2">
                    <h3 class="text-2xl font-bold text-white">{{ $t('free_trial_active') }}</h3>
                    <span class="px-3 py-1 bg-cyan-400 text-blue-900 text-xs font-bold rounded-full uppercase tracking-wide">{{ daysRemaining }} {{ $t('days_left') }}</span>
                  </div>
                  <p class="text-blue-100 text-lg mb-4">
                    {{ $t('your_trial_ends_on') }} <strong>{{ formatDate(trialEndsAt) }}</strong>. {{ $t('upgrade_to_paid_plan') }}
                  </p>
                  
                  <!-- Trial Usage Stats -->
                  <div v-if="trialUsage" class="grid grid-cols-3 gap-4 mb-4">
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3">
                      <div class="text-xs text-blue-200 mb-1">{{ $t('workspaces') }}</div>
                      <div class="text-2xl font-bold text-white">{{ trialUsage.workspaces.current }}/{{ trialUsage.workspaces.allowed }}</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3">
                      <div class="text-xs text-blue-200 mb-1">{{ $t('projects') }}</div>
                      <div class="text-2xl font-bold text-white">{{ trialUsage.projects.current }}/{{ trialUsage.projects.allowed }}</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3">
                      <div class="text-xs text-blue-200 mb-1">{{ $t('members') }}</div>
                      <div class="text-2xl font-bold text-white">{{ trialUsage.members.current }}/{{ trialUsage.members.allowed }}</div>
                    </div>
                  </div>
                  
                  <div class="flex items-center gap-4">
                    <a href="#plans" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-blue-600 font-bold rounded-lg hover:bg-blue-50 transition-colors shadow-lg">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                      </svg>
                      {{ $t('upgrade_now') }}
                    </a>
                    <div class="flex items-center gap-4 text-sm text-blue-200">
                      <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ $t('no_credit_card_req') }}</span>
                      </div>
                      <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ $t('cancel_anytime_sub') }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Current Subscription -->
            <div v-if="!isSuperAdmin && currentSubscription" class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl p-8 border border-slate-200/50 dark:border-slate-700/50">
              <div class="flex items-start justify-between mb-6">
                <div>
                  <h2 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $t('current_plan') }}</h2>
                  <p class="text-slate-600 dark:text-slate-400 mt-1">{{ $t('your_active_subscription') }}</p>
                </div>
                <div class="flex items-center gap-3">
                  <span v-if="showTrial" class="px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 font-bold rounded-lg text-sm">
                    {{ $t('trial') }}
                  </span>
                  <span v-else class="px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 font-bold rounded-lg text-sm">
                    {{ tierStatus }}
                  </span>
                  <span class="px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 font-bold rounded-lg text-sm">
                    ✓ {{ $t('active') }}
                  </span>
                </div>
              </div>

              <div v-if="showTrial" class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700/50 rounded-xl p-4">
                <div class="flex items-start gap-3">
                  <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                  <div>
                    <p class="text-sm font-semibold text-blue-900 dark:text-blue-200">{{ $t('trial_period_ends') }} {{ formatDate(trialEndsAt) }}</p>
                    <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">{{ $t('after_trial_continue') }} {{ currentSubscription?.plan.name }} {{ $t('plan') }} {{ $t('unless_you_cancel') }}</p>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="md:col-span-2">
                  <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-6 border border-blue-200 dark:border-blue-700/50">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">{{ currentSubscription.plan.name }}</h3>
                    
                    <div class="grid grid-cols-2 gap-4 mb-6">
                      <div>
                        <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-1">{{ $t('price') }}</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">${{ currentSubscription.plan.price }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">{{ $t('per') }} {{ currentSubscription.plan.billing_cycle }}</p>
                      </div>
                      <div>
                        <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-1">{{ $t('started') }}</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ formatDate(currentSubscription.started_at) }}</p>
                      </div>
                    </div>

                    <div v-if="currentSubscription.expires_at" class="mb-4">
                      <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-2">{{ $t('expires') }}</p>
                      <div class="flex items-baseline gap-2">
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ formatDate(currentSubscription.expires_at) }}</p>
                        <span class="text-sm font-semibold px-3 py-1 rounded-full" :class="currentSubscription.days_remaining > 30 ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : currentSubscription.days_remaining > 0 ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300'">
                          {{ currentSubscription.days_remaining }} {{ $t('days') }}
                        </span>
                      </div>
                    </div>

                    <div class="inline-flex gap-1 px-3 py-1 rounded-full text-xs font-semibold" :class="currentSubscription.source === 'purchase' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300' : 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300'">
                      {{ currentSubscription.source === 'purchase' ? $t('purchased') : $t('admin_assigned') }}
                    </div>
                  </div>
                </div>

                <div>
                  <h4 class="font-bold text-slate-900 dark:text-white mb-4">{{ $t('features') }}</h4>
                  <ul class="space-y-3">
                    <li v-for="feature in currentSubscription.plan.features" :key="feature" class="flex items-start gap-3">
                      <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                      <span class="text-sm text-slate-700 dark:text-slate-300">{{ feature }}</span>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="flex flex-wrap gap-3">
                <button
                  @click="showChangePlanModal = true"
                  class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition"
                >
                  {{ $t('change_plan_btn') }}
                </button>
                <button
                  @click="showRebuyModal = true"
                  class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition"
                >
                  {{ $t('renew_subscription') }}
                </button>
              </div>
            </div>

            <!-- No Subscription Message -->
            <div v-if="!isSuperAdmin && !currentSubscription" class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl p-8 border border-slate-200/50 dark:border-slate-700/50">
              <div class="text-center py-8">
                <svg class="w-16 h-16 text-slate-300 dark:text-slate-600 mx-auto mb-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/></svg>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">{{ $t('no_active_subscription') }}</h3>
                <p class="text-slate-600 dark:text-slate-400 mb-6">{{ $t('no_subscription_desc') }}</p>
              </div>
            </div>

            <!-- Available Plans -->
            <div v-if="!isSuperAdmin" id="plans" class="mb-8">
              <div class="mb-8">
                <h2 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">{{ $t('all_plans') }}</h2>
                <p class="text-slate-600 dark:text-slate-400">{{ $t('choose_perfect_plan') }}</p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                  v-for="plan in availablePlans"
                  :key="plan.id"
                  :class="[
                    'relative rounded-2xl transition-all duration-300',
                    plan.is_current
                      ? 'bg-gradient-to-br from-blue-600 to-blue-700 shadow-2xl scale-105 ring-2 ring-blue-400'
                      : 'bg-white dark:bg-slate-900 shadow-lg hover:shadow-xl border border-slate-200 dark:border-slate-700 hover:border-blue-300 dark:hover:border-blue-600'
                  ]"
                >
                  <!-- Current Plan Badge -->
                  <div v-if="plan.is_current" class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1.5 bg-gradient-to-r from-green-400 to-emerald-500 text-white font-bold rounded-full text-sm shadow-lg">
                    ✓ {{ $t('your_current_plan') }}
                  </div>

                  <!-- Popular Badge -->
                  <div v-if="plan.highlighted && !plan.is_current" class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1.5 bg-gradient-to-r from-amber-400 to-orange-500 text-white font-bold rounded-full text-sm shadow-lg">
                    {{ $t('most_popular') }}
                  </div>

                  <div class="p-8">
                    <!-- Plan Name -->
                    <h3 :class="[
                      'text-2xl font-bold mb-3',
                      plan.is_current ? 'text-white' : 'text-slate-900 dark:text-white'
                    ]">
                      {{ plan.name }}
                    </h3>

                    <!-- Description -->
                    <p :class="[
                      'text-sm mb-6',
                      plan.is_current ? 'text-blue-100' : 'text-slate-600 dark:text-slate-400'
                    ]">
                      {{ plan.description }}
                    </p>

                    <!-- Price Section -->
                    <div class="mb-8">
                      <div class="flex items-baseline gap-2 mb-2">
                        <span :class="[
                          'text-5xl font-bold',
                          plan.is_current ? 'text-white' : 'text-slate-900 dark:text-white'
                        ]">
                          {{ plan.currency_sign }}{{ plan.price }}
                        </span>
                        <span :class="[
                          'text-sm font-medium',
                          plan.is_current ? 'text-blue-100' : 'text-slate-600 dark:text-slate-400'
                        ]">
                          {{ plan.billing_cycle }}
                        </span>
                      </div>
                      <p v-if="plan.price === 0" :class="[
                        'text-sm',
                        plan.is_current ? 'text-blue-100' : 'text-slate-500 dark:text-slate-500'
                      ]">
                        {{ $t('start_free_14_day') }}
                      </p>
                      <p :class="[
                        'text-xs mt-1',
                        plan.is_current ? 'text-blue-100' : 'text-slate-500 dark:text-slate-500'
                      ]">
                        {{ $t('currency') }}: {{ plan.currency }}
                      </p>
                    </div>

                    <!-- Divider -->
                    <div :class="[
                      'h-px mb-8',
                      plan.is_current ? 'bg-blue-400/50' : 'bg-slate-200 dark:bg-slate-700'
                    ]"></div>

                    <!-- Features List -->
                    <ul class="space-y-4 mb-8">
                      <li v-for="(feature, index) in plan.features.slice(0, 4)" :key="index" class="flex items-start gap-3">
                        <svg :class="[
                          'w-5 h-5 flex-shrink-0 mt-0.5',
                          plan.is_current ? 'text-blue-100' : 'text-green-500 dark:text-green-400'
                        ]" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span :class="[
                          'text-sm font-medium',
                          plan.is_current ? 'text-white' : 'text-slate-700 dark:text-slate-300'
                        ]">
                          {{ feature }}
                        </span>
                      </li>
                    </ul>

                    <!-- View All Features Link -->
                    <button v-if="plan.features.length > 4" class="text-sm font-semibold mb-8 transition" :class="[
                      plan.is_current ? 'text-blue-100 hover:text-white' : 'text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300'
                    ]">
                      + {{ plan.features.length - 4 }} {{ $t('more_features') }}
                    </button>

                    <!-- Action Button -->
                    <div v-if="plan.is_current" class="space-y-3">
                      <button
                        @click="showRebuyModal = true"
                        class="w-full px-4 py-3 bg-white hover:bg-blue-50 text-blue-600 font-semibold rounded-lg transition duration-200 flex items-center justify-center gap-2"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        {{ $t('renew_plan') }}
                      </button>
                      <button
                        @click="showChangePlanModal = true"
                        class="w-full px-4 py-2 border border-blue-200 text-blue-100 hover:bg-blue-500/20 font-semibold rounded-lg transition duration-200"
                      >
                        {{ $t('change_plan') }}
                      </button>
                    </div>
                    <div v-else>
                      <button
                        @click="selectPlanForChange(plan)"
                        class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-200 flex items-center justify-center gap-2"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        {{ $t('upgrade_to_plan').replace('{plan}', plan.name) }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Subscription History -->
            <div v-if="!isSuperAdmin && subscriptionHistory && subscriptionHistory.length" class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl p-8 border border-slate-200/50 dark:border-slate-700/50">
              <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">{{ $t('subscription_history') }}</h2>
          
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead>
                    <tr class="border-b-2 border-slate-200 dark:border-slate-700">
                      <th class="text-left py-4 px-4 text-sm font-bold text-slate-900 dark:text-white uppercase">{{ $t('plan') }}</th>
                      <th class="text-left py-4 px-4 text-sm font-bold text-slate-900 dark:text-white uppercase">{{ $t('started') }}</th>
                      <th class="text-left py-4 px-4 text-sm font-bold text-slate-900 dark:text-white uppercase">{{ $t('expires') }}</th>
                      <th class="text-left py-4 px-4 text-sm font-bold text-slate-900 dark:text-white uppercase">{{ $t('status') }}</th>
                      <th class="text-left py-4 px-4 text-sm font-bold text-slate-900 dark:text-white uppercase">{{ $t('source') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="subscription in subscriptionHistory" :key="subscription.id" class="border-b border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                      <td class="py-4 px-4 text-sm font-semibold text-slate-900 dark:text-white">{{ subscription.plan_name }}</td>
                      <td class="py-4 px-4 text-sm text-slate-600 dark:text-slate-400">{{ formatDate(subscription.started_at) }}</td>
                      <td class="py-4 px-4 text-sm text-slate-600 dark:text-slate-400">{{ subscription.expires_at ? formatDate(subscription.expires_at) : '—' }}</td>
                      <td class="py-4 px-4 text-sm">
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold" :class="subscription.is_current ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : subscription.status === 'expired' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' : 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300'">
                          {{ subscription.is_current ? $t('active') : subscription.status }}
                        </span>
                      </td>
                      <td class="py-4 px-4 text-sm">
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold" :class="subscription.source === 'purchase' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300' : 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300'">
                          {{ subscription.source === 'purchase' ? $t('purchased') : $t('admin_assigned') }}
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
    </div>

    <!-- Change Plan Modal -->
    <div v-if="showChangePlanModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-sm w-full">
        <div class="p-8">
          <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ $t('change_plan') }}</h2>
          <p class="text-slate-600 dark:text-slate-400 text-sm mb-6">{{ $t('are_you_sure_change_plan').replace('{plan}', selectedPlanForChange?.name) }}</p>
          
          <div class="flex items-center justify-end gap-3">
            <button
              @click="showChangePlanModal = false"
              class="px-5 py-2 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition"
            >
              {{ $t('cancel') }}
            </button>
            <button
              @click="confirmChangePlan"
              class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition"
            >
              {{ $t('change_plan') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Renew Modal -->
    <div v-if="showRebuyModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-sm w-full">
        <div class="p-8">
          <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ $t('renew_subscription_modal') }}</h2>
          <p class="text-slate-600 dark:text-slate-400 text-sm mb-6">{{ $t('renew_extend_subscription').replace('{plan}', currentSubscription?.plan.name).replace('{billing_cycle}', currentSubscription?.plan.billing_cycle) }}</p>
          
          <div class="flex items-center justify-end gap-3">
            <button
              @click="showRebuyModal = false"
              class="px-5 py-2 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition"
            >
              {{ $t('cancel') }}
            </button>
            <button
              @click="confirmRebuy"
              class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition"
            >
              {{ $t('renew_now') }}
            </button>
          </div>
        </div>
      </div>
    </div>


  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
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
  isSuperAdmin: Boolean,
  isOnTrial: Boolean,
  daysRemaining: Number,
  trialEndsAt: String,
  trialUsage: Object,
})

const showChangePlanModal = ref(false)
const showRebuyModal = ref(false)
const selectedPlanForChange = ref(null)

const showTrial = computed(() => {
  return props.currentSubscription?.is_trial ?? false
})

const tierStatus = computed(() => {
  return props.currentSubscription?.plan.name ?? 'Free'
})

const trialEndsAt = computed(() => {
  return props.currentSubscription?.trial_ends_at ?? null
})

const formatDate = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const selectPlanForChange = (plan) => {
  selectedPlanForChange.value = plan
  // For trial users, go directly to Stripe checkout without modal
  if (props.isOnTrial) {
    confirmChangePlan()
  } else {
    // For existing subscribers, show confirmation modal
    showChangePlanModal.value = true
  }
}

const confirmChangePlan = () => {
  // Create a hidden form and submit it to handle the redirect properly
  const form = document.createElement('form')
  form.method = 'POST'
  form.action = '/settings/subscriptions/change-plan'
  form.style.display = 'none'

  // Add CSRF token
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  if (token) {
    const csrfInput = document.createElement('input')
    csrfInput.type = 'hidden'
    csrfInput.name = '_token'
    csrfInput.value = token
    form.appendChild(csrfInput)
  }

  // Add plan_id
  const planInput = document.createElement('input')
  planInput.type = 'hidden'
  planInput.name = 'plan_id'
  planInput.value = selectedPlanForChange.value.id
  form.appendChild(planInput)

  document.body.appendChild(form)
  form.submit()
  
  showChangePlanModal.value = false
}

const confirmRebuy = () => {
  // Create a hidden form and submit it to handle the redirect properly
  const form = document.createElement('form')
  form.method = 'POST'
  form.action = '/settings/subscriptions/rebuy'
  form.style.display = 'none'

  // Add CSRF token
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  if (token) {
    const csrfInput = document.createElement('input')
    csrfInput.type = 'hidden'
    csrfInput.name = '_token'
    csrfInput.value = token
    form.appendChild(csrfInput)
  }

  document.body.appendChild(form)
  form.submit()
  
  showRebuyModal.value = false
}
</script>
