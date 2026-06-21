<template>
  <div class="bg-[#2C2C2C] border-b border-[#1F1F1F] px-4 py-2 h-14 flex items-center justify-between">
    <!-- Left Section: Hamburger + Create Button -->
    <div class="flex items-center gap-3">
      <!-- Hamburger Menu (Sidebar Toggle) -->
      <button
        @click="toggleSidebar"
        class="lg:hidden text-gray-300 hover:text-white hover:bg-gray-700 p-2 rounded transition"
        title="Toggle Sidebar"
      >
        <svg 
          class="w-4 h-4 transition-transform duration-300" 
          :class="{ 'rotate-180': sidebarOpen }"
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7M18 19l-7-7 7-7"></path>
        </svg>
      </button>

      <!-- Create Button with Dropdown -->
      <div class="relative" data-create-menu>
        <button
          @click="showCreateMenu = !showCreateMenu"
          class="flex items-center gap-2 bg-[#F06A6A] hover:bg-[#E15555] text-white px-4 py-1.5 rounded-full transition font-medium text-sm cursor-pointer"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10.5 1.5H9.5V9.5H1.5V10.5H9.5V18.5H10.5V10.5H18.5V9.5H10.5V1.5Z" fill="currentColor"/>
          </svg>
          <span>Create</span>
        </button>

        <!-- Create Dropdown Menu -->
        <div
          v-if="showCreateMenu"
          class="absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50"
          @click.stop
        >
          <Link 
            href="/projects/create" 
            class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 text-gray-700 transition cursor-pointer"
            @click="showCreateMenu = false"
          >
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
            </svg>
            <span class="text-sm font-medium">Project</span>
          </Link>
        </div>
      </div>
    </div>

    <!-- Center Section: Search Bar -->
    <div class="flex-1 max-w-2xl mx-auto px-6">
      <SearchDropdown />
    </div>

    <!-- Right Section: User Profile -->
    <div class="flex items-center gap-4">
      <!-- User Profile Dropdown -->
      <div class="relative" data-profile-menu>
        <button
          @click="showProfileMenu = !showProfileMenu"
          class="rounded-full hover:ring-2 hover:ring-gray-500 transition cursor-pointer"
        >
          <Avatar :src="userAvatar" :name="userName" size="md" />
        </button>
        <div
          v-if="showProfileMenu"
          class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50"
          @click.stop
        >
          <Link href="/profile" class="block px-4 py-2.5 hover:bg-gray-50 text-sm text-gray-700 cursor-pointer">
            Profile
          </Link>
          <Link href="/settings" class="block px-4 py-2.5 hover:bg-gray-50 text-sm text-gray-700 cursor-pointer">
            Settings
          </Link>
          <Link href="/logout" method="post" class="block px-4 py-2.5 hover:bg-gray-50 text-sm text-gray-700 border-t border-gray-200 cursor-pointer">
            Logout
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import Avatar from '@/Components/Avatar.vue'
import SearchDropdown from '@/Components/GlobalSearch/SearchDropdown.vue'

const emit = defineEmits(['toggle-sidebar'])

const showProfileMenu = ref(false)
const showCreateMenu = ref(false)
const sidebarOpen = ref(false)

const page = usePage()
const userName = computed(() => page.props.auth?.user?.name || '')
const userAvatar = computed(() => page.props.auth?.user?.avatar || null)

// Toggle sidebar and emit event
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
  emit('toggle-sidebar', sidebarOpen.value)
}

// Close menus when clicking outside
const handleClickOutside = (event) => {
  const createBtn = event.target.closest('[data-create-menu]')
  const profileBtn = event.target.closest('[data-profile-menu]')
  
  if (!createBtn) showCreateMenu.value = false
  if (!profileBtn) showProfileMenu.value = false
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
