<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-full">
    <div class="flex items-center justify-between mb-5">
      <div class="flex items-center gap-2">
        <h2 class="text-base font-semibold text-gray-900">{{ $t('projects') }}</h2>
        
       
      </div>
    </div>

    <!-- Projects Grid (Create button + Projects) -->
    <div v-if="sortedProjects.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <!-- Create Project Button -->
      <Link
        href="/projects/create"
        class="flex items-center gap-3 px-4 py-4 border-2 border-dashed border-gray-300 rounded-xl hover:border-blue-400 hover:bg-blue-50/30 transition-all duration-150 cursor-pointer group"
      >
        <div class="w-10 h-10 rounded-lg border-2 border-dashed border-gray-300 group-hover:border-blue-400 flex items-center justify-center transition-colors duration-150">
          <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
        </div>
        <span class="text-sm font-medium text-gray-600 group-hover:text-blue-600">{{ $t('create_project_btn') }}</span>
      </Link>

      <!-- Project Cards -->
      <Link
        v-for="project in sortedProjects"
        :key="project.id"
        :href="`/projects/${project.id}`"
        class="group flex items-center gap-3 p-4 rounded-xl border border-gray-200 hover:border-gray-300 hover:shadow-md hover:bg-gray-50 transition-all duration-150 cursor-pointer"
      >
        <!-- Color + Icon Square -->
        <div
          class="shrink-0 w-10 h-10 rounded-lg flex items-center justify-center shadow-sm hover:scale-105 transition-transform duration-150 cursor-pointer overflow-hidden"
          :style="{ backgroundColor: project.color || '#6366f1' }"
          @click.prevent="selectProject(project)"
          :title="`${project.name} - Click to edit`"
        >
          <!-- New SVG format (svg:star, svg:list, etc.) -->
          <span v-if="project.icon && project.icon.startsWith('svg:')" class="w-5 h-5 flex items-center justify-center text-white" v-html="getSvgIcon(project.icon)" />
          <!-- Old icon palette format -->
          <svg v-else-if="project.icon && iconPalette.find(i => i.icon === project.icon)" class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path :d="iconPalette.find(i => i.icon === project.icon)?.svg"/>
          </svg>
          <!-- Default SVG Icon -->
          <svg v-else class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M5 5C5 6.3805 3.8805 7.5 2.5 7.5C1.1195 7.5 0 6.3805 0 5C0 3.6195 1.1195 2.5 2.5 2.5C3.8805 2.5 5 3.6195 5 5ZM2.5 9.5C1.1195 9.5 0 10.6195 0 12C0 13.3805 1.1195 14.5 2.5 14.5C3.8805 14.5 5 13.3805 5 12C5 10.6195 3.8805 9.5 2.5 9.5ZM2.5 16.5C1.1195 16.5 0 17.6195 0 19C0 20.3805 1.1195 21.5 2.5 21.5C3.8805 21.5 5 20.3805 5 19C5 17.6195 3.8805 16.5 2.5 16.5ZM9 3V7H24V3H9ZM9 14H24V10H9V14ZM9 21H24V17H9V21Z"/>
          </svg>
        </div>

        <!-- Project Info -->
        <div class="flex-1 min-w-0">
          <p class="font-medium text-gray-900 text-sm truncate group-hover:text-blue-600 transition-colors">{{ project.name }}</p>
          <div class="flex items-center gap-2 mt-0.5">
            <!-- Privacy icon -->
            <svg v-if="project.privacy === 'private'" class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <svg v-else class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>

        <!-- Arrow icon -->
        <svg class="w-4 h-4 text-gray-400 opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all duration-150 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
      </Link>
    </div>

    </div>

    <!-- Show More Button -->
    <button
      v-if="sortedProjects.length > 6"
      class="w-full text-left px-4 py-3 text-sm text-gray-600 hover:text-gray-900 font-medium cursor-pointer transition-colors mt-3"
    >
      {{ $t('show_more') }}
    </button>

    <!-- Empty State (only show when NO projects) -->
    <div v-if="sortedProjects.length === 0" class="text-center py-10">
      <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
        </svg>
      </div>
      <p class="text-gray-600 font-medium text-sm">{{ $t('no_projects_yet_desc') }}</p>
      <p class="text-gray-500 text-xs mt-1">{{ $t('create_first_project') }}</p>

    <!-- Color/Icon Editor Modal -->
    <div v-if="selectedProject" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-6">{{ $t('edit') }} {{ selectedProject.name }}</h3>

          <!-- Color Picker -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">{{ $t('color') }}</label>
            <div class="grid grid-cols-5 gap-3">
              <button
                v-for="color in colorPalette"
                :key="color"
                @click="editForm.color = color"
                :class="[
                  'w-10 h-10 rounded-xl transition-all duration-150 cursor-pointer',
                  editForm.color === color ? 'ring-2 ring-offset-2 ring-blue-500 scale-110' : 'hover:scale-105'
                ]"
                :style="{ backgroundColor: color }"
              />
            </div>
          </div>

          <!-- Icon Picker -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">{{ $t('icon') }}</label>
            <div class="grid grid-cols-4 gap-3">
              <!-- Default Icon Option -->
              <button
                @click="editForm.icon = null"
                :class="[
                  'w-12 h-12 rounded-xl transition-all duration-150 cursor-pointer flex items-center justify-center overflow-hidden',
                  !editForm.icon ? 'ring-2 ring-offset-2 ring-blue-500 scale-110' : 'hover:scale-105 hover:bg-gray-50'
                ]"
                :style="{ backgroundColor: !editForm.icon ? editForm.color : 'transparent' }"
                :title="$t('default')"
              >
                <svg :class="!editForm.icon ? 'w-6 h-6 text-white' : 'w-6 h-6 text-gray-400'" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M5 5C5 6.3805 3.8805 7.5 2.5 7.5C1.1195 7.5 0 6.3805 0 5C0 3.6195 1.1195 2.5 2.5 2.5C3.8805 2.5 5 3.6195 5 5ZM2.5 9.5C1.1195 9.5 0 10.6195 0 12C0 13.3805 1.1195 14.5 2.5 14.5C3.8805 14.5 5 13.3805 5 12C5 10.6195 3.8805 9.5 2.5 9.5ZM2.5 16.5C1.1195 16.5 0 17.6195 0 19C0 20.3805 1.1195 21.5 2.5 21.5C3.8805 21.5 5 20.3805 5 19C5 17.6195 3.8805 16.5 2.5 16.5ZM9 3V7H24V3H9ZM9 14H24V10H9V14ZM9 21H24V17H9V21Z"/>
                </svg>
              </button>
              <!-- Custom Icons -->
              <button
                v-for="icon in iconPalette"
                :key="icon.name"
                @click="editForm.icon = icon.icon"
                :class="[
                  'w-12 h-12 rounded-xl transition-all duration-150 cursor-pointer flex items-center justify-center',
                  editForm.icon === icon.icon ? 'ring-2 ring-offset-2 ring-blue-500 scale-110' : 'hover:scale-105 hover:bg-gray-50'
                ]"
                :style="{ backgroundColor: editForm.icon === icon.icon ? editForm.color : 'transparent' }"
              >
                <svg v-if="icon.svg" class="w-6 h-6" :class="editForm.icon === icon.icon ? 'text-white' : 'text-gray-600'" fill="currentColor" viewBox="0 0 24 24">
                  <path :d="icon.svg"/>
                </svg>
                <span v-else class="text-lg" :class="editForm.icon === icon.icon ? 'text-white' : ''">{{ icon.icon }}</span>
              </button>
            </div>
          </div>

          <!-- Preview -->
          <div class="mb-6 p-4 bg-gray-50 rounded-xl text-center">
            <p class="text-xs text-gray-600 mb-3 font-medium">{{ $t('preview') }}</p>
            <div
              class="w-16 h-16 rounded-xl flex items-center justify-center mx-auto shadow-lg overflow-hidden"
              :style="{ backgroundColor: editForm.color }"
            >
              <svg v-if="editForm.icon && iconPalette.find(i => i.icon === editForm.icon)?.svg" class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path :d="iconPalette.find(i => i.icon === editForm.icon)?.svg"/>
              </svg>
              <svg v-else class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M5 5C5 6.3805 3.8805 7.5 2.5 7.5C1.1195 7.5 0 6.3805 0 5C0 3.6195 1.1195 2.5 2.5 2.5C3.8805 2.5 5 3.6195 5 5ZM2.5 9.5C1.1195 9.5 0 10.6195 0 12C0 13.3805 1.1195 14.5 2.5 14.5C3.8805 14.5 5 13.3805 5 12C5 10.6195 3.8805 9.5 2.5 9.5ZM2.5 16.5C1.1195 16.5 0 17.6195 0 19C0 20.3805 1.1195 21.5 2.5 21.5C3.8805 21.5 5 20.3805 5 19C5 17.6195 3.8805 16.5 2.5 16.5ZM9 3V7H24V3H9ZM9 14H24V10H9V14ZM9 21H24V17H9V21Z"/>
              </svg>
            </div>
          </div>

          <!-- Buttons -->
          <div class="flex gap-3">
            <button
              @click="selectedProject = null"
              class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 text-sm font-medium cursor-pointer transition-colors"
            >
              {{ $t('cancel') }}
            </button>
            <button
              @click="updateProjectStyle"
              :disabled="isUpdating"
              class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium cursor-pointer transition-colors"
            >
              {{ isUpdating ? $t('saving') : $t('save') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  projects: {
    type: Array,
    default: () => [],
  },
})

