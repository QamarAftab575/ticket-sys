<template>
  <div class="min-h-screen flex bg-white dark:bg-slate-950">
    <!-- Left Side - Login Form -->
    <div class="flex-1 flex items-center justify-center px-6 py-12">
      <!-- Home Badge (Fixed Position) -->
      <Link 
        href="/" 
        class="fixed top-4 left-4 inline-flex items-center gap-2 px-3 py-2 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium transition-colors duration-200 cursor-pointer group"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l4-4m0 0l4-4m-4 4L9 9m4 4l4 4" />
        </svg>
        <span>Home</span>
      </Link>

      <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="mb-8">
          <Link href="/" class="inline-flex items-center gap-3 group cursor-pointer">
            <img src="/assets/images/logo/default-logo-main.png" alt="tasqo" class="h-8 w-auto" />
          </Link>
        </div>

        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">{{ $t('welcome_back') }}</h1>
          <p class="text-slate-600 dark:text-slate-400">{{ $t('sign_in_continue') }}</p>
        </div>

        <!-- Error Message -->
        <div v-if="errors.general" class="mb-6 p-4 bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800 rounded-lg">
          <p class="text-red-700 dark:text-red-400 text-sm">{{ errors.general }}</p>
        </div>

        <!-- Login Form -->
        <form @submit.prevent="handleLogin" class="space-y-5">
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
            <p v-if="errors.email" class="text-red-600 dark:text-red-400 text-sm">{{ errors.email }}</p>
          </div>

          <!-- Password Field -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                {{ $t('password') }}
              </label>
              <Link 
                href="/forgot-password" 
                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors cursor-pointer"
              >
                {{ $t('forgot_password') }}
              </Link>
            </div>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
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
          </div>

          <!-- Sign In Button -->
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <span v-if="!isLoading">{{ $t('sign_in') }}</span>
            <span v-else class="flex items-center gap-2">
              <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ $t('signing_in') }}
            </span>
          </button>

          <!-- Divider -->
          <div v-if="googleLoginEnabled" class="relative my-6">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-slate-200 dark:border-slate-800"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-3 bg-white dark:bg-slate-950 text-slate-500 dark:text-slate-400">{{ $t('or_continue_with') }}</span>
            </div>
          </div>

          <!-- Google Sign In -->
          <button
            v-if="googleLoginEnabled"
            type="button"
            @click="handleGoogleLogin"
            class="w-full py-2.5 px-4 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium rounded-lg transition-colors duration-200 flex items-center justify-center gap-3 border border-slate-300 dark:border-slate-700"
          >
            <svg class="w-5 h-5" viewBox="0 0 24 24">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            {{ $t('continue_with_google') }}
          </button>

          <!-- Google Login Error -->
          <div v-if="googleLoginError" class="p-4 bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800 rounded-lg">
            <p class="text-red-700 dark:text-red-400 text-sm">{{ googleLoginError }}</p>
          </div>
        </form>

        <!-- Sign Up Link -->
        <div class="mt-8 text-center">
          <p class="text-sm text-slate-600 dark:text-slate-400">
            {{ $t('no_account') }}
            <Link href="/register" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium transition-colors cursor-pointer ml-1">
              {{ $t('create_account') }}
            </Link>
          </p>
        </div>
      </div>
    </div>

    <!-- Right Side - Feature Showcase -->
    <div class="hidden lg:flex flex-1 bg-slate-50 dark:bg-slate-900 items-center justify-center p-12 relative overflow-hidden">
      <!-- Subtle gradient background -->
      <div class="absolute inset-0 bg-gradient-to-br from-blue-50 dark:from-blue-950/20 to-slate-50 dark:to-slate-900"></div>
      
      <!-- Grid pattern -->
      <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzAwMDAwMCIgc3Ryb2tlLW9wYWNpdHk9IjAuMDMiIHN0cm9rZS13aWR0aD0iMSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIvPjwvc3ZnPg==')] opacity-40 dark:opacity-20"></div>

      <div class="relative z-10 max-w-lg">
        <!-- Feature Content -->
        <div class="mb-8">
          <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-100 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 rounded-full text-sm font-medium mb-6 border border-blue-200 dark:border-blue-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <span>Trusted by 50,000+ teams</span>
          </div>
          <h2 class="text-4xl font-bold text-slate-900 dark:text-white mb-4 leading-tight">
            Ship faster with<br />better workflow
          </h2>
          <p class="text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
            Join thousands of teams using tasqo to manage projects, track progress, and collaborate seamlessly.
          </p>
        </div>

        <!-- Feature List -->
        <div class="space-y-4">
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-950 flex items-center justify-center flex-shrink-0 mt-0.5">
              <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Multiple views</h3>
              <p class="text-sm text-slate-600 dark:text-slate-400">Kanban, List, Timeline, and Calendar views for every workflow</p>
            </div>
          </div>

          <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-950 flex items-center justify-center flex-shrink-0 mt-0.5">
              <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Real-time collaboration</h3>
              <p class="text-sm text-slate-600 dark:text-slate-400">Work together with your team in real-time</p>
            </div>
          </div>

          <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-950 flex items-center justify-center flex-shrink-0 mt-0.5">
              <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Advanced reporting</h3>
              <p class="text-sm text-slate-600 dark:text-slate-400">Gain insights with powerful analytics and reports</p>
            </div>
          </div>
        </div>

        <!-- Testimonial -->
        <div class="mt-12 p-6 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
          <div class="flex gap-1 mb-3">
            <svg v-for="i in 5" :key="i" class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
          </div>
          <p class="text-slate-600 dark:text-slate-400 mb-4 text-sm leading-relaxed">
            "tasqo transformed how our team works. The interface is clean, fast, and intuitive. We're shipping features 2x faster."
          </p>
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold text-sm">
              SM
            </div>
            <div>
              <div class="font-semibold text-slate-900 dark:text-white text-sm">Sarah Mitchell</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">Head of Product, TechCorp</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'

const page = usePage()

const form = reactive({
  email: '',
  password: ''
})

const errors = reactive({
  email: null,
  password: null,
  general: null
})

const isLoading = ref(false)
const showPassword = ref(false)
const googleLoginEnabled = ref(page.props.googleLoginEnabled || false)
const googleLoginError = ref(null)

const handleLogin = async () => {
  isLoading.value = true
  errors.general = null
  errors.email = null
  errors.password = null

  router.post('/login', form, {
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

const handleGoogleLogin = () => {
  window.location.href = '/auth/google'
}
</script>

<style scoped>
/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
