<template>
  <div class="space-y-4">
    <!-- Upload area -->
    <div
      @drop.prevent="handleDrop"
      @dragover.prevent="isDragging = true"
      @dragleave="isDragging = false"
      :class="[
        'border-2 border-dashed rounded-lg p-8 text-center transition-colors',
        isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 bg-gray-50 hover:border-gray-400',
      ]"
    >
      <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
      </svg>
      <p class="text-gray-700 font-medium mb-2">Drag and drop files here</p>
      <p class="text-gray-600 text-sm mb-4">or</p>
      <button
        @click="triggerFileInput"
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
      >
        Select files
      </button>
      <p class="text-gray-500 text-xs mt-4">Maximum file size: 100 MB</p>
    </div>

    <!-- File input -->
    <input
      ref="fileInput"
      type="file"
      multiple
      class="hidden"
      @change="handleFileSelect"
      aria-label="Select files to upload"
    />

    <!-- Upload progress -->
    <div v-if="uploadingFiles.length > 0" class="space-y-3">
      <div v-for="file in uploadingFiles" :key="file.name" class="bg-white border border-gray-200 rounded-lg p-3">
        <div class="flex items-center justify-between mb-2">
          <p class="text-sm font-medium text-gray-900 truncate">{{ file.name }}</p>
          <p class="text-xs text-gray-600">{{ file.progress }}%</p>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div
            class="bg-blue-600 h-2 rounded-full transition-all"
            :style="{ width: file.progress + '%' }"
          ></div>
        </div>
      </div>
    </div>

    <!-- Uploaded files list -->
    <div v-if="uploadedFiles.length > 0" class="bg-green-50 border border-green-200 rounded-lg p-4">
      <p class="text-sm font-medium text-green-900 mb-3">Successfully uploaded:</p>
      <ul class="space-y-2">
        <li v-for="file in uploadedFiles" :key="file.id" class="text-sm text-green-800 flex items-center gap-2">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          {{ file.filename }}
        </li>
      </ul>
    </div>

    <!-- Error messages -->
    <div v-if="errors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4">
      <p class="text-sm font-medium text-red-900 mb-2">Upload errors:</p>
      <ul class="space-y-1">
        <li v-for="(error, index) in errors" :key="index" class="text-sm text-red-800">
          {{ error }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useToast } from '@/Composables/useToast'

const props = defineProps({
  projectId: String,
})

const emit = defineEmits(['files-uploaded'])

const { success: showSuccess, error: showError } = useToast()

const fileInput = ref(null)
const isDragging = ref(false)
const uploadingFiles = ref([])
const uploadedFiles = ref([])
const errors = ref([])

const MAX_FILE_SIZE = 100 * 1024 * 1024 // 100 MB

const triggerFileInput = () => {
  fileInput.value?.click()
}

const handleFileSelect = (event) => {
  const files = event.target.files
  if (files) {
    handleFiles(Array.from(files))
  }
  // Reset input
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const handleDrop = (event) => {
  isDragging.value = false
  const files = event.dataTransfer.files
  if (files) {
    handleFiles(Array.from(files))
  }
}

const handleFiles = async (files) => {
  errors.value = []
  const validFiles = []

  // Validate files
  for (const file of files) {
    if (file.size > MAX_FILE_SIZE) {
      errors.value.push(`${file.name} exceeds maximum file size of 100 MB`)
    } else {
      validFiles.push(file)
    }
  }

  if (validFiles.length === 0) {
    if (errors.value.length > 0) {
      showError('Some files could not be uploaded')
    }
    return
  }

  // Upload files
  await uploadFiles(validFiles)
}

const uploadFiles = async (filesToUpload) => {
  const formData = new FormData()
  filesToUpload.forEach((file) => {
    formData.append('files[]', file)
  })

  // Initialize progress tracking
  uploadingFiles.value = filesToUpload.map((file) => ({
    name: file.name,
    progress: 0,
  }))

  try {
    const xhr = new XMLHttpRequest()

    xhr.upload.addEventListener('progress', (e) => {
      if (e.lengthComputable) {
        const progress = Math.round((e.loaded / e.total) * 100)
        uploadingFiles.value.forEach((file) => {
          file.progress = progress
        })
      }
    })

    xhr.addEventListener('load', () => {
      if (xhr.status === 200 || xhr.status === 201) {
        const response = JSON.parse(xhr.responseText)
        uploadedFiles.value = response.files || []
        uploadingFiles.value = []
        showSuccess(`${filesToUpload.length} file(s) uploaded successfully`)
        emit('files-uploaded', response.files)
      } else {
        const response = JSON.parse(xhr.responseText)
        errors.value.push(response.message || 'Upload failed')
        uploadingFiles.value = []
        showError('Failed to upload files')
      }
    })

    xhr.addEventListener('error', () => {
      errors.value.push('Network error during upload')
      uploadingFiles.value = []
      showError('Upload failed')
    })

    xhr.open('POST', `/projects/${props.projectId}/attachments`)
    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')?.content)
    xhr.send(formData)
  } catch (err) {
    errors.value.push(err.message || 'Upload error')
    uploadingFiles.value = []
    showError('Failed to upload files')
    console.error('Upload error:', err)
  }
}
</script>

