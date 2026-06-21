<template>
  <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="space-y-8">
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900">Invite Members</h1>
        <p class="mt-2 text-sm text-gray-600">
          Invite new members to join your workspace.
        </p>
      </div>

      <div v-if="successMessage" class="rounded-md bg-green-50 p-4">
        <p class="text-sm font-medium text-green-800">{{ successMessage }}</p>
      </div>

      <div v-if="errorMessage" class="rounded-md bg-red-50 p-4">
        <p class="text-sm font-medium text-red-800">{{ errorMessage }}</p>
      </div>

      <div class="bg-white shadow rounded-lg p-6">
        <InviteForm @submit="handleInviteSubmit" @error="handleInviteError" />
      </div>

      <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Pending Invitations</h2>
        <PendingInvitesList
          :invitations="invitations"
          @resend="handleResend"
          @cancel="handleCancel"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import InviteForm from '@/Components/InviteForm.vue'
import PendingInvitesList from '@/Components/PendingInvitesList.vue'

defineProps({
  invitations: {
    type: Array,
    default: () => []
  }
})

const successMessage = ref('')
const errorMessage = ref('')

const handleInviteSubmit = (formData) => {
  successMessage.value = ''
  errorMessage.value = ''

  router.post('/invitations', formData, {
    onSuccess: () => {
      successMessage.value = 'Invitation sent successfully!'
      setTimeout(() => {
        successMessage.value = ''
      }, 3000)
    },
    onError: (errors) => {
      if (errors.message) {
        errorMessage.value = errors.message
      } else {
        errorMessage.value = 'Failed to send invitation. Please try again.'
      }
    }
  })
}

const handleInviteError = (error) => {
  errorMessage.value = error
}

const handleResend = (invitationId) => {
  router.post(`/invitations/${invitationId}/resend`, {}, {
    onSuccess: () => {
      successMessage.value = 'Invitation resent successfully!'
      setTimeout(() => {
        successMessage.value = ''
      }, 3000)
    },
    onError: () => {
      errorMessage.value = 'Failed to resend invitation. Please try again.'
    }
  })
}

const handleCancel = (invitationId) => {
  if (confirm('Are you sure you want to cancel this invitation?')) {
    router.delete(`/invitations/${invitationId}`, {
      onSuccess: () => {
        successMessage.value = 'Invitation cancelled successfully!'
        setTimeout(() => {
          successMessage.value = ''
        }, 3000)
      },
      onError: () => {
        errorMessage.value = 'Failed to cancel invitation. Please try again.'
      }
    })
  }
}
</script>

