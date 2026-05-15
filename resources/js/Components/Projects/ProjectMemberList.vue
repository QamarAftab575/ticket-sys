<template>
  <div class="space-y-4">
    <div v-if="members.length === 0" class="text-gray-500 text-center py-8">
      No members assigned yet
    </div>

    <div v-else class="space-y-2">
      <div
        v-for="member in members"
        :key="member.id"
        class="flex items-center justify-between p-4 border rounded-lg"
      >
        <div>
          <p class="font-semibold">{{ member.name }}</p>
          <p class="text-sm text-gray-600">
            Role: <strong>{{ formatRole(member.pivot.role) }}</strong>
            | Assigned: <strong>{{ formatDate(member.pivot.assigned_at) }}</strong>
          </p>
        </div>
        <button
          v-if="canManage"
          @click="removeMember(member.id)"
          class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200"
        >
          Remove
        </button>
      </div>
    </div>

    <div v-if="canManage" class="mt-6 pt-6 border-t">
      <h3 class="font-semibold mb-4">Add Member</h3>
      <form @submit.prevent="addMember" class="flex gap-2">
        <select
          v-model="selectedUserId"
          class="flex-1 px-4 py-2 border rounded-lg"
        >
          <option value="">Select a team member...</option>
          <option v-for="user in availableUsers" :key="user.id" :value="user.id">
            {{ user.name }}
          </option>
        </select>
        <button
          type="submit"
          :disabled="!selectedUserId"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
        >
          Add
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  project: Object,
  canManage: Boolean,
  teamMembers: Array,
});

const selectedUserId = ref('');

const members = computed(() => {
  return props.project.members || [];
});

const availableUsers = computed(() => {
  const memberIds = members.value.map(m => m.id);
  return (props.teamMembers || []).filter(user => !memberIds.includes(user.id));
});

const formatRole = (role) => {
  const roleMap = {
    commenter: 'Commenter',
    editor: 'Editor',
  };
  return roleMap[role] || role;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const addMember = () => {
  if (selectedUserId.value) {
    router.post(`/projects/${props.project.id}/members`, {
      user_id: selectedUserId.value,
    });
    selectedUserId.value = '';
  }
};

const removeMember = (userId) => {
  if (confirm('Are you sure you want to remove this member?')) {
    router.delete(`/projects/${props.project.id}/members/${userId}`);
  }
};
</script>
