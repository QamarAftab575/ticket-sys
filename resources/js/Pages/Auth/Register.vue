<template>
  <div class="min-h-screen flex bg-white dark:bg-slate-950">
    <!-- Left Side - Register Form -->
    <div class="flex-1 flex items-center justify-center px-6 py-12 overflow-y-auto">
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
            <img src="/assets/images/logo/asira-logo-main.png" alt="Asira" class="h-8 w-auto" />
          </Link>
        </div>

        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">Create an account</h1>
          <p class="text-slate-600 dark:text-slate-400">Start your 14-day free trial. No credit card required.</p>
        </div>

        <!-- Error Message -->
        <div v-if="errors.general" class="mb-6 p-4 bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800 rounded-lg">
          <p class="text-red-700 dark:text-red-400 text-sm">{{ errors.general }}</p>
        </div>

        <!-- Register Form -->
        <form @submit.prevent="handleRegister" class="space-y-5">
          <!-- Name Field -->
          <div class="space-y-2">
            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
              Full name
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              autocomplete="name"
              required
              :class="[
                'w-full px-4 py-2.5 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-colors duration-200',
                'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                errors.name ? 'border-red-300 dark:border-red-700' : 'border-slate-300 dark:border-slate-700'
              ]"
              placeholder="John Doe"
            />
            <p v-if="errors.name" class="text-red-600 dark:text-red-400 text-sm">{{ errors.name[0] }}</p>
          </div>

          <!-- Email Field -->
          <div class="space-y-2">
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
              Email address
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
              placeholder="name@company.com"
            />
            <p v-if="errors.email" class="text-red-600 dark:text-red-400 text-sm">{{ errors.email[0] }}</p>
          </div>

          <!-- Password Field -->
          <div class="space-y-2">
            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
              Password
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
            
            <!-- Password Strength Indicator -->
            <div v-if="form.password" class="space-y-2">
              <div class="flex items-center gap-2">
                <div class="flex-1 bg-slate-200 dark:bg-slate-800 rounded-full h-1.5">
                  <div 
                    :class="[
                      'h-1.5 rounded-full transition-all duration-300',
                      passwordStrength.color
                    ]"
                    :style="{ width: passwordStrength.width }"
                  ></div>
                </div>
                <span class="text-xs font-medium text-slate-600 dark:text-slate-400">{{ passwordStrength.text }}</span>
              </div>
              <p class="text-xs text-slate-500 dark:text-slate-400">Use 8+ characters with a mix of letters, numbers & symbols</p>
            </div>
          </div>

          <!-- Confirm Password Field -->
          <div class="space-y-2">
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
              Confirm password
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
              Passwords do not match
            </p>
          </div>

          <!-- Terms Checkbox -->
          <div class="flex items-start gap-3 pt-2">
            <div class="flex items-center h-5">
              <input
                id="terms"
                v-model="form.terms"
                type="checkbox"
                required
                class="w-4 h-4 text-blue-600 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer"
              />
            </div>
            <label for="terms" class="text-sm text-slate-600 dark:text-slate-400 leading-5 cursor-pointer">
              I agree to the 
              <a href="/terms" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium transition-colors">Terms of Service</a>
              and 
              <a href="/privacy" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium transition-colors">Privacy Policy</a>
            </label>
          </div>

          <!-- Create Account Button -->
          <button
            type="submit"
            :disabled="isLoading || !form.terms || form.password !== form.password_confirmation"
            class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <span v-if="!isLoading">Create account</span>
            <span v-else class="flex items-center gap-2">
              <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Creating account...
            </span>
          </button>
        </form>

        <!-- Sign In Link -->
        <div class="mt-8 text-center">
          <p class="text-sm text-slate-600 dark:text-slate-400">
            Already have an account?
            <Link href="/login" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium transition-colors cursor-pointer ml-1">
              Sign in
            </Link>
          </p>
        </div>
      </div>
    </div>

    <!-- Right Side - Benefits -->
    <div class="hidden lg:flex flex-1 bg-slate-50 dark:bg-slate-900 items-center justify-center p-12 relative overflow-hidden">
      <!-- Subtle gradient background -->
      <div class="absolute inset-0 bg-gradient-to-br from-blue-50 dark:from-blue-950/20 to-slate-50 dark:to-slate-900"></div>
      
      <!-- Grid pattern -->
      <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzAwMDAwMCIgc3Ryb2tlLW9wYWNpdHk9IjAuMDMiIHN0cm9rZS13aWR0aD0iMSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIvPjwvc3ZnPg==')] opacity-40 dark:opacity-20"></div>

      <div class="relative z-10 max-w-lg">
        <!-- Header -->
        <div class="mb-10">
          <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-100 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 rounded-full text-sm font-medium mb-6 border border-blue-200 dark:border-blue-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
            </svg>
            <span>Join 50,000+ teams</span>
          </div>
          <h2 class="text-4xl font-bold text-slate-900 dark:text-white mb-4 leading-tight">
            Start shipping<br />better projects
          </h2>
          <p class="text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
            Everything you need to manage projects and collaborate with your team.
          </p>
        </div>

        <!-- Benefits -->
        <div class="space-y-6">
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-950 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-slate-900 dark:text-white mb-1">14-day free trial</h3>
              <p class="text-sm text-slate-600 dark:text-slate-400">Try all features free for 14 days. No credit card required.</p>
            </div>
          </div>

          <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-950 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Enterprise security</h3>
              <p class="text-sm text-slate-600 dark:text-slate-400">Bank-level encryption and SOC 2 Type II certified.</p>
            </div>
          </div>

          <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-950 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Unlimited team members</h3>
              <p class="text-sm text-slate-600 dark:text-slate-400">Invite your entire team. No per-user pricing on Pro plan.</p>
            </div>
          </div>

          <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-950 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Lightning fast</h3>
              <p class="text-sm text-slate-600 dark:text-slate-400">Built for speed with real-time updates and offline support.</p>
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div class="mt-12 grid grid-cols-3 gap-8 pt-8 border-t border-slate-200 dark:border-slate-800">
          <div>
            <div class="text-3xl font-bold text-slate-900 dark:text-white mb-1">50K+</div>
            <div class="text-sm text-slate-600 dark:text-slate-400">Active teams</div>
          </div>
          <div>
            <div class="text-3xl font-bold text-slate-900 dark:text-white mb-1">10M+</div>
            <div class="text-sm text-slate-600 dark:text-slate-400">Tasks completed</div>
          </div>
          <div>
            <div class="text-3xl font-bold text-slate-900 dark:text-white mb-1">4.9/5</div>
            <div class="text-sm text-slate-600 dark:text-slate-400">User rating</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false
})

