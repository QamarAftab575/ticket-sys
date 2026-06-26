<template>
  <div class="space-y-6">
    <!-- Files header with storage info -->
    <div class="flex items-center justify-between bg-gradient-to-r from-indigo-50 via-purple-50 to-pink-50 rounded-xl border border-indigo-100 p-6 shadow-sm">
      <div>
        <h3 class="font-bold text-gray-900 text-lg">{{ $t('files') }}</h3>
        <p class="text-sm text-gray-600 mt-1">{{ totalSize }} {{ $t('used') }}</p>
      </div>
      <div class="flex gap-2">
        <div class="flex gap-1 bg-gray-100 rounded-lg p-1">
          <button
            @click="viewMode = 'grid'"
            :class="[
              'px-4 py-2 rounded-md text-sm font-medium transition-all cursor-pointer',
              viewMode === 'grid' ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-700 hover:text-gray-900',
            ]"
            :aria-label="$t('grid')"
            title="Grid view"
          >
            <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
            </svg>
          </button>
          <button
            @click="viewMode = 'list'"
            :class="[
              'px-4 py-2 rounded-md text-sm font-medium transition-all cursor-pointer',
              viewMode === 'list' ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-700 hover:text-gray-900',
            ]"
            :aria-label="$t('list')"
            title="List view"
          >
            <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
        <button
          @click="triggerFileInput"
          class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors cursor-pointer shadow-md"
          :aria-label="$t('upload')"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
          </svg>
          {{ $t('upload') }}
        </button>
      </div>
    </div>

    <!-- Filters and search -->
    <div class="flex flex-col md:flex-row gap-3 bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
      <div class="flex-1 relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="$t('search_files')"
          class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white transition-all"
          :aria-label="$t('search_files')"
        />
      </div>
      <select
        v-model="fileTypeFilter"
        class="px-4 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white transition-all cursor-pointer"
        :aria-label="$t('type')"
      >
        <option value="">{{ $t('all_types') }}</option>
        <option value="images">{{ $t('images') }}</option>
        <option value="documents">{{ $t('documents') }}</option>
        <option value="videos">{{ $t('videos') }}</option>
        <option value="other">{{ $t('other') }}</option>
      </select>
      <select
        v-model="uploadedByFilter"
        class="px-4 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white transition-all cursor-pointer"
        :aria-label="$t('uploaded_by')"
      >
        <option value="">{{ $t('all_users') }}</option>
        <option v-for="user in uniqueUploaders" :key="user.id" :value="user.id">
          {{ user.name }}
        </option>
      </select>
      <button
        v-if="hasActiveFilters"
        @click="clearFilters"
        class="px-4 py-2.5 text-sm font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer"
      >
        {{ $t('clear_filters') }}
      </button>
    </div>

    <!-- Empty state -->
    <div v-if="filteredFiles.length === 0" class="flex flex-col items-center justify-center py-16 bg-white rounded-xl border border-gray-200">
      <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
      </div>
      <p class="text-gray-600 font-medium mb-2">{{ searchQuery || fileTypeFilter || uploadedByFilter ? $t('no_files_match') : $t('no_files_yet') }}</p>
      <p class="text-gray-500 text-sm mb-6">{{ searchQuery || fileTypeFilter || uploadedByFilter ? $t('try_different_filters') : $t('upload_to_get_started') }}</p>
      <button
        @click="triggerFileInput"
        class="flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium cursor-pointer"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        {{ $t('upload_file') }}
      </button>
    </div>

    <!-- Grid view -->
    <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <FileCard
        v-for="file in filteredFiles"
        :key="file.id"
        :file="file"
        @download="downloadFile"
        @delete="deleteFile"
        @copy-link="copyFileLink"
        @go-to-task="goToTask"
      />
    </div>

    <!-- List view -->
    <div v-else class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gradient-to-r from-gray-50 to-gray-50 border-b border-gray-200">
            <tr>
              <th
                @click="sortBy('filename')"
                class="px-6 py-4 text-left text-sm font-bold text-gray-900 cursor-pointer hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center gap-2">
                  Filename
                  <span v-if="sortField === 'filename'" class="text-indigo-600">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
                </div>
              </th>
              <th
                @click="sortBy('type')"
                class="px-6 py-4 text-left text-sm font-bold text-gray-900 cursor-pointer hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center gap-2">
                  Type
                  <span v-if="sortField === 'type'" class="text-indigo-600">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
                </div>
              </th>
              <th
                @click="sortBy('file_size')"
                class="px-6 py-4 text-left text-sm font-bold text-gray-900 cursor-pointer hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center gap-2">
                  Size
                  <span v-if="sortField === 'file_size'" class="text-indigo-600">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
                </div>
              </th>
              <th
                @click="sortBy('user')"
                class="px-6 py-4 text-left text-sm font-bold text-gray-900 cursor-pointer hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center gap-2">
                  Uploaded by
                  <span v-if="sortField === 'user'" class="text-indigo-600">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
                </div>
              </th>
              <th
                @click="sortBy('created_at')"
                class="px-6 py-4 text-left text-sm font-bold text-gray-900 cursor-pointer hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center gap-2">
                  Date
                  <span v-if="sortField === 'created_at'" class="text-indigo-600">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
                </div>
              </th>
              <th class="px-6 py-4 text-left text-sm font-bold text-gray-900">Source</th>
              <th class="px-6 py-4 text-right text-sm font-bold text-gray-900">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="file in filteredFiles" :key="file.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 text-sm text-gray-900 font-medium truncate">{{ file.filename }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ getFileType(file) }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ formatSize(file.file_size) }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ file.user?.name || 'Unknown' }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(file.created_at) }}</td>
              <td class="px-6 py-4 text-sm">
                <span v-if="file.task" class="text-indigo-600 hover:text-indigo-800 cursor-pointer font-medium" @click="goToTask(file.task.id)">
                  From task: {{ file.task.name }}
                </span>
                <span v-else class="text-gray-500">From project</span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex gap-2 justify-end">
                  <button
                    @click="downloadFile(file)"
                    class="flex items-center gap-1 text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors cursor-pointer"
                    title="Download"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download
                  </button>
                  <button
                    @click="copyFileLink(file)"
                    class="flex items-center gap-1 text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors cursor-pointer"
                    title="Copy link"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Copy
                  </button>
                  <button
                    @click="deleteFile(file)"
                    class="flex items-center gap-1 text-red-600 hover:text-red-800 text-sm font-medium transition-colors cursor-pointer"
                    title="Delete"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Hidden file input -->
    <input
      ref="fileInput"
      type="file"
      multiple
      class="hidden"
      @change="handleFileSelect"
      aria-label="Select files to upload"
    />

    <!-- Upload progress -->
    <div v-if="uploadProgress > 0 && uploadProgress < 100" class="fixed bottom-4 right-4 bg-white rounded-xl shadow-xl p-6 w-80 border border-gray-200">
      <p class="text-sm font-semibold text-gray-900 mb-3">Uploading files...</p>
      <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
        <div
          class="bg-gradient-to-r from-indigo-600 to-purple-600 h-2 rounded-full transition-all"
          :style="{ width: uploadProgress + '%' }"
        ></div>
      </div>
      <p class="text-xs text-gray-600 mt-3 text-right">{{ uploadProgress }}%</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import FileCard from './FileCard.vue'
