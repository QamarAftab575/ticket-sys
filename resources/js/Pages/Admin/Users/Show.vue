<template>
  <AdminLayout>
    <template #header>
      <!-- Hero Section with User Header -->
      <div class="bg-gradient-to-r from-slate-900 via-purple-900 to-slate-900 rounded-2xl shadow-2xl p-8 mb-8 text-white">
        <div class="flex items-start justify-between mb-6">
          <div class="flex items-start gap-4">
            <Link href="/admin/users" class="flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg transition font-medium text-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
              Back
            </Link>
            <div>
              <h1 class="text-4xl font-bold">{{ user.name }}</h1>
              <p class="text-purple-200 mt-2">{{ user.email }}</p>
            </div>
          </div>

          <!-- Avatar Badge -->
          <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center text-3xl font-bold shadow-lg">
            {{ user.name.charAt(0).toUpperCase() }}
          </div>
        </div>

        <!-- Status Badges Row -->
        <div class="flex flex-wrap gap-2">
          <span v-if="stats.is_suspended" class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-red-500/20 border border-red-500/50 text-red-200">
            <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
            Suspended
          </span>
          <span v-else class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 border border-green-500/50 text-green-200">
            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
            Active
          </span>
          <span v-if="stats.is_super_admin" class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-amber-500/20 border border-amber-500/50 text-amber-200">
            ðŸ‘‘ Super Admin
          </span>
          <span v-if="stats.email_verified" class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-blue-500/20 border border-blue-500/50 text-blue-200">
            âœ“ Email Verified
          </span>
          <span v-else class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-yellow-500/20 border border-yellow-500/50 text-yellow-200">
            âš  Email Unverified
          </span>
        </div>
      </div>

      <!-- Action Buttons - Enhanced Grid -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-8">
        <button
          @click="showImpersonateModal = true"
          class="group px-4 py-3 bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl hover:shadow-lg hover:shadow-purple-500/50 transition font-medium text-sm flex items-center justify-center gap-2"
        >
          <svg class="w-4 h-4 group-hover:scale-110 transition" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
          <span class="hidden sm:inline">Login</span>
        </button>

        <button
          @click="showPlanModal = true"
          class="group px-4 py-3 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl hover:shadow-lg hover:shadow-green-500/50 transition font-medium text-sm flex items-center justify-center gap-2"
        >
          <svg class="w-4 h-4 group-hover:scale-110 transition" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/></svg>
          <span class="hidden sm:inline">Plan</span>
        </button>

        <button
          v-if="!stats.is_suspended"
          @click="confirmSuspend"
          class="group px-4 py-3 bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-xl hover:shadow-lg hover:shadow-orange-500/50 transition font-medium text-sm flex items-center justify-center gap-2"
        >
          <svg class="w-4 h-4 group-hover:scale-110 transition" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-5-9h10v2H7z"/></svg>
          <span class="hidden sm:inline">Suspend</span>
        </button>

        <button
          v-else
          @click="confirmActivate"
          class="group px-4 py-3 bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-xl hover:shadow-lg hover:shadow-emerald-500/50 transition font-medium text-sm flex items-center justify-center gap-2"
        >
          <svg class="w-4 h-4 group-hover:scale-110 transition" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
          <span class="hidden sm:inline">Activate</span>
        </button>

        <button
          @click="confirmDelete"
          class="group px-4 py-3 bg-gradient-to-br from-red-500 to-red-600 text-white rounded-xl hover:shadow-lg hover:shadow-red-500/50 transition font-medium text-sm flex items-center justify-center gap-2"
        >
          <svg class="w-4 h-4 group-hover:scale-110 transition" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-9l-1 1H5v2h14V4z"/></svg>
          <span class="hidden sm:inline">Delete</span>
        </button>
      </div>

      <!-- User Stats Grid - Enhanced -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Workspaces -->
        <div class="group relative bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-2xl shadow-lg p-8 border border-blue-200/50 dark:border-blue-700/50 hover:shadow-xl hover:shadow-blue-500/20 transition-all overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 to-blue-500/0 group-hover:from-blue-500/5 group-hover:to-blue-500/10 transition"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-4">
              <p class="text-blue-600 dark:text-blue-400 text-sm font-semibold uppercase tracking-wider">Workspaces</p>
              <svg class="w-8 h-8 text-blue-400 group-hover:scale-110 transition" fill="currentColor" viewBox="0 0 24 24"><path d="M20 3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H4V5h16v14z"/></svg>
            </div>
            <p class="text-4xl font-bold text-blue-900 dark:text-blue-100">{{ stats.total_workspaces }}</p>
            <p class="text-blue-600/60 dark:text-blue-400/60 text-sm mt-3">member of workspaces</p>
          </div>
        </div>

        <!-- Total Projects -->
        <div class="group relative bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-2xl shadow-lg p-8 border border-purple-200/50 dark:border-purple-700/50 hover:shadow-xl hover:shadow-purple-500/20 transition-all overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-purple-500/0 to-purple-500/0 group-hover:from-purple-500/5 group-hover:to-purple-500/10 transition"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-4">
              <p class="text-purple-600 dark:text-purple-400 text-sm font-semibold uppercase tracking-wider">Projects</p>
              <svg class="w-8 h-8 text-purple-400 group-hover:scale-110 transition" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/></svg>
            </div>
            <p class="text-4xl font-bold text-purple-900 dark:text-purple-100">{{ stats.total_projects }}</p>
            <p class="text-purple-600/60 dark:text-purple-400/60 text-sm mt-3">active projects</p>
          </div>
        </div>

        <!-- Total Tasks -->
        <div class="group relative bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-2xl shadow-lg p-8 border border-green-200/50 dark:border-green-700/50 hover:shadow-xl hover:shadow-green-500/20 transition-all overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-green-500/0 to-green-500/0 group-hover:from-green-500/5 group-hover:to-green-500/10 transition"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-4">
              <p class="text-green-600 dark:text-green-400 text-sm font-semibold uppercase tracking-wider">Tasks</p>
              <svg class="w-8 h-8 text-green-400 group-hover:scale-110 transition" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
            </div>
            <p class="text-4xl font-bold text-green-900 dark:text-green-100">{{ stats.total_tasks }}</p>
            <p class="text-green-600/60 dark:text-green-400/60 text-sm mt-3">assigned tasks</p>
          </div>
        </div>

        <!-- Account Age -->
        <div class="group relative bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-2xl shadow-lg p-8 border border-orange-200/50 dark:border-orange-700/50 hover:shadow-xl hover:shadow-orange-500/20 transition-all overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-orange-500/0 to-orange-500/0 group-hover:from-orange-500/5 group-hover:to-orange-500/10 transition"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-4">
              <p class="text-orange-600 dark:text-orange-400 text-sm font-semibold uppercase tracking-wider">Member Since</p>
              <svg class="w-8 h-8 text-orange-400 group-hover:scale-110 transition" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
            </div>
            <p class="text-2xl font-bold text-orange-900 dark:text-orange-100">{{ formatDate(stats.created_at) }}</p>
            <p class="text-orange-600/60 dark:text-orange-400/60 text-sm mt-3">{{ daysActive }} days active</p>
          </div>
        </div>
      </div>

      <!-- Detailed User Info - Enhanced -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- User Profile Info -->
        <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 rounded-2xl shadow-xl p-8 border border-slate-200/50 dark:border-slate-700/50">
          <div class="flex items-center gap-3 mb-6">
            <svg class="w-6 h-6 text-slate-600 dark:text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Profile Information</h2>
          </div>
          <div class="space-y-5">
            <div class="pb-5 border-b border-slate-200 dark:border-slate-700">
              <p class="text-slate-600 dark:text-slate-400 text-sm font-medium uppercase tracking-wider mb-2">Full Name</p>
              <p class="text-slate-900 dark:text-white font-semibold text-lg">{{ user.name }}</p>
            </div>
            <div class="pb-5 border-b border-slate-200 dark:border-slate-700">
              <p class="text-slate-600 dark:text-slate-400 text-sm font-medium uppercase tracking-wider mb-2">Email Address</p>
              <p class="text-slate-900 dark:text-white font-semibold text-lg break-all">{{ user.email }}</p>
            </div>
            <div class="pb-5 border-b border-slate-200 dark:border-slate-700">
              <p class="text-slate-600 dark:text-slate-400 text-sm font-medium uppercase tracking-wider mb-2">Timezone</p>
              <p class="text-slate-900 dark:text-white font-semibold text-lg">{{ user.timezone || 'â€”' }}</p>
            </div>
            <div class="pb-5 border-b border-slate-200 dark:border-slate-700">
              <p class="text-slate-600 dark:text-slate-400 text-sm font-medium uppercase tracking-wider mb-2">Account Created</p>
              <p class="text-slate-900 dark:text-white font-semibold text-lg">{{ formatDate(stats.created_at) }}</p>
            </div>
            <div>
              <p class="text-slate-600 dark:text-slate-400 text-sm font-medium uppercase tracking-wider mb-2">Last Login</p>
              <p class="text-slate-900 dark:text-white font-semibold text-lg">{{ stats.last_login || 'â€”' }}</p>
            </div>
          </div>
        </div>

        <!-- Trial & Subscription Info -->
        <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 rounded-2xl shadow-xl p-8 border border-slate-200/50 dark:border-slate-700/50">
          <div class="flex items-center gap-3 mb-6">
            <svg class="w-6 h-6 text-slate-600 dark:text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Trial & Subscription</h2>
          </div>
          <div class="space-y-5">
            <!-- If user has active paid plan -->
            <div v-if="activePlan && stats.plan_starts_at" class="p-6 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100/50 border-2 border-emerald-300 dark:from-emerald-900/20 dark:to-emerald-800/20 dark:border-emerald-700/50">
              <div class="flex items-start justify-between mb-4">
                <div>
                  <p class="text-sm font-bold text-emerald-900 dark:text-emerald-200 flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                    ðŸ’³ Active Subscription
                  </p>
                  <p class="text-emerald-900 dark:text-emerald-100 font-bold text-2xl mt-3">{{ activePlan.name }}</p>
                </div>
                <span class="px-4 py-2 rounded-lg text-sm font-bold bg-emerald-600 text-white">{{ activePlan.billing_cycle }}</span>
              </div>

              <div class="grid grid-cols-2 gap-4 mt-6 pt-6 border-t-2 border-emerald-200 dark:border-emerald-700/50">
                <div>
                  <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase mb-1">Plan Price</p>
                  <p class="text-lg font-bold text-emerald-900 dark:text-emerald-100">${{ activePlan.price }}/{{ activePlan.billing_cycle }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase mb-1">Subscription Start</p>
                  <p class="text-lg font-bold text-emerald-900 dark:text-emerald-100">{{ formatDate(stats.plan_starts_at) }}</p>
                </div>
              </div>

              <button
                @click="showPlanModal = true"
                class="w-full mt-6 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold text-sm transition"
              >
                Change Plan
              </button>
            </div>

            <!-- If user is on trial -->
            <div v-else-if="stats.trial_ends_at && !activePlan" class="p-6 rounded-xl" :class="isTrialActive ? 'bg-gradient-to-br from-yellow-50 to-yellow-100/50 border-2 border-yellow-300 dark:from-yellow-900/20 dark:to-yellow-800/20 dark:border-yellow-700/50' : 'bg-gradient-to-br from-red-50 to-red-100/50 border-2 border-red-300 dark:from-red-900/20 dark:to-red-800/20 dark:border-red-700/50'">
              <p class="text-sm font-bold flex items-center gap-2" :class="isTrialActive ? 'text-yellow-900 dark:text-yellow-200' : 'text-red-900 dark:text-red-200'">
                <span :class="['w-3 h-3 rounded-full', isTrialActive ? 'bg-yellow-500 animate-pulse' : 'bg-red-500 animate-pulse']"></span>
                {{ isTrialActive ? 'ðŸ”„ Active Trial' : 'â° Trial Expired' }}
              </p>
              <p class="text-slate-700 dark:text-slate-300 text-sm mt-3">Trial ends: <span class="font-semibold">{{ formatDate(stats.trial_ends_at) }}</span></p>
              <p v-if="isTrialActive" class="text-slate-700 dark:text-slate-300 text-sm font-medium mt-2">â³ {{ daysUntilTrialExpires }} days remaining</p>

              <div class="grid grid-cols-2 gap-3 mt-4">
                <button
                  @click="showExtendTrialModal = true"
                  class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg font-semibold text-sm transition"
                >
                  Extend Trial
                </button>
                <button
                  @click="showPlanModal = true"
                  class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold text-sm transition"
                >
                  Upgrade to Plan
                </button>
              </div>
            </div>

            <!-- If no trial and no plan -->
            <div v-else class="p-6 rounded-xl bg-gradient-to-br from-gray-50 to-gray-100/50 border-2 border-gray-300 dark:from-gray-900/20 dark:to-gray-800/20 dark:border-gray-700/50">
              <p class="text-sm font-bold text-gray-900 dark:text-gray-200 flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-gray-500"></span>
                âš ï¸ No Plan or Trial
              </p>
              <p class="text-gray-700 dark:text-gray-300 text-sm mt-3">This user doesn't have an active trial or plan. Assign them a plan to get started.</p>

              <button
                @click="showPlanModal = true"
                class="w-full mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold text-sm transition"
              >
                Assign Plan
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Workspaces - Enhanced -->
      <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 rounded-2xl shadow-xl p-8 border border-slate-200/50 dark:border-slate-700/50 mb-8">
        <div class="mb-8">
          <div class="flex items-center gap-3 mb-2">
            <svg class="w-6 h-6 text-slate-600 dark:text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/></svg>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Workspaces</h2>
            <span class="ml-auto px-3 py-1 bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white rounded-full text-sm font-semibold">{{ workspaces.length }}</span>
          </div>
          <p class="text-slate-600 dark:text-slate-400 text-sm mt-2">All workspaces this user is a member of</p>
        </div>

        <div v-if="workspaces.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div
            v-for="workspace in workspaces"
            :key="workspace.id"
            class="group relative p-6 border border-slate-200 dark:border-slate-700 rounded-xl hover:shadow-lg hover:border-slate-300 dark:hover:border-slate-600 transition-all bg-white dark:bg-slate-900"
          >
            <div class="flex items-start justify-between mb-4">
              <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg group-hover:shadow-xl transition" :style="{ backgroundColor: workspace.avatar_color }">
                {{ workspace.name.charAt(0).toUpperCase() }}
              </div>
              <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                {{ workspace.role }}
              </span>
            </div>
            <h3 class="font-bold text-slate-900 dark:text-white text-lg mb-1 line-clamp-2">{{ workspace.name }}</h3>
            <p class="text-slate-600 dark:text-slate-400 text-sm mb-4">Joined {{ formatDate(workspace.created_at) }}</p>
            <div class="flex items-center justify-between text-xs font-semibold">
              <div class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                <span>{{ workspace.members_count }}</span>
              </div>
              <div class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/></svg>
                <span>{{ workspace.projects_count }}</span>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-16">
          <svg class="w-16 h-16 text-slate-300 dark:text-slate-600 mx-auto mb-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9h-3V8h-1v4h-3v1h3v3h1v-3h3v-1z"/></svg>
          <p class="text-slate-600 dark:text-slate-400 font-medium">This user is not a member of any workspaces</p>
        </div>
      </div>

      <!-- Projects - Enhanced -->
      <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 rounded-2xl shadow-xl p-8 border border-slate-200/50 dark:border-slate-700/50 mb-8">
        <div class="mb-8">
          <div class="flex items-center gap-3 mb-2">
            <svg class="w-6 h-6 text-slate-600 dark:text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/></svg>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Projects</h2>
            <span class="ml-auto px-3 py-1 bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white rounded-full text-sm font-semibold">{{ projects.length }}</span>
          </div>
          <p class="text-slate-600 dark:text-slate-400 text-sm mt-2">All projects this user is a member of</p>
        </div>

        <div v-if="projects.length > 0" class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b-2 border-slate-200 dark:border-slate-700">
                <th class="text-left py-4 px-4 text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Project Name</th>
                <th class="text-left py-4 px-4 text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Workspace</th>
                <th class="text-left py-4 px-4 text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Role</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="project in projects" :key="project.id" class="border-b border-slate-100 dark:border-slate-700 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 transition">
                <td class="py-4 px-4 text-sm font-semibold text-slate-900 dark:text-white">{{ project.name }}</td>
                <td class="py-4 px-4 text-sm text-slate-600 dark:text-slate-400">{{ project.org_name }}</td>
                <td class="py-4 px-4">
                  <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                    {{ project.role }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-16">
          <svg class="w-16 h-16 text-slate-300 dark:text-slate-600 mx-auto mb-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9h-3V8h-1v4h-3v1h3v3h1v-3h3v-1z"/></svg>
          <p class="text-slate-600 dark:text-slate-400 font-medium">This user is not a member of any projects</p>
        </div>
      </div>

      <!-- Subscription History Section -->
      <div v-if="subscriptionHistory && subscriptionHistory.length" class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl p-8 border border-slate-200/50 dark:border-slate-700/50">
        <div class="flex items-center gap-3 mb-6">
          <svg class="w-6 h-6 text-slate-600 dark:text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5-7h-4v4h4v-4z"/></svg>
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Subscription History</h2>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-slate-200 dark:border-slate-700">
                <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-400">Plan</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-400">Source</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-400">Started</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-400">Expires</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-400">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="subscription in subscriptionHistory" :key="subscription.id" class="border-b border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                <td class="px-6 py-4 text-sm">
                  <div class="font-semibold text-slate-900 dark:text-white">{{ subscription.plan_name }}</div>
                  <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ subscription.plan_price }} / {{ subscription.billing_cycle }}</div>
                </td>
                <td class="px-6 py-4 text-sm">
                  <span :class="[
                    'inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-semibold',
                    subscription.source === 'purchase' 
                      ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' 
                      : subscription.source === 'admin'
                      ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300'
                      : 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300'
                  ]">
                    {{ subscription.source === 'purchase' ? '💳 Purchase' : subscription.source === 'admin' ? '👤 Admin' : subscription.source }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">
                  {{ formatDate(subscription.started_at) }}
                </td>
                <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">
                  {{ subscription.expires_at ? formatDate(subscription.expires_at) : '—' }}
                </td>
                <td class="px-6 py-4 text-sm">
                  <span :class="[
                    'inline-flex px-3 py-1 rounded-full text-xs font-semibold',
                    subscription.status === 'active'
                      ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300'
                      : subscription.status === 'expired'
                      ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300'
                      : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300'
                  ]">
                    {{ subscription.status === 'active' ? '✓ Active' : subscription.status === 'expired' ? '✕ Expired' : '⏸ ' + subscription.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Confirmation Modal - Enhanced -->
    </template>

    <div v-if="showConfirmModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-sm w-full transform transition-all">
        <div class="p-8">
          <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 mx-auto mb-4">
            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
          </div>
          <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-2 text-center">{{ confirmTitle }}</h2>
          <p class="text-slate-600 dark:text-slate-400 mb-8 text-center">{{ confirmMessage }}</p>
          <div class="flex items-center justify-end space-x-3">
            <button
              @click="showConfirmModal = false"
              class="px-5 py-2 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold transition"
            >
              Cancel
            </button>
            <button
              @click="confirmAction"
              :class="confirmActionClass"
              class="px-5 py-2 rounded-lg text-white font-semibold hover:opacity-90 transition shadow-lg"
            >
              {{ confirmButtonText }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Impersonate Modal - Enhanced -->
    <div v-if="showImpersonateModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-sm w-full transform transition-all">
        <div class="p-8">
          <div class="flex items-center justify-center w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900/30 mx-auto mb-4">
            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
          </div>
          <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-3 text-center">Login as {{ user.name }}</h2>
          <p class="text-slate-600 dark:text-slate-400 mb-8 text-center text-sm">You're about to login as <span class="font-semibold">{{ user.email }}</span>. You can see exactly what they see and debug issues. Visit <code class="bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded text-xs">/admin/stop-impersonating</code> to stop.</p>
          <div class="flex items-center justify-end space-x-3">
            <button
              @click="showImpersonateModal = false"
              class="px-5 py-2 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold transition"
            >
              Cancel
            </button>
            <button
              @click="impersonateUser"
              class="px-5 py-2 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white rounded-lg font-semibold transition shadow-lg"
            >
              Login as User
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Assign Plan Modal - Enhanced -->
    <div v-if="showPlanModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-sm w-full transform transition-all">
        <div class="p-8">
          <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30 mx-auto mb-4">
            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1C5.9 1 1 5.9 1 12s4.9 11 11 11 11-4.9 11-11S18.1 1 12 1zm-2 16l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
          </div>
          <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6 text-center">Assign Plan</h2>
          <div class="mb-6">
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-3">Select Plan</label>
            <select
              v-model="selectedPlanId"
              class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium transition"
            >
              <option value="">-- Choose a plan --</option>
              <option v-for="plan in availablePlans" :key="plan.id" :value="plan.id">
                {{ plan.name }} - ${{ plan.price }}/{{ plan.billing_cycle }}
              </option>
            </select>
          </div>
          <div class="flex items-center justify-end space-x-3">
            <button
              @click="showPlanModal = false"
              class="px-5 py-2 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold transition"
            >
              Cancel
            </button>
            <button
              @click="assignPlan"
              :disabled="!selectedPlanId"
              class="px-5 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg font-semibold transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Assign Plan
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Extend Trial Modal - Enhanced -->
    <div v-if="showExtendTrialModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-sm w-full transform transition-all">
        <div class="p-8">
          <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 mx-auto mb-4">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
          </div>
          <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6 text-center">Extend Trial Days</h2>
          <div class="mb-6">
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-3">Days to Add</label>
            <input
              v-model.number="extendDays"
              type="number"
              min="1"
              max="365"
              placeholder="e.g., 30"
              class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium transition"
            />
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Enter the number of days to extend the trial (1-365)</p>
          </div>
          <div class="flex items-center justify-end space-x-3">
            <button
              @click="showExtendTrialModal = false"
              class="px-5 py-2 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold transition"
            >
              Cancel
            </button>
            <button
              @click="extendTrial"
              :disabled="!extendDays || extendDays < 1"
              class="px-5 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg font-semibold transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Extend Trial
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  user: Object,
  stats: Object,
  workspaces: Array,
  projects: Array,
  availablePlans: Array,
  activePlan: Object,
  subscriptionHistory: Array,
})

const showConfirmModal = ref(false)
const showImpersonateModal = ref(false)
const showPlanModal = ref(false)
const showExtendTrialModal = ref(false)

const confirmTitle = ref('')
const confirmMessage = ref('')
const confirmButtonText = ref('')
const confirmActionClass = ref('')
const pendingAction = ref(null)

const selectedPlanId = ref('')
const extendDays = ref(30)

const daysActive = computed(() => {
  const created = new Date(props.stats.created_at)
  const now = new Date()
  const diff = Math.floor((now - created) / (1000 * 60 * 60 * 24))
  return diff
})

const isTrialActive = computed(() => {
  if (!props.stats.trial_ends_at) return false
  return new Date(props.stats.trial_ends_at) > new Date()
})

const daysUntilTrialExpires = computed(() => {
  if (!props.stats.trial_ends_at) return 0
  const trialEnd = new Date(props.stats.trial_ends_at)
  const now = new Date()
  const diff = Math.floor((trialEnd - now) / (1000 * 60 * 60 * 24))
  return diff > 0 ? diff : 0
})

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const confirmSuspend = () => {
  confirmTitle.value = 'Suspend User'
  confirmMessage.value = `Are you sure you want to suspend ${props.user.email}? They will no longer be able to access the platform.`
  confirmButtonText.value = 'Suspend'
  confirmActionClass.value = 'bg-red-600'
  pendingAction.value = { type: 'suspend' }
  showConfirmModal.value = true
}

const confirmActivate = () => {
  confirmTitle.value = 'Activate User'
  confirmMessage.value = `Activate ${props.user.email}? They will regain access to the platform.`
  confirmButtonText.value = 'Activate'
  confirmActionClass.value = 'bg-green-600'
  pendingAction.value = { type: 'activate' }
  showConfirmModal.value = true
}

const confirmDelete = () => {
  confirmTitle.value = 'Delete User'
  confirmMessage.value = `Are you sure you want to permanently delete ${props.user.email}? This action cannot be undone and all their data will be removed.`
  confirmButtonText.value = 'Delete'
  confirmActionClass.value = 'bg-gray-600'
  pendingAction.value = { type: 'delete' }
  showConfirmModal.value = true
}

const confirmAction = () => {
  const action = pendingAction.value
  showConfirmModal.value = false

  if (action.type === 'suspend') {
    router.patch(`/admin/users/${props.user.id}/suspend`)
  } else if (action.type === 'activate') {
    router.patch(`/admin/users/${props.user.id}/activate`)
  } else if (action.type === 'delete') {
    router.delete(`/admin/users/${props.user.id}`)
  }
}

const impersonateUser = () => {
  showImpersonateModal.value = false
  router.post(`/admin/users/${props.user.id}/impersonate`)
}

const assignPlan = () => {
  if (!selectedPlanId.value) return
  showPlanModal.value = false
  router.post(`/admin/users/${props.user.id}/assign-plan`, {
    plan_id: selectedPlanId.value
  })
}

const extendTrial = () => {
  if (!extendDays.value || extendDays.value < 1) return
  showExtendTrialModal.value = false
  router.post(`/admin/users/${props.user.id}/extend-trial`, {
    days: extendDays.value
  })
}
</script>

