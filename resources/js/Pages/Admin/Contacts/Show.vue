<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between gap-4">
        <Link
          href="/admin/contacts"
          class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium mb-4"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          {{ $t('back_to_contacts') }}
        </Link>
        <button
          @click="showActions = !showActions"
          class="flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors md:hidden"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
          </svg>
          {{ $t('actions') }}
        </button>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Contact Info Card -->
      <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h2 class="text-2xl font-bold text-white">{{ contact.name }}</h2>
              <p class="text-blue-100 text-sm mt-1">{{ contact.email }}</p>
            </div>
            <span :class="getStatusBadgeClass(contact.status)">
              {{ capitalizeStatus(contact.status) }}
            </span>
          </div>
        </div>

        <!-- Contact Details -->
        <div class="px-6 py-4 space-y-4 border-b border-slate-200">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $t('table_subject') }}</label>
              <p class="text-slate-900 font-medium mt-1">{{ contact.subject }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $t('submitted_on') }}</label>
              <p class="text-slate-900 font-medium mt-1">{{ formatDateTime(contact.created_at) }}</p>
            </div>
          </div>

          <!-- Message -->
          <div>
            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $t('message') }}</label>
            <div class="mt-2 bg-slate-50 rounded-lg p-4 border border-slate-200">
              <p class="text-slate-900 whitespace-pre-wrap text-sm leading-relaxed">{{ contact.message }}</p>
            </div>
            <!-- Copy Message Button -->
            <div class="mt-2 flex gap-2">
              <button
                @click="copyMessage"
                class="inline-flex items-center gap-2 px-3 py-2 text-xs sm:text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                {{ copyMessageText }}
              </button>
            </div>
          </div>
        </div>

        <!-- Admin Reply (if exists) -->
        <div v-if="contact.admin_reply" class="px-6 py-4 bg-green-50 border-b border-green-200">
          <div class="flex items-start gap-3">
            <div class="w-2 h-2 rounded-full bg-green-600 mt-2 flex-shrink-0"></div>
            <div class="flex-1">
              <label class="text-xs font-semibold text-green-900 uppercase tracking-wide">{{ $t('admin_reply') }}</label>
              <p class="text-green-900 mt-2 whitespace-pre-wrap text-sm leading-relaxed">{{ contact.admin_reply }}</p>
              <p class="text-xs text-green-700 mt-2">{{ $t('replied_on') }} {{ formatDateTime(contact.replied_at) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div :class="['space-y-3', showActions ? '' : 'hidden md:block']">
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
          <h3 class="font-semibold text-slate-900 mb-4">{{ $t('actions') }}</h3>
          <div class="space-y-2 flex flex-col">
            <!-- Gmail Link -->
            <a
              :href="`mailto:${contact.email}?subject=Re: ${encodeURIComponent(contact.subject)}&body=Hello ${encodeURIComponent(contact.name)},%0D%0A%0D%0A`"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-700 font-medium rounded-lg hover:bg-red-100 transition-colors text-sm"
              @click="toast.info(page.props.translations?.opening_email || 'Opening email client...')"
            >
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
              </svg>
              {{ $t('open_gmail') }}
            </a>

            <!-- Mark as Read -->
            <button
              v-if="contact.status === 'new'"
              @click="markAsRead"
              class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 font-medium rounded-lg hover:bg-blue-100 transition-colors text-sm"
              :disabled="isSubmitting"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              {{ $t('mark_as_read') }}
            </button>

            <!-- Mark as Closed -->
            <button
              v-if="contact.status !== 'closed'"
              @click="markAsClosed"
              class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-700 font-medium rounded-lg hover:bg-slate-200 transition-colors text-sm"
              :disabled="isSubmitting"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              {{ $t('mark_as_closed') }}
            </button>

            <!-- Delete -->
            <button
              @click="confirmDelete"
              class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-700 font-medium rounded-lg hover:bg-red-100 transition-colors text-sm"
              :disabled="isSubmitting"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              {{ $t('delete_contact') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div class="bg-white rounded-lg shadow-xl max-w-sm w-full">
            <div class="p-6">
              <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 0v2m0-6v-2m0 0v-2m0 6v2m4-6h.01M8 19h8a2 2 0 002-2V7a2 2 0 00-2-2H8a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <h3 class="mt-4 text-lg font-medium text-slate-900 text-center">Delete Contact</h3>
              <p class="mt-2 text-sm text-slate-600 text-center">
                Are you sure you want to delete this contact message? This action cannot be undone.
              </p>
            </div>
            <div class="bg-slate-50 px-6 py-4 flex gap-3 justify-end rounded-b-lg">
              <button
                @click="showDeleteModal = false"
                class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors"
              >
                Cancel
              </button>
              <button
                @click="deleteContact"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"
                :disabled="isSubmitting"
              >
                {{ isSubmitting ? 'Deleting...' : 'Delete' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  contact: Object,
})

const page = usePage()

// State
const showActions = ref(false)
const showDeleteModal = ref(false)
const isSubmitting = ref(false)
const copyMessageText = ref(page.props.translations?.copy_message || 'Copy Message')

// Show success message if it exists
if (page.props.flash?.success) {
  toast.success(page.props.flash.success)
}

// Methods
const markAsRead = () => {
  isSubmitting.value = true
  router.patch(
    `/admin/contacts/${props.contact.id}/mark-as-read`,
    {},
    {
      onSuccess: () => {
        toast.success(page.props.translations?.contact_marked_read || 'Contact marked as read successfully')
      },
      onError: (errors) => {
        toast.error(page.props.translations?.failed_mark_read || 'Failed to mark contact as read')
        console.error('Error:', errors)
      },
      onFinish: () => {
        isSubmitting.value = false
      }
    }
  )
}

const markAsClosed = () => {
  isSubmitting.value = true
  router.patch(
    `/admin/contacts/${props.contact.id}/mark-as-closed`,
    {},
    {
      onSuccess: () => {
        toast.success(page.props.translations?.contact_marked_closed || 'Contact marked as closed successfully')
      },
      onError: (errors) => {
        toast.error(page.props.translations?.failed_mark_closed || 'Failed to mark contact as closed')
        console.error('Error:', errors)
      },
      onFinish: () => {
        isSubmitting.value = false
      }
    }
  )
}

const confirmDelete = () => {
  showDeleteModal.value = true
}

const deleteContact = () => {
  isSubmitting.value = true
  showDeleteModal.value = false
  router.delete(
    `/admin/contacts/${props.contact.id}`,
    {
      onSuccess: () => {
        toast.success(page.props.translations?.contact_deleted || 'Contact deleted successfully')
      },
      onError: (errors) => {
        toast.error(page.props.translations?.failed_delete_contact || 'Failed to delete contact')
        console.error('Error:', errors)
      },
      onFinish: () => {
        isSubmitting.value = false
      }
    }
  )
}

const copyMessage = async () => {
  try {
    await navigator.clipboard.writeText(props.contact.message)
    copyMessageText.value = 'Copied!'
    toast.success('Message copied to clipboard')
    setTimeout(() => {
      copyMessageText.value = 'Copy Message'
    }, 2000)
  } catch (error) {
    console.error('Failed to copy message:', error)
    toast.error('Failed to copy message to clipboard')
    
    // Fallback for older browsers
    try {
      const textArea = document.createElement('textarea')
      textArea.value = props.contact.message
      document.body.appendChild(textArea)
      textArea.select()
      document.execCommand('copy')
      document.body.removeChild(textArea)
      
      copyMessageText.value = 'Copied!'
      toast.success('Message copied to clipboard')
      setTimeout(() => {
        copyMessageText.value = 'Copy Message'
      }, 2000)
    } catch (fallbackError) {
      console.error('Fallback copy failed:', fallbackError)
      toast.error('Your browser does not support clipboard access')
    }
  }
}

const capitalizeStatus = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const getStatusBadgeClass = (status) => {
  const baseClass = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold'
  const statusClasses = {
    'new': `${baseClass} bg-blue-100 text-blue-800`,
    'read': `${baseClass} bg-yellow-100 text-yellow-800`,
    'replied': `${baseClass} bg-green-100 text-green-800`,
    'closed': `${baseClass} bg-slate-100 text-slate-800`,
  }
  return statusClasses[status] || baseClass
}

const formatDateTime = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s;
}

.modal-enter-from, .modal-leave-to {
  opacity: 0;
}
</style>
