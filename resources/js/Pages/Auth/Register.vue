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

    <!-- Register Card -->
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
          <h1 class="text-3xl font-bold text-slate-800 mb-2">Join Asira</h1>
          <p class="text-slate-600">Create your account to get started</p>
        </div>

        <!-- Error Message -->
        <div v-if="errors.general" class="mb-6 p-4 bg-red-500/20 border border-red-400/30 rounded-2xl backdrop-blur-sm">
          <p class="text-red-700 text-sm font-medium">{{ errors.general }}</p>
        </div>

        <!-- Register Form -->
        <form @submit.prevent="handleRegister" class="space-y-6">
          <!-- Name Field -->
          <div class="space-y-2">
            <label for="name" class="block text-sm font-medium text-slate-700">Full Name</label>
            <div class="relative">
              <input
                id="name"
                v-model="form.name"
                type="text"
                autocomplete="name"
                required
                :class="[
                  'w-full px-4 py-3 bg-white/40 border-0 rounded-2xl text-slate-800 placeholder-slate-500 backdrop-blur-sm transition-all duration-200',
                  'focus:outline-none focus:ring-2 focus:ring-rose-400/50 focus:bg-white/60',
                  errors.name ? 'ring-2 ring-red-400/50' : ''
                ]"
                placeholder="Enter your full name"
              />
              <svg class="absolute right-3 top-3.5 w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <p v-if="errors.name" class="text-red-600 text-sm">{{ errors.name[0] }}</p>
          </div>

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
            <p v-if="errors.email" class="text-red-600 text-sm">{{ errors.email[0] }}</p>
          </div>

          <!-- Password Field -->
          <div class="space-y-2">
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                :class="[
                  'w-full px-4 py-3 bg-white/40 border-0 rounded-2xl text-slate-800 placeholder-slate-500 backdrop-blur-sm transition-all duration-200',
                  'focus:outline-none focus:ring-2 focus:ring-rose-400/50 focus:bg-white/60',
                  errors.password ? 'ring-2 ring-red-400/50' : ''
                ]"
                placeholder="Create a password"
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
            
            <!-- Password Strength Indicator -->
            <div v-if="form.password" class="mt-2">
              <div class="flex items-center gap-2">
                <div class="flex-1 bg-slate-200/50 rounded-full h-2 backdrop-blur-sm">
                  <div 
                    :class="[
                      'h-2 rounded-full transition-all duration-300',
                      passwordStrength.color
                    ]"
                    :style="{ width: passwordStrength.width }"
                  ></div>
                </div>
                <span class="text-xs text-slate-600">{{ passwordStrength.text }}</span>
              </div>
            </div>
          </div>

          <!-- Confirm Password Field -->
          <div class="space-y-2">
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm Password</label>
            <div class="relative">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                class="w-full px-4 py-3 bg-white/40 border-0 rounded-2xl text-slate-800 placeholder-slate-500 backdrop-blur-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-rose-400/50 focus:bg-white/60"
                placeholder="Confirm your password"
              />
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="absolute right-3 top-3.5 text-slate-500 hover:text-slate-700 transition-colors"
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
            <p v-if="form.password_confirmation && form.password !== form.password_confirmation" class="text-red-600 text-sm">
              Passwords do not match
            </p>
          </div>

          <!-- Terms Checkbox -->
          <div class="flex items-start gap-3">
            <div class="flex items-center h-5">
              <input
                id="terms"
                v-model="form.terms"
                type="checkbox"
                required
                class="w-4 h-4 text-rose-600 bg-white/40 border-slate-300 rounded focus:ring-rose-500 focus:ring-2 backdrop-blur-sm"
              />
            </div>
            <label for="terms" class="text-sm text-slate-600 leading-5">
              I agree to the 
              <a href="/terms" class="text-slate-800 hover:text-rose-600 underline transition-colors">Terms of Service</a>
              and 
              <a href="/privacy" class="text-slate-800 hover:text-rose-600 underline transition-colors">Privacy Policy</a>
            </label>
          </div>

          <!-- Create Account Button -->
          <button
            type="submit"
            :disabled="isLoading || !form.terms || form.password !== form.password_confirmation"
            class="w-full py-3 px-4 bg-gradient-to-r from-rose-500 to-amber-500 text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
          >
            <span v-if="!isLoading" class="flex items-center justify-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
              Create Account
            </span>
            <span v-else class="flex items-center justify-center gap-2">
              <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Creating Account...
            </span>
          </button>
        </form>

        <!-- Login Link -->
        <div class="mt-8 text-center">
          <p class="text-slate-600">
            Already have an account?
            <Link href="/login" class="text-slate-800 font-semibold hover:text-rose-600 transition-colors ml-1">
              Sign in
            </Link>
          </p>
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
  let feedback = []
  
  // Length check
  if (password.length >= 8) score += 1
  else feedback.push('8+ characters')
  
  // Uppercase check
  if (/[A-Z]/.test(password)) score += 1
  else feedback.push('uppercase')
  
  // Lowercase check
  if (/[a-z]/.test(password)) score += 1
  else feedback.push('lowercase')
  
  // Number check
  if (/\d/.test(password)) score += 1
  else feedback.push('number')
  
  // Special character check
  if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) score += 1
  else feedback.push('special char')
  
  const strengthLevels = {
    0: { width: '20%', color: 'bg-red-400', text: 'Very Weak' },
    1: { width: '20%', color: 'bg-red-400', text: 'Very Weak' },
    2: { width: '40%', color: 'bg-orange-400', text: 'Weak' },
    3: { width: '60%', color: 'bg-yellow-400', text: 'Fair' },
    4: { width: '80%', color: 'bg-blue-400', text: 'Good' },
    5: { width: '100%', color: 'bg-green-400', text: 'Strong' }
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
      // Field-level validation errors — wrap in array since template uses [0]
      errors.name = pageErrors.name ? [pageErrors.name] : null
      errors.password = pageErrors.password ? [pageErrors.password] : null

      // email field may carry a general server error message instead of a validation error
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