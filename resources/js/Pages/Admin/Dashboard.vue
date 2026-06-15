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
            📈 Trending
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

      <!-- Subscription Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Paid Subscribers -->
        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl shadow-lg p-8 border border-emerald-200">
          <div class="flex items-center justify-between mb-4">
            <p class="text-emerald-600 text-sm font-semibold uppercase">Paid Subscribers</p>
            <svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
            </svg>
          </div>
          <p class="text-4xl font-bold text-emerald-900">{{ stats.paid_subscribers }}</p>
          <p class="text-emerald-600 text-sm mt-4">Active subscriptions</p>
        </div>

        <!-- Expired Trials -->
        <div class="bg-gradient-to-br from-rose-50 to-rose-100 rounded-xl shadow-lg p-8 border border-rose-200">
          <div class="flex items-center justify-between mb-4">
            <p class="text-rose-600 text-sm font-semibold uppercase">Expired Trials</p>
            <svg class="w-6 h-6 text-rose-600" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
            </svg>
          </div>
          <p class="text-4xl font-bold text-rose-900">{{ stats.expired_trials }}</p>
          <p class="text-rose-600 text-sm mt-4">Need upgrade</p>
        </div>

        <!-- Monthly Revenue -->
        <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl shadow-lg p-8 border border-amber-200">
          <div class="flex items-center justify-between mb-4">
            <p class="text-amber-600 text-sm font-semibold uppercase">Monthly Revenue</p>
            <svg class="w-6 h-6 text-amber-600" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z" />
            </svg>
          </div>
          <p class="text-4xl font-bold text-amber-900">${{ formatNumber(stats.monthly_revenue) }}</p>
          <p class="text-amber-600 text-sm mt-4">From active plans</p>
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
  recentSignups: Array,
})

const lastUpdated = ref('')

onMounted(() => {
  // Update timestamp
  lastUpdated.value = new Date().toLocaleTimeString()

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
                return '$' + value
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

