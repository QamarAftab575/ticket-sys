<template>
  <aside
    :class="[
      'fixed inset-y-0 left-0 z-30 bg-white border-r border-gray-100 flex flex-col transition-all duration-300 ease-in-out shadow-sm',
      'lg:static lg:translate-x-0 lg:z-auto lg:shadow-none',
      collapsed ? 'w-16' : 'w-64',
      mobileOpen ? 'translate-x-0 !w-64' : '-translate-x-full lg:translate-x-0',
    ]"
  >
    <!-- ── Logo + Collapse Toggle ── -->
    <div
      :class="[
        'flex items-center h-14 border-b border-gray-100 shrink-0 transition-all duration-300',
        collapsed ? 'justify-center px-3' : 'justify-between px-5'
      ]"
    >
      <Link href="/" class="flex items-center gap-2 min-w-0">
        <span class="w-7 h-7 rounded-lg bg-blue-600 flex items-center justify-center shrink-0">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
        </span>
        <span v-if="!collapsed" class="text-base font-bold text-gray-900 truncate">Asira</span>
      </Link>

      <button
        v-if="!mobileOpen"
        @click="toggleCollapse"
        :title="collapsed ? 'Expand' : 'Collapse'"
        class="hidden lg:flex items-center justify-center w-7 h-7 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition shrink-0"
      >
        <svg class="w-4 h-4 transition-transform duration-300" :class="collapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7M18 19l-7-7 7-7"/>
        </svg>
      </button>

      <button @click="$emit('close')" class="lg:hidden text-gray-400 hover:text-gray-700 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- ── Workspace Switcher ── -->
    <div v-if="userWorkspaces && userWorkspaces.length > 0"
      :class="['border-b border-gray-100 py-3 transition-all duration-300', collapsed ? 'px-2' : 'px-3']"
    >
      <p v-if="!collapsed" class="px-2 mb-2.5 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Workspace</p>
      <WorkspaceSwitcher
        :user-workspaces="userWorkspaces"
        :current-workspace-id="currentWorkspaceId"
        :collapsed="collapsed"
        @close="$emit('close')"
      />
    </div>

    <!-- ── Scrollable nav area ── -->
    <nav :class="['flex-1 py-3 overflow-y-auto transition-all duration-300', collapsed ? 'px-2' : 'px-3']">

      <!-- Main nav items -->
      <div class="space-y-0.5 mb-3">
        <SidebarNavItem
          v-for="item in mainNav" :key="item.href"
          :href="item.href" :label="item.label" :icon="item.icon"
          :active="currentRoute === item.route"
          :collapsed="collapsed"
          :badge="item.badge"
          :unread-count="item.badge ? unreadCount : 0"
          @click="$emit('close')"
        />
      </div>

      <!-- ── Projects Section ── -->
      <div class="border-t border-gray-100 pt-3">
        <!-- Section header -->
        <div
          :class="[
            'flex items-center mb-1 rounded-lg transition-colors',
            collapsed ? 'justify-center px-1 py-1' : 'px-2 py-1'
          ]"
        >
          <!-- Collapse toggle (only when sidebar expanded) -->
          <button
            v-if="!collapsed"
            @click="projectsExpanded = !projectsExpanded"
            class="flex items-center gap-1.5 flex-1 min-w-0 group"
            title="Toggle projects"
          >
            <svg
              class="w-3 h-3 text-gray-400 transition-transform duration-200 shrink-0"
              :class="projectsExpanded ? 'rotate-90' : ''"
              fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-widest group-hover:text-gray-700 transition-colors">
              Projects
            </span>
          </button>

          <!-- Collapsed: just a folder icon -->
          <button
            v-if="collapsed"
            @click="projectsExpanded = !projectsExpanded"
            class="group relative flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-100 transition"
            title="Projects"
          >
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
            </svg>
            <span class="pointer-events-none absolute left-full ml-3 px-2.5 py-1.5 rounded-lg bg-gray-900 text-white text-xs font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50 shadow-lg">
              Projects
              <span class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"/>
            </span>
          </button>

          <!-- + New project button (only when expanded) -->
          <Link
            v-if="!collapsed"
            href="/projects/create"
            @click="$emit('close')"
            class="ml-auto flex items-center justify-center w-5 h-5 rounded text-gray-400 hover:text-gray-700 hover:bg-gray-200 transition"
            title="New project"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
          </Link>
        </div>

        <!-- Project list (only when expanded and sidebar not collapsed) -->
        <div v-if="projectsExpanded && !collapsed" class="space-y-0.5">
          <!-- Empty state -->
          <div v-if="visibleProjects.length === 0" class="px-3 py-3 text-center">
            <p class="text-xs text-gray-400">No projects yet</p>
            <Link href="/projects/create" class="text-xs text-blue-600 hover:underline mt-0.5 inline-block">
              Create one →
            </Link>
          </div>

          <!-- Project rows -->
          <Link
            v-for="project in visibleProjects"
            :key="project.id"
            :href="`/projects/${project.id}`"
            @click="$emit('close')"
            :class="[
              'group flex items-center gap-2 px-2 py-1.5 rounded-lg text-sm transition-colors',
              isActiveProject(project.id)
                ? 'bg-indigo-50 text-indigo-700'
                : 'text-gray-700 hover:bg-gray-100',
            ]"
          >
            <!-- Color + icon square -->
            <span
              class="shrink-0 w-5 h-5 rounded flex items-center justify-center text-white text-[10px] font-bold"
              :style="{ backgroundColor: project.color || '#6366f1' }"
            >
              <template v-if="project.icon && !project.icon.startsWith('svg:')">
                {{ project.icon }}
              </template>
              <template v-else>
                {{ (project.name || '?').charAt(0).toUpperCase() }}
              </template>
            </span>

            <!-- Name -->
            <span class="flex-1 truncate text-[13px]">{{ project.name }}</span>

            <!-- Private lock -->
            <svg
              v-if="project.privacy === 'private'"
              class="w-3 h-3 text-gray-400 shrink-0"
              fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
          </Link>

          <!-- View all projects link -->
          <div class="px-2 pt-1">
            <Link
              href="/projects"
              @click="$emit('close')"
              class="w-full text-left text-xs text-gray-400 hover:text-gray-600 py-1 flex items-center gap-1.5 transition-colors"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
              </svg>
              View all projects
            </Link>
          </div>
        </div>
      </div>

      <!-- ── Account section ── -->
      <div class="pt-3 mt-3 border-t border-gray-100 space-y-0.5">
        <p v-if="!collapsed" class="px-3 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Account</p>
        <SidebarNavItem
          v-for="item in accountNav" :key="item.href"
          :href="item.href" :label="item.label" :icon="item.icon"
          :active="currentRoute === item.route"
          :collapsed="collapsed"
          @click="$emit('close')"
        />
      </div>
    </nav>

    <!-- ── Invite Button ── -->
    <div :class="['border-t border-gray-100 p-3 shrink-0']">
      <button
        @click="$emit('invite')"
        :title="collapsed ? 'Invite Teammates' : ''"
        :class="[
          'group relative w-full flex items-center gap-2 rounded-xl bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 active:scale-95 transition-all duration-150 shadow-sm',
          collapsed ? 'justify-center p-2.5' : 'px-4 py-2.5'
        ]"
      >
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
        </svg>
        <span v-if="!collapsed">Invite Teammates</span>
        <span v-if="collapsed"
          class="pointer-events-none absolute left-full ml-3 px-2.5 py-1.5 rounded-lg bg-gray-900 text-white text-xs font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-150 z-50 shadow-lg"
        >
          Invite Teammates
          <span class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"/>
        </span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import SidebarNavItem from '@/Components/Layout/SidebarNavItem.vue'
