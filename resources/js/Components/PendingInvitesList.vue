<template>
  <div class="overflow-x-auto">
    <table v-if="invitations.length > 0" class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Email
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Sent Date
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Expires
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Status
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Actions
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="invitation in invitations" :key="invitation.id">
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            {{ invitation.user.email }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ formatDate(invitation.created_at) }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ formatDate(invitation.expires_at) }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm">
            <span
              v-if="isExpired(invitation.expires_at)"
              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"
            >
              Expired
            </span>
            <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
              Pending
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
            <button
              @click="$emit('resend', invitation.id)"
              :disabled="isExpired(invitation.expires_at)"
              class="text-blue-600 hover:text-blue-900 disabled:text-gray-400 disabled:cursor-not-allowed"
            >
              Resend
            </button>
            <button
              @click="$emit('cancel', invitation.id)"
              class="text-red-600 hover:text-red-900"
            >
              Cancel
            </button>
          </td>
        </tr>
      </tbody>
    </table>
    <div v-else class="text-center py-12">
      <p class="text-gray-500">No pending invitations</p>
    </div>
  </div>
</template>

<script setup>
import { defineEmits } from 'vue'

defineProps({
  invitations: {
    type: Array,
    default: () => []
  }
})

defineEmits(['resend', 'cancel'])

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const isExpired = (expiresAt) => {
  return new Date(expiresAt) < new Date()
}
</script>

