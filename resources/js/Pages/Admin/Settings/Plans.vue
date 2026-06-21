<template>
  <div class="w-full space-y-6">
    <!-- Existing Plans Grid -->
    <div v-if="plans.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="plan in plans" :key="plan.id" class="bg-white rounded-lg shadow p-6 space-y-4 hover:shadow-lg transition">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 class="font-bold text-lg text-gray-900">{{ plan.name }}</h3>
            <p class="text-sm text-gray-500">{{ plan.slug }}</p>
          </div>
          <span class="inline-block px-3 py-1 rounded-full text-xs font-medium"
            :class="plan.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
          >
            {{ plan.is_active ? 'Active' : 'Inactive' }}
          </span>
        </div>

        <div class="border-t border-gray-200 pt-4">
          <p class="text-2xl font-bold text-gray-900">
            ${{ parseFloat(plan.price).toFixed(2) }}
            <span class="text-sm text-gray-600 font-normal">/{{ plan.billing_cycle }}</span>
          </p>
        </div>

        <div class="space-y-3 text-sm text-gray-600 py-4">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m0 0L4 7m8 4v10l8-4v-10L12 11m0 0L4 7" />
            </svg>
            <span>Workspaces: <strong class="text-gray-900">{{ plan.max_workspaces === 0 ? 'Unlimited' : plan.max_workspaces }}</strong></span>
          </div>
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3.654a1 1 0 01-.894-1.447l5.394-7.72A6 6 0 1113.16 21z" />
            </svg>
            <span>Members/workspace: <strong class="text-gray-900">{{ plan.max_members_per_workspace === 0 ? 'Unlimited' : plan.max_members_per_workspace }}</strong></span>
          </div>
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span>Projects/workspace: <strong class="text-gray-900">{{ plan.max_projects_per_workspace === 0 ? 'Unlimited' : plan.max_projects_per_workspace }}</strong></span>
          </div>
        </div>

        <div v-if="plan.features && plan.features.length" class="space-y-2 py-4 border-t border-gray-200">
          <p class="font-medium text-gray-700 text-xs uppercase tracking-wide">Features</p>
          <ul class="space-y-1">
            <li v-for="(feature, idx) in plan.features" :key="idx" class="text-sm text-gray-600 flex items-center gap-2">
              <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
              {{ feature }}
            </li>
          </ul>
        </div>

        <div class="flex gap-2 pt-4 border-t border-gray-200">
          <button
            @click="editPlan(plan)"
            class="flex-1 px-3 py-2 text-sm bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition font-medium flex items-center justify-center gap-1"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
          </button>
          <button
            @click="confirmDelete(plan)"
            class="flex-1 px-3 py-2 text-sm bg-red-50 text-red-600 rounded hover:bg-red-100 transition font-medium flex items-center justify-center gap-1"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Add New Plan -->
    <div class="bg-white rounded-lg shadow p-8">
      <div class="flex items-center gap-3 mb-6">
        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <h2 class="text-xl font-semibold text-gray-900">{{ editingPlanId ? 'Edit Plan' : 'Add New Plan' }}</h2>
      </div>

      <form @submit.prevent="savePlan" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Plan Name</label>
            <input
              v-model="newPlan.name"
              type="text"
              placeholder="e.g. Pro"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Price (USD)</label>
            <input
              v-model.number="newPlan.price"
              type="number"
              min="0"
              step="0.01"
              placeholder="29.99"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Billing Cycle</label>
            <select
              v-model="newPlan.billing_cycle"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            >
              <option value="monthly">Monthly</option>
              <option value="yearly">Yearly</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Max Workspaces (0 = unlimited)</label>
            <input
              v-model.number="newPlan.max_workspaces"
              type="number"
              min="0"
              placeholder="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Max Members per Workspace (0 = unlimited)</label>
            <input
              v-model.number="newPlan.max_members_per_workspace"
              type="number"
              min="0"
              placeholder="10"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Max Projects per Workspace (0 = unlimited)</label>
            <input
              v-model.number="newPlan.max_projects_per_workspace"
              type="number"
              min="0"
              placeholder="5"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            />
          </div>

          <div class="md:col-span-2">
            <label class="flex items-center gap-3 cursor-pointer">
              <input
                v-model="newPlan.is_active"
                type="checkbox"
                class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
              />
              <span class="text-sm font-medium text-gray-700">Plan is active</span>
            </label>
          </div>
        </div>

        <div class="border-t border-gray-200 pt-6">
          <label class="block text-sm font-medium text-gray-700 mb-3">Features</label>
          <div class="flex gap-2 mb-3">
            <input
              v-model="newFeatureInput"
              type="text"
              placeholder="Enter a feature..."
              @keyup.enter="addFeature"
              class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            />
            <button
              type="button"
              @click="addFeature"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium flex items-center gap-1"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Add
            </button>
          </div>
          <ul class="space-y-2 max-h-40 overflow-y-auto">
            <li v-for="(feature, idx) in newPlan.features" :key="idx" class="flex items-center justify-between text-sm text-gray-700 bg-gray-50 px-3 py-2 rounded">
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                {{ feature }}
              </span>
              <button
                type="button"
                @click="removeFeature(idx)"
                class="text-red-500 hover:text-red-700 ml-2"
                title="Remove feature"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </li>
            <li v-if="newPlan.features.length === 0" class="text-sm text-gray-400 italic px-3 py-2">
              No features added yet. Add one above.
            </li>
          </ul>
        </div>

        <div class="flex justify-end gap-3 border-t border-gray-200 pt-6">
          <button
            v-if="editingPlanId"
            type="button"
            @click="cancelEdit"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ editingPlanId ? 'Update Plan' : 'Create Plan' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Confirmation Modal -->
    <div v-if="showConfirmDelete" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
      <div class="bg-white rounded-lg shadow-lg max-w-sm w-full mx-4 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Delete Plan</h2>
        <p class="text-gray-600 mb-6">
          Are you sure you want to delete the <strong>{{ planToDelete?.name }}</strong> plan? This cannot be undone.
        </p>
        <div class="flex gap-3 justify-end">
          <button
            @click="showConfirmDelete = false"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium"
          >
            Cancel
          </button>
          <button
            @click="deletePlan"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'

