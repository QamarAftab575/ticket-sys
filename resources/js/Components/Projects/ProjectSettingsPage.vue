<template>
  <div class="project-settings-page">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Project Settings</h1>
      <p class="text-gray-600 mt-1">Manage all project configurations</p>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-6">
      <div class="flex gap-8">
        <button
          v-for="tab in tabs"
          :key="tab"
          @click="activeTab = tab"
          :class="[
            'px-4 py-3 font-medium border-b-2 transition-colors',
            activeTab === tab
              ? 'border-blue-600 text-blue-600'
              : 'border-transparent text-gray-600 hover:text-gray-900'
          ]"
        >
          {{ formatTabName(tab) }}
        </button>
      </div>
    </div>

    <!-- Tab content -->
    <div class="space-y-6">
      <!-- General Tab -->
      <div v-if="activeTab === 'general'" class="space-y-6">
        <form @submit.prevent="submitGeneral" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Project Name</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              v-model="form.description"
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <ProjectColorPicker v-model="form.color" />
            <ProjectIconPicker v-model="form.icon" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
              <option value="on_track">On Track</option>
              <option value="at_risk">At Risk</option>
              <option value="off_track">Off Track</option>
              <option value="on_hold">On Hold</option>
              <option value="complete">Complete</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Owner</label>
            <select v-model="form.owner_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
              <option v-for="member in project.members" :key="member.id" :value="member.id">
                {{ member.name }}
              </option>
            </select>
          </div>

          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Save Changes
          </button>
        </form>
      </div>

      <!-- Members Tab -->
      <div v-if="activeTab === 'members'" class="space-y-4">
        <ProjectMemberList :project="project" :can-manage="true" />
      </div>

      <!-- Privacy Tab -->
      <div v-if="activeTab === 'privacy'" class="space-y-4">
        <form @submit.prevent="submitPrivacy" class="space-y-4">
          <ProjectPrivacySelector
            v-model="form.privacy"
            :members="project.members"
            :selected-members="form.member_ids"
            @update:selectedMembers="form.member_ids = $event"
          />

          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Save Privacy Settings
          </button>
        </form>
      </div>

      <!-- Archive/Delete Tab -->
      <div v-if="activeTab === 'archive'" class="space-y-4">
        <div class="border border-yellow-200 bg-yellow-50 p-4 rounded-lg">
          <h3 class="font-semibold text-yellow-900 mb-2">Archive Project</h3>
          <p class="text-sm text-yellow-800 mb-4">
            Archived projects are hidden from the sidebar but can be restored later.
          </p>
          <button
            v-if="!project.archived_at"
            @click="archiveProject"
            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700"
          >
            Archive Project
          </button>
          <button
            v-else
            @click="unarchiveProject"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
          >
            Unarchive Project
          </button>
        </div>

        <div class="border border-red-200 bg-red-50 p-4 rounded-lg">
          <h3 class="font-semibold text-red-900 mb-2">Delete Project</h3>
          <p class="text-sm text-red-800 mb-4">
            Deleting a project is permanent and cannot be undone. All tasks and data will be deleted.
          </p>
          <button
            @click="deleteProject"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
          >
            Delete Project
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import ProjectColorPicker from './ProjectColorPicker.vue'
import ProjectIconPicker from './ProjectIconPicker.vue'
import ProjectPrivacySelector from './ProjectPrivacySelector.vue'
import ProjectMemberList from './ProjectMemberList.vue'

const props = defineProps({
  project: Object,
})

const activeTab = ref('general')
const tabs = ['general', 'members', 'privacy', 'archive']

const form = reactive({
  name: props.project.name,
  description: props.project.description,
  color: props.project.color,
  icon: props.project.icon,
  status: props.project.status,
  owner_id: props.project.owner_id,
  privacy: props.project.privacy,
  member_ids: props.project.members?.map(m => m.id) || [],
})

const formatTabName = (tab) => {
  const names = {
    general: 'General',
    members: 'Members',
    privacy: 'Privacy',
    archive: 'Archive/Delete',
  }
  return names[tab] || tab
}

const submitGeneral = () => {
  router.put(`/projects/${props.project.id}/settings/general`, {
    name: form.name,
    description: form.description,
    color: form.color,
    icon: form.icon,
    status: form.status,
    owner_id: form.owner_id,
  })
}

const submitPrivacy = () => {
  router.put(`/projects/${props.project.id}/settings/privacy`, {
    privacy: form.privacy,
    member_ids: form.member_ids,
  })
}

const archiveProject = () => {
  if (confirm('Are you sure you want to archive this project?')) {
    router.post(`/projects/${props.project.id}/archive`)
  }
}

const unarchiveProject = () => {
  if (confirm('Are you sure you want to unarchive this project?')) {
    router.post(`/projects/${props.project.id}/unarchive`)
  }
}

const deleteProject = () => {
  const projectName = prompt('Type the project name to confirm deletion:')
  if (projectName === props.project.name) {
    router.post(`/projects/${props.project.id}/delete`, { project_name: projectName })
  } else if (projectName !== null) {
    alert('Project name does not match')
  }
}
</script>

