<template>
  <AppLayout :user-workspaces="userWorkspaces" :current-workspace="currentWorkspace">
    <MyTasksLayout />
  </AppLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import MyTasksLayout from '@/Components/MyTasks/Layout/MyTasksLayout.vue';

interface Props {
  userWorkspaces?: any[];
  currentWorkspace?: any;
}

const props = withDefaults(defineProps<Props>(), {
  userWorkspaces: () => [],
  currentWorkspace: null,
});
</script>