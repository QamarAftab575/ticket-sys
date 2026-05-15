<template>
  <form @submit.prevent="submitForm" class="space-y-6">
    <!-- Name -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Project Name *</label>
      <input
        v-model="form.name"
        type="text"
        required
        maxlength="255"
        class="mt-1 block w-full px-4 py-2 border rounded-lg"
        :class="{ 'border-red-500': errors.name }"
      />
      <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
    </div>

    <!-- Description -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Description</label>
      <textarea
        v-model="form.description"
        class="mt-1 block w-full px-4 py-2 border rounded-lg"
        rows="4"
      ></textarea>
    </div>

    <!-- Status -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Status *</label>
      <select
        v-model="form.status"
        required
        class="mt-1 block w-full px-4 py-2 border rounded-lg"
        :class="{ 'border-red-500': errors.status }"
      >
        <option value="on_track">On Track</option>
        <option value="at_risk">At Risk</option>
        <option value="off_track">Off Track</option>
        <option value="archived">Archived</option>
      </select>
      <p v-if="errors.status" class="text-red-500 text-sm mt-1">{{ errors.status }}</p>
    </div>

    <!-- Visibility -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Visibility *</label>
      <select
        v-model="form.visibility"
        required
        class="mt-1 block w-full px-4 py-2 border rounded-lg"
        :class="{ 'border-red-500': errors.visibility }"
      >
        <option value="public_to_team">Public to Team</option>
        <option value="private_to_members">Private to Members</option>
      </select>
      <p v-if="errors.visibility" class="text-red-500 text-sm mt-1">{{ errors.visibility }}</p>
    </div>

    <!-- Start Date -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Start Date</label>
      <input
        v-model="form.start_date"
        type="date"
        class="mt-1 block w-full px-4 py-2 border rounded-lg"
        :class="{ 'border-red-500': errors.start_date }"
      />
      <p v-if="errors.start_date" class="text-red-500 text-sm mt-1">{{ errors.start_date }}</p>
    </div>

    <!-- Target Date -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Target Date</label>
      <input
        v-model="form.target_date"
        type="date"
        class="mt-1 block w-full px-4 py-2 border rounded-lg"
        :class="{ 'border-red-500': errors.target_date }"
      />
      <p v-if="errors.target_date" class="text-red-500 text-sm mt-1">{{ errors.target_date }}</p>
    </div>

    <!-- Project Lead -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Project Lead *</label>
      <select
        v-model="form.manager_id"
        required
        class="mt-1 block w-full px-4 py-2 border rounded-lg"
        :class="{ 'border-red-500': errors.manager_id }"
      >
        <option value="">Select a team member</option>
        <option v-for="member in teamMembers" :key="member.id" :value="member.id">
          {{ member.name }}
        </option>
      </select>
      <p v-if="errors.manager_id" class="text-red-500 text-sm mt-1">{{ errors.manager_id }}</p>
    </div>

    <!-- Members (as tags) -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Initial Members</label>
      <div class="mt-2 space-y-2">
        <div class="flex flex-wrap gap-2 mb-3">
          <div
            v-for="memberId in form.member_ids"
            :key="memberId"
            class="inline-flex items-center gap-2 px-3 py-1 bg-gray-200 text-gray-800 rounded-full text-sm"
          >
            {{ getTeamMemberName(memberId) }}
            <button
              type="button"
              @click="removeMember(memberId)"
              class="text-gray-600 hover:text-gray-800 font-bold"
            >
              ×
            </button>
          </div>
        </div>
        <select
          @change="addMember"
          class="block w-full px-4 py-2 border rounded-lg"
        >
          <option value="">+ Add Member</option>
          <option v-for="member in availableMembers" :key="member.id" :value="member.id">
            {{ member.name }}
          </option>
        </select>
      </div>
    </div>

    <!-- Color -->
    <div>
      <ProjectColorPicker v-model="form.color" />
    </div>

    <!-- Icon -->
    <div>
      <ProjectIconPicker v-model="form.icon" />
    </div>

    <!-- Privacy -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Privacy Level *</label>
      <select
        v-model="form.privacy"
        required
        class="mt-1 block w-full px-4 py-2 border rounded-lg"
        :class="{ 'border-red-500': errors.privacy }"
      >
        <option value="public_to_team">Public to Team</option>
        <option value="private">Private</option>
        <option value="specific_members">Specific Members</option>
      </select>
      <p v-if="errors.privacy" class="text-red-500 text-sm mt-1">{{ errors.privacy }}</p>
    </div>

    <!-- Submit -->
    <div class="flex gap-4">
      <button
        type="submit"
        :disabled="processing"
        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
      >
        {{ processing ? 'Saving...' : 'Save Project' }}
      </button>
      <Link
        href="/projects"
        class="px-6 py-2 border rounded-lg hover:bg-gray-100"
      >
        Cancel
      </Link>
    </div>
  </form>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import ProjectColorPicker from './ProjectColorPicker.vue';
import ProjectIconPicker from './ProjectIconPicker.vue';
import { ref, computed } from 'vue';

const props = defineProps({
  project: Object,
  teamMembers: Array,
});

const processing = ref(false);
const errors = ref({});

const form = useForm({
  name: props.project?.name || '',
  description: props.project?.description || '',
  status: props.project?.status || 'on_track',
  visibility: props.project?.visibility || 'public_to_team',
  start_date: props.project?.start_date || '',
  target_date: props.project?.target_date || '',
  manager_id: props.project?.manager_id || '',
  member_ids: props.project?.members?.map(m => m.id) || [],
  color: props.project?.color || null,
  icon: props.project?.icon || null,
  privacy: props.project?.privacy || 'public_to_team',
});

const availableMembers = computed(() => {
  return (props.teamMembers || []).filter(m => !form.member_ids.includes(m.id) && m.id !== form.manager_id);
});

const getTeamMemberName = (memberId) => {
  const member = props.teamMembers.find(m => m.id === memberId);
  return member?.name || 'Unknown';
};

const addMember = (event) => {
  const memberId = event.target.value;
  if (memberId && !form.member_ids.includes(memberId)) {
    form.member_ids.push(memberId);
  }
  event.target.value = '';
};

const removeMember = (memberId) => {
  form.member_ids = form.member_ids.filter(id => id !== memberId);
};

const submitForm = async () => {
  processing.value = true;
  try {
    if (props.project) {
      await form.put(`/projects/${props.project.id}`);
    } else {
      await form.post('/projects');
    }
  } catch (error) {
    errors.value = form.errors;
  } finally {
    processing.value = false;
  }
};
</script>
