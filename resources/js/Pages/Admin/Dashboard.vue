<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-4xl font-bold text-gray-900">Dashboard</h1>
          <p class="text-gray-600 mt-1">Welcome to your admin panel</p>
        </div>
        <div class="text-sm text-gray-500">
          Last updated: {{ lastUpdated }}
        </div>
      </div>
    </template>

    <div class="space-y-8">
      <!-- Key Metrics Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Users -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl shadow-lg p-6 border border-blue-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-600 text-sm font-semibold uppercase tracking-wide">Total Users</p>
              <p class="text-4xl font-bold text-blue-900 mt-3">{{ formatNumber(stats.total_users) }}</p>
              <p class="text-blue-600 text-xs mt-3">
                <span class="font-bold text-green-600">+{{ stats.new_users_today }}</span> new today
              </p>
            </div>
            <div class="w-16 h-16 bg-blue-200 rounded-full flex items-center justify-center opacity-80">
              <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM16 12a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Total Workspaces -->
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl shadow-lg p-6 border border-purple-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-purple-600 text-sm font-semibold uppercase tracking-wide">Workspaces</p>
              <p class="text-4xl font-bold text-purple-900 mt-3">{{ formatNumber(stats.total_workspaces) }}</p>
              <p class="text-purple-600 text-xs mt-3">Active organizations</p>
            </div>
            <div class="w-16 h-16 bg-purple-200 rounded-full flex items-center justify-center opacity-80">
              <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4l2-3h2l2 3h4a2 2 0 012 2v14a2 2 0 01-2 2z" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Total Projects -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl shadow-lg p-6 border border-green-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-green-600 text-sm font-semibold uppercase tracking-wide">Projects</p>
              <p class="text-4xl font-bold text-green-900 mt-3">{{ formatNumber(stats.total_projects) }}</p>
              <p class="text-green-600 text-xs mt-3">Across all workspaces</p>
            </div>
            <div class="w-16 h-16 bg-green-200 rounded-full flex items-center justify-center opacity-80">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Total Tasks -->
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl shadow-lg p-6 border border-orange-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-orange-600 text-sm font-semibold uppercase tracking-wide">Tasks</p>
              <p class="text-4xl font-bold text-orange-900 mt-3">{{ formatNumber(stats.total_tasks) }}</p>
              <p class="text-orange-600 text-xs mt-3">Total tasks created</p>
            </div>
            <div class="w-16 h-16 bg-orange-200 rounded-full flex items-center justify-center opacity-80">
              <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Secondary Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- New Signups Today -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
          <p class="text-gray-600 text-sm font-medium">New Signups Today</p>
          <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.new_users_today }}</p>
          <div class="mt-4 text-xs text-gray-500">
            ðŸ“ˆ Trending
          </div>
        </div>

        <!-- New Signups Week -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-indigo-500">
          <p class="text-gray-600 text-sm font-medium">New Signups This Week</p>
          <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.new_users_week }}</p>
          <div class="mt-4 text-xs text-gray-500">
            Avg {{ (stats.new_users_week / 7).toFixed(1) }} per day
          </div>
        </div>

        <!-- New Signups Month -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-cyan-500">
          <p class="text-gray-600 text-sm font-medium">New Signups This Month</p>
          <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.new_users_month }}</p>
          <div class="mt-4 text-xs text-gray-500">
            Avg {{ (stats.new_users_month / 30).toFixed(1) }} per day
          </div>
        </div>

        <!-- Active Trials -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-teal-500">
          <p class="text-gray-600 text-sm font-medium">Active Trials</p>
          <p class="text-3xl font-bold text-teal-600 mt-2">{{ stats.active_trials }}</p>
          <div class="mt-4 text-xs text-gray-500">
            Trial users
          </div>
        </div>
      </div>

      <!-- Subscription Overview Section -->
      <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-900">Subscription Overview</h2>
          <p class="text-sm text-gray-600 mt-1">Real-time subscription and revenue metrics</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Active Subscriptions KPI -->
          <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl shadow-md p-8 border border-emerald-200 hover:shadow-xl transition-shadow">
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <p class="text-emerald-700 text-sm font-bold uppercase tracking-wide mb-3">Active Subscriptions</p>
                <p class="text-5xl font-black text-emerald-900 mb-4">{{ formatNumber(stats.active_subscriptions) }}</p>
                
                <div class="space-y-2">
                  <div class="flex items-center gap-2">
                    <svg v-if="stats.subscription_growth >= 0" class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd" />
                    </svg>
                    <span :class="stats.subscription_growth >= 0 ? 'text-emerald-700' : 'text-red-700'" class="text-sm font-bold">
                      {{ stats.subscription_growth }}% vs last month
                    </span>
                  </div>
                  
                  <div class="text-sm text-emerald-600 font-medium">
                    MRR: ${{ formatNumber(stats.mrr) }}
                  </div>
                </div>
              </div>
              
              <div class="w-16 h-16 bg-emerald-200/50 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-8 h-8 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Expired Subscriptions KPI -->
          <div class="bg-gradient-to-br from-rose-50 to-rose-100 rounded-xl shadow-md p-8 border border-rose-200 hover:shadow-xl transition-shadow">
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <p class="text-rose-700 text-sm font-bold uppercase tracking-wide mb-3">Expired Subscriptions</p>
                <p class="text-5xl font-black text-rose-900 mb-4">{{ formatNumber(stats.expired_subscriptions) }}</p>
                
                <div class="space-y-2">
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-bold text-rose-700">
                      {{ stats.expired_this_month }} expired this month
                    </span>
                  </div>
                  
                  <div class="text-sm text-rose-600 font-medium">
                    Lost: ${{ formatNumber(stats.potential_revenue_lost) }}
                  </div>
                  
                  <div class="mt-2 inline-flex items-center gap-1 px-2 py-1 bg-rose-200 text-rose-800 rounded-md text-xs font-semibold">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Upgrade opportunity
                  </div>
                </div>
              </div>
              
              <div class="w-16 h-16 bg-rose-200/50 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-8 h-8 text-rose-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Total Revenue KPI -->
          <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-md p-8 border border-blue-200 hover:shadow-xl transition-shadow">
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <p class="text-blue-700 text-sm font-bold uppercase tracking-wide mb-3">Total Subscription Revenue</p>
                <p class="text-5xl font-black text-blue-900 mb-4">${{ formatNumber(stats.total_revenue) }}</p>
                
                <div class="space-y-2">
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-bold text-blue-700">
                      This month: ${{ formatNumber(stats.monthly_revenue) }}
                    </span>
                  </div>
                  
                  <div class="text-sm text-blue-600 font-medium">
                    Previous: ${{ formatNumber(stats.previous_month_revenue) }}
                  </div>
                  
                  <div class="flex items-center gap-1 mt-2">
                    <svg v-if="stats.revenue_growth >= 0" class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd" />
                    </svg>
                    <span :class="stats.revenue_growth >= 0 ? 'text-green-700' : 'text-red-700'" class="text-sm font-bold">
                      {{ stats.revenue_growth }}% growth
                    </span>
                  </div>
                </div>
              </div>
              
              <div class="w-16 h-16 bg-blue-200/50 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-8 h-8 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Revenue & Subscription Trends Chart -->
      <div class="bg-white rounded-2xl shadow-lg p-8">
        <div class="mb-6">
          <h2 class="text-2xl font-bold text-gray-900">Revenue & Subscription Trends</h2>
          <p class="text-sm text-gray-600 mt-1">Last 12 months performance overview</p>
        </div>
        <div class="relative" style="height: 400px;">
          <canvas id="revenueSubscriptionTrendChart"></canvas>
        </div>
        
        <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t border-gray-200">
          <div class="text-center">
            <div class="flex items-center justify-center gap-2 mb-2">
              <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
              <span class="text-sm font-medium text-gray-700">Active Subscriptions</span>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.active_subscriptions) }}</p>
          </div>
          <div class="text-center border-l border-r border-gray-200">
            <div class="flex items-center justify-center gap-2 mb-2">
              <div class="w-3 h-3 rounded-full bg-rose-500"></div>
              <span class="text-sm font-medium text-gray-700">Expired Subscriptions</span>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.expired_subscriptions) }}</p>
          </div>
          <div class="text-center">
            <div class="flex items-center justify-center gap-2 mb-2">
              <div class="w-3 h-3 rounded-full bg-blue-500"></div>
              <span class="text-sm font-medium text-gray-700">Total Revenue</span>
            </div>
            <p class="text-2xl font-bold text-gray-900">${{ formatNumber(stats.total_revenue) }}</p>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- User Growth Chart -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
          <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900">User Growth</h2>
            <p class="text-sm text-gray-600 mt-1">Last 30 days</p>
          </div>
          <div class="relative h-80">
            <canvas id="userGrowthChart"></canvas>
          </div>
        </div>

        <!-- Revenue Chart -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
          <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900">Revenue Trend</h2>
            <p class="text-sm text-gray-600 mt-1">Last 12 months</p>
          </div>
          <div class="relative h-80">
            <canvas id="revenueChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Recent Signups -->
      <div class="bg-white rounded-2xl shadow-lg p-8">
        <div class="mb-8">
          <h2 class="text-xl font-bold text-gray-900">Recent Signups</h2>
          <p class="text-sm text-gray-600 mt-1">Latest 10 registered users</p>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-200">
                <th class="text-left py-4 px-4 text-sm font-semibold text-gray-700">Name</th>
                <th class="text-left py-4 px-4 text-sm font-semibold text-gray-700">Email</th>
                <th class="text-left py-4 px-4 text-sm font-semibold text-gray-700">Joined</th>
                <th class="text-left py-4 px-4 text-sm font-semibold text-gray-700">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in recentSignups" :key="user.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
                <td class="py-4 px-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-semibold text-sm">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <span class="font-medium text-gray-900">{{ user.name }}</span>
                  </div>
                </td>
                <td class="py-4 px-4 text-gray-600 text-sm">{{ user.email }}</td>
                <td class="py-4 px-4 text-gray-600 text-sm">{{ formatDate(user.created_at) }}</td>
                <td class="py-4 px-4">
                  <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                    Active
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <Link href="/admin/users" class="group relative bg-gradient-to-br from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 rounded-2xl shadow-lg p-8 text-white transition transform hover:scale-105">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-2xl font-bold">Manage Users</h3>
              <p class="text-blue-100 mt-2">View, edit, or suspend users</p>
            </div>
            <svg class="w-12 h-12 text-blue-300 group-hover:translate-x-2 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </div>
        </Link>

        <Link href="/admin/workspaces" class="group relative bg-gradient-to-br from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 rounded-2xl shadow-lg p-8 text-white transition transform hover:scale-105">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-2xl font-bold">Manage Workspaces</h3>
              <p class="text-purple-100 mt-2">View and manage all workspaces</p>
            </div>
            <svg class="w-12 h-12 text-purple-300 group-hover:translate-x-2 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </div>
        </Link>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  stats: Object,
  userGrowthChart: Object,
  revenueChart: Object,
  revenueAndSubscriptionChart: Object,
  recentSignups: Array,
})

