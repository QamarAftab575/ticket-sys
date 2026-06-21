<template>
  <div class="space-y-6">
    <!-- Existing Plans Grid -->
    <div v-if="plans.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="plan in plans" :key="plan.id" class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="flex justify-between items-start">
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

        <div class="border-t border-gray-200 pt-3">
          <p class="text-2xl font-bold text-gray-900">
            ${{ parseFloat(plan.price).toFixed(2) }}
            <span class="text-sm text-gray-600 font-normal">/{{ plan.billing_cycle }}</span>
          </p>
        </div>

        <div class="space-y-2 text-sm text-gray-600">
          <p>ðŸ“ Workspaces: <strong class="text-gray-900">{{ plan.max_workspaces === 0 ? 'âˆž Unlimited' : plan.max_workspaces }}</strong></p>
          <p>ðŸ‘¥ Members/workspace: <strong class="text-gray-900">{{ plan.max_members_per_workspace === 0 ? 'âˆž Unlimited' : plan.max_members_per_workspace }}</strong></p>
          <p>ðŸ“Š Projects/workspace: <strong class="text-gray-900">{{ plan.max_projects_per_workspace === 0 ? 'âˆž Unlimited' : plan.max_projects_per_workspace }}</strong></p>
        </div>

        <div v-if="plan.features && plan.features.length" class="space-y-1 text-xs">
          <p class="font-medium text-gray-700">Features:</p>
          <ul class="list-disc list-inside text-gray-600 space-y-0.5">
            <li v-for="(feature, idx) in plan.features" :key="idx">{{ feature }}</li>
          </ul>
        </div>

        <div class="flex gap-2 pt-2 border-t border-gray-200">
          <button
            @click="editPlan(plan)"
            class="flex-1 px-3 py-2 text-sm bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition"
          >
            Edit
          </button>
          <button
            @click="confirmDelete(plan)"
            class="flex-1 px-3 py-2 text-sm bg-red-50 text-red-600 rounded hover:bg-red-100 transition"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Add New Plan -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ editingPlanId ? 'âœï¸ Edit Plan' : 'âž• Add New Plan' }}</h2>

      <form @submit.prevent="savePlan" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Plan Name</label>
            <input
              v-model="newPlan.name"
              type="text"
              placeholder="e.g. Pro"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Price (USD)</label>
            <input
              v-model.number="newPlan.price"
              type="number"
              min="0"
              step="0.01"
              placeholder="29.99"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Billing Cycle</label>
            <select
              v-model="newPlan.billing_cycle"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            >
              <option value="monthly">Monthly</option>
              <option value="yearly">Yearly</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Max Workspaces (0 = unlimited)</label>
            <input
              v-model.number="newPlan.max_workspaces"
              type="number"
              min="0"
              placeholder="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Max Members per Workspace (0 = unlimited)</label>
            <input
              v-model.number="newPlan.max_members_per_workspace"
              type="number"
              min="0"
              placeholder="10"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Max Projects per Workspace (0 = unlimited)</label>
            <input
              v-model.number="newPlan.max_projects_per_workspace"
              type="number"
              min="0"
              placeholder="5"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
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

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Features</label>
          <div class="flex gap-2 mb-2">
            <input
              v-model="newFeatureInput"
              type="text"
              placeholder="Enter a feature..."
              @keyup.enter="addFeature"
              class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            />
            <button
              type="button"
              @click="addFeature"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium"
            >
              Add
            </button>
          </div>
          <ul class="space-y-1 max-h-32 overflow-y-auto">
            <li v-for="(feature, idx) in newPlan.features" :key="idx" class="flex items-center justify-between text-xs text-gray-600 bg-gray-50 px-3 py-1.5 rounded">
              <span>{{ feature }}</span>
              <button
                type="button"
                @click="removeFeature(idx)"
                class="text-red-500 hover:text-red-700 ml-2"
                title="Remove feature"
              >
                Ã—
              </button>
            </li>
            <li v-if="newPlan.features.length === 0" class="text-xs text-gray-400 italic px-3 py-1">
              No features added yet. Add one above.
            </li>
          </ul>
        </div>

        <div class="flex justify-end gap-3">
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
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium"
          >
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
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium"
          >
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

