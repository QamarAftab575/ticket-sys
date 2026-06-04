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
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import Avatar from '@/Components/Avatar.vue'
import SearchDropdown from '@/Components/GlobalSearch/SearchDropdown.vue'

defineEmits(['toggle-sidebar'])

const showProfileMenu = ref(false)

const page = usePage()
const userName = computed(() => page.props.auth?.user?.name || '')
const userAvatar = computed(() => page.props.auth?.user?.avatar || null)
</script>