const lastUpdated = ref('')

onMounted(() => {
  // Update timestamp
  lastUpdated.value = new Date().toLocaleTimeString()

  // Initialize Revenue & Subscription Trends Chart
  const revSubTrendCtx = document.getElementById('revenueSubscriptionTrendChart')
  if (revSubTrendCtx && props.revenueAndSubscriptionChart) {
    new Chart(revSubTrendCtx, {
      type: 'line',
      data: {
        labels: props.revenueAndSubscriptionChart.labels,
        datasets: props.revenueAndSubscriptionChart.datasets,
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false,
        },
        plugins: {
          legend: {
            display: true,
            position: 'top',
            labels: {
              usePointStyle: true,
              padding: 20,
              font: {
                size: 13,
                weight: '600',
              },
            },
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleColor: '#fff',
            bodyColor: '#fff',
            bodySpacing: 4,
            cornerRadius: 8,
            displayColors: true,
            callbacks: {
              label: function(context) {
                let label = context.dataset.label || '';
                if (label) {
                  label += ': ';
                }
                if (context.dataset.label === 'Monthly Revenue') {
                  label += '$' + context.parsed.y.toLocaleString();
                } else {
                  label += context.parsed.y;
                }
                return label;
              }
            }
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              color: '#6B7280',
              font: {
                size: 12,
              },
            },
            grid: {
              color: 'rgba(229, 231, 235, 0.5)',
              drawBorder: false,
            },
          },
          x: {
            ticks: {
              color: '#6B7280',
              font: {
                size: 12,
              },
            },
            grid: {
              display: false,
            },
          },
        },
      },
    })
  }

  // Initialize User Growth Chart
  const userGrowthCtx = document.getElementById('userGrowthChart')
  if (userGrowthCtx) {
    new Chart(userGrowthCtx, {
      type: 'line',
      data: {
        labels: props.userGrowthChart.labels,
        datasets: [
          {
            label: 'New Users',
            data: props.userGrowthChart.data,
            borderColor: '#3B82F6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: '#3B82F6',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointHoverRadius: 6,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              color: '#9CA3AF',
            },
            grid: {
              color: 'rgba(243, 244, 246, 0.5)',
            },
          },
          x: {
            ticks: {
              color: '#9CA3AF',
            },
            grid: {
              display: false,
            },
          },
        },
      },
    })
  }

  // Initialize Revenue Chart
  const revenueCtx = document.getElementById('revenueChart')
  if (revenueCtx) {
    new Chart(revenueCtx, {
      type: 'bar',
      data: {
        labels: props.revenueChart.labels,
        datasets: [
          {
            label: 'Monthly Revenue',
            data: props.revenueChart.data,
            backgroundColor: '#10B981',
            borderColor: '#059669',
            borderWidth: 0,
            borderRadius: 8,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              color: '#9CA3AF',
              callback: function(value) {
                return '$' + value.toLocaleString()
              },
            },
            grid: {
              color: 'rgba(243, 244, 246, 0.5)',
            },
          },
          x: {
            ticks: {
              color: '#9CA3AF',
            },
            grid: {
              display: false,
            },
          },
        },
      },
    })
  }
})

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>


