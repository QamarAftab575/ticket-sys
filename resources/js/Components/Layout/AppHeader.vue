<template>
  <div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
      <!-- Hamburger (mobile only) -->
      <button
        class="lg:hidden mr-4 text-gray-600 hover:text-gray-900"
        @click="$emit('toggle-sidebar')"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
      <!-- Search -->
      <SearchDropdown />

      <!-- Right Actions -->
      <div class="flex items-center gap-4">
        <!-- Inbox / Notifications -->
        <Link href="/inbox" class="relative p-1.5 rounded-md text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <span
            v-if="unreadCount > 0"
            class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[16px] h-4 px-1 rounded-full bg-red-500 text-white text-[10px] font-semibold leading-none"
          >
            {{ unreadCount > 99 ? '99+' : unreadCount }}
          </span>
        </Link>

        <!-- User Profile -->
        <div class="relative">
          <button
            @click="showProfileMenu = !showProfileMenu"
            class="rounded-full hover:ring-2 hover:ring-blue-400 transition"
          >
            <Avatar :src="userAvatar" :name="userName" size="md" />
          </button>
          <div
            v-if="showProfileMenu"
            class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-10"
          >
            <Link href="/profile" class="block px-4 py-2 hover:bg-gray-50 text-sm">
              Profile
            </Link>
            <Link href="/settings" class="block px-4 py-2 hover:bg-gray-50 text-sm">
              Settings
            </Link>
            <Link href="/logout" method="post" class="block px-4 py-2 hover:bg-gray-50 text-sm border-t border-gray-200">
              Logout
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import Avatar from '@/Components/Avatar.vue'
import SearchDropdown from '@/Components/GlobalSearch/SearchDropdown.vue'
import { useNotificationStore } from '@/Stores/useNotificationStore'
import { api } from '@/Services/api'

defineEmits(['toggle-sidebar'])

const showProfileMenu = ref(false)

const page = usePage()
const userName = computed(() => page.props.auth?.user?.name || '')
const userAvatar = computed(() => page.props.auth?.user?.avatar || null)

const notificationStore = useNotificationStore()
const unreadCount = computed(() => notificationStore.unreadCount)

// Seed the store with the server-provided unread count on first load
onMounted(async () => {
  try {
    const response = await api.get('/inbox/unread-count')
    notificationStore.setUnreadCount(response.count ?? 0)
  } catch {
    // non-critical — badge simply stays at 0
  }
})
</script>
