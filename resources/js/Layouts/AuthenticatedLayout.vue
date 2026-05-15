<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center space-x-8">
            <Link href="/dashboard" class="text-xl font-bold text-blue-600">
              Asira
            </Link>
            
            <!-- Navigation Links -->
            <div class="hidden md:flex items-center space-x-6">
              <Link href="/dashboard" class="text-gray-700 hover:text-gray-900 font-medium">
                Dashboard
              </Link>
              <Link href="/projects" class="text-gray-700 hover:text-gray-900 font-medium">
                Projects
              </Link>
            </div>
          </div>

          <div class="flex items-center space-x-4">
            <!-- Mobile Menu Button -->
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="md:hidden text-gray-700 hover:text-gray-900"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
            
            <!-- Profile Dropdown -->
            <div class="relative">
              <button
                @click="profileDropdownOpen = !profileDropdownOpen"
                class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none"
              >
                <Avatar :src="userAvatar" :name="userName" size="sm" />
                <span class="text-sm font-medium hidden sm:inline">{{ userName }}</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
              </button>

              <!-- Dropdown Menu -->
              <div
                v-if="profileDropdownOpen"
                @click.away="profileDropdownOpen = false"
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-50"
              >
                <Link
                  href="/profile"
                  class="block px-4 py-2 text-gray-700 hover:bg-gray-100 first:rounded-t-lg"
                >
                  Manage Account
                </Link>
                <Link
                  href="/settings"
                  class="block px-4 py-2 text-gray-700 hover:bg-gray-100"
                >
                  Settings
                </Link>
                <button
                  @click="logout"
                  class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 last:rounded-b-lg"
                >
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile Menu -->
        <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-200 py-2">
          <Link href="/dashboard" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
            Dashboard
          </Link>
          <Link href="/projects" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
            Projects
          </Link>
        </div>
      </div>
    </nav>

    <!-- Header -->
    <header v-if="$slots.header" class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <slot name="header" />
      </div>
    </header>

    <!-- Main Content -->
    <main>
      <slot />
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import Avatar from '@/Components/Avatar.vue'

const profileDropdownOpen = ref(false)
const mobileMenuOpen = ref(false)
const page = usePage()

const userName = computed(() => page.props.auth?.user?.name || 'User')
const userAvatar = computed(() => page.props.auth?.user?.avatar || null)

const logout = () => {
  router.post('/logout')
}
</script>
