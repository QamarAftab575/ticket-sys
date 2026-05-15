<template>
  <div class="min-h-screen flex items-center justify-center relative overflow-hidden">
    <!-- Animated Background -->
    <div class="absolute inset-0 bg-gradient-to-br from-rose-100 via-orange-50 to-amber-100">
      <div class="absolute inset-0 bg-gradient-to-tr from-pink-200/40 via-transparent to-amber-200/40"></div>
      
      <!-- Floating Elements -->
      <div class="absolute top-20 left-10 w-32 h-32 bg-rose-300/20 rounded-full blur-xl animate-float"></div>
      <div class="absolute top-40 right-20 w-24 h-24 bg-amber-300/20 rounded-full blur-xl animate-float" style="animation-delay: 2s;"></div>
      <div class="absolute bottom-32 left-1/4 w-40 h-40 bg-pink-300/20 rounded-full blur-xl animate-float" style="animation-delay: 4s;"></div>
      <div class="absolute bottom-20 right-1/3 w-28 h-28 bg-orange-300/20 rounded-full blur-xl animate-float" style="animation-delay: 6s;"></div>
    </div>

    <!-- Login Card -->
    <div class="relative z-10 w-full max-w-md mx-4">
      <!-- Glassmorphism Card -->
      <div class="glass-card rounded-3xl p-8 shadow-2xl">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
          <div class="w-16 h-16 bg-gradient-to-br from-rose-400 to-amber-400 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <h1 class="text-3xl font-bold text-slate-800 mb-2">Welcome back</h1>
          <p class="text-slate-600">Sign in to your Asira account</p>
        </div>

        <!-- Error Message -->
        <div v-if="errors.general" class="mb-6 p-4 bg-red-500/20 border border-red-400/30 rounded-2xl backdrop-blur-sm">
          <p class="text-red-700 text-sm font-medium">{{ errors.general }}</p>
        </div>

        <!-- Login Form -->
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Email Field -->
          <div class="space-y-2">
            <label for="email" class="block text-sm font-medium text-slate-700">Email address</label>
            <div class="relative">
              <input
                id="email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                required
                :class="[
                  'w-full px-4 py-3 bg-white/40 border-0 rounded-2xl text-slate-800 placeholder-slate-500 backdrop-blur-sm transition-all duration-200',
                  'focus:outline-none focus:ring-2 focus:ring-rose-400/50 focus:bg-white/60',
                  errors.email ? 'ring-2 ring-red-400/50' : ''
                ]"
                placeholder="Enter your email"
              />
              <svg class="absolute right-3 top-3.5 w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
              </svg>
            </div>
            <p v-if="errors.email" class="text-red-600 text-sm">{{ errors.email }}</p>
          </div>

          <!-- Password Field -->
          <div class="space-y-2">
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                required
                :class="[
                  'w-full px-4 py-3 bg-white/40 border-0 rounded-2xl text-slate-800 placeholder-slate-500 backdrop-blur-sm transition-all duration-200',
                  'focus:outline-none focus:ring-2 focus:ring-rose-400/50 focus:bg-white/60',
                  errors.password ? 'ring-2 ring-red-400/50' : ''
                ]"
                placeholder="Enter your password"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-3.5 text-slate-500 hover:text-slate-700 transition-colors"
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
            <p v-if="errors.password" class="text-red-600 text-sm">{{ errors.password[0] }}</p>
          </div>

          <!-- Forgot Password -->
          <div class="text-right">
            <Link href="/forgot-password" class="text-sm text-slate-600 hover:text-slate-800 transition-colors">
              Forgot your password?
            </Link>
          </div>

          <!-- Sign In Button -->
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full py-3 px-4 bg-gradient-to-r from-rose-500 to-amber-500 text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!isLoading" class="flex items-center justify-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
              </svg>
              Sign in
            </span>
            <span v-else class="flex items-center justify-center gap-2">
              <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Signing in...
            </span>
          </button>

          <!-- Divider -->
          <div v-if="googleLoginEnabled" class="relative my-6">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-slate-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-4 bg-transparent text-slate-600">Or continue with</span>
            </div>
          </div>

          <!-- Google Sign In -->
          <button
            v-if="googleLoginEnabled"
            type="button"
            @click="handleGoogleLogin"
            class="w-full py-3 px-4 bg-white/60 hover:bg-white/80 text-slate-700 font-medium rounded-2xl backdrop-blur-sm transition-all duration-200 flex items-center justify-center gap-3 border border-slate-200 hover:border-slate-300"
          >
            <svg class="w-5 h-5" viewBox="0 0 24 24">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continue with Google
          </button>

          <!-- Google Login Error -->
          <div v-if="googleLoginError" class="p-4 bg-red-500/20 border border-red-400/30 rounded-2xl backdrop-blur-sm">
            <p class="text-red-700 text-sm font-medium">{{ googleLoginError }}</p>
          </div>
        </form>

        <!-- Login Link -->
        <div class="mt-8 text-center">
          <p class="text-slate-600">
            Don't have an account?
            <Link href="/register" class="text-slate-800 font-semibold hover:text-rose-600 transition-colors ml-1">
              Create account
            </Link>
          </p>
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
  // Redirect to Google OAuth
  window.location.href = '/auth/google'
}
</script>

<style scoped>
/* Glassmorphism card */
.glass-card {
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.4);
}

/* Floating animation */
@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  33% { transform: translateY(-10px) rotate(1deg); }
  66% { transform: translateY(-5px) rotate(-1deg); }
}

.animate-float {
  animation: float 8s ease-in-out infinite;
}

/* Smooth transitions */
* {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
