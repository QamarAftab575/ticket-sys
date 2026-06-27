<template>
  <aside
    :class="[
      'fixed inset-y-0 left-0 z-30 bg-[#2A2C2E] border-r border-gray-800 flex flex-col transition-all duration-300 ease-in-out',
      'lg:static lg:translate-x-0 lg:z-auto lg:h-screen',
      collapsed ? 'w-16' : 'w-64',
      mobileOpen ? 'translate-x-0 !w-64' : '-translate-x-full lg:translate-x-0',
    ]"
  >
    <!-- Logo + Collapse Toggle -->
    <div
      :class="[
        'flex items-center h-14 border-b border-gray-800 shrink-0 transition-all duration-300',
        collapsed ? 'justify-center px-3' : 'justify-between px-5'
      ]"
    >
      <Link href="/" class="flex items-center gap-2 min-w-0">
        <img 
          src="/assets/images/logo/default-white-logo.png" 
          alt="Logo"
          class="h-8 w-auto shrink-0"
          @error="handleLogoError"
        />
       
      </Link>

      <button
        v-if="!mobileOpen"
        @click="toggleCollapse"
        :title="collapsed ? $t('expand_sidebar') : $t('collapse_sidebar')"
        class="cursor-pointer hidden lg:flex items-center justify-center w-7 h-7 rounded-lg text-gray-400 hover:text-gray-300 hover:bg-gray-700 transition shrink-0"
      >
        <svg class="w-4 h-4 transition-transform duration-300" :class="collapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7M18 19l-7-7 7-7"/>
        </svg>
      </button>

      <button @click="$emit('close')" class="cursor-pointer lg:hidden text-gray-400 hover:text-gray-300 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Scrollable nav area -->
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

      <!-- Projects Section -->
      <div class="border-t border-gray-800 pt-3">
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
            class="flex items-center gap-1.5 flex-1 min-w-0 group cursor-pointer"
            :title="$t('toggle_projects')"
          >
            <svg
              class="w-3 h-3 text-gray-400 transition-transform duration-200 shrink-0"
              :class="projectsExpanded ? 'rotate-90' : ''"
              fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest group-hover:text-gray-300 transition-colors">
              {{ $t('projects') }}
            </span>
          </button>

          <!-- Collapsed: just a folder icon -->
          <button
            v-if="collapsed"
            @click="projectsExpanded = !projectsExpanded"
            class="group relative flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-700 transition cursor-pointer"
            :title="$t('projects')"
          >
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
            </svg>
            <span class="pointer-events-none absolute left-full ml-3 px-2.5 py-1.5 rounded-lg bg-gray-900 text-white text-xs font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50 shadow-lg">
              {{ $t('projects') }}
              <span class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"/>
            </span>
          </button>

          <!-- New project button (only when expanded) -->
          <Link
            v-if="!collapsed"
            href="/projects/create"
            @click="$emit('close')"
            class="ml-auto flex items-center justify-center w-5 h-5 rounded text-gray-400 hover:text-gray-300 hover:bg-gray-700 transition cursor-pointer"
            :title="$t('create_project')"
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
            <p class="text-xs text-gray-400">{{ $t('no_projects_yet') }}</p>
            <Link href="/projects/create" class="text-xs text-blue-600 hover:underline mt-0.5 inline-block">
              {{ $t('create_one') }} →
            </Link>
          </div>

          <!-- Project rows -->
          <Link
            v-for="project in visibleProjects"
            :key="project.id"
            :href="`/projects/${project.id}`"
            @click="$emit('close')"
            :class="[
              'cursor-pointer group flex items-center gap-2 px-2 py-1.5 rounded-lg text-sm transition-colors',
              isActiveProject(project.id)
                ? 'bg-indigo-50 text-indigo-700'
                : 'text-gray-300 hover:bg-gray-700',
            ]"
          >
            <!-- Color + icon square -->
            <span
              class="shrink-0 w-5 h-5 rounded flex items-center justify-center text-white text-[10px] font-bold"
              :style="{ backgroundColor: project.color || '#6366f1' }"
            >
              <!-- SVG icon -->
              <template v-if="project.icon && project.icon.startsWith('svg:')">
                <span class="w-3 h-3 flex items-center justify-center text-white" v-html="getSvgForProject(project.icon)" />
              </template>
              <!-- Emoji icon -->
              <template v-else-if="project.icon && !project.icon.startsWith('svg:')">
                {{ project.icon }}
              </template>
              <!-- Default: First letter -->
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
              class="cursor-pointer w-full text-left text-xs text-gray-400 hover:text-gray-400 py-1 flex items-center gap-1.5 transition-colors"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
              </svg>
              {{ $t('view_all_projects') }}
            </Link>
          </div>
        </div>
      </div>

      <!-- Account section -->
      <div class="pt-3 mt-3 border-t border-gray-800 space-y-0.5">
        <p v-if="!collapsed" class="px-3 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">{{ $t('account') }}</p>
        <SidebarNavItem
          v-for="item in accountNav" :key="item.href"
          :href="item.href" :label="item.label" :icon="item.icon"
          :active="currentRoute === item.route"
          :collapsed="collapsed"
          @click="$emit('close')"
        />
      </div>
    </nav>

    <!-- Subscription Alert -->
    <div v-if="subscriptionStatus && subscriptionStatus.status !== 'active'" 
      :class="[
        'border-t border-gray-800 px-3 py-2 shrink-0',
        subscriptionStatus.status === 'warning' ? 'bg-yellow-50' :
        subscriptionStatus.status === 'expired_grace' ? 'bg-orange-50' :
        subscriptionStatus.status === 'suspended' ? 'bg-red-50' : ''
      ]"
    >
      <!-- Warning: 1 day remaining -->
      <div v-if="subscriptionStatus.status === 'warning'" class="flex items-start gap-2">
        <svg class="w-5 h-5 text-yellow-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div class="flex-1 min-w-0">
          <p v-if="!collapsed" class="text-xs font-semibold text-yellow-800 mb-1">{{ $t('expiring_soon') }}</p>
          <p v-if="!collapsed" class="text-xs text-yellow-700 line-clamp-2">{{ subscriptionStatus.message }}</p>
          <Link v-if="!collapsed" href="/settings/subscriptions" class="text-xs text-yellow-700 hover:text-yellow-900 font-semibold cursor-pointer">{{ $t('renew') }} →</Link>
        </div>
      </div>

      <!-- Grace Period: Expired but within grace days -->
      <div v-else-if="subscriptionStatus.status === 'expired_grace'" class="flex items-start gap-2">
        <svg class="w-5 h-5 text-orange-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div class="flex-1 min-w-0">
          <p v-if="!collapsed" class="text-xs font-semibold text-orange-800 mb-1">{{ $t('grace_period') }}</p>
          <p v-if="!collapsed" class="text-xs text-orange-700">{{ subscriptionStatus.message }}</p>
          <div v-if="!collapsed" class="flex items-center gap-2 mt-1">
            <Link href="/settings/subscriptions" class="text-xs text-orange-700 hover:text-orange-900 font-semibold cursor-pointer">{{ $t('renew') }} →</Link>
            <span class="text-xs text-orange-600 font-semibold">{{ subscriptionStatus.days_in_grace_period }}d {{ $t('left') }}</span>
          </div>
        </div>
      </div>

      <!-- Suspended: Grace period ended -->
      <div v-else-if="subscriptionStatus.status === 'suspended'" class="flex items-start gap-2">
        <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <div class="flex-1 min-w-0">
          <p v-if="!collapsed" class="text-xs font-semibold text-red-800 mb-1">{{ $t('suspended') }}</p>
          <p v-if="!collapsed" class="text-xs text-red-700 line-clamp-2">{{ subscriptionStatus.message }}</p>
          <Link v-if="!collapsed" href="/settings/subscriptions" class="text-xs text-red-700 hover:text-red-900 font-semibold cursor-pointer">{{ $t('renew_now') }} →</Link>
        </div>
      </div>

      <!-- Collapsed tooltip -->
      <div v-if="collapsed" class="group relative flex justify-center">
        <div class="w-2 h-2 rounded-full"
          :class="subscriptionStatus.status === 'warning' ? 'bg-yellow-500' :
                  subscriptionStatus.status === 'expired_grace' ? 'bg-orange-500' :
                  subscriptionStatus.status === 'suspended' ? 'bg-red-500' : 'bg-gray-400'"
        />
        <span class="pointer-events-none absolute left-full ml-3 px-2.5 py-1.5 rounded-lg bg-gray-900 text-white text-xs font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50 shadow-lg">
          {{ subscriptionStatus.status === 'warning' ? $t('expiring_soon') :
             subscriptionStatus.status === 'expired_grace' ? $t('grace_period') :
             subscriptionStatus.status === 'suspended' ? $t('suspended') : $t('status') }}
          <span class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"/>
        </span>
      </div>
    </div>

    <!-- Workspace Switcher (moved to bottom) -->
    <div v-if="userWorkspaces && userWorkspaces.length > 0"
      :class="['border-t border-gray-800 py-3 transition-all duration-300 shrink-0', collapsed ? 'px-2' : 'px-3']"
    >
      <p v-if="!collapsed" class="px-2 mb-2.5 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">{{ $t('workspace') }}</p>
      <WorkspaceSwitcher
        :user-workspaces="userWorkspaces"
        :current-workspace-id="currentWorkspaceId"
        :collapsed="collapsed"
        @close="$emit('close')"
      />
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

