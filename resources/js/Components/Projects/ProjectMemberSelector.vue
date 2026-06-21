<template>
  <div class="space-y-2">
    <div class="flex flex-wrap gap-2">
      <div
        v-for="memberId in modelValue"
        :key="memberId"
        class="flex items-center gap-2 bg-blue-100 px-3 py-1 rounded-full"
      >
        <span>{{ getMemberName(memberId) }}</span>
        <button
          type="button"
          @click="removeMember(memberId)"
          class="text-blue-600 hover:text-blue-800 font-bold"
        >
          Ã—
        </button>
      </div>
    </div>

    <select
      @change="addMember"
      class="block w-full px-4 py-2 border rounded-lg"
    >
      <option value="">Add a team member...</option>
      <option
        v-for="member in availableMembers"
        :key="member.id"
        :value="member.id"
      >
        {{ member.name }}
      </option>
    </select>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: Array,
  teamMembers: Array,
  excludedMember: String,
});

const emit = defineEmits(['update:modelValue']);

const availableMembers = computed(() => {
  return props.teamMembers.filter(
    member =>
      !props.modelValue.includes(member.id) &&
      member.id !== props.excludedMember
  );
});

const getMemberName = (memberId) => {
  const member = props.teamMembers.find(m => m.id === memberId);
  return member?.name || 'Unknown';
};

const addMember = (event) => {
  const memberId = event.target.value;
  if (memberId) {
    emit('update:modelValue', [...props.modelValue, memberId]);
    event.target.value = '';
  }
};

const removeMember = (memberId) => {
  emit('update:modelValue', props.modelValue.filter(id => id !== memberId));
};
</script>

