<template>
  <div class="fixed inset-0 bg-white flex flex-col">
    <!-- Top bar -->
    <div class="flex items-center justify-between px-8 py-4 border-b border-gray-200">
      <button @click="goBack" class="flex items-center gap-2 text-gray-500 hover:text-gray-700">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <button @click="goBack" class="text-gray-400 hover:text-gray-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Body -->
    <div class="flex flex-1 overflow-hidden">
      <!-- Left panel: form -->
      <div class="w-96 flex-shrink-0 px-10 py-10 flex flex-col gap-6 overflow-y-auto border-r border-gray-100">
        <h1 class="text-2xl font-semibold text-gray-900">New project</h1>

        <!-- Project name -->
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-gray-700">Project name</label>
          <input
            v-model="form.name"
            type="text"
            placeholder="e.g. Marketing Campaign"
            autofocus
            class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-400': errors.name }"
          />
          <p v-if="errors.name" class="text-xs text-red-500">{{ errors.name }}</p>
        </div>

        <!-- Privacy -->
        <div class="flex flex-col gap-2">
          <label class="text-sm font-medium text-gray-700">Privacy</label>

          <button
            type="button"
            @click="privacyOpen = !privacyOpen"
            class="flex items-center justify-between px-3 py-2 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50"
          >
            <span class="flex items-center gap-2">
              <svg v-if="form.visibility === 'public_to_team'" class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <svg v-else class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              {{ form.visibility === 'public_to_team' ? 'My workspace' : 'Private to members' }}
            </span>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Dropdown -->
          <div v-if="privacyOpen" class="border border-gray-200 rounded-md shadow-sm bg-white overflow-hidden">
            <button
              type="button"
              @click="selectVisibility('public_to_team')"
              :class="['w-full text-left px-4 py-3 hover:bg-gray-50 flex items-start gap-3', form.visibility === 'public_to_team' ? 'bg-blue-50' : '']"
            >
              <svg class="w-4 h-4 mt-0.5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <div>
                <p class="text-sm font-medium text-gray-900">My workspace</p>
                <p class="text-xs text-gray-500">Everyone in your workspace can find and access this project.</p>
              </div>
            </button>
            <button
              type="button"
              @click="selectVisibility('private_to_members')"
              :class="['w-full text-left px-4 py-3 hover:bg-gray-50 flex items-start gap-3 border-t border-gray-100', form.visibility === 'private_to_members' ? 'bg-blue-50' : '']"
            >
              <svg class="w-4 h-4 mt-0.5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              <div>
                <p class="text-sm font-medium text-gray-900">Private to members</p>
                <p class="text-xs text-gray-500">Only invited members can find and access this project.</p>
              </div>
            </button>
          </div>
        </div>

        <!-- Member selector (only when private) -->
        <div v-if="form.visibility === 'private_to_members'" class="flex flex-col gap-2">
          <label class="text-sm font-medium text-gray-700">Add members</label>
          
          <!-- Search input -->
          <div class="relative">
            <input
              v-model="memberSearch"
              type="text"
              placeholder="Search members..."
              class="w-full px-3 py-2 pl-9 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              @focus="showMemberDropdown = true"
            />
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          <!-- Member dropdown -->
          <div 
            v-if="showMemberDropdown && filteredMembers.length > 0" 
            class="border border-gray-200 rounded-md shadow-lg bg-white max-h-60 overflow-y-auto"
          >
            <button
              v-for="member in filteredMembers"
              :key="member.id"
              type="button"
              @click="addMemberById(member.id)"
              class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-3"
            >
              <Avatar :name="member.name" :src="member.avatar" size="sm" />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ member.name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ member.email }}</p>
              </div>
            </button>
          </div>

          <!-- Selected members -->
          <div v-if="form.member_ids.length > 0" class="flex flex-col gap-2 mt-1">
            <div
              v-for="id in form.member_ids"
              :key="id"
              class="flex items-center gap-2 px-2 py-1.5 bg-gray-50 rounded-md border border-gray-200"
            >
              <Avatar :name="getMemberName(id)" :src="getMemberAvatar(id)" size="xs" />
              <span class="flex-1 text-sm text-gray-700">{{ getMemberName(id) }}</span>
              <button 
                type="button" 
                @click="removeMember(id)" 
                class="text-gray-400 hover:text-gray-600"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Continue button -->
        <button
          @click="submit"
          :disabled="!form.name.trim() || isLoading"
          class="mt-auto px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
        >
          {{ isLoading ? 'Creating...' : 'Continue' }}
        </button>
      </div>

      <!-- Right panel: preview -->
      <div class="flex-1 bg-gray-50 flex items-start justify-center pt-16 px-8 overflow-y-auto">
        <div class="w-full max-w-2xl bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <!-- Project header preview -->
          <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">
                {{ form.name ? form.name.charAt(0).toUpperCase() : '?' }}
              </div>
              <span class="font-semibold text-gray-800 text-sm">{{ form.name || 'Project name' }}</span>
            </div>
            <!-- Fake tab bar -->
            <div class="flex gap-4 mt-3">
              <div v-for="i in 4" :key="i" class="h-2 rounded bg-gray-200" :style="{ width: (40 + i * 12) + 'px' }" />
            </div>
          </div>
          <!-- Fake task rows -->
          <div class="divide-y divide-gray-100">
            <div v-for="row in previewRows" :key="row.id" class="flex items-center gap-4 px-6 py-3">
              <div :class="['w-4 h-4 rounded-full border-2 flex-shrink-0', row.done ? 'bg-green-500 border-green-500' : 'border-gray-300']" />
              <div class="flex-1 h-2 rounded bg-gray-200" :style="{ width: row.w }" />
              <div class="w-6 h-6 rounded-full bg-gray-200 flex-shrink-0" />
              <div class="w-16 h-2 rounded bg-gray-200" />
              <div class="w-12 h-4 rounded flex-shrink-0" :style="{ backgroundColor: row.color }" />
              <div class="w-16 h-4 rounded flex-shrink-0" :style="{ backgroundColor: row.tag }" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Avatar from '@/Components/Avatar.vue'

