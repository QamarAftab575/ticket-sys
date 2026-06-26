<template>
  <div class="min-h-screen flex items-center justify-center px-6 py-12 bg-slate-50 dark:bg-slate-950">
    <div class="w-full max-w-md">
      <!-- Logo -->
      <div class="mb-8">
        <Link href="/" class="inline-flex items-center gap-3 group cursor-pointer">
          <img src="/assets/images/logo/default-logo-main.png" alt="Logo" class="h-8 w-auto" />
        </Link>
      </div>

      <!-- Card -->
      <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-8 shadow-sm">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">{{ $t('forgot_password') }}</h1>
          <p class="text-slate-600 dark:text-slate-400">{{ $t('enter_email_reset_password') }}</p>
        </div>

        <!-- Success Message -->
        <div v-if="emailSent" class="mb-6 p-4 bg-green-50 dark:bg-green-950/50 border border-green-200 dark:border-green-800 rounded-lg">
          <div class="flex gap-3">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
               <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <div>
              <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ $t('email_sent') }}</p>
              <p class="text-sm text-green-700 dark:text-green-400 mt-1">{{ $t('reset_link_sent_message') }}</p>
            </div>
          </div>
        </div>

        <!-- Form -->
        <form v-if="!emailSent" @submit.prevent="handleSubmit" class="space-y-5">
          <!-- Error Message -->
          <div v-if="errors.general" class="p-4 bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800 rounded-lg">
            <p class="text-red-700 dark:text-red-400 text-sm">{{ errors.general }}</p>
          </div>

          <!-- Email Field -->
          <div class="space-y-2">
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
              {{ $t('email_address') }}
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              required
              :class="[
                'w-full px-4 py-2.5 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-colors duration-200',
                'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                errors.email ? 'border-red-300 dark:border-red-700' : 'border-slate-300 dark:border-slate-700'
              ]"
              :placeholder="$t('email_placeholder')"
            />
            <p v-if="errors.email" class="text-red-600 dark:text-red-400 text-sm">{{ errors.email[0] }}</p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <span v-if="!isLoading">{{ $t('send_reset_link') }}</span>
            <span v-else class="flex items-center gap-2">
              <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ $t('sending') }}
            </span>
          </button>
        </form>

        <!-- Back to Login -->
        <div class="mt-6 text-center">
          <Link href="/login" class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            {{ $t('back_to_login') }}
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'

const form = reactive({
  email: ''
})

const errors = reactive({
  email: null,
  general: null
})

const isLoading = ref(false)
const emailSent = ref(false)

const handleSubmit = async () => {
  isLoading.value = true
  errors.general = null
  errors.email = null

  router.post('/forgot-password', form, {
    onError: (pageErrors) => {
      if (pageErrors.email) errors.email = pageErrors.email
      if (pageErrors.message) errors.general = pageErrors.message
    },
    onSuccess: () => {
      emailSent.value = true
    },
    onFinish: () => {
      isLoading.value = false
    }
  })
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
