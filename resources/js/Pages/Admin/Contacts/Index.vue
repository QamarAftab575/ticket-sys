<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center gap-4">
        <div class="min-w-0">
          <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">{{ $t('contact_messages') }}</h1>
          <p class="text-slate-600 text-sm sm:text-base mt-1">{{ $t('view_manage_contacts') }}</p>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-4 border border-slate-200">
          <div class="text-2xl font-bold text-slate-900">{{ stats.total }}</div>
          <div class="text-xs text-slate-600 mt-1">{{ $t('total_contacts') }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-slate-200">
          <div class="text-2xl font-bold text-blue-600">{{ stats.new }}</div>
          <div class="text-xs text-slate-600 mt-1">{{ $t('new_status') }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-slate-200">
          <div class="text-2xl font-bold text-yellow-600">{{ stats.read }}</div>
          <div class="text-xs text-slate-600 mt-1">{{ $t('read_status') }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-slate-200">
          <div class="text-2xl font-bold text-green-600">{{ stats.replied }}</div>
          <div class="text-xs text-slate-600 mt-1">{{ $t('replied_status') }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-slate-200">
          <div class="text-2xl font-bold text-slate-400">{{ stats.closed }}</div>
          <div class="text-xs text-slate-600 mt-1">{{ $t('closed_status') }}</div>
        </div>
      </div>

      <!-- Mobile Filter Toggle Button -->
      <div class="md:hidden">
        <button
          @click="showFilters = !showFilters"
          class="w-full bg-white border border-slate-200 rounded-lg px-4 py-3 flex items-center justify-between hover:bg-slate-50 transition-colors"
        >
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <span class="text-sm font-medium text-slate-700">Filters</span>
          </div>
          <svg 
            :class="['w-5 h-5 text-slate-600 transition-transform', showFilters ? 'rotate-180' : '']" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
          </svg>
        </button>
      </div>

      <!-- Filters -->
      <transition name="fade">
        <div 
          v-if="showFilters || isDesktop"
          class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border border-slate-200"
        >
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Search -->
            <div>
              <label class="block text-xs sm:text-sm font-medium text-slate-700 mb-1 sm:mb-2">{{ $t('search') }}</label>
              <input
                v-model="searchQuery"
                type="text"
                :placeholder="$t('search_placeholder')"
                @input="updateSearch"
                class="w-full px-3 sm:px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
              />
            </div>

            <!-- Status Filter -->
            <div>
              <label class="block text-xs sm:text-sm font-medium text-slate-700 mb-1 sm:mb-2">{{ $t('status') }}</label>
              <select
                v-model="statusFilter"
                @change="updateFilters"
                class="w-full px-3 sm:px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
              >
                <option value="">{{ $t('all_status') }}</option>
                <option value="new">{{ $t('new_status') }}</option>
                <option value="read">{{ $t('read_status') }}</option>
                <option value="replied">{{ $t('replied_status') }}</option>
                <option value="closed">{{ $t('closed_status') }}</option>
              </select>
            </div>

            <!-- From Date -->
            <div>
              <label class="block text-xs sm:text-sm font-medium text-slate-700 mb-1 sm:mb-2">{{ $t('from_date') }}</label>
              <input
                v-model="fromDate"
                type="date"
                @change="updateFilters"
                class="w-full px-3 sm:px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
              />
            </div>

            <!-- To Date -->
            <div>
              <label class="block text-xs sm:text-sm font-medium text-slate-700 mb-1 sm:mb-2">{{ $t('to_date') }}</label>
              <input
                v-model="toDate"
                type="date"
                @change="updateFilters"
                class="w-full px-3 sm:px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
              />
            </div>
          </div>
        </div>
      </transition>

      <!-- Contacts Table -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-slate-200">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-semibold text-slate-900">{{ $t('table_name') }}</th>
                <th class="hidden sm:table-cell px-3 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-semibold text-slate-900">{{ $t('table_email') }}</th>
                <th class="hidden md:table-cell px-3 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-semibold text-slate-900">{{ $t('table_subject') }}</th>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-semibold text-slate-900">{{ $t('table_status') }}</th>
                <th class="hidden lg:table-cell px-3 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-semibold text-slate-900">{{ $t('table_date') }}</th>
                <th class="px-3 sm:px-6 py-3 sm:py-4 text-right text-xs sm:text-sm font-semibold text-slate-900">{{ $t('table_action') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr v-for="contact in contacts.data" :key="contact.id" class="hover:bg-slate-50 transition-colors cursor-pointer">
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm">
                  <div class="font-medium text-slate-900">{{ contact.name }}</div>
                </td>
                <td class="hidden sm:table-cell px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-600">{{ contact.email }}</td>
                <td class="hidden md:table-cell px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-600 truncate">{{ contact.subject }}</td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm">
                  <span :class="getStatusBadgeClass(contact.status)">
                    {{ capitalizeStatus(contact.status) }}
                  </span>
                </td>
                <td class="hidden lg:table-cell px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-600">{{ formatDate(contact.created_at) }}</td>
                <td class="px-3 sm:px-6 py-3 sm:py-4 text-right">
                  <Link
                    :href="`/admin/contacts/${contact.id}`"
                    class="inline-flex items-center gap-2 px-3 py-2 text-xs sm:text-sm font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors"
                  >
                    {{ $t('view_action') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </Link>
                </td>
              </tr>
              <tr v-if="contacts.data.length === 0">
                <td colspan="6" class="px-3 sm:px-6 py-8 text-center text-slate-600">
                  {{ $t('no_contacts_found') }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="contacts.last_page > 1" class="bg-slate-50 border-t border-slate-200 px-3 sm:px-6 py-4 flex items-center justify-between">
          <div class="text-xs sm:text-sm text-slate-600">
            {{ $t('showing_results').replace('{from}', contacts.from).replace('{to}', contacts.to).replace('{total}', contacts.total) }}
          </div>
          <div class="flex gap-2">
            <Link
              v-if="contacts.prev_page_url"
              :href="contacts.prev_page_url"
              class="px-3 py-2 text-xs sm:text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors"
            >
              {{ $t('previous') }}
            </Link>
            <Link
              v-if="contacts.next_page_url"
              :href="contacts.next_page_url"
              class="px-3 py-2 text-xs sm:text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors"
            >
              {{ $t('next') }}
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  contacts: Object,
  stats: Object,
  filters: Object,
})

const page = usePage()

// Filter state
const searchQuery = ref(props.filters.search || '')
const statusFilter = ref(props.filters.status || '')
const fromDate = ref(props.filters.from_date || '')
const toDate = ref(props.filters.to_date || '')
const showFilters = ref(false)

// Responsive
const isDesktop = ref(window.innerWidth >= 768)
window.addEventListener('resize', () => {
  isDesktop.value = window.innerWidth >= 768
})

// Methods
const updateSearch = () => {
  updateFilters()
}

const updateFilters = () => {
  const url = new URL(window.location)
  url.searchParams.set('search', searchQuery.value)
  url.searchParams.set('status', statusFilter.value)
  url.searchParams.set('from_date', fromDate.value)
  url.searchParams.set('to_date', toDate.value)
  window.location.href = url.toString()
}

const capitalizeStatus = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const getStatusBadgeClass = (status) => {
  const baseClass = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold'
  const statusClasses = {
    'new': `${baseClass} bg-blue-100 text-blue-800`,
    'read': `${baseClass} bg-yellow-100 text-yellow-800`,
    'replied': `${baseClass} bg-green-100 text-green-800`,
    'closed': `${baseClass} bg-slate-100 text-slate-800`,
  }
  return statusClasses[status] || baseClass
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
