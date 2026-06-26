<template>
  <div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <!-- Search & Filters -->
      <div class="flex-1 flex gap-3">
        <div class="flex-1 relative">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search projects..."
            class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white transition-all"
            @input="applyFilters"
          />
        </div>
        <select
          v-model="filters.status"
          class="px-4 py-2.5 border border-gray-200 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all cursor-pointer"
          @change="applyFilters"
        >
          <option value="">All Statuses</option>
          <option value="on_track">On Track</option>
          <option value="at_risk">At Risk</option>
          <option value="off_track">Off Track</option>
          <option value="archived">Archived</option>
        </select>
      </div>
    </div>

    <!-- Projects Grid -->
    <div v-if="projects && projects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <Link
        v-for="project in projects"
        :key="project.id"
        :href="`/projects/${project.id}`"
        class="group h-full"
      >
        <div class="h-full flex flex-col p-6 rounded-xl border border-gray-200 bg-white hover:border-blue-300 hover:shadow-lg hover:shadow-blue-100 transition-all duration-200 cursor-pointer">
          <!-- Header with color and info -->
          <div class="flex items-start justify-between gap-3 mb-4">
            <!-- Avatar with color -->
            <div
              class="shrink-0 w-12 h-12 rounded-lg flex items-center justify-center shadow-sm"
              :style="{ backgroundColor: project.color || '#2563EB' }"
            >
              <!-- SVG Icon -->
              <span v-if="project.icon && project.icon.startsWith('svg:')" class="w-6 h-6 flex items-center justify-center text-white" v-html="getSvgIcon(project.icon)" />
              <!-- Old icon format -->
              <svg v-else class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M5 5C5 6.3805 3.8805 7.5 2.5 7.5C1.1195 7.5 0 6.3805 0 5C0 3.6195 1.1195 2.5 2.5 2.5C3.8805 2.5 5 3.6195 5 5ZM2.5 9.5C1.1195 9.5 0 10.6195 0 12C0 13.3805 1.1195 14.5 2.5 14.5C3.8805 14.5 5 13.3805 5 12C5 10.6195 3.8805 9.5 2.5 9.5ZM2.5 16.5C1.1195 16.5 0 17.6195 0 19C0 20.3805 1.1195 21.5 2.5 21.5C3.8805 21.5 5 20.3805 5 19C5 17.6195 3.8805 16.5 2.5 16.5ZM9 3V7H24V3H9ZM9 14H24V10H9V14ZM9 21H24V17H9V21Z"/>
              </svg>
            </div>

            <!-- Status badge -->
            <span
              v-if="project.status"
              :class="[
                'px-2.5 py-1 rounded-full text-xs font-medium shrink-0',
                getStatusBadgeClass(project.status)
              ]"
            >
              {{ formatStatus(project.status) }}
            </span>
          </div>

          <!-- Title and description -->
          <div class="flex-1 min-w-0 mb-4">
            <h3 class="text-base font-semibold text-gray-900 group-hover:text-blue-600 transition-colors truncate">
              {{ project.name }}
            </h3>
            <p v-if="project.description" class="text-sm text-gray-600 line-clamp-2 mt-1">
              {{ project.description }}
            </p>
          </div>

          <!-- Stats row -->
          <div class="flex items-center gap-4 pt-4 border-t border-gray-100 text-xs text-gray-500">
            <!-- Members -->
            <div class="flex items-center gap-1.5">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0h.01M9 12h.01M7.08 9.32a4 4 0 110 5.36m3.32-3.32a1 1 0 11-2 0 1 1 0 012 0z" />
              </svg>
              <span>{{ project.members?.length || 0 }}</span>
            </div>

            <!-- Privacy -->
            <div v-if="project.privacy === 'private'" class="flex items-center gap-1.5">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              <span>Private</span>
            </div>
            <div v-else class="flex items-center gap-1.5">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>Shared</span>
            </div>

            <!-- Archived status -->
            <div v-if="project.archived_at" class="flex items-center gap-1.5 text-amber-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h6a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V5z" />
              </svg>
              <span>Archived</span>
            </div>
          </div>

          <!-- Footer: Owner/Lead -->
          <div v-if="project.manager || project.owner" class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-2 text-xs text-gray-600">
            <span>Lead:</span>
            <span class="font-medium text-gray-900">{{ project.manager?.name || project.owner?.name || 'Unassigned' }}</span>
          </div>
        </div>
      </Link>
    </div>

    <!-- Empty State -->
    <div v-else class="flex flex-col items-center justify-center py-16">
      <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
        </svg>
      </div>
      <p class="text-gray-600 font-medium text-base mb-1">No projects found</p>
      <p class="text-gray-500 text-sm">Try adjusting your search or create a new project</p>
    </div>

    <!-- Pagination -->
    <div v-if="pagination && (pagination.prev_page_url || pagination.next_page_url)" class="flex justify-center items-center gap-2 mt-8 pt-6 border-t border-gray-200">
      <Link
        v-if="pagination.prev_page_url"
        :href="pagination.prev_page_url"
        class="flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Previous
      </Link>

      <div v-if="pagination.current_page" class="text-sm text-gray-600 px-2">
        Page <span class="font-medium">{{ pagination.current_page }}</span>
      </div>

      <Link
        v-if="pagination.next_page_url"
        :href="pagination.next_page_url"
        class="flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
      >
        Next
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </Link>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  projects: Array,
  pagination: Object,
  filters: Object,
});

const filters = ref({ search: props.filters?.search || '', status: props.filters?.status || '' });

let searchTimeout = null

const applyFilters = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    router.get('/projects', {
      search: filters.value.search || undefined,
      status: filters.value.status || undefined,
    }, { preserveState: true, replace: true })
  }, 300)
}

const formatStatus = (status) => {
  const statusMap = {
    on_track: 'On Track',
    at_risk: 'At Risk',
    off_track: 'Off Track',
    on_hold: 'On Hold',
    complete: 'Complete',
    archived: 'Archived',
  }
  return statusMap[status] || status
}

const getStatusBadgeClass = (status) => {
  const classes = {
    on_track: 'bg-green-100 text-green-700 border border-green-300',
    at_risk: 'bg-yellow-100 text-yellow-700 border border-yellow-300',
    off_track: 'bg-red-100 text-red-700 border border-red-300',
    on_hold: 'bg-gray-100 text-gray-700 border border-gray-300',
    complete: 'bg-blue-100 text-blue-700 border border-blue-300',
    archived: 'bg-gray-100 text-gray-700 border border-gray-300',
  }
  return classes[status] || 'bg-gray-100 text-gray-700 border border-gray-300'
}

// SVG icons
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

const getSvgIcon = (iconId) => {
  const found = SVG_ICONS.find(i => i.id === iconId)
  return found?.svg ?? ''
}
</script>
