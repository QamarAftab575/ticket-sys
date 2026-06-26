<template>
  <AppLayout :user-workspaces="userWorkspaces" :current-workspace="currentWorkspace" :user-role="userRole">
    <div class="py-12">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <!-- Profile Picture Card -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $t('profile_picture') }}</h3>
          
          <div class="flex items-center space-x-6">
            <!-- Avatar Preview -->
            <div class="relative">
              <div v-if="avatarPreview || props.user?.avatar" class="w-24 h-24 rounded-full overflow-hidden border-2 border-gray-200">
                <img 
                  :src="avatarPreview || props.user?.avatar" 
                  :alt="$t('profile_picture')"
                  class="w-full h-full object-cover"
                />
              </div>
              <div v-else class="w-24 h-24 rounded-full bg-blue-500 flex items-center justify-center text-white text-2xl font-semibold">
                {{ getInitials(props.user?.name) }}
              </div>
            </div>

            <!-- Upload Controls -->
            <div class="flex-1 space-y-3">
              <div>
                <input
                  ref="fileInput"
                  type="file"
                  accept="image/jpeg,image/png,image/jpg,image/gif"
                  @change="handleFileSelect"
                  class="hidden"
                />
                <button
                  @click="$refs.fileInput.click()"
                  class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                  {{ props.user?.avatar || avatarPreview ? $t('change_picture') : $t('upload_picture') }}
                </button>
                <button
                  v-if="props.user?.avatar || avatarPreview"
                  @click="removeAvatar"
                  :disabled="isRemovingAvatar"
                  class="ml-2 px-4 py-2 border border-red-600 text-red-600 rounded-lg hover:bg-red-50 disabled:bg-gray-100 disabled:text-gray-400 disabled:border-gray-300 transition-colors"
                >
                  {{ isRemovingAvatar ? $t('removing') : $t('delete') }}
                </button>
              </div>
              <p class="text-xs text-gray-500">{{ $t('image_format_help') }}</p>
              <p v-if="errors.avatar" class="text-sm text-red-600">{{ errors.avatar }}</p>
              
              <!-- Upload button (shown when file is selected) -->
              <div v-if="selectedFile && avatarPreview" class="flex space-x-2">
                <button
                  @click="uploadAvatar"
                  :disabled="isUploadingAvatar"
                  class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 transition-colors"
                >
                  {{ isUploadingAvatar ? $t('uploading') : $t('save_picture') }}
                </button>
                <button
                  @click="cancelUpload"
                  :disabled="isUploadingAvatar"
                  class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 disabled:bg-gray-100 transition-colors"
                >
                  {{ $t('cancel') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Profile Information Card -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $t('profile_information') }}</h3>
          
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ $t('name') }}</label>
              <input
                v-model="form.name"
                type="text"
                class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">{{ $t('email') }}</label>
              <input
                :value="props.user?.email"
                type="email"
                disabled
                class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
              />
              <p class="mt-1 text-xs text-gray-500">{{ $t('email_cannot_change') }}</p>
            </div>

            <!-- Timezone -->
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ $t('timezone') }}</label>
              <SearchableSelect
                v-model="form.timezone"
                :options="timezoneOptions"
                value-key="timezone"
                label-key="label"
                :placeholder="$t('select_timezone')"
                :search-placeholder="$t('search_timezone')"
                clearable
                :clear-label="$t('no_timezone')"
                class="mt-1"
              />
              <p v-if="errors.timezone" class="mt-1 text-sm text-red-600">{{ errors.timezone }}</p>
              <p class="mt-1 text-xs text-gray-500">
                {{ $t('timezone_description') }}
              </p>
            </div>

            <button
              @click="updateProfile"
              :disabled="isUpdating"
              class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition-colors"
            >
              {{ isUpdating ? $t('saving') : $t('save_changes') }}
            </button>
          </div>
        </div>

        <!-- Change Password Card -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $t('change_password') }}</h3>
          
          <div class="space-y-4">
            <p class="text-sm text-gray-600">
              {{ $t('password_update_desc') }} {{ props.user?.email }}.
            </p>

            <div>
              <label class="block text-sm font-medium text-gray-700">{{ $t('new_password') }}</label>
              <div class="relative">
                <input
                  v-model="passwordForm.password"
                  :type="showPassword ? 'text' : 'password'"
                  :placeholder="$t('enter_new_password')"
                  class="mt-1 w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                >
                  <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                  </svg>
                </button>
              </div>
              <p class="mt-1 text-xs text-gray-500">{{ $t('password_requirements') }}</p>
              <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">{{ $t('confirm_password') }}</label>
              <div class="relative">
                <input
                  v-model="passwordForm.password_confirmation"
                  :type="showPasswordConfirmation ? 'text' : 'password'"
                  :placeholder="$t('confirm_password_placeholder')"
                  class="mt-1 w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <button
                  type="button"
                  @click="showPasswordConfirmation = !showPasswordConfirmation"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                >
                  <svg v-if="!showPasswordConfirmation" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                  </svg>
                </button>
              </div>
              <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ errors.password_confirmation }}</p>
            </div>

            <button
              @click="changePassword"
              :disabled="isChangingPassword"
              class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition-colors"
            >
              {{ isChangingPassword ? $t('changing_password') : $t('change_password') }}
            </button>
          </div>
        </div>

        <!-- Delete Account Card -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $t('delete_account') }}</h3>
          
          <div class="space-y-4">
            <p class="text-sm text-gray-600">
              {{ $t('delete_account_desc') }}
            </p>

            <button
              @click="showDeleteConfirm = true"
              class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
            >
              {{ $t('delete_account') }}
            </button>
          </div>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
          <p class="text-green-800">{{ successMessage }}</p>
        </div>
      </div>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $t('delete_account') }}</h3>

        <div class="space-y-4">
          <p class="text-sm text-gray-600">
            {{ $t('delete_confirm_text') }}
          </p>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ $t('type_email_confirm') }}</label>
            <input
              v-model="deleteForm.email"
              type="email"
              :placeholder="props.user?.email"
              class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
            />
            <p v-if="errors.deleteEmail" class="mt-1 text-sm text-red-600">{{ errors.deleteEmail }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ $t('enter_password') }}</label>
            <input
              v-model="deleteForm.password"
              type="password"
              :placeholder="$t('enter_password')"
              class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
            />
            <p v-if="errors.deletePassword" class="mt-1 text-sm text-red-600">{{ errors.deletePassword }}</p>
          </div>

          <div class="flex space-x-3">
            <button
              @click="deleteAccount"
              :disabled="isDeletingAccount"
              class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:bg-gray-400"
            >
              {{ isDeletingAccount ? $t('deleting') : $t('delete_account') }}
            </button>
            <button
              @click="showDeleteConfirm = false"
              class="flex-1 px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              {{ $t('cancel') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'

