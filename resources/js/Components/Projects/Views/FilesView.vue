<template>
  <div class="space-y-4">
    <!-- Files header with storage info -->
    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-100">
      <div>
        <h3 class="font-semibold text-gray-900">Files</h3>
        <p class="text-sm text-gray-600">{{ totalSize }} used</p>
      </div>
      <div class="flex gap-2">
        <button
          @click="viewMode = 'grid'"
          :class="[
            'px-3 py-1 rounded text-sm font-medium transition-colors',
            viewMode === 'grid' ? 'bg-blue-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-100',
          ]"
          aria-label="Grid view"
        >
          Grid
        </button>
        <button
          @click="viewMode = 'list'"
          :class="[
            'px-3 py-1 rounded text-sm font-medium transition-colors',
            viewMode === 'list' ? 'bg-blue-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-100',
          ]"
          aria-label="List view"
        >
          List
        </button>
        <button
          @click="triggerFileInput"
          class="px-3 py-1 bg-blue-600 text-white rounded text-sm font-medium hover:bg-blue-700 transition-colors"
          aria-label="Upload files"
        >
          + Upload
        </button>
      </div>
    </div>

    <!-- Filters and search -->
    <div class="flex flex-col md:flex-row gap-3 p-4 bg-white rounded-lg border border-gray-200">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search files..."
        class="flex-1 px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        aria-label="Search files"
      />
      <select
        v-model="fileTypeFilter"
        class="px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        aria-label="Filter by file type"
      >
        <option value="">All types</option>
        <option value="images">Images</option>
        <option value="documents">Documents</option>
        <option value="videos">Videos</option>
        <option value="other">Other</option>
      </select>
      <select
        v-model="uploadedByFilter"
        class="px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        aria-label="Filter by uploader"
      >
        <option value="">All users</option>
        <option v-for="user in uniqueUploaders" :key="user.id" :value="user.id">
          {{ user.name }}
        </option>
      </select>
      <button
        v-if="hasActiveFilters"
        @click="clearFilters"
        class="px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded hover:bg-gray-50 transition-colors"
      >
        Clear filters
      </button>
    </div>

    <!-- Empty state -->
    <div v-if="filteredFiles.length === 0" class="text-center py-12 bg-white rounded-lg border border-gray-200">
      <p class="text-gray-500 mb-4">{{ searchQuery || fileTypeFilter || uploadedByFilter ? 'No files match your filters' : 'No files yet' }}</p>
      <button
        @click="triggerFileInput"
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
      >
        + Upload file
      </button>
    </div>

    <!-- Grid view -->
    <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
    <div v-else class="bg-white rounded-lg border border-gray-200 overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th
              @click="sortBy('filename')"
              class="px-4 py-3 text-left text-sm font-semibold text-gray-900 cursor-pointer hover:bg-gray-100"
            >
              Filename
              <span v-if="sortField === 'filename'" class="ml-1">{{ sortDirection === 'asc' ? 'â†‘' : 'â†“' }}</span>
            </th>
            <th
              @click="sortBy('type')"
              class="px-4 py-3 text-left text-sm font-semibold text-gray-900 cursor-pointer hover:bg-gray-100"
            >
              Type
              <span v-if="sortField === 'type'" class="ml-1">{{ sortDirection === 'asc' ? 'â†‘' : 'â†“' }}</span>
            </th>
            <th
              @click="sortBy('file_size')"
              class="px-4 py-3 text-left text-sm font-semibold text-gray-900 cursor-pointer hover:bg-gray-100"
            >
              Size
              <span v-if="sortField === 'file_size'" class="ml-1">{{ sortDirection === 'asc' ? 'â†‘' : 'â†“' }}</span>
            </th>
            <th
              @click="sortBy('user')"
              class="px-4 py-3 text-left text-sm font-semibold text-gray-900 cursor-pointer hover:bg-gray-100"
            >
              Uploaded by
              <span v-if="sortField === 'user'" class="ml-1">{{ sortDirection === 'asc' ? 'â†‘' : 'â†“' }}</span>
            </th>
            <th
              @click="sortBy('created_at')"
              class="px-4 py-3 text-left text-sm font-semibold text-gray-900 cursor-pointer hover:bg-gray-100"
            >
              Date
              <span v-if="sortField === 'created_at'" class="ml-1">{{ sortDirection === 'asc' ? 'â†‘' : 'â†“' }}</span>
            </th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Source</th>
            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-900">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="file in filteredFiles" :key="file.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 text-sm text-gray-900 font-medium truncate">{{ file.filename }}</td>
            <td class="px-4 py-3 text-sm text-gray-600">{{ getFileType(file) }}</td>
            <td class="px-4 py-3 text-sm text-gray-600">{{ formatSize(file.file_size) }}</td>
            <td class="px-4 py-3 text-sm text-gray-600">{{ file.user?.name || 'Unknown' }}</td>
            <td class="px-4 py-3 text-sm text-gray-600">{{ formatDate(file.created_at) }}</td>
            <td class="px-4 py-3 text-sm text-gray-600">
              <span v-if="file.task" class="text-blue-600 hover:underline cursor-pointer" @click="goToTask(file.task.id)">
                From task: {{ file.task.name }}
              </span>
              <span v-else class="text-gray-500">From project</span>
            </td>
            <td class="px-4 py-3 text-right">
              <div class="flex gap-2 justify-end">
                <button
                  @click="downloadFile(file)"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                  title="Download"
                >
                  Download
                </button>
                <button
                  @click="copyFileLink(file)"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                  title="Copy link"
                >
                  Copy
                </button>
                <button
                  @click="deleteFile(file)"
                  class="text-red-600 hover:text-red-800 text-sm font-medium"
                  title="Delete"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
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
    <div v-if="uploadProgress > 0 && uploadProgress < 100" class="fixed bottom-4 right-4 bg-white rounded-lg shadow-lg p-4 w-80">
      <p class="text-sm font-medium text-gray-900 mb-2">Uploading files...</p>
      <div class="w-full bg-gray-200 rounded-full h-2">
        <div
          class="bg-blue-600 h-2 rounded-full transition-all"
          :style="{ width: uploadProgress + '%' }"
        ></div>
      </div>
      <p class="text-xs text-gray-600 mt-2">{{ uploadProgress }}%</p>
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

