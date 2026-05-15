<template>
  <AppLayout :user-workspaces="userWorkspaces" :current-workspace="workspace" :user-role="userRole">
    <!-- Notification Toast -->
    <div
      v-if="notification.show"
      :class="[
        'fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white text-sm font-medium z-50 transition-opacity',
        notification.type === 'success' ? 'bg-green-500' : 'bg-red-500'
      ]"
    >
      {{ notification.message }}
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Workspace Header -->
      <div class="mb-8">
        <div class="flex items-center gap-4 mb-6">
          <div
            :style="{ backgroundColor: workspace.avatar_color }"
            class="w-16 h-16 rounded-lg flex items-center justify-center text-white text-2xl font-bold"
          >
            {{ workspace.name.charAt(0).toUpperCase() }}
          </div>
          <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900">{{ workspace.name }}</h1>
            <p v-if="workspace.description" class="text-gray-600 mt-1">{{ workspace.description }}</p>
          </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200">
          <nav class="flex gap-8" aria-label="Tabs">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              {{ tab.label }}
            </button>
          </nav>
        </div>
      </div>

      <!-- Tab Content -->
      <div>
        <!-- Overview Tab -->
        <div v-if="activeTab === 'overview'" class="space-y-8">
          <!-- Getting Started Card -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Finish setting up your workspace</h2>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">{{ setupProgress.completed }} of {{ setupProgress.total }} steps completed</span>
                <span class="text-sm font-medium text-gray-900">{{ setupProgress.percentage }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-blue-600 h-2 rounded-full transition-all"
                  :style="{ width: setupProgress.percentage + '%' }"
                />
              </div>
              <div class="space-y-2 mt-6">
                <button
                  v-for="step in setupProgress.steps"
                  :key="step.id"
                  @click="handleSetupStep(step.id)"
                  :class="[
                    'w-full flex items-center gap-3 p-3 rounded-lg border transition-colors',
                    step.completed
                      ? 'bg-green-50 border-green-200'
                      : 'bg-gray-50 border-gray-200 hover:border-blue-300'
                  ]"
                >
                  <div
                    :class="[
                      'w-5 h-5 rounded-full flex items-center justify-center text-white text-xs font-bold',
                      step.completed ? 'bg-green-500' : 'bg-gray-300'
                    ]"
                  >
                    {{ step.completed ? '✓' : '○' }}
                  </div>
                  <span :class="step.completed ? 'text-green-700 line-through' : 'text-gray-700'">
                    {{ step.title }}
                  </span>
                </button>
              </div>
            </div>
          </div>

          <!-- Projects Section -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-semibold text-gray-900">Projects</h2>
              <Link href="/projects/create" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                + New Project
              </Link>
            </div>
            <div v-if="projects.length > 0" class="space-y-3">
              <div v-for="project in projects" :key="project.id" class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                <div>
                  <p class="font-medium text-gray-900">{{ project.name }}</p>
                  <p class="text-sm text-gray-600">{{ project.members?.length || 0 }} members</p>
                </div>
                <Link :href="`/projects/${project.id}`" class="text-blue-600 hover:text-blue-700">
                  View →
                </Link>
              </div>
            </div>
            <div v-else class="text-center py-8">
              <p class="text-gray-600">No projects yet. Create one to get started!</p>
            </div>
          </div>

          <!-- Workspace Members Section -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-semibold text-gray-900">
                Workspace Members ({{ members.length }}<template v-if="pendingInvitations.length > 0"> + {{ pendingInvitations.length }} pending</template>)
              </h2>
              <button
                v-if="canManageMembers()"
                @click="showInviteModal = true"
                class="text-blue-600 hover:text-blue-700 text-sm font-medium"
              >
                + Invite
              </button>
            </div>
            <div class="space-y-3">
              <!-- Active members -->
              <div v-for="member in members" :key="member.id" class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-white font-bold">
                    {{ member.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">{{ member.name }}</p>
                    <p class="text-sm text-gray-500">{{ member.email }}</p>
                  </div>
                </div>
                <span class="text-xs font-medium px-2 py-1 bg-blue-100 text-blue-700 rounded">
                  {{ member.organization_memberships?.[0]?.role || 'member' }}
                </span>
              </div>
              <!-- Pending invitations -->
              <div v-for="inv in pendingInvitations" :key="inv.id" class="flex items-center justify-between p-3 border border-dashed border-gray-200 rounded-lg opacity-75">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 font-bold">
                    ?
                  </div>
                  <p class="text-sm text-gray-600">{{ inv.email }}</p>
                </div>
                <span :class="['text-xs font-medium px-2 py-1 rounded', isExpired(inv) ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-700']">
                  {{ isExpired(inv) ? 'Expired' : 'Pending' }}
                </span>
              </div>
              <div v-if="members.length === 0 && pendingInvitations.length === 0" class="text-center py-8">
                <p class="text-gray-600">No workspace members yet. Invite someone to collaborate!</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Members Tab -->
        <div v-if="activeTab === 'members'" class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Workspace Members</h2>
            <button
              v-if="canManageMembers()"
              @click="showInviteModal = true"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
            >
              + Invite Member
            </button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Role</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <!-- Active Members -->
                <tr v-for="member in members" :key="`member-${member.id}`" class="hover:bg-gray-50">
                  <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ member.name }}</td>
                  <td class="px-6 py-4 text-sm text-gray-600">{{ member.email }}</td>
                  <td class="px-6 py-4 text-sm">
                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-medium">
                      {{ member.organization_memberships?.[0]?.role || 'member' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <span :class="[
                      'px-2 py-1 rounded text-xs font-medium',
                      member.organization_memberships?.[0]?.is_active !== false
                        ? 'bg-green-100 text-green-700'
                        : 'bg-gray-100 text-gray-700'
                    ]">
                      {{ member.organization_memberships?.[0]?.is_active !== false ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600">
                    {{ formatDate(member.organization_memberships?.[0]?.joined_at) }}
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <div class="flex gap-2">
                      <button
                        v-if="canManageMembers() && member.organization_memberships?.[0]?.role !== 'owner' && member.organization_memberships?.[0]?.is_active !== false"
                        @click="deactivateMember(member.id, member.organization_memberships?.[0]?.id)"
                        class="text-red-600 hover:text-red-700 text-xs font-medium"
                      >
                        Deactivate
                      </button>
                      <button
                        v-if="canManageMembers() && member.organization_memberships?.[0]?.role !== 'owner' && member.organization_memberships?.[0]?.is_active === false"
                        @click="activateMember(member.id, member.organization_memberships?.[0]?.id)"
                        class="text-green-600 hover:text-green-700 text-xs font-medium"
                      >
                        Activate
                      </button>
                    </div>
                  </td>
                </tr>
                
                <!-- Pending Invitations -->
                <tr v-for="invitation in pendingInvitations" :key="`invitation-${invitation.id}`" class="hover:bg-gray-50">
                  <td class="px-6 py-4 text-sm font-medium text-gray-400 italic">Invited</td>
                  <td class="px-6 py-4 text-sm text-gray-600">{{ invitation.email }}</td>
                  <td class="px-6 py-4 text-sm">
                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-medium">
                      {{ invitation.role }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <span :class="['px-2 py-1 rounded text-xs font-medium', isExpired(invitation) ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-700']">
                      {{ isExpired(invitation) ? 'Expired' : 'Pending' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600">
                    {{ formatDate(invitation.created_at) }}
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <div class="flex gap-2">
                      <button
                        v-if="canManageMembers() && !isExpired(invitation)"
                        @click="expireInvitation(invitation.id)"
                        class="text-red-600 hover:text-red-700 text-xs font-medium"
                      >
                        Expire
                      </button>
                      <button
                        v-if="canManageMembers()"
                        @click="deleteInvitation(invitation.id)"
                        class="text-gray-500 hover:text-gray-700 text-xs font-medium"
                      >
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="members.length === 0 && pendingInvitations.length === 0" class="text-center py-8">
            <p class="text-gray-600">No workspace members yet. Invite someone to collaborate!</p>
          </div>
        </div>

        <!-- Work Tab -->
        <div v-if="activeTab === 'work'" class="bg-white rounded-lg shadow p-6">
          <p class="text-gray-600">Work view coming soon...</p>
        </div>

        <!-- Messages Tab -->
        <div v-if="activeTab === 'messages'" class="bg-white rounded-lg shadow p-6">
          <p class="text-gray-600">Messages coming soon...</p>
        </div>

        <!-- Calendar Tab -->
        <div v-if="activeTab === 'calendar'" class="bg-white rounded-lg shadow p-6">
          <p class="text-gray-600">Calendar coming soon...</p>
        </div>

        <!-- Knowledge Tab -->
        <div v-if="activeTab === 'knowledge'" class="bg-white rounded-lg shadow p-6">
          <p class="text-gray-600">Knowledge base coming soon...</p>
        </div>

        <!-- Settings Tab -->
        <div v-if="activeTab === 'settings'" class="space-y-6">
          <!-- Danger Zone -->
          <div class="bg-white rounded-lg shadow p-6 border border-red-200">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Danger Zone</h2>
            <p class="text-sm text-gray-500 mb-6">Actions here are permanent and cannot be undone.</p>

            <div class="flex items-center justify-between p-4 border border-red-200 rounded-lg bg-red-50">
              <div>
                <p class="font-medium text-gray-900">Delete this workspace</p>
                <p class="text-sm text-gray-500 mt-0.5">Permanently deletes the workspace, all projects, tasks, and members. This cannot be undone.</p>
              </div>
              <button
                @click="deleteStep = 1"
                class="ml-6 flex-shrink-0 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors"
              >
                Delete Workspace
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Workspace Modal — Step 1: Warning -->
    <div v-if="deleteStep === 1" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
        <div class="p-6">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Delete workspace?</h3>
          </div>
          <p class="text-sm text-gray-600 mb-2">You are about to permanently delete <strong>{{ workspace.name }}</strong>. This will delete:</p>
          <ul class="text-sm text-gray-600 list-disc list-inside space-y-1 mb-6 ml-1">
            <li>All projects and tasks inside this workspace</li>
            <li>All members and their access</li>
            <li>All comments, attachments, and activity</li>
          </ul>
          <p class="text-sm font-medium text-red-600 mb-6">This action is permanent and cannot be undone.</p>
          <div class="flex gap-3">
            <button
              @click="deleteStep = 0"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium"
            >
              Cancel
            </button>
            <button
              @click="deleteStep = 2"
              class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium"
            >
              Yes, continue
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Workspace Modal — Step 2: Type name to confirm -->
    <div v-if="deleteStep === 2" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Confirm deletion</h3>
          <p class="text-sm text-gray-600 mb-4">
            To confirm, type <strong class="text-gray-900 select-all">{{ workspace.name }}</strong> in the box below.
          </p>
          <input
            v-model="deleteConfirmName"
            type="text"
            placeholder="Type workspace name"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm mb-2"
            @keyup.enter="confirmDelete"
          />
          <p v-if="deleteError" class="text-sm text-red-600 mb-3">{{ deleteError }}</p>
          <div class="flex gap-3 mt-4">
            <button
              @click="deleteStep = 0; deleteConfirmName = ''; deleteError = ''"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium"
            >
              Cancel
            </button>
            <button
              @click="confirmDelete"
              :disabled="isDeletingWorkspace || deleteConfirmName.trim().toLowerCase() !== workspace.name.trim().toLowerCase()"
              class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed text-sm font-medium transition-colors"
            >
              {{ isDeletingWorkspace ? 'Deleting...' : 'Delete forever' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Invite Modal -->
    <InviteModal
      v-if="showInviteModal"
      :workspace="workspace"
      @close="showInviteModal = false"
      @invited="handleInviteSent"
      @error="handleInviteError"
    />
  </AppLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import InviteModal from '@/Components/Workspace/InviteModal.vue'

const props = defineProps({
  workspace: Object,
  userRole: String,
  userWorkspaces: Array,
  projects: Array,
  members: Array,
  invitations: Array,
  setupProgress: Object,
  canEdit: Boolean,
  canInvite: Boolean,
})

const activeTab = ref('overview')
const showInviteModal = ref(false)
const notification = reactive({ show: false, message: '', type: 'success' })
const pendingInvitations = ref(props.invitations || [])

// Delete workspace state
const deleteStep = ref(0)          // 0 = hidden, 1 = warning, 2 = confirm name
const deleteConfirmName = ref('')
const deleteError = ref('')
const isDeletingWorkspace = ref(false)

const confirmDelete = () => {
  if (deleteConfirmName.value.trim().toLowerCase() !== props.workspace.name.trim().toLowerCase()) {
    deleteError.value = 'Workspace name does not match. Please try again.'
    return
  }
  isDeletingWorkspace.value = true
  deleteError.value = ''
  router.delete(`/workspace/${props.workspace.id}`, {
    data: { workspace_name: deleteConfirmName.value },
    onError: (errors) => {
      deleteError.value = errors.workspace_name || 'Something went wrong. Please try again.'
      isDeletingWorkspace.value = false
    },
    onFinish: () => {
      isDeletingWorkspace.value = false
    },
  })
}

const tabs = [
  { id: 'overview', label: 'Overview' },
  { id: 'members', label: 'Members' },
  { id: 'work', label: 'Work' },
  { id: 'messages', label: 'Messages' },
  { id: 'calendar', label: 'Calendar' },
  { id: 'knowledge', label: 'Knowledge' },
  ...(props.userRole === 'owner' ? [{ id: 'settings', label: 'Settings' }] : []),
]

const showNotification = (message, type = 'success') => {
  notification.message = message
  notification.type = type
  notification.show = true
  setTimeout(() => {
    notification.show = false
  }, 4000)
}

const handleSetupStep = (stepId) => {
  if (stepId === 'description') {
    // Open edit description modal
  } else if (stepId === 'project') {
    window.location.href = '/projects/create'
  } else if (stepId === 'teammates') {
    showInviteModal.value = true
  }
}

const handleInviteSent = () => {
  showInviteModal.value = false
  showNotification('Invitation sent successfully!')
  router.reload({ only: ['invitations', 'members'] })
}

const handleInviteError = (errorMessage) => {
  showNotification(errorMessage, 'error')
}

const canManageMembers = () => {
  return props.userRole === 'owner' || props.userRole === 'admin'
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString()
}

const isExpired = (invitation) => {
  if (!invitation.expires_at) return false
  return new Date(invitation.expires_at) < new Date()
}

const deactivateMember = async (userId, membershipId) => {
  try {
    const response = await fetch(`/api/organization-memberships/${membershipId}/deactivate`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content },
    })
    if (response.ok) {
      showNotification('Member deactivated successfully')
      router.reload({ only: ['members'] })
    } else {
      showNotification('Failed to deactivate member', 'error')
    }
  } catch {
    showNotification('Error deactivating member', 'error')
  }
}

const activateMember = async (userId, membershipId) => {
  try {
    const response = await fetch(`/api/organization-memberships/${membershipId}/activate`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content },
    })
    if (response.ok) {
      showNotification('Member activated successfully')
      router.reload({ only: ['members'] })
    } else {
      showNotification('Failed to activate member', 'error')
    }
  } catch {
    showNotification('Error activating member', 'error')
  }
}

const expireInvitation = async (invitationId) => {
  try {
    const response = await fetch(`/api/invitations/${invitationId}/expire`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content },
    })
    if (response.ok) {
      showNotification('Invitation expired')
      router.reload({ only: ['invitations'] })
    } else {
      showNotification('Failed to expire invitation', 'error')
    }
  } catch {
    showNotification('Error expiring invitation', 'error')
  }
}

const deleteInvitation = async (invitationId) => {
  if (!confirm('Delete this invitation?')) return
  try {
    const response = await fetch(`/api/invitations/${invitationId}`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content },
    })
    if (response.ok) {
      showNotification('Invitation deleted')
      router.reload({ only: ['invitations'] })
    } else {
      showNotification('Failed to delete invitation', 'error')
    }
  } catch {
    showNotification('Error deleting invitation', 'error')
  }
}

// Keep pendingInvitations in sync with props after Inertia reloads
watch(() => props.invitations, (val) => { pendingInvitations.value = val || [] })
</script>
