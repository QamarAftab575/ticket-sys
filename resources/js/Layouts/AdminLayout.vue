<template>
  <div class="min-h-screen bg-slate-50">
    <!-- Navigation Bar -->
    <nav class="sticky top-0 z-50 bg-white border-b border-slate-200">
      <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Left Side - Logo & Nav -->
          <div class="flex items-center space-x-4 sm:space-x-8 min-w-0">
            <!-- Logo -->
            <Link href="/admin" class="flex-shrink-0">
              <img 
                src="/assets/images/logo/asira-logo-main.png" 
                alt="Asira Admin" 
                class="h-8 w-auto"
              />
            </Link>
            
            <!-- Navigation Links - Hidden on mobile -->
            <div class="hidden md:flex items-center space-x-1">
              <Link 
                href="/admin" 
                class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 rounded-lg transition-colors"
                :class="{ 'text-slate-900 bg-slate-100': isActive('/admin') }"
              >
                Dashboard
              </Link>
              <Link 
                href="/admin/users" 
                class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 rounded-lg transition-colors"
                :class="{ 'text-slate-900 bg-slate-100': isActive('/admin/users') }"
              >
                Users
              </Link>
              <Link 
                href="/admin/languages" 
                class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 rounded-lg transition-colors"
                :class="{ 'text-slate-900 bg-slate-100': isActive('/admin/languages') }"
              >
                Languages
              </Link>
              <Link 
                href="/admin/contacts" 
                class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 rounded-lg transition-colors"
                :class="{ 'text-slate-900 bg-slate-100': isActive('/admin/contacts') }"
              >
                Contacts
              </Link>
              <Link 
                href="/admin/workspaces" 
                class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 rounded-lg transition-colors"
                :class="{ 'text-slate-900 bg-slate-100': isActive('/admin/workspaces') }"
              >
                Workspaces
              </Link>
              <Link 
                href="/admin/settings" 
                class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 rounded-lg transition-colors"
                :class="{ 'text-slate-900 bg-slate-100': isActive('/admin/settings') }"
              >
                Settings
              </Link>
            </div>
          </div>

          <!-- Right Side - Profile & Menu Toggle -->
          <div class="flex items-center space-x-2 sm:space-x-3">
            <!-- Profile Dropdown -->
            <div class="relative">
              <button
                @click="profileDropdownOpen = !profileDropdownOpen"
                class="flex items-center space-x-2 text-slate-700 hover:text-slate-900 focus:outline-none transition-colors"
              >
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg text-white text-xs font-semibold flex items-center justify-center cursor-pointer flex-shrink-0">
                  {{ userInitials }}
                </div>
                <span class="text-sm font-medium hidden sm:inline text-slate-700 max-w-[120px] truncate">
                  {{ userName }}
                </span>
              </button>

              <!-- Dropdown Menu -->
              <div
                v-if="profileDropdownOpen"
                @click.away="profileDropdownOpen = false"
                class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-lg shadow-lg z-50 overflow-hidden"
              >
                <Link
                  href="/dashboard"
                  class="block px-4 py-2 text-slate-700 hover:bg-slate-50 text-sm transition-colors border-b border-slate-100"
                >
                  Return to Dashboard
                </Link>
                <Link
                  href="/profile"
                  class="block px-4 py-2 text-slate-700 hover:bg-slate-50 text-sm transition-colors border-b border-slate-100"
                >
                  Profile
                </Link>
                <button
                  @click="logout"
                  class="w-full text-left px-4 py-2 text-slate-700 hover:bg-slate-50 text-sm transition-colors"
                >
                  Logout
                </button>
              </div>
            </div>

            <!-- Mobile Menu Toggle -->
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="md:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Mobile Menu -->
        <div v-if="mobileMenuOpen" class="md:hidden border-t border-slate-200 py-2 space-y-1">
          <Link 
            href="/admin" 
            class="block px-4 py-2 text-slate-700 hover:bg-slate-100 text-sm rounded-lg transition-colors"
            :class="{ 'text-slate-900 bg-slate-100': isActive('/admin') }"
            @click="mobileMenuOpen = false"
          >
            Dashboard
          </Link>
          <Link 
            href="/admin/users" 
            class="block px-4 py-2 text-slate-700 hover:bg-slate-100 text-sm rounded-lg transition-colors"
            :class="{ 'text-slate-900 bg-slate-100': isActive('/admin/users') }"
            @click="mobileMenuOpen = false"
          >
            Users
          </Link>
          <Link 
            href="/admin/languages" 
            class="block px-4 py-2 text-slate-700 hover:bg-slate-100 text-sm rounded-lg transition-colors"
            :class="{ 'text-slate-900 bg-slate-100': isActive('/admin/languages') }"
            @click="mobileMenuOpen = false"
          >
            Languages
          </Link>
          <Link 
            href="/admin/contacts" 
            class="block px-4 py-2 text-slate-700 hover:bg-slate-100 text-sm rounded-lg transition-colors"
            :class="{ 'text-slate-900 bg-slate-100': isActive('/admin/contacts') }"
            @click="mobileMenuOpen = false"
          >
            Contacts
          </Link>
          <Link 
            href="/admin/workspaces" 
            class="block px-4 py-2 text-slate-700 hover:bg-slate-100 text-sm rounded-lg transition-colors"
            :class="{ 'text-slate-900 bg-slate-100': isActive('/admin/workspaces') }"
            @click="mobileMenuOpen = false"
          >
            Workspaces
          </Link>
          <Link 
            href="/admin/settings" 
            class="block px-4 py-2 text-slate-700 hover:bg-slate-100 text-sm rounded-lg transition-colors"
            :class="{ 'text-slate-900 bg-slate-100': isActive('/admin/settings') }"
            @click="mobileMenuOpen = false"
          >
            Settings
          </Link>
        </div>
      </div>
    </nav>

    <!-- Header -->
    <header v-if="$slots.header" class="bg-white border-b border-slate-200">
      <div class="px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto py-6 sm:py-8">
          <slot name="header" />
        </div>
      </div>
    </header>

    <!-- Flash Messages -->
    <div v-if="flashMessage" class="max-w-7xl mx-auto mt-6 px-4 sm:px-6 lg:px-8">
      <div :class="`rounded-lg px-4 py-3 text-sm border ${flashMessage.type === 'success' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-red-50 text-red-800 border-red-200'}`">
        {{ flashMessage.message }}
      </div>
    </div>

    <!-- Main Content -->
    <main class="px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
      <div class="max-w-7xl mx-auto">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
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
