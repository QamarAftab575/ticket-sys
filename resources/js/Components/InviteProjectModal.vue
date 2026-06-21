<template>
  <div class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50" @click.self="$emit('close')">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 flex flex-col max-h-[90vh]">

      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between flex-shrink-0">
        <h2 class="text-base font-semibold text-gray-900">Share "{{ contextName }}"</h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Body -->
      <div class="px-6 py-4 overflow-y-auto flex-1 space-y-5">

        <!-- Invite with email -->
        <div>
          <p class="text-sm font-semibold text-gray-800 mb-2">Invite with email</p>
          <div class="flex gap-2">
            <input
              v-model="inviteEmail"
              type="email"
              placeholder="Add members by name or email..."
              class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              @keydown.enter.prevent="submitInvite"
            />
            <select
              v-model="selectedRole"
              class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-indigo-500 bg-white"
            >
              <option value="editor">Editor</option>
              <option value="viewer">Viewer</option>
              <option value="commenter">Commenter</option>
              <option value="project_admin">Admin</option>
            </select>
            <button
              @click="submitInvite"
              :disabled="!inviteEmail.trim() || isInviting"
              class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
            >
              {{ isInviting ? '...' : 'Invite' }}
            </button>
          </div>
          <p v-if="inviteError" class="text-xs text-red-500 mt-1">{{ inviteError }}</p>
          <p v-if="inviteSuccess" class="text-xs text-green-600 mt-1">{{ inviteSuccess }}</p>
        </div>

        <!-- Access settings -->
        <div>
          <p class="text-sm font-semibold text-gray-800 mb-2">Access settings</p>
          <div class="relative">
            <button
              type="button"
              @click="accessDropdownOpen = !accessDropdownOpen"
              class="w-full flex items-center justify-between px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white hover:bg-gray-50 transition"
            >
              <span class="flex items-center gap-2 text-gray-700">
                <svg v-if="localVisibility === 'public_to_team'" class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg v-else class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                {{ localVisibility === 'public_to_team' ? 'My workspace' : 'Private to members' }}
              </span>
              <svg class="w-4 h-4 text-gray-400 transition-transform" :class="accessDropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <div v-if="accessDropdownOpen" class="absolute z-10 mt-1 w-full border border-gray-200 rounded-lg shadow-lg bg-white overflow-hidden">
              <button
                v-for="opt in visibilityOptions"
                :key="opt.value"
                type="button"
                @click="setVisibility(opt.value)"
                :class="['w-full flex items-start gap-3 px-4 py-3 text-left hover:bg-gray-50 transition', localVisibility === opt.value ? 'bg-indigo-50' : '']"
              >
                <svg v-if="opt.value === 'public_to_team'" class="w-4 h-4 mt-0.5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg v-else class="w-4 h-4 mt-0.5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ opt.label }}</p>
                  <p class="text-xs text-gray-500">{{ opt.description }}</p>
                </div>
              </button>
            </div>
          </div>
          <p v-if="visibilityMsg" class="text-xs mt-1" :class="visibilityError ? 'text-red-500' : 'text-green-600'">{{ visibilityMsg }}</p>
        </div>

        <!-- Who has access -->
        <div>
          <p class="text-sm font-semibold text-gray-800 mb-3">Who has access</p>

          <div v-if="loading" class="text-sm text-gray-400 py-2">Loading...</div>

          <div v-else class="space-y-1">

            <!-- Task collaborators row (always shown) -->
            <div class="flex items-center gap-3 py-2">
              <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm text-gray-800">Task collaborators</p>
              </div>
              <div class="relative flex-shrink-0" data-member-menu>
                <button type="button" @click="toggleMemberMenu('task_collaborators', $event)" class="flex items-center gap-1 text-xs text-gray-500 hover:text-gray-700 transition">
                  {{ formatRole(taskCollaboratorsRole) }}
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div v-if="openMemberMenu === 'task_collaborators'" class="absolute right-0 z-20 mt-1 w-64 bg-white border border-gray-200 rounded-lg shadow-xl py-1">
                  <button v-for="opt in roleOptions" :key="opt.value" type="button" @click="taskCollaboratorsRole = opt.value; openMemberMenu = null" class="w-full flex items-start gap-2 px-4 py-2.5 text-left hover:bg-gray-50 transition">
                    <svg v-if="taskCollaboratorsRole === opt.value" class="w-4 h-4 mt-0.5 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    <span v-else class="w-4 flex-shrink-0" />
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ opt.label }}</p>
                      <p class="text-xs text-gray-500">{{ opt.description }}</p>
                    </div>
                  </button>
                </div>
              </div>
            </div>

            <!-- My workspace row (only when public_to_team) -->
            <div v-if="localVisibility === 'public_to_team'" class="flex items-center gap-3 py-2">
              <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm text-gray-800">My workspace</p>
                <p class="text-xs text-gray-500">{{ workspaceMemberCount }} {{ workspaceMemberCount === 1 ? 'person' : 'people' }}</p>
              </div>
              <div class="relative flex-shrink-0" data-member-menu>
                <button type="button" @click="toggleMemberMenu('workspace', $event)" class="flex items-center gap-1 text-xs text-gray-500 hover:text-gray-700 transition">
                  {{ formatRole(workspaceRole) }}
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div v-if="openMemberMenu === 'workspace'" class="absolute right-0 z-20 mt-1 w-64 bg-white border border-gray-200 rounded-lg shadow-xl py-1">
                  <button v-for="opt in roleOptions" :key="opt.value" type="button" @click="saveWorkspaceRole(opt.value)" class="w-full flex items-start gap-2 px-4 py-2.5 text-left hover:bg-gray-50 transition">
                    <svg v-if="workspaceRole === opt.value" class="w-4 h-4 mt-0.5 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    <span v-else class="w-4 flex-shrink-0" />
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ opt.label }}</p>
                      <p class="text-xs text-gray-500">{{ opt.description }}</p>
                    </div>
                  </button>
                </div>
              </div>
            </div>

            <!-- Workspace owner â€” always fixed as Project admin -->
            <div v-if="workspaceOwner" class="flex items-center gap-3 py-2">
              <div
                class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-semibold flex-shrink-0"
                :style="{ backgroundColor: avatarColor(workspaceOwner.name) }"
              >
                {{ workspaceOwner.name?.charAt(0).toUpperCase() }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ workspaceOwner.name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ workspaceOwner.email }}</p>
              </div>
              <span class="text-xs text-gray-500 font-medium flex-shrink-0">Project admin</span>
            </div>

            <!-- Project members (explicit) -->
            <div
              v-for="member in members"
              :key="member.id"
              class="flex items-center gap-3 py-2"
            >
              <div
                class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-semibold flex-shrink-0"
                :style="{ backgroundColor: avatarColor(member.name) }"
              >
                {{ member.name?.charAt(0).toUpperCase() }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ member.name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ member.email }}</p>
              </div>

              <!-- Role dropdown (fixed for project_admin) -->
              <div class="relative flex-shrink-0" data-member-menu>
                <span v-if="member.role === 'project_admin'" class="text-xs text-gray-500 font-medium">
                  Project admin
                </span>
                <template v-else>
                  <button
                    type="button"
                    @click="toggleMemberMenu(member.id, $event)"
                    class="flex items-center gap-1 text-xs text-gray-500 hover:text-gray-700 transition"
                  >
                    {{ formatRole(member.role) }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>
                  <div
                    v-if="openMemberMenu === member.id"
                    class="absolute right-0 z-20 mt-1 w-64 bg-white border border-gray-200 rounded-lg shadow-xl"
                  >
                    <div class="py-1">
                      <button
                        v-for="opt in roleOptions"
                        :key="opt.value"
                        type="button"
                        @click="changeMemberRole(member, opt.value)"
                        class="w-full flex items-start gap-2 px-4 py-2.5 text-left hover:bg-gray-50 transition"
                      >
                        <svg v-if="member.role === opt.value" class="w-4 h-4 mt-0.5 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span v-else class="w-4 flex-shrink-0" />
                        <div>
                          <p class="text-sm font-medium text-gray-900">{{ opt.label }}</p>
                          <p class="text-xs text-gray-500">{{ opt.description }}</p>
                        </div>
                      </button>
                    </div>
                    <div class="border-t border-gray-100 py-1">
                      <button
                        type="button"
                        @click="removeMember(member)"
                        class="w-full flex items-start gap-2 px-4 py-2.5 text-left hover:bg-red-50 transition"
                      >
                        <svg class="w-4 h-4 mt-0.5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <div>
                          <p class="text-sm font-medium text-red-600">Remove from project</p>
                          <p class="text-xs text-gray-500">May still have access via tasks or other projects.</p>
                        </div>
                      </button>
                    </div>
                  </div>
                </template>
              </div>
            </div>

            <!-- Pending invitations -->
            <div
              v-for="inv in invitations"
              :key="inv.id"
              class="flex items-center gap-3 py-2"
            >
              <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-700 truncate">{{ inv.email }}</p>
                <p v-if="resentIds[inv.id]" class="text-xs text-green-600">âœ“ Invite re-sent successfully!</p>
                <p v-else class="text-xs text-amber-500">â³ Pending acceptance</p>
              </div>
              <div class="flex items-center gap-1 flex-shrink-0">
                <!-- Resend icon button -->
                <button
                  type="button"
                  @click="resendInvite(inv)"
                  :disabled="resendingId === inv.id"
                  title="Resend invite"
                  class="p-1.5 text-gray-400 hover:text-indigo-600 rounded transition disabled:opacity-40"
                >
                  <svg v-if="resendingId !== inv.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                  <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                  </svg>
                </button>
                <!-- Role + cancel dropdown -->
                <div class="relative" data-member-menu>
                  <button
                    type="button"
                    @click="toggleMemberMenu('inv_' + inv.id, $event)"
                    class="flex items-center gap-1 text-xs text-gray-500 hover:text-gray-700 transition"
                  >
                    {{ formatRole(inv.role) }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>
                  <div
                    v-if="openMemberMenu === 'inv_' + inv.id"
                    class="absolute right-0 z-20 mt-1 w-64 bg-white border border-gray-200 rounded-lg shadow-xl"
                  >
                    <div class="py-1">
                      <button
                        v-for="opt in roleOptions"
                        :key="opt.value"
                        type="button"
                        @click="changeInvitationRole(inv, opt.value)"
                        class="w-full flex items-start gap-2 px-4 py-2.5 text-left hover:bg-gray-50 transition"
                      >
                        <svg v-if="inv.role === opt.value" class="w-4 h-4 mt-0.5 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span v-else class="w-4 flex-shrink-0" />
                        <div>
                          <p class="text-sm font-medium text-gray-900">{{ opt.label }}</p>
                          <p class="text-xs text-gray-500">{{ opt.description }}</p>
                        </div>
                      </button>
                    </div>
                    <div class="border-t border-gray-100 py-1">
                      <button
                        type="button"
                        @click="cancelInvitation(inv)"
                        class="w-full flex items-start gap-2 px-4 py-2.5 text-left hover:bg-red-50 transition"
                      >
                        <svg class="w-4 h-4 mt-0.5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <div>
                          <p class="text-sm font-medium text-red-600">Cancel invite</p>
                          <p class="text-xs text-gray-500">Revoke this pending invitation.</p>
                        </div>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>

      <!-- Footer -->
      <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-end flex-shrink-0">
        <button
          @click="copyLink"
          class="flex items-center gap-1.5 text-sm text-gray-600 border border-gray-300 px-3 py-2 rounded-lg hover:bg-gray-50 transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
          </svg>
          {{ copyMsg || 'Copy project link' }}
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  /** workspace or project object â€” must have .id, .name, .visibility, .organization_id */
  context: { type: Object, required: true },
})