const STORAGE_KEY      = 'dashbord_project_sidebar_collapsed'
const PROJECTS_KEY     = 'dashbord_project_projects_expanded'
const INITIAL_SHOW     = 5

const props = defineProps({
  currentRoute:   String,
  userWorkspaces: Array,
  currentWorkspaceId: String,
  userRole:       String,
  mobileOpen:     Boolean,
  subscriptionStatus: Object,
})

const emit = defineEmits(['invite', 'close', 'collapsed-change'])

const page             = usePage()
const collapsed        = ref(false)
const projectsExpanded = ref(true)
const unreadCount      = ref(0)

const { userWorkspaces, currentWorkspaceId } = useSidebarData()

onMounted(() => {
  collapsed.value        = localStorage.getItem(STORAGE_KEY)  === 'true'
  projectsExpanded.value = localStorage.getItem(PROJECTS_KEY) !== 'false'
  
  fetchUnreadCount()
  
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

const handleLogoError = (e) => {
  e.target.style.display = 'none'
  e.target.parentElement.innerHTML = '<span class="w-7 h-7 rounded-lg bg-blue-600 flex items-center justify-center shrink-0"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></span> '
}

const toggleCollapse = () => {
  collapsed.value = !collapsed.value
  localStorage.setItem(STORAGE_KEY, String(collapsed.value))
  emit('collapsed-change', collapsed.value)
}

const allProjects = computed(() => page.props.sidebarProjects || [])

const activeProjects = computed(() =>
  allProjects.value.filter(p => !p.archived_at)
)

const visibleProjects = computed(() =>
  activeProjects.value.slice(0, INITIAL_SHOW)
)

const isActiveProject = (id) => {
  return page.url.includes(`/projects/${id}`)
}

const mainNav = computed(() => {
  const nav = [
    {
      route: 'dashboard', href: '/dashboard', label: page.props.translations?.dashboard || 'Dashboard',
      icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    },
    {
      route: 'my-tasks', href: '/my-tasks', label: page.props.translations?.my_tasks || 'My Tasks',
      icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
      route: 'inbox', href: '/inbox', label: page.props.translations?.inbox || 'Inbox',
      icon: 'M3 8a6 6 0 016-6h8a6 6 0 016 6v9a6 6 0 01-6 6H9a6 6 0 01-6-6V8z',
      badge: true,
    },
  ]

  if (props.userRole === 'admin' || props.userRole === 'owner') {
    nav.push({
      route: 'reports', href: '/reports', label: page.props.translations?.reports || 'Reports',
      icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    })
  }

  return nav
})

const accountNav = [
  {
    route: 'profile', href: '/profile', label: page.props.translations?.profile || 'Profile',
    icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
  },
  {
    route: 'settings', href: '/settings', label: page.props.translations?.settings || 'Settings',
    icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
  },
]

// SVG icons for projects
const SVG_ICONS = [
  { id: 'svg:list', svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="stroke: currentColor"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>` },
  { id: 'svg:board', svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="stroke: currentColor"><rect x="3" y="3" width="7" height="18" rx="1"/><rect x="14" y="3" width="7" height="10" rx="1"/></svg>` },
  { id: 'svg:chart', svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="stroke: currentColor"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>` },
  { id: 'svg:star', svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="stroke: currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>` },
  { id: 'svg:settings', svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="stroke: currentColor"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>` },
  { id: 'svg:globe', svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="stroke: currentColor"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>` },
  { id: 'svg:check', svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="stroke: currentColor"><polyline points="20 6 9 17 4 12"/></svg>` },
  { id: 'svg:users', svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="stroke: currentColor"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>` },
  { id: 'svg:lightning', svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="stroke: currentColor"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>` },
  { id: 'svg:flag', svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="stroke: currentColor"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>` },
  { id: 'svg:lock', svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="stroke: currentColor"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>` },
  { id: 'svg:target', svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="stroke: currentColor"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>` },
  { id: 'svg:box', svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="stroke: currentColor"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>` },
  { id: 'svg:trending', svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="stroke: currentColor"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>` },
  { id: 'svg:calendar', svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="stroke: currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>` },
  { id: 'svg:message', svg: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="stroke: currentColor"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>` },
]

// Helper function to get SVG icon for project
const getSvgForProject = (iconId) => {
  const found = SVG_ICONS.find(i => i.id === iconId)
  return found?.svg ?? ''
}
</script>
