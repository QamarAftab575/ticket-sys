<template>
  <AppLayout :user-workspaces="userWorkspaces" :current-workspace="currentWorkspace" :user-role="userRole">
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-4">
      <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8 pt-8">
          <h1 class="text-3xl font-bold text-gray-900">Create New Workspace</h1>
          <p class="text-gray-600 mt-2">Set up a new workspace for your team</p>
        </div>

        <!-- Progress Indicator -->
        <StepProgressIndicator 
          :current-step="currentStep"
          :total-steps="2"
          :step-labels="['Workspace Details', 'Invite Members']"
        />

        <!-- Content Card -->
        <div class="bg-white rounded-lg shadow-lg p-8">
          <!-- Step 1: Workspace Details -->
          <div v-if="currentStep === 1" class="space-y-6">
            <div>
              <h2 class="text-2xl font-bold text-gray-900 mb-2">Workspace Details</h2>
            </div>

            <WorkspaceFormStep
              :workspace="workspace"
              :is-name-duplicate="isNameDuplicate"
              :errors="errors"
              :show-skip="false"
              name-label="Workspace Name"
              name-placeholder="e.g., Marketing Team"
              duplicate-error-message="You already have a workspace with this name"
              next-button-label="Next"
              @update:workspace="workspace = $event"
              @next="nextStep"
            >
              <template #cancel>
                <Link
                  href="/dashboard"
                  class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition text-center"
                >
                  Cancel
                </Link>
              </template>
            </WorkspaceFormStep>
          </div>

          <!-- Step 2: Invite Members -->
          <div v-if="currentStep === 2" class="space-y-6">
            <div>
              <h2 class="text-2xl font-bold text-gray-900 mb-2">Invite Members (Optional)</h2>
              <p class="text-gray-600">Add team members to your new workspace. Type an email and press Enter or comma to add.</p>
            </div>

            <form @submit.prevent="createWorkspace" class="space-y-6">
              <!-- Email Tag Input -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Addresses</label>
                <EmailTagInput ref="tagInputRef" v-model="emailTags" />
              </div>

              <!-- Navigation Buttons -->
              <div class="flex gap-3 pt-6 border-t">
                <button
                  type="button"
                  @click="previousStep"
                  class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition"
                >
                  Back
                </button>
                <button
                  type="button"
                  @click="skipInvites"
                  class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition"
                >
                  Skip Invites
                </button>
                <button
                  type="submit"
                  :disabled="isLoading || invalidCount > 0"
                  class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ isLoading ? 'Creating...' : 'Create Workspace' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'
import AppLayout from '@/Layouts/AppLayout.vue'
import EmailTagInput from '@/Components/EmailTagInput.vue'
import StepProgressIndicator from '@/Components/Workspace/StepProgressIndicator.vue'
import WorkspaceFormStep from '@/Components/Workspace/WorkspaceFormStep.vue'
import { dispatchWorkspaceEvent } from '@/Composables/useCacheInvalidation'

const props = defineProps({
  userWorkspaces: Array,
  currentWorkspace: Object,
  userRole: String,
})

const currentStep = ref(1)
const isLoading = ref(false)

const workspace = ref({ name: '', types: [], description: '' })
const errors = ref({ name: null })

const tagInputRef = ref(null)
const emailTags = ref([])

const invalidCount = computed(() => emailTags.value.filter(t => !t.valid).length)

// Check if workspace name is duplicate for current user
const isNameDuplicate = computed(() => {
  if (!workspace.value.name.trim()) return false
  return props.userWorkspaces.some(ws => 
    ws.name.toLowerCase() === workspace.value.name.toLowerCase()
  )
})

const nextStep = () => {
  if (workspace.value.name.trim() && !isNameDuplicate.value) {
    currentStep.value = 2
  }
}

const previousStep = () => { currentStep.value = 1 }

const skipInvites = async () => { await createWorkspace() }

const createWorkspace = async () => {
  if (!workspace.value.name.trim() || isNameDuplicate.value) {
    toast.error('Please enter a valid workspace name', { autoClose: 5000 })
    return
  }

  tagInputRef.value?.commit()

  isLoading.value = true
  try {
    const invites = emailTags.value
      .filter(t => t.valid)
      .map(t => ({ email: t.email, role: 'member' }))

    await router.post('/workspace/create', {
      workspace: workspace.value,
      invites,
    }, {
      onSuccess: () => {
        // Clear sidebar cache when workspace is created
        dispatchWorkspaceEvent('created')
      },
      onError: (pageErrors) => {
        if (pageErrors.name) {
          errors.value.name = [pageErrors.name]
          currentStep.value = 1
        } else {
          Object.keys(pageErrors).forEach(key => {
            toast.error(pageErrors[key], { autoClose: 5000 })
          })
        }
      }
    })
  } catch (error) {
    toast.error('An error occurred while creating workspace', { autoClose: 5000 })
  } finally {
    isLoading.value = false
  }
}
</script>
