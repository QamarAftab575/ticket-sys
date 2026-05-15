<template>
  <div class="flex h-screen bg-slate-100">
    <!-- Sidebar Navigation -->
    <div class="w-64 bg-white shadow-lg">
      <div class="p-6">
        <h2 class="text-xl font-bold text-slate-900">Settings</h2>
      </div>

      <nav class="space-y-1 px-4">
        <!-- Workspace Settings -->
        <Link
          href="/settings"
          :class="[
            'block px-4 py-2 rounded-lg font-medium transition-colors',
            isActive('/settings') 
              ? 'bg-blue-50 text-blue-600' 
              : 'text-slate-700 hover:bg-slate-50'
          ]"
        >
          <div class="flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Workspace Settings
          </div>
        </Link>

        <!-- Integrations Section -->
        <div class="mt-6">
          <h3 class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Integrations</h3>
          
          <!-- Google Social Login -->
          <Link
            href="/settings/integrations/google"
            :class="[
              'block px-4 py-2 rounded-lg font-medium transition-colors ml-2',
              isActive('/settings/integrations/google') 
                ? 'bg-blue-50 text-blue-600' 
                : 'text-slate-700 hover:bg-slate-50'
            ]"
          >
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
              </svg>
              Google Social Login
            </div>
          </Link>
        </div>

        <!-- Other Settings -->
        <div class="mt-6">
          <h3 class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Other</h3>
          
          <!-- Notifications -->
          <Link
            href="/settings/notifications"
            :class="[
              'block px-4 py-2 rounded-lg font-medium transition-colors ml-2',
              isActive('/settings/notifications') 
                ? 'bg-blue-50 text-blue-600' 
                : 'text-slate-700 hover:bg-slate-50'
            ]"
          >
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              Notifications
            </div>
          </Link>

          <!-- Security -->
          <Link
            href="/settings/security"
            :class="[
              'block px-4 py-2 rounded-lg font-medium transition-colors ml-2',
              isActive('/settings/security') 
                ? 'bg-blue-50 text-blue-600' 
                : 'text-slate-700 hover:bg-slate-50'
            ]"
          >
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              Security
            </div>
          </Link>
        </div>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 overflow-auto">
      <div class="p-8">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()

const isActive = (path) => {
  return page.url === path || page.url.startsWith(path + '/')
}
</script>
