<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Accept Invitation</h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Set your password to complete your account setup.
        </p>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleAccept">
        <div v-if="errors.general" class="rounded-md bg-red-50 p-4">
          <p class="text-sm font-medium text-red-800">{{ errors.general }}</p>
        </div>

        <div class="space-y-4">
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-500': errors.password }"
              placeholder="Password"
            />
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
            <PasswordStrengthIndicator :password="form.password" class="mt-2" />
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              placeholder="Confirm Password"
            />
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="isLoading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <span v-if="!isLoading">Accept Invitation</span>
            <span v-else>Processing...</span>
          </button>
        </div>

        <div class="text-center">
          <Link href="/login" class="font-medium text-blue-600 hover:text-blue-500">
            Back to login
          </Link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import PasswordStrengthIndicator from '@/Components/PasswordStrengthIndicator.vue'

defineProps({
  token: {
    type: String,
    required: true
  },
  email: {
    type: String,
    required: true
  }
})

const form = reactive({
  password: '',
  password_confirmation: '',
  token: ''
})

const errors = reactive({
  password: null,
  general: null
})

const isLoading = ref(false)

const handleAccept = async () => {
  isLoading.value = true
  errors.general = null
  errors.password = null

  router.post('/accept-invitation', form, {
    onError: (pageErrors) => {
      if (pageErrors.password) errors.password = pageErrors.password
      if (pageErrors.message) errors.general = pageErrors.message
    },
    onFinish: () => {
      isLoading.value = false
    }
  })
}
</script>

