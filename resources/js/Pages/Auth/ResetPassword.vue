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
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">{{ $t('reset_password_title') }}</h1>
          <p class="text-slate-600 dark:text-slate-400">{{ $t('enter_new_password') }}</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleReset" class="space-y-5">
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

          <!-- Password Field -->
          <div class="space-y-2">
            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
              {{ $t('new_password') }}
            </label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                :class="[
                  'w-full px-4 py-2.5 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-colors duration-200',
                  'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                  errors.password ? 'border-red-300 dark:border-red-700' : 'border-slate-300 dark:border-slate-700'
                ]"
                placeholder="••••••••"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors cursor-pointer"
              >
                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                </svg>
              </button>
            </div>
            <p v-if="errors.password" class="text-red-600 dark:text-red-400 text-sm">{{ errors.password[0] }}</p>
            
            <!-- Password Strength -->
            <PasswordStrengthIndicator :password="form.password" />
          </div>

          <!-- Confirm Password Field -->
          <div class="space-y-2">
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
              {{ $t('confirm_password') }}
            </label>
            <div class="relative">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="••••••••"
              />
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors cursor-pointer"
              >
                <svg v-if="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                </svg>
              </button>
            </div>
            <p v-if="form.password_confirmation && form.password !== form.password_confirmation" class="text-red-600 dark:text-red-400 text-sm">
              {{ $t('passwords_do_not_match') }}
            </p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="isLoading || form.password !== form.password_confirmation"
            class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <span v-if="!isLoading">{{ $t('reset_password') }}</span>
            <span v-else class="flex items-center gap-2">
              <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ $t('resetting') }}
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
import { ref, reactive, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import PasswordStrengthIndicator from '@/Components/PasswordStrengthIndicator.vue'

const props = defineProps({
  token: {
    type: String,
    required: true
  }
})

const form = reactive({
  email: '',
  password: '',
  password_confirmation: '',
  token: props.token
})

const errors = reactive({
  email: null,
  password: null,
  general: null
})

const isLoading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const handleReset = async () => {
  isLoading.value = true
  errors.general = null
  errors.email = null
  errors.password = null

  router.post('/reset-password', form, {
    onError: (pageErrors) => {
      if (pageErrors.email) errors.email = pageErrors.email
      if (pageErrors.password) errors.password = pageErrors.password
      if (pageErrors.message) errors.general = pageErrors.message
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