const emit = defineEmits(['close', 'invited'])

const contextName = computed(() => props.context?.name || '')

// State
const loading = ref(false)
const workspaceOwner = ref(null)
const members = ref([])
const invitations = ref([])
const workspaceMemberCount = ref(0)
const localVisibility = ref(props.context?.visibility || 'public_to_team')

// Invite form
const inviteEmail = ref('')
const selectedRole = ref('editor')
const isInviting = ref(false)
const inviteError = ref('')
const inviteSuccess = ref('')

// Access settings dropdown
const accessDropdownOpen = ref(false)
const visibilityMsg = ref('')
const visibilityError = ref(false)

// Resend
const resendingId = ref(null)
const resentIds = ref({})

// Copy link
const copyMsg = ref('')

// Task collaborators and workspace default roles
const taskCollaboratorsRole = ref('editor')
const workspaceRole = ref('editor') // persisted per project

// Role dropdown per member/invitation
const openMemberMenu = ref(null)

const toggleMemberMenu = (id, event) => {
  event?.stopPropagation()
  openMemberMenu.value = openMemberMenu.value === id ? null : id
}

// Close menu when clicking outside â€” use stopPropagation on toggle instead
const handleOutsideClick = (e) => {
  if (!e.target.closest('[data-member-menu]')) openMemberMenu.value = null
}
onMounted(() => document.addEventListener('click', handleOutsideClick))
onUnmounted(() => document.removeEventListener('click', handleOutsideClick))

