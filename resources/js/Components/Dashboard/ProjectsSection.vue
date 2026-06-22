<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-full">
    <div class="flex items-center justify-between mb-5">
      <div class="flex items-center gap-2">
        <h2 class="text-base font-semibold text-gray-900">Projects</h2>
        <button
          @click="showMenu = !showMenu"
          class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-md transition-colors cursor-pointer"
          title="Sort"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
          </svg>
        </button>
        <div v-if="showMenu" class="absolute right-8 top-32 bg-white border border-gray-200 rounded-xl shadow-lg z-10 min-w-[160px] overflow-hidden">
          <button
            @click="sortBy = 'recent'; showMenu = false"
            :class="['w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 cursor-pointer transition-colors', sortBy === 'recent' ? 'text-blue-600 font-medium' : 'text-gray-700']"
          >
            Recents
          </button>
          <button
            @click="sortBy = 'name'; showMenu = false"
            :class="['w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 border-t border-gray-100 cursor-pointer transition-colors', sortBy === 'name' ? 'text-blue-600 font-medium' : 'text-gray-700']"
          >
            A-Z
          </button>
        </div>
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
        <span class="text-sm font-medium text-gray-600 group-hover:text-blue-600">Create project</span>
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
          <!-- Custom Icon (SVG paths from iconPalette) -->
          <svg v-if="project.icon && iconPalette.find(i => i.icon === project.icon)" class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
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
      Show more
    </button>

    <!-- Empty State (only show when NO projects) -->
    <div v-if="sortedProjects.length === 0" class="text-center py-10">
      <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
        </svg>
      </div>
      <p class="text-gray-600 font-medium text-sm">No projects yet</p>
      <p class="text-gray-500 text-xs mt-1">Create your first project to get started</p>

    <!-- Color/Icon Editor Modal -->
    <div v-if="selectedProject" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-6">Edit {{ selectedProject.name }}</h3>

          <!-- Color Picker -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">Color</label>
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
            <label class="block text-sm font-medium text-gray-700 mb-3">Icon</label>
            <div class="grid grid-cols-4 gap-3">
              <!-- Default Icon Option -->
              <button
                @click="editForm.icon = null"
                :class="[
                  'w-12 h-12 rounded-xl transition-all duration-150 cursor-pointer flex items-center justify-center overflow-hidden',
                  !editForm.icon ? 'ring-2 ring-offset-2 ring-blue-500 scale-110' : 'hover:scale-105 hover:bg-gray-50'
                ]"
                :style="{ backgroundColor: !editForm.icon ? editForm.color : 'transparent' }"
                title="Default"
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
            <p class="text-xs text-gray-600 mb-3 font-medium">Preview</p>
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
              Cancel
            </button>
            <button
              @click="updateProjectStyle"
              :disabled="isUpdating"
              class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium cursor-pointer transition-colors"
            >
              {{ isUpdating ? 'Saving...' : 'Save' }}
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
