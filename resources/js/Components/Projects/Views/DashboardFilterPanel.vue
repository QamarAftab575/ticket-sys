<template>
  <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
    <div class="flex flex-col gap-4">
      <!-- Header with title and reset button -->
      <div class="flex items-center justify-between">
        <h3 class="font-semibold text-gray-900 text-sm">Filters</h3>
        <button
          v-if="hasActiveFilters"
          @click="resetFilters"
          class="text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors"
        >
          Reset filters
        </button>
      </div>

      <!-- Filters Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Date Range Section -->
        <div class="space-y-2">
          <label class="block text-xs font-medium text-gray-700">Date Range</label>
          <div class="flex gap-2">
            <select
              v-model="selectedPreset"
              @change="applyPreset"
              class="flex-1 px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Custom</option>
              <option value="this_week">This Week</option>
              <option value="this_month">This Month</option>
              <option value="last_30_days">Last 30 Days</option>
              <option value="last_90_days">Last 90 Days</option>
              <option value="all_time">All Time</option>
            </select>
          </div>

          <!-- Custom Date Inputs -->
          <div v-if="selectedPreset === ''" class="flex gap-2">
            <div class="flex-1">
              <input
                v-model="dateFrom"
                type="date"
                placeholder="From"
                class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div class="flex-1">
              <input
                v-model="dateTo"
                type="date"
                placeholder="To"
                class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>
        </div>

        <!-- Project Members Section -->
        <div class="space-y-2">
          <label class="block text-xs font-medium text-gray-700">Assigned To</label>
          <div class="relative">
            <button
              @click="showMemberDropdown = !showMemberDropdown"
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-left focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white flex items-center justify-between"
            >
              <span v-if="selectedMembers.length === 0" class="text-gray-500">All members</span>
              <span v-else class="text-gray-900">
                {{ selectedMembers.length }} selected
              </span>
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
              </svg>
            </button>

            <!-- Dropdown Menu -->
            <div
              v-if="showMemberDropdown"
              class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded shadow-lg z-50"
            >
              <div class="p-2 space-y-1">
                <label class="flex items-center gap-2 px-3 py-2 hover:bg-gray-50 rounded cursor-pointer">
                  <input
                    type="checkbox"
                    :checked="selectedMembers.length === 0"
                    @change="toggleSelectAll"
                    class="rounded"
                  />
                  <span class="text-sm text-gray-700 font-medium">All members</span>
                </label>

                <div class="border-t border-gray-200 my-1" />

                <label
                  v-for="member in projectMembers"
                  :key="member.id"
                  class="flex items-center gap-2 px-3 py-2 hover:bg-gray-50 rounded cursor-pointer"
                >
                  <input
                    type="checkbox"
                    :checked="selectedMembers.includes(member.id)"
                    @change="toggleMember(member.id)"
                    class="rounded"
                  />
                  <img
                    v-if="member.avatar"
                    :src="member.avatar"
                    :alt="member.name"
                    class="w-5 h-5 rounded-full"
                  />
                  <div v-else class="w-5 h-5 rounded-full bg-gray-300 flex items-center justify-center text-xs font-bold text-white">
                    {{ member.name.charAt(0).toUpperCase() }}
                  </div>
                  <span class="text-sm text-gray-700">{{ member.name }}</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Apply Button -->
        <div class="flex items-end">
          <button
            @click="applyFilters"
            :disabled="isLoading"
            class="w-full px-4 py-2 bg-blue-600 text-white rounded text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            {{ isLoading ? 'Applying...' : 'Apply Filters' }}
          </button>
        </div>
      </div>

      <!-- Active Filters Display -->
      <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 pt-2 border-t border-gray-200">
        <div v-if="dateFrom || dateTo" class="flex items-center gap-2 px-3 py-1 bg-blue-50 border border-blue-200 rounded-full text-xs">
          <span class="font-medium">📅 Date:</span>
          <span>
            {{ formatDate(dateFrom) }} to {{ formatDate(dateTo) }}
          </span>
        </div>
        <div v-if="selectedMembers.length > 0" class="flex items-center gap-2 px-3 py-1 bg-blue-50 border border-blue-200 rounded-full text-xs">
          <span class="font-medium">👥 Members:</span>
          <span>{{ selectedMembers.length }} selected</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  projectMembers: Array,
  loading: Boolean,
})

const emit = defineEmits(['filters-changed'])

const dateFrom = ref('')
const dateTo = ref('')
const selectedMembers = ref([])
const selectedPreset = ref('')
const showMemberDropdown = ref(false)
const isLoading = ref(props.loading)

const hasActiveFilters = computed(() => {
  return dateFrom.value || dateTo.value || selectedMembers.value.length > 0
})

const applyPreset = (preset) => {
  const today = new Date()
  const startOfWeek = new Date(today)
  startOfWeek.setDate(today.getDate() - today.getDay())

  switch (preset) {
    case 'this_week':
      dateFrom.value = formatDateForInput(startOfWeek)
      dateTo.value = formatDateForInput(today)
      break
    case 'this_month':
      dateFrom.value = formatDateForInput(new Date(today.getFullYear(), today.getMonth(), 1))
      dateTo.value = formatDateForInput(today)
      break
    case 'last_30_days':
      const thirtyDaysAgo = new Date(today)
      thirtyDaysAgo.setDate(today.getDate() - 30)
      dateFrom.value = formatDateForInput(thirtyDaysAgo)
      dateTo.value = formatDateForInput(today)
      break
    case 'last_90_days':
      const ninetyDaysAgo = new Date(today)
      ninetyDaysAgo.setDate(today.getDate() - 90)
      dateFrom.value = formatDateForInput(ninetyDaysAgo)
      dateTo.value = formatDateForInput(today)
      break
    case 'all_time':
      dateFrom.value = ''
      dateTo.value = ''
      break
  }
}

const toggleMember = (memberId) => {
  const index = selectedMembers.value.indexOf(memberId)
  if (index > -1) {
    selectedMembers.value.splice(index, 1)
  } else {
    selectedMembers.value.push(memberId)
  }
}

const toggleSelectAll = () => {
  if (selectedMembers.value.length > 0) {
    selectedMembers.value = []
  } else {
    selectedMembers.value = props.projectMembers.map(m => m.id)
  }
}

const applyFilters = () => {
  emit('filters-changed', {
    dateFrom: dateFrom.value,
    dateTo: dateTo.value,
    memberIds: selectedMembers.value,
  })
}

const resetFilters = () => {
  dateFrom.value = ''
  dateTo.value = ''
  selectedMembers.value = []
  selectedPreset.value = ''
  applyFilters()
}

const formatDate = (dateString) => {
  if (!dateString) return 'Any'
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

const formatDateForInput = (date) => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

watch(() => props.loading, (newVal) => {
  isLoading.value = newVal
})
</script>