const errors = reactive({
  name: null,
  email: null,
  password: null,
  general: null
})

const isLoading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

// Password strength calculation
const passwordStrength = computed(() => {
  const password = form.password
  if (!password) return { width: '0%', color: '', text: '' }
  
  let score = 0
  
  // Length check
  if (password.length >= 8) score += 1
  
  // Uppercase check
  if (/[A-Z]/.test(password)) score += 1
  
  // Lowercase check
  if (/[a-z]/.test(password)) score += 1
  
  // Number check
  if (/\d/.test(password)) score += 1
  
  // Special character check
  if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) score += 1
  
  const strengthLevels = {
    0: { width: '20%', color: 'bg-red-500', text: 'Very Weak' },
    1: { width: '20%', color: 'bg-red-500', text: 'Very Weak' },
    2: { width: '40%', color: 'bg-orange-500', text: 'Weak' },
    3: { width: '60%', color: 'bg-yellow-500', text: 'Fair' },
    4: { width: '80%', color: 'bg-blue-500', text: 'Good' },
    5: { width: '100%', color: 'bg-green-500', text: 'Strong' }
  }
  
  return strengthLevels[score] || strengthLevels[0]
})

const handleRegister = async () => {
  isLoading.value = true
  errors.general = null
  errors.name = null
  errors.email = null
  errors.password = null

  router.post('/register', form, {
    onError: (pageErrors) => {
      errors.name = pageErrors.name ? [pageErrors.name] : null
      errors.password = pageErrors.password ? [pageErrors.password] : null

      const emailError = pageErrors.email || null
      const isGeneralError = emailError && !emailError.toLowerCase().includes('email')
      if (isGeneralError) {
        toast.error(emailError)
        errors.email = null
      } else {
        errors.email = emailError ? [emailError] : null
      }

      if (pageErrors.message) {
        toast.error(pageErrors.message)
        errors.general = null
      }
    },
    onFinish: () => {
      isLoading.value = false
    }
  })
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
