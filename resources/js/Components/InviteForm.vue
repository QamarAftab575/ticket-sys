<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <div class="space-y-4">
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          required
          class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          :class="{ 'border-red-500': errors.name }"
          placeholder="Full Name"
        />
        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
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
        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
        <select
          id="role"
          v-model="form.role"
          class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          :class="{ 'border-red-500': errors.role }"
        >
          <option value="">Select a role</option>
          <option value="manager">Manager</option>
          <option value="agent">Agent</option>
          <option value="employee">Employee</option>
        </select>
        <p v-if="errors.role" class="mt-1 text-sm text-red-600">{{ errors.role[0] }}</p>
      </div>
    </div>

    <div>
      <button
        type="submit"
        :disabled="isLoading"
        class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
      >
        <span v-if="!isLoading">Send Invitation</span>
        <span v-else>Sending...</span>
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, reactive } from 'vue'

const emit = defineEmits(['submit', 'error'])

const form = reactive({
  name: '',
  email: '',
  role: ''
})

const errors = reactive({
  name: null,
  email: null,
  role: null
})

const isLoading = ref(false)

const handleSubmit = () => {
  // Reset errors
  errors.name = null
  errors.email = null
  errors.role = null

  // Basic validation
  if (!form.name.trim()) {
    errors.name = ['Name is required']
    emit('error', 'Please fill in all required fields')
    return
  }

  if (!form.email.trim()) {
    errors.email = ['Email is required']
    emit('error', 'Please fill in all required fields')
    return
  }

  if (!form.role) {
    errors.role = ['Role is required']
    emit('error', 'Please select a role')
    return
  }

  isLoading.value = true
  emit('submit', {
    name: form.name,
    email: form.email,
    role: form.role
  })

  // Reset form after submission
  setTimeout(() => {
    form.name = ''
    form.email = ''
    form.role = ''
    isLoading.value = false
  }, 1000)
}
</script>

