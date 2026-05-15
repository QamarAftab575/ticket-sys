<template>
  <div class="rich-editor" ref="rootEl">
    <!-- Toolbar -->
    <Transition
      enter-active-class="transition-all duration-150 ease-out overflow-hidden"
      enter-from-class="max-h-0 opacity-0"
      enter-to-class="max-h-16 opacity-100"
      leave-active-class="transition-all duration-100 ease-in overflow-hidden"
      leave-from-class="max-h-16 opacity-100"
      leave-to-class="max-h-0 opacity-0"
    >
      <div
        v-if="editor && toolbarVisible"
        class="flex items-center gap-0.5 px-2 py-1.5 border-b border-gray-100 flex-wrap"
        @mousedown.prevent
      >
        <ToolbarBtn @click="editor.chain().focus().toggleBold().run()" :active="editor.isActive('bold')" title="Bold">
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7.04c2.09 0 3.71-1.7 3.71-3.79 0-1.52-.86-2.82-2.15-3.42zM10 6.5h3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z"/></svg>
        </ToolbarBtn>
        <ToolbarBtn @click="editor.chain().focus().toggleItalic().run()" :active="editor.isActive('italic')" title="Italic">
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M10 4v3h2.21l-3.42 8H6v3h8v-3h-2.21l3.42-8H18V4z"/></svg>
        </ToolbarBtn>
        <ToolbarBtn @click="editor.chain().focus().toggleUnderline().run()" :active="editor.isActive('underline')" title="Underline">
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 17c3.31 0 6-2.69 6-6V3h-2.5v8c0 1.93-1.57 3.5-3.5 3.5S8.5 12.93 8.5 11V3H6v8c0 3.31 2.69 6 6 6zm-7 2v2h14v-2H5z"/></svg>
        </ToolbarBtn>
        <ToolbarBtn @click="editor.chain().focus().toggleStrike().run()" :active="editor.isActive('strike')" title="Strikethrough">
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M10 19h4v-3h-4v3zM5 4v3h5v3h4V7h5V4H5zM3 14h18v-2H3v2z"/></svg>
        </ToolbarBtn>
        <div class="w-px h-4 bg-gray-200 mx-1"/>
        <ToolbarBtn @click="editor.chain().focus().toggleBulletList().run()" :active="editor.isActive('bulletList')" title="Bullet list">
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M4 10.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm0-6c-.83 0-1.5.67-1.5 1.5S3.17 7.5 4 7.5 5.5 6.83 5.5 6 4.83 4.5 4 4.5zm0 12c-.83 0-1.5.68-1.5 1.5s.68 1.5 1.5 1.5 1.5-.68 1.5-1.5-.67-1.5-1.5-1.5zM7 19h14v-2H7v2zm0-6h14v-2H7v2zm0-8v2h14V5H7z"/></svg>
        </ToolbarBtn>
        <ToolbarBtn @click="editor.chain().focus().toggleOrderedList().run()" :active="editor.isActive('orderedList')" title="Numbered list">
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M2 17h2v.5H3v1h1v.5H2v1h3v-4H2v1zm1-9h1V4H2v1h1v3zm-1 3h1.8L2 13.1v.9h3v-1H3.2L5 10.9V10H2v1zm5-6v2h14V5H7zm0 14h14v-2H7v2zm0-6h14v-2H7v2z"/></svg>
        </ToolbarBtn>
        <ToolbarBtn @click="editor.chain().focus().toggleTaskList().run()" :active="editor.isActive('taskList')" title="Task list">
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9 14l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
        </ToolbarBtn>
        <div class="w-px h-4 bg-gray-200 mx-1"/>
        <ToolbarBtn @click="editor.chain().focus().toggleCode().run()" :active="editor.isActive('code')" title="Inline code">
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M9.4 16.6L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4zm5.2 0l4.6-4.6-4.6-4.6L16 6l6 6-6 6-1.4-1.4z"/></svg>
        </ToolbarBtn>

        <!-- Upload image button (only when taskId provided) -->
        <template v-if="taskId">
          <div class="w-px h-4 bg-gray-200 mx-1"/>
          <label :title="uploading ? 'Uploading…' : 'Insert image'" class="cursor-pointer">
            <input type="file" accept="image/*" class="hidden" @change="onFileInputChange"/>
            <span :class="['flex p-1.5 rounded transition-colors', uploading ? 'text-indigo-400' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700']">
              <svg v-if="!uploading" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
              </svg>
              <svg v-else class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
            </span>
          </label>
        </template>
      </div>
    </Transition>

    <!-- Editor content -->
    <EditorContent
      :editor="editor"
      class="prose prose-sm max-w-none px-3 py-2 min-h-[80px] focus-within:outline-none text-gray-800"
    />
  </div>
</template>