const props = defineProps({
  teamMembers: { type: Array, default: () => [] },
  organizationId: String,
})

const page = usePage()
const errors = computed(() => page.props.errors || {})

const form = reactive({
  name: '',
  visibility: 'public_to_team',
  member_ids: [],
})

const privacyOpen = ref(false)
const isLoading = ref(false)
const memberSearch = ref('')
const showMemberDropdown = ref(false)

const selectVisibility = (val) => {
  form.visibility = val
  privacyOpen.value = false
  if (val === 'public_to_team') form.member_ids = []
}

const availableMembers = computed(() =>
  (props.teamMembers || []).filter(m => !form.member_ids.includes(m.id))
)

const filteredMembers = computed(() => {
  if (!memberSearch.value.trim()) return availableMembers.value
  
  const search = memberSearch.value.toLowerCase()
  return availableMembers.value.filter(m => 
    m.name.toLowerCase().includes(search) || 
    m.email?.toLowerCase().includes(search)
  )
})

const getMemberName = (id) => props.teamMembers.find(m => m.id === id)?.name || id

const getMemberAvatar = (id) => props.teamMembers.find(m => m.id === id)?.avatar || null

const addMemberById = (id) => {
  if (id && !form.member_ids.includes(id)) {
    form.member_ids.push(id)
    memberSearch.value = ''
    showMemberDropdown.value = false
  }
}

const addMember = (e) => {
  const id = e.target.value
  if (id && !form.member_ids.includes(id)) form.member_ids.push(id)
  e.target.value = ''
}

const removeMember = (id) => {
  form.member_ids = form.member_ids.filter(i => i !== id)
}

const goBack = () => router.visit('/projects')

const submit = () => {
  if (!form.name.trim()) return
  isLoading.value = true
  router.post('/projects', {
    name: form.name,
    visibility: form.visibility,
    member_ids: form.member_ids,
    organization_id: props.organizationId,
  }, {
    onFinish: () => { isLoading.value = false },
  })
}

// Close dropdown when clicking outside
const handleClickOutside = (e) => {
  if (!e.target.closest('.relative')) {
    showMemberDropdown.value = false
  }
}

// Static preview rows
const previewRows = [
  { id: 1, done: false, w: '55%', color: '#f87171', tag: '#c084fc' },
  { id: 2, done: false, w: '40%', color: '#4ade80', tag: '#4ade80' },
  { id: 3, done: false, w: '65%', color: '#4ade80', tag: '' },
  { id: 4, done: false, w: '35%', color: '#2dd4bf', tag: '#c084fc' },
  { id: 5, done: true,  w: '50%', color: '#4ade80', tag: '#f87171' },
  { id: 6, done: true,  w: '45%', color: '#4ade80', tag: '#c084fc' },
  { id: 7, done: true,  w: '60%', color: '', tag: '' },
]
</script>
