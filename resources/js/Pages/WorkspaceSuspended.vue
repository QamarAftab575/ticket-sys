<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center p-4">
    <div class="max-w-md w-full">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-8 text-center">
        <!-- Icon -->
        <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 0 1 5.11 2.523a6 6 0 0 1 8.367 8.368zM9 11.5a2.5 2.5 0 1 0-5 0 2.5 2.5 0 0 0 5 0zm6.364-10.364A1 1 0 0 0 14 0H6a1 1 0 0 0 0 2h8a1 1 0 0 0 1-1zm0 18.728a1 1 0 0 0 1.364-1.364l-8-8a1 1 0 0 0-1.414 1.414l8 8z" clip-rule="evenodd"/>
          </svg>
        </div>

        <!-- Title -->
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">Workspace Unavailable</h1>

        <!-- Description -->
        <p class="text-slate-600 dark:text-slate-300 text-sm mb-6">
          This workspace is currently unavailable due to an account issue with the workspace owner. Please contact the workspace owner to resolve the issue.
        </p>

        <!-- Additional Info -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-6">
          <p class="text-sm text-blue-800 dark:text-blue-200">
            The workspace owner's subscription may have expired. Once they renew their subscription, you'll regain access to this workspace.
          </p>
        </div>

        <!-- Actions -->
        <div class="space-y-3">
          <a
            href="/dashboard"
            class="block w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors"
          >
            Go to Dashboard
          </a>
          <a
            href="/workspaces"
            class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-700 font-semibold rounded-lg transition-colors"
          >
            View Other Workspaces
          </a>
        </div>

        <!-- Support Info -->
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-6">
          If you believe this is a mistake, please <a href="/support" class="text-blue-600 dark:text-blue-400 hover:underline">contact support</a>.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
// This page is shown when a member tries to access a workspace whose owner's subscription is suspended
</script>
