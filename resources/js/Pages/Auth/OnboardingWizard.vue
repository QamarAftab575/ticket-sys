<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Welcome to Asira</h1>
        <p class="text-gray-600 mt-2">Let's set up your workspace in just a few steps</p>
      </div>

      <!-- Progress Indicator -->
      <StepProgressIndicator 
        :current-step="currentStep"
        :total-steps="2"
        :step-labels="['Workspace Setup', 'Invite Members']"
      />

      <!-- Content Card -->
      <div class="bg-white rounded-lg shadow-lg p-8">
        <!-- Step 1: Workspace Setup -->
        <div v-if="currentStep === 1" class="space-y-6">
          <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Set Up Your Workspace</h2>
          </div>

          <WorkspaceFormStep
            :workspace="workspace"
            :is-name-duplicate="false"
            :errors="{}"
            :show-skip="true"
            name-label="Workspace"
            name-placeholder="Acme Studio"
            type-label="What type of work do you do?"
            description-label="Tell us about your workspace"
            skip-button-label="Skip"
            next-button-label="Next"
            @update:workspace="workspace = $event"
            @next="nextStep"
            @skip="skipToStep2"
          />
        </div>

        <!-- Step 2: Invite Members -->
        <div v-if="currentStep === 2" class="space-y-6">
          <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Invite Workspace Members</h2>
            <p class="text-gray-600">Type an email and press Enter or comma to add. All invited members join as <strong>Member</strong>.</p>
          </div>

          <form @submit.prevent="completeOnboarding" class="space-y-6">
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
                Skip
              </button>
              <button
                type="submit"
                :disabled="isLoading || invalidCount > 0"
                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ isLoading ? 'Completing...' : 'Complete Onboarding' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'
import EmailTagInput from '@/Components/EmailTagInput.vue'
import StepProgressIndicator from '@/Components/Workspace/StepProgressIndicator.vue'
import WorkspaceFormStep from '@/Components/Workspace/WorkspaceFormStep.vue'

const currentStep = ref(1)
const isLoading = ref(false)

const workspace = ref({ name: '', types: [], description: '' })

const tagInputRef = ref(null)
const emailTags = ref([])

const invalidCount = computed(() => emailTags.value.filter(t => !t.valid).length)

const nextStep = () => { currentStep.value = 2 }
const skipToStep2 = () => { currentStep.value = 2 }
const previousStep = () => { currentStep.value = 1 }

const skipInvites = async () => { await completeOnboarding() }

const completeOnboarding = async () => {
  tagInputRef.value?.commit()

  isLoading.value = true
  try {
    const invites = emailTags.value
      .filter(t => t.valid)
      .map(t => ({ email: t.email, role: 'member' }))

    await router.post('/onboarding/complete', {
      workspace: workspace.value,
      invites,
    }, {
      onError: (errors) => {
        Object.keys(errors).forEach(key => {
          toast.error(errors[key], { autoClose: 5000, position: 'top-right' })
        })
      }
    })
  } catch (error) {
    toast.error('An error occurred while completing onboarding', { autoClose: 5000, position: 'top-right' })
  } finally {
    isLoading.value = false
  }
}
</script>
