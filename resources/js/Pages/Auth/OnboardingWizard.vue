<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Welcome to Asira</h1>
        <p class="text-gray-600 mt-2">Let's set up your workspace in just a few steps</p>
      </div>

      <!-- Progress Indicator -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div v-for="step in 2" :key="step" class="flex items-center flex-1">
            <div
              :class="[
                'w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all',
                step < currentStep
                  ? 'bg-green-500 text-white'
                  : step === currentStep
                  ? 'bg-blue-600 text-white'
                  : 'bg-gray-300 text-gray-600'
              ]"
            >
              {{ step }}
            </div>
            <div
              v-if="step < 2"
              :class="[
                'flex-1 h-1 mx-2 transition-all',
                step < currentStep ? 'bg-green-500' : 'bg-gray-300'
              ]"
            />
          </div>
        </div>
        <div class="flex justify-between mt-4 text-xs font-medium text-gray-600">
          <span>Workspace Setup</span>
          <span>Invite Members</span>
        </div>
      </div>

      <!-- Content Card -->
      <div class="bg-white rounded-lg shadow-lg p-8">
        <!-- Step 1: Workspace Setup -->
        <div v-if="currentStep === 1" class="space-y-6">
          <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Set Up Your Workspace</h2>
            <p class="text-gray-600">Customize your workspace to match your needs</p>
          </div>

         

          <form @submit.prevent="nextStep" class="space-y-6">
            <!-- Workspace Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Workspace / Organization Name
              </label>
              <input
                v-model="workspace.name"
                type="text"
                placeholder="e.g., My Company, Design Studio (optional)"
                maxlength="255"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            
            </div>

            <!-- Organization Type -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                What type of work do you do?
              </label>
              <div class="relative">
                <button
                  type="button"
                  @click="typeDropdownOpen = !typeDropdownOpen"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg text-left bg-white hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent flex items-center justify-between"
                >
                  <span v-if="workspace.types.length === 0" class="text-gray-500">Select one or more (optional)</span>
                  <span v-else class="text-gray-900">{{ workspace.types.join(', ') }}</span>
                  <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                  </svg>
                </button>

                <div v-if="typeDropdownOpen" class="absolute z-10 w-full mt-2 bg-white border border-gray-300 rounded-lg shadow-lg">
                  <div class="p-2 space-y-1">
                    <label v-for="type in organizationTypes" :key="type" class="flex items-center px-3 py-2 hover:bg-gray-100 rounded cursor-pointer">
                      <input
                        type="checkbox"
                        :checked="workspace.types.includes(type)"
                        @change="toggleType(type)"
                        class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                      />
                      <span class="ml-3 text-sm text-gray-700">{{ type }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Workspace Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Tell us about your workspace
              </label>
              <textarea
                v-model="workspace.description"
                placeholder="What will you use this workspace for? (optional)"
                maxlength="1000"
                rows="4"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
              />
              <p class="text-xs text-gray-500 mt-1">{{ workspace.description.length }}/1000 characters</p>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex gap-3 pt-6">
              <button
                type="button"
                @click="skipToStep2"
                class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition"
              >
                Skip
              </button>
              <button
                type="submit"
                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition"
              >
                Next
              </button>
            </div>
          </form>
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

const currentStep = ref(1)
const isLoading = ref(false)
const typeDropdownOpen = ref(false)

const organizationTypes = [
  'Design', 'HR', 'Engineering', 'Education / Teacher',
  'IT Company', 'Marketing', 'Finance', 'Sales', 'Other'
]

const workspace = ref({ name: '', types: [], description: '' })

const tagInputRef = ref(null)
const emailTags = ref([])

const invalidCount = computed(() => emailTags.value.filter(t => !t.valid).length)

const toggleType = (type) => {
  const index = workspace.value.types.indexOf(type)
  index > -1 ? workspace.value.types.splice(index, 1) : workspace.value.types.push(type)
}

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
