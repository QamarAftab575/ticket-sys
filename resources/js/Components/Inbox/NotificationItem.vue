<template>
  <div
    @click="$emit('click')"
    :class="[
      'px-4 py-3 cursor-pointer transition-colors border-b border-gray-50 hover:bg-gray-50',
      selected ? 'bg-indigo-50 border-l-4 border-l-indigo-600' : '',
      !notification.is_read ? 'bg-blue-50/30' : ''
    ]"
  >
    <div class="flex items-start gap-3">
      <!-- Actor avatar -->
      <Avatar
        :name="notification.actor?.name || 'Unknown'"
        :src="notification.actor?.avatar"
        size="sm"
        class="flex-shrink-0 mt-0.5"
      />

      <div class="flex-1 min-w-0">
        <!-- Title -->
        <div class="flex items-start justify-between gap-2 mb-1">
          <p class="text-sm text-gray-900 font-medium leading-snug">
            {{ notification.title }}
          </p>
          <button
            @click.stop="toggleMenu"
            class="flex-shrink-0 p-1 text-gray-400 hover:text-gray-600 rounded opacity-0 group-hover:opacity-100 transition-opacity"
          >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
            </svg>
          </button>
        </div>

        <!-- Message preview -->
        <p v-if="notification.message" class="text-xs text-gray-500 line-clamp-2 mb-1">
          {{ notification.message }}
        </p>

        <!-- Meta info -->
        <div class="flex items-center gap-2 text-xs text-gray-400">
          <!-- Project badge -->
          <div v-if="notification.task?.project" class="flex items-center gap-1">
            <span
              class="w-2 h-2 rounded-sm flex-shrink-0"
              :style="{ backgroundColor: notification.task.project.color || '#6366f1' }"
            />
            <span>{{ notification.task?.project?.name }}</span>
          </div>
          <span>·</span>
          <span>{{ formatTime(notification.created_at) }}</span>
          <span v-if="!notification.is_read" class="w-2 h-2 bg-indigo-600 rounded-full ml-1"/>
        </div>
      </div>
    </div>

    <!-- Dropdown menu -->
    <div
      v-if="menuOpen"
      v-click-outside="closeMenu"
      class="absolute right-4 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-10 w-40"
    >
      <button
        v-if="!notification.is_read"
        @click.stop="$emit('mark-read'); closeMenu()"
        class="w-full px-3 py-1.5 text-left text-sm text-gray-700 hover:bg-gray-50"
      >
        Mark as read
      </button>
      <button
        v-else
        @click.stop="$emit('mark-unread'); closeMenu()"
        class="w-full px-3 py-1.5 text-left text-sm text-gray-700 hover:bg-gray-50"
      >
        Mark as unread
      </button>
      <button
        @click.stop="$emit('delete'); closeMenu()"
        class="w-full px-3 py-1.5 text-left text-sm text-red-600 hover:bg-red-50"
      >
        Delete
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Avatar from '@/Components/Avatar.vue'

defineProps({
  notification: { type: Object, required: true },
  selected: { type: Boolean, default: false },
})

defineEmits(['click', 'mark-read', 'mark-unread', 'delete'])

const menuOpen = ref(false)

function toggleMenu() {
  menuOpen.value = !menuOpen.value
}

function closeMenu() {
  menuOpen.value = false
}

function formatTime(ts) {
  if (!ts) return ''
  let str = typeof ts === 'string' ? ts.trim() : String(ts)
  // Normalize MySQL "YYYY-MM-DD HH:MM:SS" → ISO UTC
  if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(str)) str = str.replace(' ', 'T') + 'Z'
  else if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/.test(str)) str = str + 'Z'
  const d = new Date(str)
  if (isNaN(d.getTime())) return ''
  const diff = Math.floor((Date.now() - d.getTime()) / 1000)
  if (diff < 60) return 'Just now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  // Older than 24h
  const day = d.getDate()
  const month = d.toLocaleDateString('en-US', { month: 'short' })
  const year = d.getFullYear()
  return `${day} ${month} ${year}`
}

const vClickOutside = {
  mounted(el, binding) {
    el._co = (e) => {
      if (!el.contains(e.target)) binding.value(e)
    }
    document.addEventListener('mousedown', el._co)
  },
  unmounted(el) {
    document.removeEventListener('mousedown', el._co)
  },
}
</script>
