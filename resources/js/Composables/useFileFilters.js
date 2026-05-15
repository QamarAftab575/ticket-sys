import { ref, computed } from 'vue'

export function useFileFilters() {
  const searchQuery = ref('')
  const fileTypeFilter = ref('')
  const uploadedByFilter = ref('')
  const dateRangeFilter = ref({
    from: null,
    to: null,
  })

  // Load from localStorage
  const loadFilters = () => {
    const stored = localStorage.getItem('fileFilters')
    if (stored) {
      const filters = JSON.parse(stored)
      searchQuery.value = filters.searchQuery || ''
      fileTypeFilter.value = filters.fileTypeFilter || ''
      uploadedByFilter.value = filters.uploadedByFilter || ''
      dateRangeFilter.value = filters.dateRangeFilter || { from: null, to: null }
    }
  }

  // Save to localStorage
  const saveFilters = () => {
    localStorage.setItem(
      'fileFilters',
      JSON.stringify({
        searchQuery: searchQuery.value,
        fileTypeFilter: fileTypeFilter.value,
        uploadedByFilter: uploadedByFilter.value,
        dateRangeFilter: dateRangeFilter.value,
      })
    )
  }

  // Apply filters to files
  const applyFilters = (files) => {
    if (!files) return []

    let result = [...files]

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

    // Date range filter
    if (dateRangeFilter.value.from || dateRangeFilter.value.to) {
      result = result.filter((f) => {
        const fileDate = new Date(f.created_at)
        if (dateRangeFilter.value.from) {
          const fromDate = new Date(dateRangeFilter.value.from)
          if (fileDate < fromDate) return false
        }
        if (dateRangeFilter.value.to) {
          const toDate = new Date(dateRangeFilter.value.to)
          toDate.setHours(23, 59, 59, 999)
          if (fileDate > toDate) return false
        }
        return true
      })
    }

    return result
  }

  // Add a filter
  const addFilter = (type, value) => {
    switch (type) {
      case 'search':
        searchQuery.value = value
        break
      case 'type':
        fileTypeFilter.value = value
        break
      case 'uploadedBy':
        uploadedByFilter.value = value
        break
      case 'dateFrom':
        dateRangeFilter.value.from = value
        break
      case 'dateTo':
        dateRangeFilter.value.to = value
        break
    }
    saveFilters()
  }

  // Remove a filter
  const removeFilter = (type) => {
    switch (type) {
      case 'search':
        searchQuery.value = ''
        break
      case 'type':
        fileTypeFilter.value = ''
        break
      case 'uploadedBy':
        uploadedByFilter.value = ''
        break
      case 'dateFrom':
        dateRangeFilter.value.from = null
        break
      case 'dateTo':
        dateRangeFilter.value.to = null
        break
    }
    saveFilters()
  }

  // Clear all filters
  const clearFilters = () => {
    searchQuery.value = ''
    fileTypeFilter.value = ''
    uploadedByFilter.value = ''
    dateRangeFilter.value = { from: null, to: null }
    saveFilters()
  }

  // Check if any filters are active
  const hasActiveFilters = computed(() => {
    return (
      searchQuery.value ||
      fileTypeFilter.value ||
      uploadedByFilter.value ||
      dateRangeFilter.value.from ||
      dateRangeFilter.value.to
    )
  })

  return {
    searchQuery,
    fileTypeFilter,
    uploadedByFilter,
    dateRangeFilter,
    loadFilters,
    saveFilters,
    applyFilters,
    addFilter,
    removeFilter,
    clearFilters,
    hasActiveFilters,
  }
}

// Helper function to categorize files
function getFileCategory(file) {
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