defineProps({
  plans: Array,
})

const editingPlanId = ref(null)
const newFeatureInput = ref('')
const showConfirmDelete = ref(false)
const planToDelete = ref(null)

const newPlan = reactive({
  name: '',
  price: 0,
  billing_cycle: 'monthly',
  max_workspaces: 0,
  max_members_per_workspace: 0,
  max_projects_per_workspace: 0,
  is_active: true,
  features: [],
})

const featuresList = computed({
  get: () => newPlan.features,
  set: (value) => {
    newPlan.features = value
  },
})

const editPlan = (plan) => {
  editingPlanId.value = plan.id
  newPlan.name = plan.name
  newPlan.price = parseFloat(plan.price)
  newPlan.billing_cycle = plan.billing_cycle
  newPlan.max_workspaces = plan.max_workspaces
  newPlan.max_members_per_workspace = plan.max_members_per_workspace
  newPlan.max_projects_per_workspace = plan.max_projects_per_workspace
  newPlan.is_active = plan.is_active
  newPlan.features = [...(plan.features || [])]
  newFeatureInput.value = ''
}

const addFeature = () => {
  const trimmed = newFeatureInput.value.trim()
  if (trimmed && !newPlan.features.includes(trimmed)) {
    newPlan.features.push(trimmed)
    newFeatureInput.value = ''
  }
}

const removeFeature = (index) => {
  newPlan.features.splice(index, 1)
}

const cancelEdit = () => {
  editingPlanId.value = null
  Object.assign(newPlan, {
    name: '',
    price: 0,
    billing_cycle: 'monthly',
    max_workspaces: 0,
    max_members_per_workspace: 0,
    max_projects_per_workspace: 0,
    is_active: true,
    features: [],
  })
  newFeatureInput.value = ''
}

const savePlan = () => {
  const data = { ...newPlan }

  if (editingPlanId.value) {
    router.patch(`/admin/settings/plans/${editingPlanId.value}`, data, {
      onSuccess: cancelEdit,
    })
  } else {
    router.post('/admin/settings/plans', data, {
      onSuccess: cancelEdit,
    })
  }
}

const confirmDelete = (plan) => {
  planToDelete.value = plan
  showConfirmDelete.value = true
}

const deletePlan = () => {
  router.delete(`/admin/settings/plans/${planToDelete.value.id}`, {
    onSuccess: () => {
      showConfirmDelete.value = false
      planToDelete.value = null
    },
  })
}
</script>
