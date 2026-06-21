<template>
  <div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
      <!-- Search -->
      <div class="flex-1 max-w-md">
        <input
          type="text"
          placeholder="Search..."
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
        />
      </div>

      <!-- Right Actions -->
      <div class="flex items-center gap-4">
        <!-- Create Button -->
        <div class="relative">
          <button
            @click="showCreateMenu = !showCreateMenu"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition"
          >
            + Create
          </button>
          <div
            v-if="showCreateMenu"
            class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-10"
          >
            <Link href="/projects/create" class="block px-4 py-2 hover:bg-gray-50 text-sm">
              New Project
            </Link>
            <button class="w-full text-left px-4 py-2 hover:bg-gray-50 text-sm">
              New Task
            </button>
          </div>
        </div>

        <!-- Help Icon -->
        <button class="text-gray-600 hover:text-gray-900">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </button>

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

const showCreateMenu = ref(false)
const showProfileMenu = ref(false)

const page = usePage()
const userName = computed(() => page.props.auth?.user?.name || '')
const userAvatar = computed(() => page.props.auth?.user?.avatar || null)
</script>

