<template>
  <div class="flex items-center gap-1.5 w-full px-1 min-h-[24px]">

    <!-- system_assignee -->
    <template v-if="fieldType === 'system_assignee'">
      <template v-if="task.assignee">
        <Avatar :name="task.assignee.name" :src="task.assignee.avatar" size="xs" class="flex-shrink-0" />
        <span class="text-xs text-gray-700 truncate">{{ task.assignee.name }}</span>
      </template>
      <span v-else class="text-xs text-gray-400"> </span>
    </template>

    <!-- system_created_by -->
    <template v-else-if="fieldType === 'system_created_by'">
      <template v-if="task.creator">
        <Avatar :name="task.creator.name" :src="task.creator.avatar" size="xs" class="flex-shrink-0" />
        <span class="text-xs text-gray-700 truncate">{{ task.creator.name }}</span>
      </template>
      <span v-else class="text-xs text-gray-400"> </span>
    </template>

    <!-- system_completed_on -->
    <template v-else-if="fieldType === 'system_completed_on'">
      <span v-if="task.completed_at" class="text-xs text-gray-700">
        {{ formatDate(task.completed_at) }}
      </span>
      <span v-else class="text-xs text-gray-400"> </span>
    </template>

    <!-- system_created_on -->
    <template v-else-if="fieldType === 'system_created_on'">
      <span class="text-xs text-gray-700">{{ formatDate(task.created_at) }}</span>
    </template>

    <!-- system_last_modified_on -->
    <template v-else-if="fieldType === 'system_last_modified_on'">
      <span class="text-xs text-gray-700">{{ formatDate(task.updated_at) }}</span>
    </template>

    <!-- system_blocked_by: list of blocking task names -->
    <template v-else-if="fieldType === 'system_blocked_by'">
      <template v-if="task.dependencies?.length">
        <span class="text-xs text-gray-700 truncate">
          {{ task.dependencies.map(d => d.name).join(', ') }}
        </span>
      </template>
      <span v-else class="text-xs text-gray-400"> </span>
    </template>

    <!-- system_blocking -->
    <template v-else-if="fieldType === 'system_blocking'">
      <template v-if="task.dependents?.length">
        <span class="text-xs text-gray-700 truncate">
          {{ task.dependents.map(d => d.name).join(', ') }}
        </span>
      </template>
      <span v-else class="text-xs text-gray-400"> </span>
    </template>

    <!-- system_collaborators: show up to 3 member avatars -->
    <template v-else-if="fieldType === 'system_collaborators'">
      <template v-if="collaborators.length">
        <div class="flex -space-x-1">
          <Avatar
            v-for="m in collaborators.slice(0, 3)"
            :key="m.id"
            :name="m.name"
            :src="m.avatar"
            size="xs"
            class="border border-white flex-shrink-0"
            :title="m.name"
          />
          <span
            v-if="collaborators.length > 3"
            class="w-6 h-6 rounded-full bg-gray-200 border border-white flex items-center justify-center text-xs text-gray-600 flex-shrink-0"
          >
            +{{ collaborators.length - 3 }}
          </span>
        </div>
      </template>
      <span v-else class="text-xs text-gray-400"> </span>
    </template>

    <!-- fallback -->
    <span v-else class="text-xs text-gray-400"> </span>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import Avatar from '@/Components/Avatar.vue'

const props = defineProps({
  field:   { type: Object, required: true },
  task:    { type: Object, required: true },
  members: { type: Array,  default: () => [] },
})

const fieldType = computed(() => props.field.field_type)

/**
 * Collaborators = all project members who are not the assignee.
 * In phase 1 we just show all members as a proxy since there's no
 * dedicated collaborators relationship on tasks yet.
 */
const collaborators = computed(() => props.members.slice(0, 10))

function formatDate(value) {
  if (!value) return ' '
  const d = new Date(value)
  if (isNaN(d)) return ' '
  return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

