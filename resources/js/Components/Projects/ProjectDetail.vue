<template>
  <div class="space-y-6">
    <!-- Project Header -->
    <div class="border-b pb-6">
      <div class="flex justify-between items-start">
        <div>
          <h1 class="text-3xl font-bold">{{ project.name }}</h1>
          <p class="text-gray-600 mt-2">{{ project.description }}</p>
        </div>
        <div v-if="canEdit" class="flex gap-2">
          <Link
            :href="`/projects/${project.id}/edit`"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Edit
          </Link>
        </div>
      </div>

      <!-- Project Info -->
      <div class="grid grid-cols-2 gap-4 mt-6">
        <div>
          <label class="text-sm text-gray-600">Status</label>
          <p class="font-semibold">{{ formatStatus(project.status) }}</p>
        </div>
        <div>
          <label class="text-sm text-gray-600">Visibility</label>
          <p class="font-semibold">{{ formatVisibility(project.visibility) }}</p>
        </div>
        <div>
          <label class="text-sm text-gray-600">Project Lead</label>
          <p class="font-semibold">{{ project.manager.name }}</p>
        </div>
        <div v-if="project.start_date">
          <label class="text-sm text-gray-600">Start Date</label>
          <p class="font-semibold">{{ formatDate(project.start_date) }}</p>
        </div>
        <div v-if="project.target_date">
          <label class="text-sm text-gray-600">Target Date</label>
          <p class="font-semibold">{{ formatDate(project.target_date) }}</p>
        </div>
      </div>
    </div>

    <!-- Members Section -->
    <div>
      <h2 class="text-xl font-bold mb-4">Members</h2>
      <ProjectMemberList :project="project" :can-manage="canManageMembers" />
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ProjectMemberList from './ProjectMemberList.vue';

const props = defineProps({
  project: Object,
  canEdit: Boolean,
  canManageMembers: Boolean,
});

const formatStatus = (status) => {
  const statusMap = {
    on_track: 'On Track',
    at_risk: 'At Risk',
    off_track: 'Off Track',
    archived: 'Archived',
  };
  return statusMap[status] || status;
};

const formatVisibility = (visibility) => {
  const visibilityMap = {
    public_to_team: 'Public to Team',
    private_to_members: 'Private to Members',
  };
  return visibilityMap[visibility] || visibility;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};
</script>