const roleOptions = [
  { value: 'project_admin', label: 'Project admin', description: 'Full access to change settings, modify, or delete the project.' },
  { value: 'editor',        label: 'Editor',        description: 'Can add, edit, and delete anything in the project.' },
  { value: 'commenter',     label: 'Commenter',     description: 'Can comment, but can\'t edit anything in the project.' },
  { value: 'viewer',        label: 'Viewer',        description: 'Can view, but can\'t add comments or edit the project.' },
]

const visibilityOptions = [
  {
    value: 'public_to_team',
    label: 'My workspace',
    description: 'Everyone in your workspace can find and access this project.',
  },
  {
    value: 'private_to_members',
    label: 'Private to members',
    description: 'Only invited members can find and access this project.',
  },
]

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content

// Load all share data in one request
onMounted(async () => {
  loading.value = true
  try {
    const res = await fetch(`/projects/${props.context.id}/share-data`, {
      headers: { 'Accept': 'application/json', 'X-CSRF-Token': csrf() },
    })
    if (res.ok) {
      const data = await res.json()
      members.value = data.members || []
      invitations.value = data.invitations || []
      workspaceOwner.value = data.workspace_owner || null
      workspaceMemberCount.value = data.workspace_member_count || 0
      localVisibility.value = data.visibility || localVisibility.value
      workspaceRole.value = data.workspace_member_role || 'editor'
    } else {
      const text = await res.text()
      console.error('share-data failed:', res.status, text)
    }
  } catch (e) {
    console.error('share-data error:', e)
  } finally {
    loading.value = false
  }
})

