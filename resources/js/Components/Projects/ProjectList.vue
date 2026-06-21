<template>
  <div class="space-y-4">
    <!-- Filters -->
    <div class="flex gap-4 mb-6">
      <input
        v-model="filters.search"
        type="text"
        placeholder="Search projects..."
        class="px-4 py-2 border rounded-lg"
        @input="applyFilters"
      />
      <select
        v-model="filters.status"
        class="px-4 py-2 border rounded-lg"
        @change="applyFilters"
      >
        <option value="">All Statuses</option>
        <option value="on_track">On Track</option>
        <option value="at_risk">At Risk</option>
        <option value="off_track">Off Track</option>
        <option value="archived">Archived</option>
      </select>
    </div>

    <!-- Projects Grid -->
    <div class="grid gap-4">
      <div
        v-for="project in projects"
        :key="project.id"
        class="border rounded-lg p-4 hover:shadow-lg transition"
      >
        <Link :href="`/projects/${project.id}`" class="block">
          <div class="flex items-start gap-3 mb-2">
            <!-- Icon -->
            <span v-if="project.icon" class="text-2xl flex-shrink-0">
              {{ project.icon }}
            </span>

            <!-- Color dot -->
            <div
              v-if="project.color"
              :style="{ backgroundColor: project.color }"
              class="w-4 h-4 rounded-full flex-shrink-0 mt-1"
            />

            <!-- Title -->
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <h3 class="text-lg font-semibold text-blue-600 hover:underline">
                  {{ project.name }}
                </h3>
                <!-- Status badge -->
                <span
                  v-if="project.status"
                  :class="[
                    'px-2 py-1 rounded text-xs font-medium',
                    getStatusBadgeClass(project.status)
                  ]"
                >
                  {{ formatStatus(project.status) }}
                </span>
                <!-- Privacy icon -->
                <span
                  v-if="project.privacy === 'private'"
                  class="text-sm"
                  title="Private"
                >
                  ðŸ”’
                </span>
              </div>
            </div>
          </div>
        </Link>

        <p class="text-gray-600 text-sm mt-1">{{ project.description }}</p>
        <div class="flex gap-4 mt-4 text-sm text-gray-500 flex-wrap">
          <span>Lead: <strong>{{ project.manager?.name }}</strong></span>
          <span>Owner: <strong>{{ project.owner?.name }}</strong></span>
          <span>Members: <strong>{{ project.members?.length || 0 }}</strong></span>
          <span v-if="project.archived_at" class="text-yellow-600">Archived</span>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="flex justify-center gap-2 mt-6">
      <Link
        v-if="pagination.prev_page_url"
        :href="pagination.prev_page_url"
        class="px-4 py-2 border rounded-lg hover:bg-gray-100"
      >
        Previous
      </Link>
      <Link
        v-if="pagination.next_page_url"
        :href="pagination.next_page_url"
        class="px-4 py-2 border rounded-lg hover:bg-gray-100"
      >
        Next
      </Link>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  projects: Array,
  pagination: Object,
  filters: Object,
});

const filters = ref({ search: props.filters?.search || '', status: props.filters?.status || '' });

let searchTimeout = null

const applyFilters = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    router.get('/projects', {
      search: filters.value.search || undefined,
      status: filters.value.status || undefined,
    }, { preserveState: true, replace: true })
  }, 300)
}

const formatStatus = (status) => {
  const statusMap = {
    on_track: 'On Track',
    at_risk: 'At Risk',
    off_track: 'Off Track',
    on_hold: 'On Hold',
    complete: 'Complete',
    archived: 'Archived',
  }
  return statusMap[status] || status
}

const getStatusBadgeClass = (status) => {
  const classes = {
    on_track: 'bg-green-100 text-green-800',
    at_risk: 'bg-yellow-100 text-yellow-800',
    off_track: 'bg-red-100 text-red-800',
    on_hold: 'bg-gray-100 text-gray-800',
    complete: 'bg-blue-100 text-blue-800',
    archived: 'bg-gray-100 text-gray-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>