const page = usePage()

const props = defineProps({
  user: {
    type: Object,
    default: () => ({})
  },
  timezones: {
    type: Array,
    default: () => []
  },
  userWorkspaces: Array,
  currentWorkspace: Object,
  userRole: String
})

// Flatten timezones for the SearchableSelect (already has timezone + label keys)
const timezoneOptions = computed(() => props.timezones)

const form = reactive({
  name: props.user?.name || '',
  timezone: props.user?.timezone || ''
})

const passwordForm = reactive({
  password: '',
  password_confirmation: ''
})

const deleteForm = reactive({
  email: '',
  password: ''
})

const errors = reactive({
  name: '',
  avatar: '',
  timezone: '',
  password: '',
  password_confirmation: '',
  deleteEmail: '',
  deletePassword: ''
})

const fileInput = ref(null)
const selectedFile = ref(null)
const avatarPreview = ref(null)
const showDeleteConfirm = ref(false)
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const isUpdating = ref(false)
const isUploadingAvatar = ref(false)
const isRemovingAvatar = ref(false)
const isChangingPassword = ref(false)
const isDeletingAccount = ref(false)
const successMessage = ref('')

const getInitials = (name) => {
  if (!name) return '?'
  const parts = name.split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (!file) return

  // Validate file type
  const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']
  if (!validTypes.includes(file.type)) {
    errors.avatar = page.props.translations?.invalid_image_format || 'Please select a valid image file (JPG, PNG, or GIF)'
    return
  }

  // Validate file size (2MB)
  if (file.size > 2 * 1024 * 1024) {
    errors.avatar = page.props.translations?.image_too_large || 'Image size must be less than 2MB'
    return
  }

  errors.avatar = ''
  selectedFile.value = file

  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    avatarPreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

const uploadAvatar = () => {
  if (!selectedFile.value) return

  const formData = new FormData()
  formData.append('avatar', selectedFile.value)

  isUploadingAvatar.value = true
  router.post('/profile/upload-avatar', formData, {
    onSuccess: () => {
      successMessage.value = page.props.translations?.profile_picture_updated || 'Profile picture updated successfully!'
      selectedFile.value = null
      avatarPreview.value = null
      if (fileInput.value) {
        fileInput.value.value = ''
      }
      setTimeout(() => {
        successMessage.value = ''
      }, 3000)
    },
    onError: (pageErrors) => {
      if (pageErrors.avatar) errors.avatar = pageErrors.avatar
    },
    onFinish: () => {
      isUploadingAvatar.value = false
    }
  })
}

const cancelUpload = () => {
  selectedFile.value = null
  avatarPreview.value = null
  errors.avatar = ''
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const removeAvatar = () => {
  const confirmMessage = page.props.translations?.confirm_remove_picture || 'Are you sure you want to remove your profile picture?'
  if (!confirm(confirmMessage)) {
    return
  }

  isRemovingAvatar.value = true
  router.delete('/profile/remove-avatar', {
    onSuccess: () => {
      successMessage.value = page.props.translations?.profile_picture_removed || 'Profile picture removed successfully!'
      avatarPreview.value = null
      selectedFile.value = null
      if (fileInput.value) {
        fileInput.value.value = ''
      }
      setTimeout(() => {
        successMessage.value = ''
      }, 3000)
    },
    onError: (pageErrors) => {
      if (pageErrors.avatar) errors.avatar = pageErrors.avatar
    },
    onFinish: () => {
      isRemovingAvatar.value = false
    }
  })
}

const formatRole = (role) => {
  if (!role) return 'No role assigned'
  return role.charAt(0).toUpperCase() + role.slice(1).replace('-', ' ')
}

const updateProfile = () => {
  errors.name = ''
  errors.timezone = ''
  
  if (!form.name.trim()) {
    errors.name = page.props.translations?.name_required || 'Name is required'
    return
  }

  isUpdating.value = true
  router.put('/profile/update', form, {
    onSuccess: () => {
      successMessage.value = page.props.translations?.profile_updated || 'Profile updated successfully!'
      setTimeout(() => {
        successMessage.value = ''
      }, 3000)
    },
    onError: (pageErrors) => {
      if (pageErrors.name) errors.name = pageErrors.name
      if (pageErrors.timezone) errors.timezone = pageErrors.timezone
    },
    onFinish: () => {
      isUpdating.value = false
    }
  })
}

const changePassword = () => {
  errors.password = ''
  errors.password_confirmation = ''

  if (!passwordForm.password.trim()) {
    errors.password = page.props.translations?.password_required || 'Password is required'
    return
  }

  if (!passwordForm.password_confirmation.trim()) {
    errors.password_confirmation = page.props.translations?.password_confirm_required || 'Please confirm your password'
    return
  }

  if (passwordForm.password !== passwordForm.password_confirmation) {
    errors.password_confirmation = page.props.translations?.passwords_not_match || 'Passwords do not match'
    return
  }

  isChangingPassword.value = true
  router.post('/profile/change-password', passwordForm, {
    onSuccess: () => {
      successMessage.value = page.props.translations?.password_changed || 'Password changed successfully! A confirmation email has been sent.'
      passwordForm.password = ''
      passwordForm.password_confirmation = ''
      setTimeout(() => {
        successMessage.value = ''
      }, 5000)
    },
    onError: (pageErrors) => {
      if (pageErrors.password) errors.password = pageErrors.password
      if (pageErrors.password_confirmation) errors.password_confirmation = pageErrors.password_confirmation
    },
    onFinish: () => {
      isChangingPassword.value = false
    }
  })
}

const deleteAccount = () => {
  errors.deleteEmail = ''
  errors.deletePassword = ''

  if (deleteForm.email !== props.user?.email) {
    errors.deleteEmail = page.props.translations?.email_not_match || 'Email does not match'
    return
  }

  if (!deleteForm.password.trim()) {
    errors.deletePassword = page.props.translations?.password_required || 'Password is required'
    return
  }

  isDeletingAccount.value = true
  router.delete('/profile/delete', {
    data: deleteForm,
    onError: (pageErrors) => {
      if (pageErrors.deleteEmail) errors.deleteEmail = pageErrors.deleteEmail
      if (pageErrors.deletePassword) errors.deletePassword = pageErrors.deletePassword
    },
    onFinish: () => {
      isDeletingAccount.value = false
    }
  })
}
</script>

