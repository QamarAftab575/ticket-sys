<template>
  <div class="min-h-screen flex items-center justify-center px-6 py-12 bg-slate-50 dark:bg-slate-950">
    <div class="w-full max-w-md">
      <!-- Logo -->
      <div class="mb-8">
        <Link href="/" class="inline-flex items-center gap-3 group cursor-pointer">
          <img src="/assets/images/logo/default-logo-main.png" alt="tasqo" class="h-8 w-auto" />
        </Link>
      </div>

      <!-- Card -->
      <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-8 shadow-sm">
        <!-- Icon -->
        <div class="flex justify-center mb-6">
          <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-950 flex items-center justify-center">
            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
        </div>

        <!-- Header -->
        <div class="text-center mb-8">
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">{{ $t('verify_email') }}</h1>
          <p class="text-slate-600 dark:text-slate-400">{{ $t('verify_email_description') }}</p>
        </div>

        <!-- Success Message -->
        <div v-if="emailSent" class="mb-6 p-4 bg-green-50 dark:bg-green-950/50 border border-green-200 dark:border-green-800 rounded-lg">
          <div class="flex gap-3">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <div>
              <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ $t('verification_email_sent') }}</p>
              <p class="text-sm text-green-700 dark:text-green-400 mt-1">{{ $t('check_inbox') }}</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="space-y-3">
          <!-- Resend Button -->
          <button
            @click="handleResend"
            :disabled="isLoading"
            class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <svg v-if="!isLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span v-if="!isLoading">{{ $t('resend_verification_email') }}</span>
            <span v-else>{{ $t('sending') }}</span>
          </button>

          <!-- Logout Button -->
          <form @submit.prevent="handleLogout">
            <button
              type="submit"
              class="w-full py-2.5 px-4 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium rounded-lg border border-slate-300 dark:border-slate-700 transition-colors duration-200"
            >
              {{ $t('logout') }}
            </button>
          </form>
        </div>

        <!-- Help text -->
        <div class="mt-6 p-4 bg-slate-50 dark:bg-slate-800 rounded-lg">
          <p class="text-xs text-slate-600 dark:text-slate-400 text-center">
            <span class="font-medium">{{ $t('didnt_receive_email') }}</span> {{ $t('check_spam_or_resend') }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'

const isLoading = ref(false)
const emailSent = ref(false)

const handleResend = async () => {
  isLoading.value = true
  emailSent.value = false
  
  router.post('/email/resend', {}, {
    onSuccess: () => {
      emailSent.value = true
    },
    onFinish: () => {
      isLoading.value = false
    }
  })
}

const handleLogout = async () => {
  router.post('/logout')
}
</script>

<style scoped>
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