const submitInvite = async () => {
  const email = inviteEmail.value.trim()
  if (!email) return

  inviteError.value = ''
  inviteSuccess.value = ''
  isInviting.value = true

  try {
    const res = await fetch(`/projects/${props.context.id}/invitations`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf(), 'Accept': 'application/json' },
      body: JSON.stringify({ email, role: selectedRole.value }),
    })
    const data = await res.json()
    if (res.ok) {
      if (data.member) {
        // Workspace member â€” added directly, show in members list immediately
        members.value.push(data.member)
      } else {
        // External user â€” show as pending invitation
        invitations.value.push(data.invitation)
      }
      inviteEmail.value = ''
      inviteSuccess.value = data.member ? `${inviteEmail.value || data.member.email} added to project.` : `Invite sent to ${inviteEmail.value}`
      setTimeout(() => { inviteSuccess.value = '' }, 3000)
      emit('invited')
    } else {
      inviteError.value = data.message || 'Failed to send invite.'
    }
  } catch {
    inviteError.value = 'Failed to send invite.'
  } finally {
    isInviting.value = false
  }
}

const setVisibility = async (val) => {
  accessDropdownOpen.value = false
  if (val === localVisibility.value) return

  const prev = localVisibility.value
  localVisibility.value = val
  visibilityMsg.value = ''
  visibilityError.value = false

  try {
    const res = await fetch(`/projects/${props.context.id}/visibility`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf(), 'Accept': 'application/json' },
      body: JSON.stringify({ visibility: val }),
    })
    if (res.ok) {
      visibilityMsg.value = 'Saved.'
    } else {
      const data = await res.json()
      visibilityMsg.value = data.message || 'Failed to update.'
      visibilityError.value = true
      localVisibility.value = prev
    }
  } catch {
    visibilityMsg.value = 'Failed to update.'
    visibilityError.value = true
    localVisibility.value = prev
  } finally {
    setTimeout(() => { visibilityMsg.value = '' }, 2500)
  }
}

