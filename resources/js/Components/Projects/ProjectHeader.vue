<template>
  <div class="project-header border-b pb-6">
    <!-- Header with icon, name, and status -->
    <div class="flex justify-between items-start mb-6">
      <div class="flex items-start gap-4">
        <!-- Icon and color -->
        <div
          v-if="project.icon"
          class="text-4xl"
          :title="project.icon"
        >
          {{ project.icon }}
        </div>
        <div
          v-if="project.color"
          :style="{ backgroundColor: project.color }"
          class="w-12 h-12 rounded-lg border border-gray-300"
        />

        <!-- Title and description -->
        <div>
          <div class="flex items-center gap-3">
            <h1 class="text-3xl font-bold">{{ project.name }}</h1>
            <!-- Status badge -->
            <span
              v-if="project.status"
              :class="[
                'px-3 py-1 rounded-full text-sm font-medium',
                getStatusBadgeClass(project.status)
              ]"
            >
              {{ formatStatus(project.status) }}
            </span>
            <!-- Privacy icon -->
            <span
              v-if="project.privacy === 'private'"
              class="text-gray-600"
              title="Private project"
            >
              ðŸ”’
            </span>
          </div>
          <p class="text-gray-600 mt-2">{{ project.description }}</p>
          <p v-if="project.owner" class="text-sm text-gray-600 mt-1">
            Owner: <span class="font-medium">{{ project.owner.name }}</span>
          </p>
        </div>
      </div>

      <!-- Quick action buttons -->
      <div class="flex gap-2">
        <!-- Set Status button -->
        <button
          v-if="canEdit"
          @click="showStatusMenu = !showStatusMenu"
          class="px-4 py-2 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200 text-sm font-medium"
        >
          Set Status
        </button>

        <!-- Invite button -->
        <button
          v-if="canManageMembers"
          @click="showInviteDialog = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
        >
          Invite
        </button>

        <!-- Settings button -->
        <Link
          v-if="canEdit"
          :href="`/projects/${project.id}/settings`"
          class="px-4 py-2 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200 text-sm font-medium"
        >
          Settings
        </Link>

        <!-- More menu -->
        <div class="relative">
          <button
            @click="showMoreMenu = !showMoreMenu"
            class="px-4 py-2 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200 text-sm font-medium"
          >
            â‹¯
          </button>

          <!-- Dropdown menu -->
          <div
            v-if="showMoreMenu"
            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-10 border border-gray-200"
          >
            <Link
              v-if="canEdit"
              :href="`/projects/${project.id}/settings`"
              class="block px-4 py-2 text-gray-700 hover:bg-gray-100 text-sm"
            >
              Settings
            </Link>
            <button
              v-if="canEdit"
              @click="archiveProject"
              class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 text-sm"
            >
              {{ project.archived_at ? 'Unarchive' : 'Archive' }}
            </button>
            <button
              v-if="canEdit"
              @click="duplicateProject"
              class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 text-sm"
            >
              Duplicate
            </button>
            <button
              @click="leaveProject"
              class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 text-sm"
            >
              Leave
            </button>
            <button
              v-if="canEdit"
              @click="deleteProject"
              class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 text-sm border-t border-gray-200"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Status menu -->
    <div v-if="showStatusMenu" class="mb-4 p-3 bg-gray-50 rounded-lg">
      <p class="text-sm font-medium text-gray-700 mb-2">Change Status</p>
      <div class="flex gap-2 flex-wrap">
        <button
          v-for="status in statuses"
          :key="status"
          @click="updateStatus(status)"
          :class="[
            'px-3 py-1 rounded text-sm font-medium',
            project.status === status
              ? 'bg-blue-600 text-white'
              : 'bg-white border border-gray-300 text-gray-700 hover:border-gray-400'
          ]"
        >
          {{ formatStatus(status) }}
        </button>
      </div>
    </div>

    <!-- Project info grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
      <div>
        <label class="text-xs text-gray-600 uppercase">Status</label>
        <p class="font-semibold text-gray-900">{{ formatStatus(project.status) }}</p>
      </div>
      <div>
        <label class="text-xs text-gray-600 uppercase">Privacy</label>
        <p class="font-semibold text-gray-900">{{ formatPrivacy(project.privacy) }}</p>
      </div>
      <div>
        <label class="text-xs text-gray-600 uppercase">Lead</label>
        <p class="font-semibold text-gray-900">{{ project.manager?.name }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  project: Object,
  canEdit: Boolean,
  canManageMembers: Boolean,
})

const showStatusMenu = ref(false)
const showMoreMenu = ref(false)
const showInviteDialog = ref(false)

const statuses = ['on_track', 'at_risk', 'off_track', 'on_hold', 'complete']

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

const formatPrivacy = (privacy) => {
  const privacyMap = {
    public_to_team: 'Public to Team',
    private: 'Private',
    specific_members: 'Specific Members',
  }
  return privacyMap[privacy] || privacy
}

const getStatusBadgeClass = (status) => {
  const classes = {
    on_track: 'bg-green-100 text-green-800',
    at_risk: 'bg-yellow-100 text-yellow-800',
    off_track: 'bg-red-100 text-red-800',
    on_hold: 'bg-gray-100 text-gray-800',
    complete: 'bg-blue-100 text-blue-800',
    archived: 'bg-gray-100 text-gray-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const updateStatus = (status) => {
  router.put(`/projects/${props.project.id}/settings/general`, { status })
  showStatusMenu.value = false
}

const archiveProject = () => {
  if (confirm(`Are you sure you want to ${props.project.archived_at ? 'unarchive' : 'archive'} this project?`)) {
    const url = props.project.archived_at
      ? `/projects/${props.project.id}/unarchive`
      : `/projects/${props.project.id}/archive`
    router.post(url)
  }
  showMoreMenu.value = false
}

const duplicateProject = () => {
  router.post(`/projects/${props.project.id}/duplicate`, {
    copy_tasks: confirm('Copy tasks?'),
    copy_members: confirm('Copy members?'),
  })
  showMoreMenu.value = false
}

const leaveProject = () => {
  if (confirm('Are you sure you want to leave this project?')) {
    router.delete(`/projects/${props.project.id}/members/me`)
  }
  showMoreMenu.value = false
}

const deleteProject = () => {
  const projectName = prompt('Type the project name to confirm deletion:')
  if (projectName === props.project.name) {
    router.post(`/projects/${props.project.id}/delete`, { project_name: projectName })
  } else if (projectName !== null) {
    alert('Project name does not match')
  }
  showMoreMenu.value = false
}
</script>

