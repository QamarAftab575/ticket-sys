<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Reset your password</h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Enter your email address and we'll send you a link to reset your password.
        </p>
      </div>

      <div v-if="emailSent" class="rounded-md bg-green-50 p-4">
        <p class="text-sm font-medium text-green-800">
          If an account exists with that email, you will receive a password reset link shortly.
        </p>
      </div>

      <form v-if="!emailSent" class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div v-if="errors.general" class="rounded-md bg-red-50 p-4">
          <p class="text-sm font-medium text-red-800">{{ errors.general }}</p>
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            :class="{ 'border-red-500': errors.email }"
            placeholder="Email address"
          />
          <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
        </div>

        <div>
          <button
            type="submit"
            :disabled="isLoading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <span v-if="!isLoading">Send Reset Link</span>
            <span v-else>Sending...</span>
          </button>
        </div>
      </form>

      <div class="text-center">
        <Link href="/login" class="font-medium text-blue-600 hover:text-blue-500">
          Back to login
        </Link>
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