const saveWorkspaceRole = async (role) => {
  openMemberMenu.value = null
  const prev = workspaceRole.value
  workspaceRole.value = role
  try {
    const res = await fetch(`/projects/${props.context.id}/workspace-member-role`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf(), 'Accept': 'application/json' },
      body: JSON.stringify({ workspace_member_role: role }),
    })
    if (!res.ok) workspaceRole.value = prev
  } catch {
    workspaceRole.value = prev
  }
}

const changeMemberRole = async (member, role) => {
  openMemberMenu.value = null
  const prev = member.role
  member.role = role
  try {
    const res = await fetch(`/projects/${props.context.id}/members/${member.id}/role`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf(), 'Accept': 'application/json' },
      body: JSON.stringify({ role }),
    })
    if (!res.ok) member.role = prev
  } catch { member.role = prev }
}

const removeMember = async (member) => {
  openMemberMenu.value = null
  try {
    const res = await fetch(`/projects/${props.context.id}/members/${member.id}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-Token': csrf(), 'Accept': 'application/json' },
    })
    if (res.ok) members.value = members.value.filter(m => m.id !== member.id)
  } catch { /* non-critical */ }
}

const changeInvitationRole = async (inv, role) => {
  openMemberMenu.value = null
  if (inv.role === role) return
  // Cancel the old invitation and send a new one with the updated role
  try {
    await fetch(`/project-invitations/${inv.id}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-Token': csrf(), 'Accept': 'application/json' },
    })
    const res = await fetch(`/projects/${props.context.id}/invitations`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf(), 'Accept': 'application/json' },
      body: JSON.stringify({ email: inv.email, role }),
    })
    if (res.ok) {
      const data = await res.json()
      const idx = invitations.value.findIndex(i => i.id === inv.id)
      if (idx !== -1) invitations.value.splice(idx, 1, data.invitation)
    }
  } catch { /* non-critical */ }
}

const cancelInvitation = async (inv) => {
  openMemberMenu.value = null
  try {
    const res = await fetch(`/project-invitations/${inv.id}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-Token': csrf(), 'Accept': 'application/json' },
    })
    if (res.ok) invitations.value = invitations.value.filter(i => i.id !== inv.id)
  } catch { /* non-critical */ }
}

const resendInvite = async (inv) => {
  resendingId.value = inv.id
  try {
    const res = await fetch(`/project-invitations/${inv.id}/resend`, {
      method: 'POST',
      headers: { 'X-CSRF-Token': csrf(), 'Accept': 'application/json' },
    })
    if (res.ok) {
      resentIds.value[inv.id] = true
      setTimeout(() => { delete resentIds.value[inv.id] }, 4000)
    }
  } catch { /* non-critical */ }
  finally { resendingId.value = null }
}

const copyLink = () => {
  navigator.clipboard.writeText(window.location.href)
  copyMsg.value = 'Link copied!'
  setTimeout(() => { copyMsg.value = '' }, 2000)
}

const formatRole = (role) => {
  const map = {
    project_admin: 'Project admin',
    editor: 'Editor',
    viewer: 'Viewer',
    commenter: 'Commenter',
  }
  return map[role] || role || ''
}

const avatarColor = (name) => {
  const colors = ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#3b82f6', '#ef4444']
  return colors[(name?.charCodeAt(0) || 0) % colors.length]
}
</script>

