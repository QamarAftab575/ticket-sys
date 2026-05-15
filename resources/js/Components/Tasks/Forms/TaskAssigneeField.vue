<template>
  <div class="relative">
    <button
      @click="showDropdown = !showDropdown"
      class="w-full px-3 py-2 text-left bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition flex items-center justify-between"
    >
      <div v-if="task.assignee" class="flex items-center gap-2">
        <img
          :src="task.assignee.avatar"
          :alt="task.assignee.name"
          class="w-5 h-5 rounded-full"
        />
        <span class="text-sm text-gray-900">{{ task.assignee.name }}</span>
      </div>
      <span v-else class="text-sm text-gray-500">Unassigned</span>
      <ChevronDownIcon class="w-4 h-4 text-gray-400" />
    </button>

    <!-- Dropdown -->
    <div
      v-if="showDropdown"
      class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-300 rounded-lg shadow-lg z-10"
      @click.stop
    >
      <button
        @click="handleAssign(null)"
        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 first:rounded-t-lg"
      >
        Unassigned
      </button>
      <button
        v-for="member in projectMembers"
        :key="member.id"
        @click="handleAssign(member.id)"
        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
      >
        <img
          :src="member.avatar"
          :alt="member.name"
          class="w-5 h-5 rounded-full"
        />
        {{ member.name }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { ChevronDownIcon } from '@heroicons/vue/24/outline';
import { useTasksStore } from '@/Stores/useTasksStore';
import type { TaskDetail } from '@/Types/tasks';
import { api } from '@/Services/api';

interface Props {
  task: TaskDetail;
  projectId?: string | null;
}

interface Emits {
  (e: 'update'): void;
}

const props = withDefaults(defineProps<Props>(), {
  projectId: null,
});

defineEmits<Emits>();

const tasksStore = useTasksStore();
const showDropdown = ref(false);
const projectMembers = ref<any[]>([]);

onMounted(async () => {
  if (props.projectId) {
    try {
      const response = await api.get(`/projects/${props.projectId}/members`);
      projectMembers.value = response.data;
    } catch (err) {
      console.error('Error fetching project members:', err);
    }
  }
});

async function handleAssign(memberId: string | null) {
  showDropdown.value = false;
  try {
    await tasksStore.updateTask(
      props.task.id,
      { assignee_id: memberId },
      props.projectId || null
    );
  } catch (err) {
    console.error('Error assigning task:', err);
  }
}
</script>