const showMenu = ref(false)
const sortBy = ref('recent')
const selectedProject = ref(null)
const isUpdating = ref(false)
const editForm = ref({
  color: '#6366f1',
  icon: '',
})

const colorPalette = [
  '#6366f1', // indigo
  '#ec4899', // pink
  '#3b82f6', // blue
  '#10b981', // emerald
  '#f59e0b', // amber
  '#ef4444', // red
  '#8b5cf6', // violet
  '#06b6d4', // cyan
  '#14b8a6', // teal
  '#f97316', // orange
]

const iconPalette = [
  { 
    name: 'list', 
    icon: 'list',
    svg: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'
  },
  { 
    name: 'chart', 
    icon: 'chart',
    svg: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
  },
  { 
    name: 'lightbulb', 
    icon: 'lightbulb',
    svg: 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'
  },
  {
    name: 'briefcase',
    icon: 'briefcase', 
    svg: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'
  },
  {
    name: 'star',
    icon: 'star',
    svg: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'
  },
  {
    name: 'code',
    icon: 'code',
    svg: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'
  },
]

// SVG icons (new format)
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

// Helper function to get SVG icon
const getSvgIcon = (iconId) => {
  const found = SVG_ICONS.find(i => i.id === iconId)
  return found?.svg ?? ''
}

const sortedProjects = computed(() => {
  const projects = [...props.projects].filter(p => !p.archived_at)

  if (sortBy.value === 'name') {
    return projects.sort((a, b) => a.name.localeCompare(b.name))
  }
  return projects
})

const selectProject = (project) => {
  selectedProject.value = project
  editForm.value = {
    color: project.color || '#6366f1',
    icon: project.icon || '',
  }
}

const updateProjectStyle = async () => {
  if (!selectedProject.value) return

  isUpdating.value = true
  try {
    router.patch(`/projects/${selectedProject.value.id}`, {
      color: editForm.value.color,
      icon: editForm.value.icon,
    }, {
      onSuccess: () => {
        selectedProject.value = null
        isUpdating.value = false
      },
      onError: () => {
        alert('Failed to update project style')
        isUpdating.value = false
      },
    })
  } catch (error) {
    console.error('Error updating project:', error)
    isUpdating.value = false
  }
}
</script>
