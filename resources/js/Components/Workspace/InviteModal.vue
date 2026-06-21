<template>
  <!-- Backdrop -->
  <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="$emit('close')">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
      <!-- Header -->
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Invite people to {{ workspace.name }}</h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Body -->
      <div class="px-6 py-5 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email Addresses</label>
          <EmailTagInput ref="tagInputRef" v-model="emailTags" />
          <p class="text-xs text-gray-500 mt-2">All invited members join as Member.</p>
        </div>

        <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>
      </div>

      <!-- Footer -->
      <div class="flex justify-end px-6 py-4 border-t border-gray-200">
        <button
          @click="send"
          :disabled="isLoading || emailTags.length === 0 || invalidCount > 0"
          class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          {{ isLoading ? 'Sending...' : 'Send' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import EmailTagInput from '@/Components/EmailTagInput.vue'

const props = defineProps({ workspace: Object })
const emit = defineEmits(['close', 'invited', 'error'])

const tagInputRef = ref(null)
const emailTags = ref([])
const isLoading = ref(false)
const errorMessage = ref('')

const invalidCount = computed(() => emailTags.value.filter(t => !t.valid).length)

const send = async () => {
  tagInputRef.value?.commit()

  if (emailTags.value.length === 0) return
  if (invalidCount.value > 0) return

  isLoading.value = true
  errorMessage.value = ''

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content

    const results = await Promise.allSettled(
      emailTags.value.map(tag =>
        fetch(`/invitations`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            organization_id: props.workspace.id,
            email: tag.email,
            role: 'member',
          }),
        }).then(async res => {
          if (!res.ok) {
            const data = await res.json()
            throw new Error(data.message || 'Failed to send invitation')
          }
          return res.json()
        })
      )
    )

    const failed = results.filter(r => r.status === 'rejected')
    if (failed.length > 0) {
      errorMessage.value = failed.map(f => f.reason?.message).join(', ')
      emit('error', errorMessage.value)
    } else {
      emit('invited')
    }
  } catch (e) {
    errorMessage.value = e.message || 'Something went wrong.'
    emit('error', errorMessage.value)
  } finally {
    isLoading.value = false
  }
}
</script>

