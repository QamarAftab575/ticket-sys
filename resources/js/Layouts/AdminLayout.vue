<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center space-x-8">
            <Link href="/admin" class="text-xl font-bold text-blue-600">
              🔐 Admin Panel
            </Link>
            
            <!-- Navigation Links -->
            <div class="hidden md:flex items-center space-x-6">
              <Link href="/admin" class="text-gray-700 hover:text-gray-900 font-medium" :class="{ 'text-blue-600': isActive('/admin') }">
                Dashboard
              </Link>
              <Link href="/admin/users" class="text-gray-700 hover:text-gray-900 font-medium" :class="{ 'text-blue-600': isActive('/admin/users') }">
                Users
              </Link>
              <Link href="/admin/workspaces" class="text-gray-700 hover:text-gray-900 font-medium" :class="{ 'text-blue-600': isActive('/admin/workspaces') }">
                Workspaces
              </Link>
              <Link href="/admin/settings" class="text-gray-700 hover:text-gray-900 font-medium" :class="{ 'text-blue-600': isActive('/admin/settings') }">
                Settings
              </Link>
            </div>
          </div>

          <div class="flex items-center space-x-4">
            <!-- Profile Dropdown -->
            <div class="relative">
              <button
                @click="profileDropdownOpen = !profileDropdownOpen"
                class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none"
              >
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                  {{ userInitials }}
                </div>
                <span class="text-sm font-medium hidden sm:inline">{{ userName }}</span>
              </button>

              <!-- Dropdown Menu -->
              <div
                v-if="profileDropdownOpen"
                @click.away="profileDropdownOpen = false"
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-50"
              >
                <Link
                  href="/dashboard"
                  class="block px-4 py-2 text-gray-700 hover:bg-gray-100 first:rounded-t-lg"
                >
                  Return to Dashboard
                </Link>
                <Link
                  href="/profile"
                  class="block px-4 py-2 text-gray-700 hover:bg-gray-100"
                >
                  Profile
                </Link>
                <button
                  @click="logout"
                  class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 last:rounded-b-lg border-t border-gray-200"
                >
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile Menu -->
        <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-200 py-2">
          <Link href="/admin" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
            Dashboard
          </Link>
          <Link href="/admin/users" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
            Users
          </Link>
          <Link href="/admin/workspaces" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
            Workspaces
          </Link>
          <Link href="/admin/settings" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
            Settings
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

    <!-- Flash Messages -->
    <div v-if="flashMessage" class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
      <div :class="`rounded-lg p-4 ${flashMessage.type === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'}`">
        {{ flashMessage.message }}
      </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

const profileDropdownOpen = ref(false)
const mobileMenuOpen = ref(false)
const page = usePage()

const userName = computed(() => page.props.auth?.user?.name || 'Admin')
const userInitials = computed(() => {
  const name = page.props.auth?.user?.name || 'A'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

const flashMessage = computed(() => {
  if (page.props.flash?.success) {
    return { type: 'success', message: page.props.flash.success }
  }
  if (page.props.flash?.error) {
    return { type: 'error', message: page.props.flash.error }
  }
  return null
})

const isActive = (path) => {
  return window.location.pathname.startsWith(path)
}

const logout = () => {
  router.post('/logout')
}
</script>