import WorkspaceSwitcher from '@/Components/Layout/WorkspaceSwitcher.vue'
import { api } from '@/Services/api'
import { useSidebarData } from '@/Composables/useSidebarData'

const STORAGE_KEY      = 'asira_sidebar_collapsed'
const PROJECTS_KEY     = 'asira_projects_expanded'
const INITIAL_SHOW     = 5

const props = defineProps({
  currentRoute:   String,
  userWorkspaces: Array, // Optional, will use composable
  currentWorkspaceId: String, // Optional, will use composable
  userRole:       String,
  mobileOpen:     Boolean,
})

const emit = defineEmits(['invite', 'close', 'collapsed-change'])

const page             = usePage()
const collapsed        = ref(false)
const projectsExpanded = ref(true)
const unreadCount      = ref(0)

// Use composable for independent workspace data loading
const { userWorkspaces, currentWorkspaceId } = useSidebarData()

onMounted(() => {
  collapsed.value        = localStorage.getItem(STORAGE_KEY)  === 'true'
  projectsExpanded.value = localStorage.getItem(PROJECTS_KEY) !== 'false' // default open
  
  // Fetch unread notification count
  fetchUnreadCount()
  
  // Poll for updates every 30 seconds
  setInterval(fetchUnreadCount, 30000)
})

async function fetchUnreadCount() {
  try {
    const response = await api.get('/inbox/unread-count')
    unreadCount.value = response.unread_count || 0
  } catch (error) {
    // Silently fail - not critical
  }
}

const toggleCollapse = () => {
  collapsed.value = !collapsed.value
  localStorage.setItem(STORAGE_KEY, String(collapsed.value))
  emit('collapsed-change', collapsed.value)
}

// Projects from shared Inertia props (set by HandleInertiaRequests middleware)
const allProjects = computed(() => page.props.sidebarProjects || [])

// Only non-archived
const activeProjects = computed(() =>
  allProjects.value.filter(p => !p.archived_at)
)

// Slice to INITIAL_SHOW — "View all" link handles the rest
const visibleProjects = computed(() =>
  activeProjects.value.slice(0, INITIAL_SHOW)
)

// Detect active project from URL
const isActiveProject = (id) => {
  return page.url.includes(`/projects/${id}`)
}

const mainNav = [
  {
    route: 'dashboard', href: '/dashboard', label: 'Dashboard',
    icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
  },
  {
    route: 'my-tasks', href: '/my-tasks', label: 'My Tasks',
    icon: 'M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m5 0h2a2 2 0 002-2V7a2 2 0 00-2-2h-2m-5 4v6m0 0l3-3m-3 3l-3-3',
  },
  {
    route: 'inbox', href: '/inbox', label: 'Inbox',
    icon: 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707.293l-2.414-2.414A1 1 0 006.586 13H4',
    badge: true, // Show unread count badge
  },
]

const accountNav = [
  {
    route: 'profile', href: '/profile', label: 'Profile',
    icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
  },
  {
    route: 'settings', href: '/settings', label: 'Settings',
    icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
  },
]
</script>
