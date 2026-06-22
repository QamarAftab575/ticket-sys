<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-full">
    <div class="flex items-center justify-between mb-5">
      <div class="flex items-center gap-2">
        <h2 class="text-base font-semibold text-gray-900">Private notepad</h2>
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
      </div>
      <div class="flex items-center gap-2">
        <!-- Auto-save indicator -->
        <div v-if="isSaving" class="flex items-center gap-1.5">
          <div class="w-1.5 h-1.5 bg-yellow-500 rounded-full animate-pulse" />
          <span class="text-xs text-gray-500">Saving...</span>
        </div>
        <div v-else-if="lastSaved" class="text-xs text-gray-500">
          Saved {{ lastSaved }}
        </div>

        <button
          @click="showMenu = !showMenu"
          class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-md transition-colors cursor-pointer"
          title="Menu"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
          </svg>
        </button>
        <div v-if="showMenu" class="absolute right-8 top-32 bg-white border border-gray-200 rounded-xl shadow-lg z-10 min-w-[140px] overflow-hidden">
          <button
            @click="clearNotes; showMenu = false"
            class="w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 cursor-pointer transition-colors"
          >
            Clear all
          </button>
        </div>
      </div>
    </div>

    <!-- Editor -->
    <div class="border border-gray-200 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent transition-all duration-150">
      <!-- Toolbar -->
      <div class="flex items-center gap-0.5 px-3 py-2.5 bg-gray-50 border-b border-gray-200 flex-wrap">
        <button
          @click="toggleFormat('bold')"
          :class="['p-2 rounded-lg hover:bg-gray-200 transition-colors cursor-pointer', isFormatActive('bold') ? 'bg-blue-100 text-blue-600' : 'text-gray-600']"
          title="Bold (Ctrl+B)"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7.04c2.09 0 3.71-1.7 3.71-3.79 0-1.52-.86-2.82-2.15-3.42zM10 6.5h3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z"/>
          </svg>
        </button>
        <button
          @click="toggleFormat('italic')"
          :class="['p-2 rounded-lg hover:bg-gray-200 transition-colors cursor-pointer', isFormatActive('italic') ? 'bg-blue-100 text-blue-600' : 'text-gray-600']"
          title="Italic (Ctrl+I)"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M10 4v3h2.21l-3.42 8H6v3h8v-3h-2.21l3.42-8H18V4z"/>
          </svg>
        </button>
        <button
          @click="toggleFormat('underline')"
          :class="['p-2 rounded-lg hover:bg-gray-200 transition-colors cursor-pointer', isFormatActive('underline') ? 'bg-blue-100 text-blue-600' : 'text-gray-600']"
          title="Underline (Ctrl+U)"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 3v8a6 6 0 0012 0V3M4 19h16"/>
          </svg>
        </button>

        <div class="w-px h-5 bg-gray-300 mx-1" />

        <button
          @click="insertList('unordered')"
          class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 transition-colors cursor-pointer"
          title="Bullet list"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
        <button
          @click="insertList('ordered')"
          class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 transition-colors cursor-pointer"
          title="Numbered list"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M3 6h18M3 18h18"/>
          </svg>
        </button>

        <div class="w-px h-5 bg-gray-300 mx-1" />

        <button
          @click="insertLink"
          class="p-2 rounded-lg hover:bg-gray-200 text-gray-600 transition-colors cursor-pointer"
          title="Insert link"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.658 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
          </svg>
        </button>

        <div class="flex-1" />

        <span class="text-xs text-gray-500">{{ wordCount }} words</span>
      </div>

      <!-- Text Editor -->
      <textarea
        v-model="noteContent"
        @input="handleContentUpdate"
        @keydown.tab="handleTab"
        placeholder="Jot down a quick note or add a link to an important resource."
        class="w-full h-64 px-4 py-4 resize-none focus:outline-none text-sm text-gray-700 leading-relaxed"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { debounce } from '@/Utils/helpers'

const props = defineProps({
  initialContent: {
    type: String,
    default: '',
  },
})

const noteContent = ref(props.initialContent)
const showMenu = ref(false)
const lastSaved = ref(null)
const isSaving = ref(false)

const wordCount = computed(() => {
  return noteContent.value.trim().split(/\s+/).filter(word => word.length > 0).length
})

// Auto-save function
const autoSaveNote = async (content) => {
  isSaving.value = true
  try {
    const response = await fetch('/api/private-note', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
      },
      body: JSON.stringify({ content }),
    })

    if (response.ok) {
      const data = await response.json()
      lastSaved.value = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    }
  } catch (error) {
    console.error('Error saving note:', error)
  } finally {
    isSaving.value = false
  }
}

// Debounced auto-save (1 second delay)
const debouncedSave = debounce((content) => {
  autoSaveNote(content)
}, 1000)

const handleContentUpdate = (event) => {
  noteContent.value = event.target.value
  debouncedSave(noteContent.value)
}

const toggleFormat = (format) => {
  document.execCommand(format, false, null)
}

const isFormatActive = (format) => {
  return document.queryCommandState(format)
}

const insertList = (type) => {
  const command = type === 'ordered' ? 'insertOrderedList' : 'insertUnorderedList'
  document.execCommand(command, false, null)
}

const insertLink = () => {
  const url = prompt('Enter URL:')
  if (url) {
    const selection = window.getSelection().toString()
    const text = selection || url
    document.execCommand('createLink', false, url)
  }
}

const handleTab = (e) => {
  if (e.key === 'Tab') {
    e.preventDefault()
    const start = e.target.selectionStart
    const end = e.target.selectionEnd
    noteContent.value = noteContent.value.substring(0, start) + '\t' + noteContent.value.substring(end)
    e.target.selectionStart = e.target.selectionEnd = start + 1
    debouncedSave(noteContent.value)
  }
}

const clearNotes = async () => {
  if (confirm('Are you sure you want to clear all notes? This cannot be undone.')) {
    noteContent.value = ''
    await autoSaveNote('')
  }
}

onMounted(async () => {
  try {
    const response = await fetch('/api/private-note')
    if (response.ok) {
      const data = await response.json()
      noteContent.value = data.content || ''
      if (data.updated_at) {
        lastSaved.value = new Date(data.updated_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
      }
    }
  } catch (error) {
    console.error('Error loading note:', error)
  }
})
</script>
