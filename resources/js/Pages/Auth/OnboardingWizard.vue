<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ $t('welcome') }} Tasqo</h1>
        <p class="text-gray-600 mt-2">{{ $t('lets_set_up_workspace') }}</p>
      </div>

      <!-- Progress Indicator -->
      <StepProgressIndicator 
        :current-step="currentStep"
        :total-steps="2"
        :step-labels="[$t('workspace_setup'), $t('invite_members')]"
      />

      <!-- Content Card -->
      <div class="bg-white rounded-lg shadow-lg p-8">
        <!-- Step 1: Workspace Setup -->
        <div v-if="currentStep === 1" class="space-y-6">
          <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $t('set_up_workspace') }}</h2>
          </div>

          <WorkspaceFormStep
            :workspace="workspace"
            :is-name-duplicate="false"
            :errors="{}"
            :show-skip="true"
            :name-label="$t('workspace')"
            :name-placeholder="$t('acme_studio')"
            :type-label="$t('what_type_of_work_do_you_do')"
            :description-label="$t('tell_us_about_workspace')"
            :skip-button-label="$t('skip')"
            :next-button-label="$t('next')"
            @update:workspace="workspace = $event"
            @next="nextStep"
            @skip="skipToStep2"
          />
        </div>

        <!-- Step 2: Invite Members -->
        <div v-if="currentStep === 2" class="space-y-6">
          <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $t('invite_workspace_members') }}</h2>
            <p class="text-gray-600">{{ $t('invite_members_instructions') }}</p>
          </div>

          <form @submit.prevent="completeOnboarding" class="space-y-6">
            <!-- Email Tag Input -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('email_address_label') }}</label>
              <EmailTagInput ref="tagInputRef" v-model="emailTags" />
            </div>

            <!-- Navigation Buttons -->
            <div class="flex gap-3 pt-6 border-t">
              <button
                type="button"
                @click="previousStep"
                class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition"
              >
                {{ $t('back') }}
              </button>
              <button
                type="button"
                @click="skipInvites"
                class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition"
              >
                {{ $t('skip') }}
              </button>
              <button
                type="submit"
                :disabled="isLoading || invalidCount > 0"
                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ isLoading ? $t('completing') : $t('complete_onboarding') }}
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
    toast.error($t('error_occurred_onboarding'), { autoClose: 5000, position: 'top-right' })
  } finally {
    isLoading.value = false
  }
}
</script>