<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Underline from '@tiptap/extension-underline'
import Placeholder from '@tiptap/extension-placeholder'
import Image from '@tiptap/extension-image'
import TaskList from '@tiptap/extension-task-list'
import TaskItem from '@tiptap/extension-task-item'
import Mention from '@tiptap/extension-mention'
import { Plugin, PluginKey } from '@tiptap/pm/state'
import { ref, computed, watch, defineComponent, h, onBeforeUnmount } from 'vue'
import { VueRenderer } from '@tiptap/vue-3'
import tippy from 'tippy.js'
import MentionList from './MentionList.vue'
import { api } from '@/Services/api'

// Custom Mention extension with proper parseHTML
const CustomMention = Mention.extend({
  parseHTML() {
    return [
      {
        tag: 'span[data-type="mention"]',
        getAttrs: dom => {
          const id = dom.getAttribute('data-id')
          const label = dom.getAttribute('data-label')
          return { id, label }
        },
      },
    ]
  },
})

const ToolbarBtn = defineComponent({
  props: { active: Boolean, title: String },
  emits: ['click'],
  setup(props, { slots, emit }) {
    return () => h('button', {
      type: 'button',
      title: props.title,
      // preventDefault on mousedown keeps editor focus when clicking toolbar buttons
      onMousedown: (e) => e.preventDefault(),
      onClick: (e) => { e.preventDefault(); emit('click') },
      class: ['p-1.5 rounded transition-colors', props.active ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700'],
    }, slots.default?.())
  },
})

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Add a description…' },
  /**
   * showToolbar: true  → always visible (description, edit-comment)
   * showToolbar: false → never visible (legacy / read-only)
   * showToolbar: 'auto' → hidden by default, shown on focus, hidden on blur
   */
  showToolbar: { type: [Boolean, String], default: true },
  editable: { type: Boolean, default: true },
  taskId: { type: String, default: null },
  projectId: { type: String, default: null },
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus'])

const rootEl = ref(null)
const isFocused = ref(false)
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
const uploading = ref(false)

// Toolbar is visible when:
// - showToolbar === true (always on)
// - showToolbar === 'auto' AND the editor is focused
const toolbarVisible = computed(() => {
  if (props.showToolbar === true) return true
  if (props.showToolbar === 'auto') return isFocused.value
  return false
})

// ── Upload image file → return public URL ────────────────────────────────
async function uploadImageFile(file) {
  if (!props.taskId) return null
  uploading.value = true
  try {
    const form = new FormData()
    form.append('file', file)
    const res = await fetch(`/api/tasks/${props.taskId}/attachments`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrfToken },
      body: form,
    })
    if (!res.ok) throw new Error('Upload failed')
    const data = await res.json()
    return data.url?.startsWith('http') ? data.url : window.location.origin + data.url
  } catch (e) {
    console.error('Image upload failed:', e)
    return null
  } finally {
    uploading.value = false
  }
}

// ── Toolbar file input handler ────────────────────────────────────────────
async function onFileInputChange(event) {
  const file = event.target.files?.[0]
  if (!file) return
  event.target.value = ''
  const url = await uploadImageFile(file)
  if (url && editor.value) {
    editor.value.chain().focus().setImage({ src: url }).run()
  }
}

// ── Mention suggestion configuration ──────────────────────────────────────
const mentionSuggestion = {
  char: '@',
  allowSpaces: false,
  
  items: async ({ query }) => {
    if (!props.projectId) {
      console.log('No projectId provided for mentions')
      return []
    }
    
    console.log('Fetching members for mention, query:', query)
    
    try {
      // api service already returns response.data, so we get the array directly
      const members = await api.get(`/projects/${props.projectId}/members`)
      
      console.log('Fetched members:', members)
      
      if (!query) {
        console.log('No query, returning all members:', members)
        return members
      }
      
      const lowerQuery = query.toLowerCase()
      const filtered = members.filter(member => 
        member.name.toLowerCase().includes(lowerQuery) ||
        (member.email || '').toLowerCase().includes(lowerQuery)
      )
      
      console.log('Filtered members for query "' + query + '":', filtered)
      return filtered
    } catch (err) {
      console.error('Error fetching members for mentions:', err)
      return []
    }
  },

  render: () => {
    let component
    let popup

    return {
      onStart: props => {
        console.log('Mention suggestion started', props)
        console.log('Items received:', props.items)
        
        component = new VueRenderer(MentionList, {
          props,
          editor: props.editor,
        })

        if (!props.clientRect) {
          console.warn('No clientRect provided')
          return
        }

        popup = tippy('body', {
          getReferenceClientRect: props.clientRect,
          appendTo: () => document.body,
          content: component.element,
          showOnCreate: true,
          interactive: true,
          trigger: 'manual',
          placement: 'bottom-start',
          maxWidth: 'none',
          zIndex: 9999,
        })
        
        console.log('Tippy popup created:', popup)
      },

      onUpdate(props) {
        console.log('Mention suggestion updated', props)
        console.log('Updated items:', props.items)
        
        component.updateProps(props)

        if (!props.clientRect) return

        popup[0].setProps({
          getReferenceClientRect: props.clientRect,
        })
      },

      onKeyDown(props) {
        if (props.event.key === 'Escape') {
          popup[0].hide()
          return true
        }

        return component.ref?.onKeyDown(props.event)
      },

      onExit() {
        console.log('Mention suggestion exited')
        popup[0].destroy()
        component.destroy()
      },
    }
  },
}

