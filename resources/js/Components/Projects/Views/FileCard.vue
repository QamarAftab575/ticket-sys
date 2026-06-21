<template>
  <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow bg-white">
    <!-- File thumbnail -->
    <div class="bg-gray-100 h-40 flex items-center justify-center relative group">
      <div v-if="isImage" class="w-full h-full">
        <img :src="file.url" :alt="file.filename" class="w-full h-full object-cover" />
      </div>
      <div v-else class="text-5xl">
        {{ getFileIcon() }}
      </div>

      <!-- Hover actions -->
      <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
        <button
          @click.stop="$emit('download')"
          class="p-2 bg-white rounded-full hover:bg-gray-100 transition-colors"
          title="Download"
          aria-label="Download file"
        >
          <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
        </button>
        <button
          @click.stop="$emit('copy-link')"
          class="p-2 bg-white rounded-full hover:bg-gray-100 transition-colors"
          title="Copy link"
          aria-label="Copy file link"
        >
          <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.658 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
          </svg>
        </button>
      </div>
    </div>

    <!-- File info -->
    <div class="p-4">
      <h4 class="font-medium text-sm truncate text-gray-900 mb-1" :title="file.filename">{{ file.filename }}</h4>
      <p class="text-xs text-gray-600 mb-2">{{ formatSize(file.file_size) }}</p>

      <!-- Source info -->
      <div class="mb-3 text-xs">
        <p v-if="file.task" class="text-blue-600 hover:underline cursor-pointer" @click="$emit('go-to-task', file.task.id)">
          From task: {{ file.task.name }}
        </p>
        <p v-else class="text-gray-500">From project</p>
      </div>

      <!-- Metadata -->
      <div class="mb-3 text-xs text-gray-500 space-y-1">
        <p>{{ formatDate(file.created_at) }}</p>
        <p v-if="file.user">{{ file.user.name }}</p>
      </div>

      <!-- Actions menu -->
      <div class="flex gap-2">
        <button
          @click="$emit('download')"
          class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors text-gray-700 font-medium"
        >
          Download
        </button>
        <button
          @click="showMenu = !showMenu"
          class="px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-50 transition-colors text-gray-700 font-medium relative"
        >
          ...
          <div v-if="showMenu" class="absolute right-0 top-full mt-1 bg-white border border-gray-200 rounded shadow-lg z-10 min-w-max">
            <button
              @click.stop="copyLink"
              class="block w-full text-left px-3 py-2 text-xs hover:bg-gray-100 text-gray-700"
            >
              Copy link
            </button>
            <button
              @click.stop="deleteFile"
              class="block w-full text-left px-3 py-2 text-xs hover:bg-gray-100 text-red-600"
            >
              Delete
            </button>
          </div>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  file: Object,
})

const emit = defineEmits(['download', 'delete', 'copy-link', 'go-to-task'])

const showMenu = ref(false)

const isImage = computed(() => {
  return props.file.type?.startsWith('image/') || /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(props.file.filename)
})

const getFileIcon = () => {
  const filename = props.file.filename.toLowerCase()
  const type = props.file.type || ''

  if (/\.(pdf)$/i.test(filename) || type.includes('pdf')) return 'ðŸ“„'
  if (/\.(doc|docx|txt)$/i.test(filename) || type.includes('document') || type.includes('text')) return 'ðŸ“'
  if (/\.(xls|xlsx)$/i.test(filename) || type.includes('sheet')) return 'ðŸ“Š'
  if (/\.(ppt|pptx)$/i.test(filename) || type.includes('presentation')) return 'ðŸŽ¯'
  if (/\.(mp4|avi|mov|mkv|webm)$/i.test(filename) || type.startsWith('video/')) return 'ðŸŽ¥'
  if (/\.(mp3|wav|m4a)$/i.test(filename) || type.startsWith('audio/')) return 'ðŸŽµ'
  if (/\.(zip|rar|7z|tar|gz)$/i.test(filename) || type.includes('archive')) return 'ðŸ“¦'
  return 'ðŸ“Ž'
}

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

const copyLink = () => {
  emit('copy-link')
  showMenu.value = false
}

const deleteFile = () => {
  if (confirm(`Are you sure you want to delete "${props.file.filename}"?`)) {
    emit('delete')
    showMenu.value = false
  }
}
</script>

