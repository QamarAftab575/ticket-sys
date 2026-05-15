<template>
  <div class="project-sidebar">
    <!-- View archived toggle -->
    <div class="mb-4 flex items-center justify-between">
      <h3 class="font-semibold text-gray-900">Projects</h3>
      <button
        @click="showArchived = !showArchived"
        class="text-xs text-blue-600 hover:text-blue-700"
      >
        {{ showArchived ? 'Hide Archived' : 'View Archived' }}
      </button>
    </div>

    <!-- Projects list -->
    <div class="space-y-1">
      <div
        v-for="project in filteredProjects"
        :key="project.id"
        class="group relative"
        @contextmenu.prevent="showContextMenu($event, project)"
      >
        <Link
          :href="`/projects/${project.id}`"
          class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
          :class="[
            isActive(project.id) ? 'bg-blue-50 text-blue-600' : ''
          ]"
        >
          <!-- Project icon/color square -->
          <span
            class="w-4 h-4 rounded flex-shrink-0 flex items-center justify-center text-white text-[10px] font-bold"
            :style="{ backgroundColor: project.color || '#6366f1' }"
          >
            <template v-if="project.icon && !project.icon.startsWith('svg:')">{{ project.icon }}</template>
            <template v-else-if="!project.icon">{{ (project.name || '?').charAt(0).toUpperCase() }}</template>
          </span>

          <!-- Project name -->
          <span class="flex-1 truncate text-sm">{{ project.name }}</span>

          <!-- Privacy icon -->
          <span
            v-if="project.privacy === 'private'"
            class="text-xs flex-shrink-0"
            title="Private"
          >
            🔒
          </span>

          <!-- Archived badge -->
          <span
            v-if="project.archived_at"
            class="text-xs bg-gray-200 text-gray-700 px-2 py-0.5 rounded flex-shrink-0"
          >
            Archived
          </span>

          <!-- Context menu button -->
          <button
            @click.prevent="showContextMenu($event, project)"
            class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-gray-600 flex-shrink-0"
          >
            ⋯
          </button>
        </Link>

        <!-- Context menu -->
        <div
          v-if="contextMenuProject?.id === project.id && showContextMenuDropdown"
          class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg z-20 border border-gray-200"
        >
          <Link
            :href="`/projects/${project.id}`"
            class="block px-4 py-2 text-gray-700 hover:bg-gray-100 text-sm"
          >
            Open
          </Link>
          <button
            v-if="canEdit(project)"
            @click="archiveProject(project)"
            class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 text-sm"
          >
            {{ project.archived_at ? 'Unarchive' : 'Archive' }}
          </button>
          <button
            v-if="canEdit(project)"
            @click="duplicateProject(project)"
            class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 text-sm"
          >
            Duplicate
          </button>
          <Link
            v-if="canEdit(project)"
            :href="`/projects/${project.id}/settings`"
            class="block px-4 py-2 text-gray-700 hover:bg-gray-100 text-sm"
          >
            Settings
          </Link>
          <button
            @click="leaveProject(project)"
            class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 text-sm"
          >
            Leave
          </button>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="filteredProjects.length === 0" class="text-center py-4">
        <p class="text-sm text-gray-600">
          {{ showArchived ? 'No archived projects' : 'No projects' }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  projects: Array,
  currentProjectId: String,
})

const page = usePage()
const showArchived = ref(false)
const showContextMenuDropdown = ref(false)
const contextMenuProject = ref(null)

const filteredProjects = computed(() => {
  if (!props.projects) return []
  return props.projects.filter(p => {
    if (showArchived.value) {
      return p.archived_at !== null
    }
    return p.archived_at === null
  })
})

const isActive = (projectId) => {
  return props.currentProjectId === projectId
}

const canEdit = (project) => {
  const user = page.props.auth.user
  return project.owner_id === user.id || user.is_admin
}

const showContextMenu = (event, project) => {
  contextMenuProject.value = project
  showContextMenuDropdown.value = true
}

const archiveProject = (project) => {
  if (confirm(`Are you sure you want to ${project.archived_at ? 'unarchive' : 'archive'} this project?`)) {
    const url = project.archived_at
      ? `/projects/${project.id}/unarchive`
      : `/projects/${project.id}/archive`
    router.post(url)
  }
  showContextMenuDropdown.value = false
}

const duplicateProject = (project) => {
  router.post(`/projects/${project.id}/duplicate`, {
    copy_tasks: confirm('Copy tasks?'),
    copy_members: confirm('Copy members?'),
  })
  showContextMenuDropdown.value = false
}

const leaveProject = (project) => {
  if (confirm('Are you sure you want to leave this project?')) {
    router.delete(`/projects/${project.id}/members/me`)
  }
  showContextMenuDropdown.value = false
}
</script>
