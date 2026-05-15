<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Verify your email</h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          We've sent a verification link to your email address. Please check your inbox and click the link to verify your email.
        </p>
      </div>

      <div v-if="emailSent" class="rounded-md bg-green-50 p-4">
        <p class="text-sm font-medium text-green-800">
          Verification email sent! Please check your inbox.
        </p>
      </div>

      <div class="space-y-4">
        <button
          @click="handleResend"
          :disabled="isLoading"
          class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
        >
          <span v-if="!isLoading">Resend Verification Email</span>
          <span v-else>Sending...</span>
        </button>

        <form @submit.prevent="handleLogout">
          <button
            type="submit"
            class="w-full flex justify-center py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Logout
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const isLoading = ref(false)
const emailSent = ref(false)

const handleResend = async () => {
  isLoading.value = true
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
