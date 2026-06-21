<template>
  <AppLayout :user-workspaces="userWorkspaces" :current-workspace="currentWorkspace">
    <MyTasksLayout
      :saved-preferences="savedPreferences"
      :my-tasks-sections="myTasksSections"
      :workspace-members="workspaceMembers"
    />
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import MyTasksLayout from '@/Components/MyTasks/Layout/MyTasksLayout.vue';

interface MyTasksSection {
  id: string;
  name: string;
  position: number;
}

interface Props {
  userWorkspaces?: any[];
  currentWorkspace?: any;
  savedPreferences?: {
    sort?: any[];
    grouping?: string;
    section_order?: string[];
    collapsed_sections?: string[];
    filters?: any;
  } | null;
  myTasksSections?: MyTasksSection[];
  workspaceMembers?: any[];
}

const props = withDefaults(defineProps<Props>(), {
  userWorkspaces: () => [],
  currentWorkspace: null,
  savedPreferences: null,
  myTasksSections: () => [],
  workspaceMembers: () => [],
});
</script>

