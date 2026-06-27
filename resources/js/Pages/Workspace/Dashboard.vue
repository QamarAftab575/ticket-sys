<template>
  <AppLayout :user-workspaces="userWorkspaces" :current-workspace="workspace" :user-role="userRole">
    <!-- Notification Toast -->
    <div
      v-if="notification.show"
      :class="[
        'fixed top-4 right-4 px-6 py-3 rounded-xl shadow-lg text-white text-sm font-medium z-50 transition-opacity',
        notification.type === 'success' ? 'bg-green-500' : 'bg-red-500'
      ]"
    >
      {{ notification.message }}
    </div>

    <div class="min-h-screen bg-gray-50/50 py-8">
      <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <!-- Workspace Header -->
        <div class="mb-8">
          <div class="flex items-center gap-4">
            <div
              :style="{ backgroundColor: workspace.avatar_color }"
              class="w-14 h-14 rounded-xl flex items-center justify-center text-white text-xl font-bold shadow-sm"
            >
              {{ workspace.name.charAt(0).toUpperCase() }}
            </div>
            <div class="flex-1">
              <h1 class="text-2xl font-bold text-gray-900">{{ workspace.name }}</h1>
              <p v-if="workspace.description" class="text-gray-600 text-sm mt-0.5">{{ workspace.description }}</p>
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="mb-8">
          <nav class="flex gap-6 border-b border-gray-200" aria-label="Tabs">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'py-3 px-1 border-b-2 font-medium text-sm transition-colors cursor-pointer',
                activeTab === tab.id
                  ? 'border-blue-600 text-blue-600'
                  : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'
              ]"
            >
              {{ $t(tab.labelKey) }}
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div>
          <!-- Overview Tab -->
          <div v-if="activeTab === 'overview'" class="space-y-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
              <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm text-gray-500">{{ $t('projects') }}</p>
                <p class="mt-3 text-3xl font-semibold text-gray-900">{{ projects.length }}</p>
                <p class="text-sm text-gray-500 mt-2">{{ $t('workspace_projects_subtitle') }}</p>
              </div>
              <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm text-gray-500">{{ $t('members') }}</p>
                <p class="mt-3 text-3xl font-semibold text-gray-900">{{ members.length }}</p>
                <p class="text-sm text-gray-500 mt-2">{{ $t('active_collaborators') }}</p>
              </div>
              <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm text-gray-500">{{ $t('pending_invites') }}</p>
                <p class="mt-3 text-3xl font-semibold text-gray-900">{{ pendingInvitations.length }}</p>
                <p class="text-sm text-gray-500 mt-2">{{ $t('awaiting_acceptance') }}</p>
              </div>
              <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm text-gray-500">{{ $t('setup_progress') }}</p>
                <p class="mt-3 text-3xl font-semibold text-gray-900">{{ setupProgress.percentage }}%</p>
                <div class="mt-4 w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                  <div class="h-2.5 bg-blue-600 transition-all duration-300" :style="{ width: setupProgress.percentage + '%' }"></div>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-[1.9fr_1fr] gap-6 items-start">
              <div class="space-y-6">
                <div v-if="setupProgress.percentage < 100" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                  <div class="flex items-center justify-between gap-4 mb-5">
                    <div>
                      <h2 class="text-base font-semibold text-gray-900">{{ $t('finish_setting_up') }}</h2>
                      <p class="text-sm text-gray-500 mt-1">{{ $t('setup_steps_description') }}</p>
                    </div>
                    <span class="text-sm font-semibold text-blue-600">{{ setupProgress.percentage }}%</span>
                  </div>
                  <div class="space-y-4">
                    <div class="flex items-center justify-between text-xs text-gray-600">
                      <span>{{ setupProgress.completed }} {{ $t('of') }} {{ setupProgress.total }} {{ $t('completed') }}</span>
                      <span class="font-medium text-gray-900">{{ setupProgress.percentage }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                      <div
                        class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                        :style="{ width: setupProgress.percentage + '%' }"
                      />
                    </div>
                    <div class="grid gap-3">
                      <button
                        v-for="step in setupProgress.steps"
                        :key="step.id"
                        @click="handleSetupStep(step.id)"
                        :class="[
                          'w-full flex items-center gap-3 p-3 rounded-xl border transition-all duration-150 cursor-pointer',
                          step.completed
                            ? 'bg-green-50 border-green-200 opacity-70'
                            : 'bg-white border-gray-200 hover:border-blue-300 hover:shadow-sm'
                        ]"
                      >
                        <div
                          :class="[
                            'w-5 h-5 rounded-full flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0',
                            step.completed ? 'bg-green-500' : 'bg-gray-300'
                          ]"
                        >
                          <svg v-if="step.completed" class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                          </svg>
                        </div>
                        <span :class="['text-sm', step.completed ? 'text-green-700 line-through' : 'text-gray-700 font-medium']">
                          {{ step.title }}
                        </span>
                      </button>
                    </div>
                  </div>
                </div>

                <ProjectsSection :projects="projects" :can-edit="canEdit" />
              </div>

              <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                  <div class="flex items-center justify-between mb-5">
                    <div>
                      <h2 class="text-base font-semibold text-gray-900">{{ $t('members') }}</h2>
                      <p class="text-sm text-gray-500 mt-1">{{ $t('members_section_description') }}</p>
                    </div>
                    <button
                      v-if="canManageMembers()"
                      @click="showInviteModal = true"
                      class="text-blue-600 hover:text-blue-700 text-xs font-medium cursor-pointer transition-colors"
                    >
                      + {{ $t('invite') }}
                    </button>
                  </div>
                  <div class="space-y-3">
                    <div v-for="member in members.slice(0, 5)" :key="member.id" class="flex items-center justify-between p-3 border border-gray-100 rounded-xl hover:border-gray-200 hover:shadow-sm transition-all duration-150">
                      <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm">
                          {{ member.name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                          <p class="font-medium text-gray-900 text-sm">{{ member.name }}</p>
                          <p class="text-xs text-gray-500">{{ member.email }}</p>
                        </div>
                      </div>
                      <span class="text-xs font-medium px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full">
                        {{ member.organization_memberships?.[0]?.role || 'member' }}
                      </span>
                    </div>
                    <div v-for="inv in pendingInvitations.slice(0, 2)" :key="inv.id" class="flex items-center justify-between p-3 border border-dashed border-gray-200 rounded-xl bg-gray-50">
                      <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 font-semibold text-sm">
                          ?
                        </div>
                        <p class="text-sm text-gray-600">{{ inv.email }}</p>
                      </div>
                      <span :class="['text-xs font-medium px-2.5 py-1 rounded-full', isExpired(inv) ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-700']">
                        {{ isExpired(inv) ? 'Expired' : 'Pending' }}
                      </span>
                    </div>
                    <button
                      v-if="members.length > 5 || pendingInvitations.length > 2"
                      @click="activeTab = 'members'"
                      class="w-full text-center py-2.5 text-xs text-blue-600 hover:text-blue-700 font-medium rounded-xl border border-gray-200 transition-colors"
                    >
                      {{ $t('view_all_members') }}
                    </button>
                  </div>
                </div>

                <PrivateNotesSection v-if="isOwner" />
              </div>
            </div>
          </div>

          <!-- Members Tab -->
          <div v-if="activeTab === 'members'" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-lg font-semibold text-gray-900">{{ $t('workspace_members') }}</h2>
              <button
                v-if="canManageMembers()"
                @click="showInviteModal = true"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium cursor-pointer transition-colors"
              >
                {{ $t('invite_member') }}
              </button>
            </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">{{ $t('name') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">{{ $t('email') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">{{ $t('role') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">{{ $t('status') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">{{ $t('date') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">{{ $t('actions') }}</th>
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
                      {{ member.organization_memberships?.[0]?.is_active !== false ? $t('active') : $t('inactive') }}
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
                        class="text-red-600 hover:text-red-700 text-xs font-medium cursor-pointer"
                      >
                        {{ $t('deactivate') }}
                      </button>
                      <button
                        v-if="canManageMembers() && member.organization_memberships?.[0]?.role !== 'owner' && member.organization_memberships?.[0]?.is_active === false"
                        @click="activateMember(member.id, member.organization_memberships?.[0]?.id)"
                        class="text-green-600 hover:text-green-700 text-xs font-medium cursor-pointer"
                      >
                        {{ $t('activate') }}
                      </button>
                    </div>
                  </td>
                </tr>
                
                <!-- Pending Invitations -->
                <tr v-for="invitation in pendingInvitations" :key="`invitation-${invitation.id}`" class="hover:bg-gray-50">
                  <td class="px-6 py-4 text-sm font-medium text-gray-400 italic">{{ $t('invited_status') }}</td>
                  <td class="px-6 py-4 text-sm text-gray-600">{{ invitation.email }}</td>
                  <td class="px-6 py-4 text-sm">
                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-medium">
                      {{ invitation.role }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <span :class="['px-2 py-1 rounded text-xs font-medium', isExpired(invitation) ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-700']">
                      {{ isExpired(invitation) ? $t('expired') : $t('pending_status') }}
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
                        class="text-red-600 hover:text-red-700 text-xs font-medium cursor-pointer"
                      >
                        {{ $t('expire') }}
                      </button>
                      <button
                        v-if="canManageMembers()"
                        @click="deleteInvitation(invitation.id)"
                        class="text-gray-500 hover:text-gray-700 text-xs font-medium cursor-pointer"
                      >
                        {{ $t('delete') }}
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="members.length === 0 && pendingInvitations.length === 0" class="text-center py-8">
            <p class="text-gray-600">{{ $t('no_members_yet') }}</p>
          </div>
        </div>

          <!-- Settings Tab -->
          <div v-if="activeTab === 'settings'" class="space-y-6">
            <!-- Danger Zone -->
            <div class="bg-white rounded-xl shadow-sm border border-red-200 p-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-1">{{ $t('danger_zone') }}</h2>
              <p class="text-sm text-gray-500 mb-6">{{ $t('permanent_actions_warning') }}</p>

              <div class="flex items-center justify-between p-4 border border-red-200 rounded-xl bg-red-50">
                <div>
                  <p class="font-medium text-gray-900">{{ $t('delete_workspace_label') }}</p>
                  <p class="text-sm text-gray-500 mt-0.5">{{ $t('delete_workspace_description') }}</p>
                </div>
                <button
                  @click="deleteStep = 1"
                  class="ml-6 flex-shrink-0 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors cursor-pointer"
                >
                  {{ $t('delete_workspace_btn') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Workspace Modal – Step 1: Warning -->
    <div v-if="deleteStep === 1" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
        <div class="p-6">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">{{ $t('delete_workspace_confirm') }}</h3>
          </div>
          <p class="text-sm text-gray-600 mb-2">
            {{ $t('delete_workspace_confirm_desc_start') }} <strong>{{ workspace.name }}</strong>{{ $t('delete_workspace_confirm_desc_end') }}
          </p>
          <ul class="text-sm text-gray-600 list-disc list-inside space-y-1 mb-6 ml-1">
            <li>{{ $t('workspace_items_deleted') }}</li>
            <li>{{ $t('members_access_deleted') }}</li>
            <li>{{ $t('comments_attachments_deleted') }}</li>
          </ul>
          <p class="text-sm font-medium text-red-600 mb-6">{{ $t('permanent_actions_warning') }}</p>
          <div class="flex gap-3">
            <button
              @click="deleteStep = 0"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium cursor-pointer"
            >
              {{ $t('cancel') }}
            </button>
            <button
              @click="deleteStep = 2"
              class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium cursor-pointer"
            >
              {{ $t('yes_continue') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Workspace Modal – Step 2: Type name to confirm -->
    <div v-if="deleteStep === 2" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $t('confirm_deletion') }}</h3>
          <p class="text-sm text-gray-600 mb-4">
            {{ $t('confirm_type_workspace_name_start') }} <strong class="text-gray-900 select-all">{{ workspace.name }}</strong> {{ $t('confirm_type_workspace_name_end') }}
          </p>
          <input
            v-model="deleteConfirmName"
            type="text"
            :placeholder="$t('type_workspace_name')"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm mb-2"
            @keyup.enter="confirmDelete"
          />
          <p v-if="deleteError" class="text-sm text-red-600 mb-3">{{ deleteError }}</p>
          <div class="flex gap-3 mt-4">
            <button
              @click="deleteStep = 0; deleteConfirmName = ''; deleteError = ''"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium cursor-pointer"
            >
              {{ $t('cancel') }}
            </button>
            <button
              @click="confirmDelete"
              :disabled="isDeletingWorkspace || deleteConfirmName.trim().toLowerCase() !== workspace.name.trim().toLowerCase()"
              class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed text-sm font-medium transition-colors cursor-pointer"
            >
              {{ isDeletingWorkspace ? $t('deleting') : $t('delete_forever') }}
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
import ProjectsSection from '@/Components/Dashboard/ProjectsSection.vue'
import PrivateNotesSection from '@/Components/Dashboard/PrivateNotesSection.vue'

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
  isOwner: Boolean,
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
    deleteError.value = page.props.translations?.workspace_name_mismatch || 'Workspace name does not match. Please try again.'
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
  { id: 'overview', labelKey: 'overview' },
  { id: 'members', labelKey: 'members' },
  ...(props.userRole === 'owner' ? [{ id: 'settings', labelKey: 'workspace_settings' }] : []),
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
  showNotification(page.props.translations?.invitation_sent_success || 'Invitation sent successfully!')
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
      showNotification(page.props.translations?.member_deactivated_success || 'Member deactivated successfully')
      router.reload({ only: ['members'] })
    } else {
      showNotification(page.props.translations?.member_deactivate_failed || 'Failed to deactivate member', 'error')
    }
  } catch {
    showNotification(page.props.translations?.error_deactivating_member || 'Error deactivating member', 'error')
  }
}

const activateMember = async (userId, membershipId) => {
  try {
    const response = await fetch(`/api/organization-memberships/${membershipId}/activate`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content },
    })
    if (response.ok) {
      showNotification(page.props.translations?.member_activated_success || 'Member activated successfully')
      router.reload({ only: ['members'] })
    } else {
      showNotification(page.props.translations?.member_activate_failed || 'Failed to activate member', 'error')
    }
  } catch {
    showNotification(page.props.translations?.error_activating_member || 'Error activating member', 'error')
  }
}

const expireInvitation = async (invitationId) => {
  try {
    const response = await fetch(`/api/invitations/${invitationId}/expire`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content },
    })
    if (response.ok) {
      showNotification(page.props.translations?.invitation_expired_success || 'Invitation expired')
      router.reload({ only: ['invitations'] })
    } else {
      showNotification(page.props.translations?.invitation_expire_failed || 'Failed to expire invitation', 'error')
    }
  } catch {
    showNotification(page.props.translations?.error_expiring_invitation || 'Error expiring invitation', 'error')
  }
}

const deleteInvitation = async (invitationId) => {
  if (!confirm(page.props.translations?.delete_invitation_confirm || 'Delete this invitation?')) return
  try {
    const response = await fetch(`/api/invitations/${invitationId}`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content },
    })
    if (response.ok) {
      showNotification(page.props.translations?.invitation_deleted_success || 'Invitation deleted')
      router.reload({ only: ['invitations'] })
    } else {
      showNotification(page.props.translations?.invitation_delete_failed || 'Failed to delete invitation', 'error')
    }
  } catch {
    showNotification(page.props.translations?.error_deleting_invitation || 'Error deleting invitation', 'error')
  }
}

// Keep pendingInvitations in sync with props after Inertia reloads
watch(() => props.invitations, (val) => { pendingInvitations.value = val || [] })
</script>