import { useToast } from '@/Composables/useToast'

const props = defineProps({
  project: Object,
  files: Array,
})

const emit = defineEmits(['file-uploaded', 'file-deleted', 'navigate-to-task'])

const { success: showSuccess, error: showError } = useToast()

const viewMode = ref('grid')
const searchQuery = ref('')
const fileTypeFilter = ref('')
const uploadedByFilter = ref('')
const sortField = ref('created_at')
const sortDirection = ref('desc')
const fileInput = ref(null)
const uploadProgress = ref(0)

const totalSize = computed(() => {
  if (!props.files) return '0 MB'
  const total = props.files.reduce((sum, f) => sum + (f.file_size || 0), 0)
  return formatSize(total)
})

const uniqueUploaders = computed(() => {
  if (!props.files) return []
  const uploaders = {}
  props.files.forEach((file) => {
    if (file.user && !uploaders[file.user.id]) {
      uploaders[file.user.id] = file.user
    }
  })
  return Object.values(uploaders)
})

const hasActiveFilters = computed(() => {
  return searchQuery.value || fileTypeFilter.value || uploadedByFilter.value
})

const filteredFiles = computed(() => {
  let result = props.files || []

  // Search filter
  if (searchQuery.value) {
    result = result.filter((f) =>
      f.filename.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  // File type filter
  if (fileTypeFilter.value) {
    result = result.filter((f) => {
      const type = getFileCategory(f)
      return type === fileTypeFilter.value
    })
  }

  // Uploaded by filter
  if (uploadedByFilter.value) {
    result = result.filter((f) => f.user_id === uploadedByFilter.value)
  }

  // Sort
  result.sort((a, b) => {
    let aVal, bVal

    switch (sortField.value) {
      case 'filename':
        aVal = a.filename.toLowerCase()
        bVal = b.filename.toLowerCase()
        break
      case 'type':
        aVal = getFileType(a)
        bVal = getFileType(b)
        break
      case 'file_size':
        aVal = a.file_size || 0
        bVal = b.file_size || 0
        break
      case 'user':
        aVal = (a.user?.name || '').toLowerCase()
        bVal = (b.user?.name || '').toLowerCase()
        break
      case 'created_at':
        aVal = new Date(a.created_at).getTime()
        bVal = new Date(b.created_at).getTime()
        break
      default:
        return 0
    }

    if (aVal < bVal) return sortDirection.value === 'asc' ? -1 : 1
    if (aVal > bVal) return sortDirection.value === 'asc' ? 1 : -1
    return 0
  })

  return result
})

const formatSize = (bytes) => {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

const getFileType = (file) => {
  const ext = file.filename.split('.').pop()?.toLowerCase() || 'unknown'
  return ext.toUpperCase()
}

const getFileCategory = (file) => {
  const mimeType = file.type || ''
  const filename = file.filename.toLowerCase()

  const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']
  const docExts = ['pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx', 'ppt', 'pptx']
  const videoExts = ['mp4', 'avi', 'mov', 'mkv', 'webm']

  const ext = filename.split('.').pop()?.toLowerCase()

  if (imageExts.includes(ext) || mimeType.startsWith('image/')) return 'images'
  if (docExts.includes(ext) || mimeType.includes('document') || mimeType.includes('sheet') || mimeType.includes('presentation')) return 'documents'
  if (videoExts.includes(ext) || mimeType.startsWith('video/')) return 'videos'
  return 'other'
}

const sortBy = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDirection.value = 'asc'
  }
}

const clearFilters = () => {
  searchQuery.value = ''
  fileTypeFilter.value = ''
  uploadedByFilter.value = ''
}

const triggerFileInput = () => {
  fileInput.value?.click()
}

const handleFileSelect = async (event) => {
  const files = event.target.files
  if (!files || files.length === 0) return

  await uploadFiles(Array.from(files))
  // Reset input
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const uploadFiles = async (filesToUpload) => {
  const formData = new FormData()
  filesToUpload.forEach((file) => {
    formData.append('files[]', file)
  })

  try {
    uploadProgress.value = 0
    const xhr = new XMLHttpRequest()

    xhr.upload.addEventListener('progress', (e) => {
      if (e.lengthComputable) {
        uploadProgress.value = Math.round((e.loaded / e.total) * 100)
      }
    })

    xhr.addEventListener('load', () => {
      if (xhr.status === 200) {
        const response = JSON.parse(xhr.responseText)
        showSuccess(`${filesToUpload.length} file(s) uploaded successfully`)
        emit('file-uploaded', response.files)
        uploadProgress.value = 0
      } else {
        showError('Failed to upload files')
        uploadProgress.value = 0
      }
    })

    xhr.addEventListener('error', () => {
      showError('Upload failed')
      uploadProgress.value = 0
    })

    xhr.open('POST', `/projects/${props.project.id}/attachments`)
    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')?.content)
    xhr.send(formData)
  } catch (err) {
    showError('Failed to upload files')
    uploadProgress.value = 0
    console.error('Upload error:', err)
  }
}

const downloadFile = (file) => {
  if (file.url) {
    const link = document.createElement('a')
    link.href = file.url
    link.download = file.filename
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  }
}

const copyFileLink = (file) => {
  if (file.url) {
    navigator.clipboard.writeText(file.url).then(() => {
      showSuccess('Link copied to clipboard')
    }).catch(() => {
      showError('Failed to copy link')
    })
  }
}

const deleteFile = async (file) => {
  if (!confirm(`Are you sure you want to delete "${file.filename}"?`)) {
    return
  }

  try {
    const response = await fetch(`/attachments/${file.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      },
    })

    if (!response.ok) {
      throw new Error('Failed to delete file')
    }

    showSuccess('File deleted successfully')
    emit('file-deleted', file.id)
  } catch (err) {
    showError('Failed to delete file')
    console.error('Delete error:', err)
  }
}

const goToTask = (taskId) => {
  emit('navigate-to-task', taskId)
}
</script>