// ── Create a custom extension for image paste handling ───────────────────
const ImagePasteExtension = {
  name: 'imagePaste',
  addProseMirrorPlugins() {
    return [
      new Plugin({
        key: new PluginKey('imagePaste'),
        props: {
          handlePaste(view, event, slice) {
            const items = Array.from(event.clipboardData?.items || [])
            const imageItem = items.find(i => i.type.startsWith('image/'))
            
            // If no image, let Tiptap handle text/HTML paste normally
            if (!imageItem) return false

            const file = imageItem.getAsFile()
            if (!file) return false

            // Prevent default and handle image
            event.preventDefault()

            if (props.taskId) {
              uploadImageFile(file).then(url => {
                if (url && editor.value) {
                  editor.value.chain().focus().setImage({ src: url }).run()
                }
              })
            } else {
              const reader = new FileReader()
              reader.onload = (e) => {
                const src = e.target?.result
                if (src && editor.value) {
                  editor.value.chain().focus().setImage({ src }).run()
                }
              }
              reader.readAsDataURL(file)
            }
            return true
          }
        }
      })
    ]
  }
}

const editor = useEditor({
  content: props.modelValue || '',
  editable: props.editable,
  extensions: [
    StarterKit,
    Underline,
    Image.configure({ inline: false, allowBase64: true }),
    TaskList,
    TaskItem.configure({ nested: true }),
    Placeholder.configure({ placeholder: props.placeholder }),
    CustomMention.configure({
      HTMLAttributes: {
        class: 'mention',
      },
      suggestion: mentionSuggestion,
      renderText({ node }) {
        return `@${node.attrs.label ?? node.attrs.id}`
      },
      renderHTML({ node, HTMLAttributes }) {
        return [
          'span',
          {
            ...HTMLAttributes,
            'data-type': 'mention',
            'data-id': node.attrs.id,
            'data-label': node.attrs.label,
          },
          `@${node.attrs.label ?? node.attrs.id}`
        ]
      },
    }),
    ImagePasteExtension,
  ],
  editorProps: {
    attributes: { class: 'outline-none' },
  },
  onFocus() {
    isFocused.value = true
    emit('focus')
  },
  onBlur({ editor }) {
    // Small delay so toolbar button clicks (mousedown → blur → click) complete
    // before we hide the toolbar. The @mousedown.prevent on the toolbar wrapper
    // prevents blur from firing at all for most interactions, but this is a
    // safety net for edge cases (e.g. file input labels).
    setTimeout(() => {
      // Only hide if focus has truly left the entire component
      if (rootEl.value && !rootEl.value.contains(document.activeElement)) {
        isFocused.value = false
      }
      // Safety check: only emit if editor is still valid
      if (editor && !editor.isDestroyed) {
        emit('blur', editor.getHTML())
      }
    }, 150)
  },
  onUpdate({ editor }) {
    // Safety check: only emit if editor is still valid
    if (editor && !editor.isDestroyed) {
      emit('update:modelValue', editor.getHTML())
    }
  },
})

// Sync when parent switches to a different task
watch(() => props.modelValue, (val) => {
  if (!editor.value) return
  if (editor.value.getHTML() !== val) {
    editor.value.commands.setContent(val || '', false)
  }
})

watch(() => props.editable, (val) => {
  editor.value?.setEditable(val)
})

// Cleanup on unmount
onBeforeUnmount(() => {
  if (editor.value) {
    editor.value.destroy()
  }
})
</script>

<style>
.tiptap p.is-editor-empty:first-child::before {
  content: attr(data-placeholder);
  float: left;
  color: #9ca3af;
  pointer-events: none;
  height: 0;
}
.tiptap ul[data-type="taskList"] { list-style: none; padding: 0; }
.tiptap ul[data-type="taskList"] li { display: flex; align-items: flex-start; gap: 6px; }
.tiptap ul[data-type="taskList"] li > label { margin-top: 2px; }
.tiptap img { max-width: 100%; height: auto; border-radius: 4px; margin: 4px 0; cursor: default; }

/* Mention styles */
.tiptap .mention,
.tiptap span[data-type="mention"] {
  background-color: #e0e7ff;
  color: #4f46e5;
  border-radius: 0.25rem;
  padding: 0.125rem 0.25rem;
  font-weight: 500;
  white-space: nowrap;
  cursor: pointer;
}

.tiptap .mention:hover,
.tiptap span[data-type="mention"]:hover {
  background-color: #c7d2fe;
}
</style>
