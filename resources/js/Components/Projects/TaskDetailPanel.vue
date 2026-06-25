<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-40 bg-black/10" @click="$emit('close')"/>

    <div class="fixed right-0 top-0 h-full z-50 flex flex-col bg-white shadow-2xl border-l border-gray-200" style="width: min(680px, 100vw)">

      <!-- Top toolbar -->
      <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-100 flex-shrink-0">
        <button @click="toggleComplete" :class="['flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium border transition-colors', isComplete ? 'bg-green-50 border-green-300 text-green-700 hover:bg-green-100' : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50']">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          {{ isComplete ? 'Completed' : 'Mark complete' }}
        </button>
        <div class="flex items-center gap-2">
          <div class="flex items-center gap-1.5 text-xs">
            <svg v-if="saveState === 'saving'" class="w-3.5 h-3.5 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
            <svg v-else-if="saveState === 'saved'" class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
            <span v-if="saveState === 'saving'" class="text-gray-500">Saving...</span>
            <span v-else-if="saveState === 'saved'" class="text-green-600">Saved</span>
          </div>
          <button @click="$emit('close')" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-md transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
      </div>

      <!-- Scrollable body -->
      <div class="flex-1 overflow-y-auto">
        <div class="px-6 py-5 space-y-5">

          <!-- Title -->
          <textarea ref="titleRef" v-model="localTask.name" rows="1"
            class="w-full text-xl font-semibold text-gray-900 resize-none border-none outline-none bg-transparent leading-snug placeholder-gray-300 overflow-hidden"
            placeholder="Task name"
            @input="autoResizeTitle"
            @blur="debouncedSave('name', localTask.name)"
            @keydown.enter.prevent="titleRef?.blur()"
          />

          <!-- Fields -->
          <div class="border border-gray-100 rounded-lg overflow-hidden">
            <FieldRow label="Assignee">
              <div class="relative">
                <button @click="toggleAssigneeOpen" class="flex items-center gap-2 text-sm text-gray-700 hover:bg-gray-50 px-2 py-1 rounded-md w-full text-left">
                  <template v-if="localTask.assignee">
                    <Avatar :name="localTask.assignee.name" :src="localTask.assignee.avatar" size="xs"/>
                    <span>{{ localTask.assignee.name }}</span>
                    <button @click.stop="clearAssignee" class="ml-auto text-gray-400 hover:text-gray-600">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                  </template>
                  <span v-else class="text-gray-400">No assignee</span>
                </button>
                <div v-if="assigneeOpen" v-click-outside="closeAssigneeDropdown"
                  class="absolute left-0 top-full mt-1 z-20 bg-white border border-gray-200 rounded-lg shadow-lg py-1 w-52">
                  <div class="px-2 pb-1 pt-1">
                    <input
                      ref="assigneeSearchRef"
                      v-model="assigneeSearch"
                      type="text"
                      placeholder="Search members..."
                      class="w-full text-xs border border-gray-200 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                  </div>
                  <div class="max-h-52 overflow-y-auto">
                    <button v-for="member in filteredAssigneeMembers" :key="member.id" @click="setAssignee(member)"
                      class="flex items-center gap-2 w-full px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                      :class="localTask.assignee_id === member.id ? 'bg-blue-50' : ''">
                      <Avatar :name="member.name" :src="member.avatar" size="xs"/>{{ member.name }}
                    </button>
                    <div v-if="filteredAssigneeMembers.length === 0" class="px-3 py-2 text-xs text-gray-400">No members found</div>
                  </div>
                </div>
              </div>
            </FieldRow>

            <FieldRow label="Due date">
              <DateRangePicker
                :start-date="localTask.start_date ?? null"
                :end-date="localTask.due_date ?? null"
                :completed="isComplete"
                @change="({ startDate, endDate }) => { localTask.start_date = startDate; localTask.due_date = endDate; debouncedSave('start_date', startDate); debouncedSave('due_date', endDate) }"
              />
            </FieldRow>

            <FieldRow label="Dependencies">
              <TaskDependenciesForm
                :task="localTask"
                :project-id="project?.id ?? null"
                @update="loadTaskDetails"
                @open-task="openDependencyTask"
              />
            </FieldRow>

 </div>

            <!-- Project with Custom Fields (no label) -->
            <div class="flex items-start gap-3   border-b border-gray-100 last:border-b-0 hover:bg-gray-50/50 transition-colors">
             
              <div class="flex-1 min-w-0">
                <button
                  @click="projectFieldsExpanded = !projectFieldsExpanded"
                  class="flex items-center gap-1.5 text-sm text-gray-700 hover:bg-gray-50 px-2 py-1 rounded-md w-full"
                >
                  <svg
                    class="w-3 h-3 text-gray-400 transition-transform flex-shrink-0"
                    :class="{ 'rotate-90': projectFieldsExpanded }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                  </svg>
                  <span class="w-2.5 h-2.5 rounded-sm flex-shrink-0" :style="{ backgroundColor: project?.color || '#6366f1' }"/>
                  <span>{{ project?.name }}</span>
                  <span class="text-gray-400 mx-0.5"> </span>
                  <span class="text-gray-500">{{ localTask.section?.name || 'No section' }}</span>
                </button>

                <!-- Custom Fields (shown when expanded) -->
                <div v-if="projectFieldsExpanded && activeCustomFields.length > 0" class="mt-2 space-y-2 pl-2 border-l-2 border-gray-200">
                  <div
                    v-for="field in activeCustomFields"
                    :key="field.id"
                    class="flex items-start gap-3 py-1"
                  >
                    <span class="w-28 flex-shrink-0 text-xs font-medium text-gray-500 pt-1">{{ field.name }}</span>
                    <div class="flex-1 min-w-0">
                      <CustomFieldCell
                        :field="field"
                        :task="localTask"
                        :raw-value="getCustomFieldValue(field.id)"
                        :members="project?.members || []"
                        @update="handleCustomFieldUpdate"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
         

          <!-- Description -->
          <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Description</p>
            <div class="border border-gray-200 rounded-lg overflow-hidden focus-within:border-indigo-300 focus-within:ring-1 focus-within:ring-indigo-200 transition-all" @click="onContentClick">
              <RichEditor v-model="localTask.description" placeholder="Add a description ¦" :show-toolbar="true" :task-id="props.task.id" :project-id="props.project?.id || props.task.project_id" @blur="onDescriptionBlur"/>
            </div>
          </div>

          <!-- Subtasks -->
          <div>
            <div class="flex items-center gap-2 mb-2">
              <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Subtasks</span>
              <span class="text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded-full font-medium">
                {{ completedSubtaskCount }}/{{ localSubtasks.length }}
              </span>
              <button
                @click="showAddSubtask = true"
                class="ml-1 w-5 h-5 flex items-center justify-center rounded-full text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
                title="Add subtask"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
              </button>
            </div>

            <div v-if="localSubtasks.length" class="border border-gray-200 rounded-lg overflow-hidden mb-2">
              <SubtaskRow
                v-for="subtask in localSubtasks"
                :key="subtask.id"
                :subtask="subtask"
                :members="project?.members || []"
                @toggle-complete="toggleSubtaskComplete"
                @update-dates="updateSubtaskDates"
                @update-assignee="updateSubtaskAssignee"
                @open="emit('open-task', $event)"
              />
            </div>

            <!-- Add subtask inline input -->
            <div v-if="showAddSubtask" class="flex items-center gap-2 mt-1">
              <div class="w-5 h-5 flex-shrink-0 rounded-full border-2 border-gray-300"/>
              <input
                ref="newSubtaskRef"
                v-model="newSubtaskName"
                type="text"
                placeholder="Subtask name"
                class="flex-1 text-sm border border-gray-200 rounded-md px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-400"
                @keydown.enter="submitSubtask"
                @keydown.esc="cancelAddSubtask"
                @blur="submitSubtask"
              />
            </div>
            <button
              v-else
              @click="openAddSubtask"
              class="text-xs text-gray-400 hover:text-indigo-600 transition-colors mt-1"
            >+ Add subtask</button>
          </div>

          <!-- Comments & Activity -->
          <div>
            <div class="flex border-b border-gray-100 mb-4">
              <button v-for="tab in ['Comments', 'Attachments', 'Activity']" :key="tab" @click="activeTab = tab"
                :class="['px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors', activeTab === tab ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700']">
                {{ tab }}
              </button>
            </div>

            <div v-if="activeTab === 'Attachments'" class="space-y-4">
              <!-- Task-level attachments -->
              <div v-if="taskAttachments.length" class="space-y-2">
                <div v-for="att in taskAttachments" :key="att.id" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group">
                  <!-- File type icon -->
                  <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-white rounded-md border border-gray-200">
                    <svg :class="['w-5 h-5', getFileTypeIconClass(att.filename || att.url)]" fill="currentColor" viewBox="0 0 24 24">
                      <component :is="getFileTypeIcon(att.filename || att.url)" />
                    </svg>
                  </div>

                  <!-- File info -->
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                      <a
                        v-if="att.type === 'file'"
                        :href="att.url"
                        target="_blank"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-700 truncate"
                      >
                        {{ att.filename }}
                      </a>
                      <a
                        v-else
                        :href="att.url"
                        target="_blank"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-700 truncate"
                      >
                        {{ att.title || att.url }}
                      </a>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5">
                      <span v-if="att.file_size">{{ formatFileSize(att.file_size) }}</span>
                      <span v-if="att.file_size && att.user"></span>
                      <span v-if="att.user">{{ att.user.name }}</span>
                      <span v-if="att.user"> </span>
                      <span>{{ formatTime(att.created_at) }}</span>
                    </div>
                  </div>

                  <!-- Actions -->
                  <div class="flex-shrink-0 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <a
                      v-if="att.type === 'file'"
                      :href="att.url"
                      download
                      class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-white rounded-md transition-colors"
                      title="Download"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                      </svg>
                    </a>
                    <button
                      @click="deleteAttachment(att.id)"
                      class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-white rounded-md transition-colors"
                      title="Delete"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Upload new attachment -->
              <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-indigo-400 hover:bg-indigo-50/30 transition-colors cursor-pointer" @click="taskAttachmentInput?.click()">
                <input
                  ref="taskAttachmentInput"
                  type="file"
                  multiple
                  class="hidden"
                  @change="onAttachTaskFiles"
                />
                <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <p class="text-sm font-medium text-gray-700">Click to upload or drag and drop</p>
                <p class="text-xs text-gray-500 mt-1">Max 100 MB per file</p>
              </div>

              <!-- Upload error message -->
              <div v-if="attachmentError" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-700">{{ attachmentError }}</p>
              </div>

              <!-- Empty state -->
              <div v-if="!taskAttachments.length && !attachmentUploading" class="text-center py-8">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm text-gray-500">No attachments yet</p>
              </div>

              <!-- Upload progress -->
              <div v-if="attachmentUploading" class="flex items-center justify-center py-4">
                <svg class="w-5 h-5 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <span class="ml-2 text-sm text-gray-600">Uploading...</span>
              </div>
            </div>

            <div v-if="activeTab === 'Comments'" class="space-y-4">

              <!-- Load more older comments   top -->
              <div v-if="commentsHasMore || commentsLoading" class="flex justify-center pb-1">
                <button
                  @click="loadMoreComments"
                  :disabled="commentsLoading"
                  class="flex items-center gap-1.5 px-3 py-1.5 text-xs text-indigo-600 hover:bg-indigo-50 rounded-md border border-indigo-200 transition-colors disabled:opacity-50"
                >
                  <svg v-if="commentsLoading" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  {{ commentsLoading ? 'Loading ¦' : 'Load older comments' }}
                </button>
              </div>

              <div v-for="comment in comments" :key="comment.id" class="flex gap-3 group">
                <Avatar :name="comment.user?.name" :src="comment.user?.avatar" size="sm" class="flex-shrink-0 mt-0.5"/>
                <div class="flex-1 min-w-0">
                  <div class="flex items-baseline gap-2 mb-1">
                    <span class="text-sm font-semibold text-gray-800">{{ comment.user?.name }}</span>
                    <span class="text-xs text-gray-400">{{ formatTime(comment.created_at) }}</span>
                    <span v-if="comment.edited_at" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-400 border border-gray-200">edited</span>
                  </div>

                  <!-- View mode -->
                  <template v-if="editingCommentId !== comment.id">
                    <div class="text-sm text-gray-700 prose prose-sm max-w-none rich-content" v-html="comment.content" @click="onContentClick"/>

                    <!-- Attachments preview -->
                    <div v-if="comment.attachments?.length" class="flex flex-wrap gap-2 mt-2">
                      <template v-for="att in comment.attachments" :key="att.id">
                        <!-- Image preview -->
                        <div
                          v-if="att.mime_type?.startsWith('image/') || /\.(png|jpe?g|gif|webp|svg)$/i.test(att.filename || att.url)"
                          class="relative group/att cursor-pointer"
                          @click="openImagePreview(att.url, att.filename)"
                        >
                          <img :src="att.url" :alt="att.filename" class="h-16 w-16 object-cover rounded-md border border-gray-200 hover:opacity-90 transition"/>
                        </div>
                        <!-- File link -->
                        <a
                          v-else
                          :href="att.url" target="_blank"
                          class="flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 rounded-md px-2 py-1 text-xs text-gray-700 transition-colors"
                        >
                          <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/>
                          </svg>
                          <span class="max-w-[140px] truncate">{{ att.filename || att.title }}</span>
                        </a>
                      </template>
                    </div>

                    <div v-if="comment.user_id === currentUserId" class="mt-1 flex items-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                      <button @click="startEditComment(comment)" class="text-xs text-gray-400 hover:text-indigo-600">Edit</button>
                      <button @click="deleteComment(comment)" class="text-xs text-gray-400 hover:text-red-500">Delete</button>
                    </div>
                  </template>

                  <!-- Edit mode -->
                  <div v-else class="border border-indigo-300 rounded-lg overflow-hidden">
                    <RichEditor v-model="editingCommentContent" :show-toolbar="true" :task-id="props.task.id" :project-id="props.project?.id || props.task.project_id" placeholder="Edit comment ¦"/>
                    <div class="flex gap-2 px-3 py-2 bg-gray-50 border-t border-gray-100">
                      <button @click="saveEditComment(comment.id)" class="px-3 py-1 text-xs font-medium bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save</button>
                      <button @click="editingCommentId = null" class="px-3 py-1 text-xs font-medium text-gray-600 hover:text-gray-800">Cancel</button>
                    </div>
                  </div>
                </div>
              </div>

              <p v-if="comments.length === 0 && !commentsLoading" class="text-sm text-gray-400 text-center py-4">No comments yet.</p>

              <div class="flex gap-3 pt-2">
                <Avatar :name="currentUser?.name" :src="currentUser?.avatar" size="sm" class="flex-shrink-0 mt-0.5"/>
                <div class="flex-1 border border-gray-200 rounded-lg overflow-hidden focus-within:border-indigo-300 focus-within:ring-1 focus-within:ring-indigo-200 transition-all">
                  <RichEditor v-model="newComment" :show-toolbar="'auto'" :task-id="props.task.id" :project-id="props.project?.id || props.task.project_id" placeholder="Add a comment ¦"/>

                  <!-- Staged attachments preview -->
                  <div v-if="stagedFiles.length" class="flex flex-wrap gap-2 px-3 pt-2">
                    <div
                      v-for="(f, i) in stagedFiles" :key="i"
                      class="flex items-center gap-1.5 bg-gray-100 rounded-md px-2 py-1 text-xs text-gray-700"
                    >
                      <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/>
                      </svg>
                      <span class="max-w-[120px] truncate">{{ f.name }}</span>
                      <span class="text-gray-400">({{ formatFileSize(f.size) }})</span>
                      <button @click="stagedFiles.splice(i, 1)" class="text-gray-400 hover:text-red-500 ml-0.5">Ã—</button>
                    </div>
                  </div>

                  <!-- Action bar   always visible -->
                  <div class="flex items-center justify-between px-3 py-2 bg-gray-50 border-t border-gray-100">
                    <!-- Attach file -->
                    <label class="flex items-center gap-1 text-xs text-gray-500 hover:text-indigo-600 cursor-pointer transition-colors" title="Attach file (max 100 MB)">
                      <input
                        type="file"
                        multiple
                        class="hidden"
                        @change="onAttachFiles"
                      />
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                      </svg>
                      Attach
                    </label>

                    <div class="flex items-center gap-2">
                      <button
                        v-if="newComment && newComment !== '<p></p>' || stagedFiles.length"
                        @click="cancelComment"
                        class="px-3 py-1 text-xs font-medium text-gray-600 hover:text-gray-800"
                      >
                        Cancel
                      </button>
                      <button
                        @click="submitComment"
                        :disabled="commentLoading || (!newComment?.trim() && !stagedFiles.length) || newComment === '<p></p>'"
                        class="px-3 py-1 text-xs font-medium bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-40 transition-colors flex items-center gap-1.5"
                      >
                        <svg v-if="commentLoading" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        Comment
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="activeTab === 'Activity'" class="space-y-3">
              <div v-for="activity in activities" :key="activity.id" class="flex gap-3">
                <Avatar :name="activity.user?.name" size="sm" class="flex-shrink-0 mt-0.5"/>
                <div class="flex-1 text-sm leading-snug">
                  <span class="font-medium text-gray-800">{{ activity.user?.name }}</span>
                  <span class="text-gray-500 mx-1">{{ formatActivity(activity) }}</span>
                  <span class="text-xs text-gray-400">  {{ formatTime(activity.created_at) }}</span>
                </div>
              </div>
              <p v-if="activities.length === 0 && !activitiesLoading" class="text-sm text-gray-400 text-center py-4">No activity yet.</p>

              <!-- Load more activities -->
              <div v-if="activitiesHasMore || activitiesLoading" class="flex justify-center pt-1">
                <button
                  @click="loadMoreActivities"
                  :disabled="activitiesLoading"
                  class="flex items-center gap-1.5 px-3 py-1.5 text-xs text-indigo-600 hover:bg-indigo-50 rounded-md border border-indigo-200 transition-colors disabled:opacity-50"
                >
                  <svg v-if="activitiesLoading" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  {{ activitiesLoading ? 'Loading ¦' : 'Load more activity' }}
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Reusable image preview modal -->
    <ImagePreviewModal :src="previewSrc" :filename="previewFilename" @close="closeImagePreview"/>
  </Teleport>

  <!-- Blocked-by warning modal -->
  <BlockedByWarningModal
    v-if="showBlockedModal"
    :tasks="incompleteDeps"
    @confirm="confirmCompleteAnyway"
    @cancel="showBlockedModal = false"
  />
</template>


<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted, defineComponent, h } from 'vue'
import Avatar from '@/Components/Avatar.vue'
import RichEditor from '@/Components/Projects/RichEditor.vue'
import ImagePreviewModal from '@/Components/ImagePreviewModal.vue'
import DateRangePicker from '@/Components/Tasks/Shared/DateRangePicker.vue'
import TaskDependenciesForm from '@/Components/Tasks/Forms/TaskDependenciesForm.vue'
import BlockedByWarningModal from '@/Components/Tasks/BlockedByWarningModal.vue'
import SubtaskRow from '@/Components/Tasks/Shared/SubtaskRow.vue'
import CustomFieldCell from '@/Components/CustomFields/CustomFieldCell.vue'
import { useCustomFields } from '@/Composables/useCustomFields'

const FieldRow = defineComponent({
  props: { label: String },
  setup(props, { slots }) {
    return () => h('div', { class: 'flex items-start gap-3 px-4 py-2.5 border-b border-gray-100 last:border-b-0 hover:bg-gray-50/50 transition-colors' }, [
      h('span', { class: 'w-24 flex-shrink-0 text-xs font-medium text-gray-500 pt-1.5' }, props.label),
      h('div', { class: 'flex-1 min-w-0' }, slots.default?.()),
    ])
  },
})

const props = defineProps({
  task: { type: Object, required: true },
  project: { type: Object, default: null },
  currentUser: { type: Object, default: null },
})
const emit = defineEmits(['close', 'update', 'open-task'])

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
const apiHeaders = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
const currentUserId = computed(() => props.currentUser?.id || null)
const localTask = ref({ ...props.task })
const projectFieldsExpanded = ref(false)

//  Custom Fields 
const { fields, fetchFields, setTaskFieldValue } = useCustomFields(props.project?.id)

const activeCustomFields = computed(() => {
  return fields.value.filter(f => f.is_active)
})

function getCustomFieldValue(fieldId) {
  const cfv = localTask.value.custom_field_values?.find(v => v.custom_field_id === fieldId)
  return cfv?.value ?? null
}

async function handleCustomFieldUpdate({ taskId, fieldId, value }) {
  // Optimistic update - update local state immediately
  if (!localTask.value.custom_field_values) {
    localTask.value.custom_field_values = []
  }

  const existingIndex = localTask.value.custom_field_values.findIndex(v => v.custom_field_id === fieldId)
  const previousValue = existingIndex >= 0 ? localTask.value.custom_field_values[existingIndex].value : null

  // Serialize value for storage (arrays need to be JSON stringified)
  const serializedValue = Array.isArray(value) ? JSON.stringify(value) : (value !== null ? String(value) : null)

  if (serializedValue === null || serializedValue === '' || (Array.isArray(value) && value.length === 0)) {
    // Remove the field value
    localTask.value.custom_field_values = localTask.value.custom_field_values.filter(v => v.custom_field_id !== fieldId)
  } else {
    if (existingIndex >= 0) {
      // Update existing value
      localTask.value.custom_field_values[existingIndex].value = serializedValue
    } else {
      // Add new value
      localTask.value.custom_field_values.push({
        custom_field_id: fieldId,
        value: serializedValue
      })
    }
  }

  // Emit update to parent immediately for TaskRow update
  emit('update', taskId, { custom_field_values: [...localTask.value.custom_field_values] })

  // Save to backend
  try {
    await setTaskFieldValue(taskId, fieldId, value)
  } catch (e) {
    console.error('Failed to update custom field:', e)
    // Revert on error
    if (previousValue === null) {
      localTask.value.custom_field_values = localTask.value.custom_field_values.filter(v => v.custom_field_id !== fieldId)
    } else {
      const cfv = localTask.value.custom_field_values.find(v => v.custom_field_id === fieldId)
      if (cfv) {
        cfv.value = previousValue
      } else {
        localTask.value.custom_field_values.push({ custom_field_id: fieldId, value: previousValue })
      }
    }
    // Re-emit to sync the reverted state
    emit('update', taskId, { custom_field_values: [...localTask.value.custom_field_values] })
  }
}

watch(() => props.task.id, () => { localTask.value = { ...props.task }; loadTaskDetails() })

//  Save state 
const saveState = ref('idle')
let savedTimer = null
const saveTimers = {}
let inflightRequest = null

function debouncedSave(field, value) {
  const capturedValue = value
  clearTimeout(saveTimers[field])
  saveTimers[field] = setTimeout(async () => {
    inflightRequest?.abort?.()
    saveState.value = 'saving'
    clearTimeout(savedTimer)
    const controller = new AbortController()
    inflightRequest = controller
    try {
      const res = await fetch(`/api/tasks/${props.task.id}`, {
        method: 'PUT',
        headers: { ...apiHeaders, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ [field]: capturedValue }),
        signal: controller.signal,
      })
      if (!res.ok) throw new Error('Save failed')
      emit('update', props.task.id, { [field]: capturedValue })
      saveState.value = 'saved'
      savedTimer = setTimeout(() => { saveState.value = 'idle' }, 2000)
    } catch (err) {
      if (err.name !== 'AbortError') { console.error('Auto-save failed:', err); saveState.value = 'idle' }
    } finally {
      if (inflightRequest === controller) inflightRequest = null
    }
  }, 600)
}

function onDescriptionBlur(freshHTML) {
  localTask.value.description = freshHTML
  debouncedSave('description', freshHTML)
}

//  Title 
const titleRef = ref(null)
function autoResizeTitle() {
  const el = titleRef.value
  if (!el) return
  el.style.height = 'auto'
  el.style.height = el.scrollHeight + 'px'
}

//  Computed 
const isComplete = computed(() => localTask.value.status === 'complete')
const isDueDateOverdue = computed(() => localTask.value.due_date && new Date(localTask.value.due_date) < new Date())
const priorities = [
  { value: 'low',    label: 'Low',    dotClass: 'bg-gray-400',   activeClass: 'bg-gray-100 border-gray-400 text-gray-700' },
  { value: 'medium', label: 'Medium', dotClass: 'bg-yellow-400', activeClass: 'bg-yellow-50 border-yellow-400 text-yellow-700' },
  { value: 'high',   label: 'High',   dotClass: 'bg-red-400',    activeClass: 'bg-red-50 border-red-400 text-red-700' },
]

//  Field actions 
const showBlockedModal = ref(false)
const incompleteDeps = computed(() =>
  (localTask.value.dependencies ?? []).filter(t => t.status !== 'complete')
)

function toggleComplete() {
  if (!isComplete.value && incompleteDeps.value.length) {
    showBlockedModal.value = true
    return
  }
  applyComplete()
}

function applyComplete() {
  const s = isComplete.value ? 'to_do' : 'complete'
  localTask.value.status = s
  debouncedSave('status', s)
}

async function confirmCompleteAnyway() {
  showBlockedModal.value = false
  // Remove each blocking dependency then mark complete
  for (const dep of incompleteDeps.value) {
    await fetch(`/api/tasks/${localTask.value.id}/dependencies/${dep.id}`, {
      method: 'DELETE',
      headers: apiHeaders,
    })
  }
  // Strip them from local state so incompleteDeps recomputes to []
  localTask.value.dependencies = (localTask.value.dependencies ?? []).filter(
    t => t.status === 'complete'
  )
  applyComplete()
}
const assigneeOpen = ref(false)
const assigneeSearch = ref('')
const assigneeSearchRef = ref(null)

const filteredAssigneeMembers = computed(() => {
  const q = assigneeSearch.value.trim().toLowerCase()
  const members = props.project?.members || []
  if (!q) return members
  return members.filter(m => m.name.toLowerCase().includes(q) || (m.email || '').toLowerCase().includes(q))
})

function toggleAssigneeOpen() {
  assigneeOpen.value = !assigneeOpen.value
  if (assigneeOpen.value) {
    assigneeSearch.value = ''
    nextTick(() => assigneeSearchRef.value?.focus())
  }
}
function closeAssigneeDropdown() { assigneeOpen.value = false; assigneeSearch.value = '' }
function setAssignee(member) { localTask.value.assignee = member; localTask.value.assignee_id = member.id; closeAssigneeDropdown(); debouncedSave('assignee_id', member.id) }
function clearAssignee() { localTask.value.assignee = null; localTask.value.assignee_id = null; closeAssigneeDropdown(); debouncedSave('assignee_id', null) }

function onAssigneeOutsideClick(e) {
  if (assigneeOpen.value && !assigneeContainerRef.value?.contains(e.target)) {
    closeAssigneeDropdown()
  }
}
function setDueDate(val) { localTask.value.due_date = val; debouncedSave('due_date', val) }
function setPriority(val) { localTask.value.priority = val; debouncedSave('priority', val) }

//  Image preview 
const previewSrc = ref(null)
const previewFilename = ref(null)
function openImagePreview(src, filename = null) { previewSrc.value = src; previewFilename.value = filename }
function closeImagePreview() { previewSrc.value = null; previewFilename.value = null }
function onContentClick(e) {
  const img = e.target.closest('img')
  if (!img) return
  openImagePreview(img.src, img.src.split('/').pop().split('?')[0] || null)
}

//  Subtasks 
const localSubtasks = ref([])
const showAddSubtask = ref(false)
const newSubtaskName = ref('')
const newSubtaskRef = ref(null)

const completedSubtaskCount = computed(() =>
  localSubtasks.value.filter(s => s.status === 'complete').length
)

async function loadSubtasks() {
  try {
    const res = await fetch(`/api/tasks/${props.task.id}/subtasks`)
    if (!res.ok) return
    const { data } = await res.json()
    localSubtasks.value = data
  } catch (e) {
    console.error('Failed to load subtasks', e)
  }
}

function openAddSubtask() {
  showAddSubtask.value = true
  nextTick(() => newSubtaskRef.value?.focus())
}

function cancelAddSubtask() {
  showAddSubtask.value = false
  newSubtaskName.value = ''
}

async function submitSubtask() {
  const name = newSubtaskName.value.trim()
  if (!name) { cancelAddSubtask(); return }

  // Optimistic add
  const tempId = `temp_${Date.now()}`
  const tempSubtask = { id: tempId, name, status: 'to_do', assignee: null, assignee_id: null, start_date: null, due_date: null }
  localSubtasks.value.push(tempSubtask)
  cancelAddSubtask()

  try {
    const res = await fetch(`/api/tasks/${props.task.id}/subtasks`, {
      method: 'POST',
      headers: apiHeaders,
      body: JSON.stringify({ name }),
    })
    if (!res.ok) throw new Error()
    const { data } = await res.json()
    const idx = localSubtasks.value.findIndex(s => s.id === tempId)
    if (idx >= 0) localSubtasks.value[idx] = data
  } catch {
    localSubtasks.value = localSubtasks.value.filter(s => s.id !== tempId)
  }
}

async function toggleSubtaskComplete(subtaskId) {
  const subtask = localSubtasks.value.find(s => s.id === subtaskId)
  if (!subtask) return
  const newStatus = subtask.status === 'complete' ? 'to_do' : 'complete'
  subtask.status = newStatus // optimistic
  try {
    await fetch(`/api/tasks/${subtaskId}`, {
      method: 'PUT',
      headers: apiHeaders,
      body: JSON.stringify({ status: newStatus }),
    })
  } catch {
    subtask.status = newStatus === 'complete' ? 'to_do' : 'complete' // revert
  }
}

async function updateSubtaskDates({ subtaskId, start_date, due_date }) {
  const subtask = localSubtasks.value.find(s => s.id === subtaskId)
  if (!subtask) return
  subtask.start_date = start_date
  subtask.due_date = due_date
  try {
    await fetch(`/api/tasks/${subtaskId}`, {
      method: 'PUT',
      headers: apiHeaders,
      body: JSON.stringify({ start_date, due_date }),
    })
  } catch (e) { console.error('Failed to update subtask dates', e) }
}

async function updateSubtaskAssignee({ taskId, assigneeId }) {
  const subtask = localSubtasks.value.find(s => s.id === taskId)
  if (!subtask) return
  subtask.assignee_id = assigneeId
  try {
    await fetch(`/api/tasks/${taskId}`, {
      method: 'PUT',
      headers: apiHeaders,
      body: JSON.stringify({ assignee_id: assigneeId }),
    })
  } catch (e) { console.error('Failed to update subtask assignee', e) }
}

//  Comments (paginated) 
const comments = ref([])
const commentsPage = ref(1)
const commentsHasMore = ref(false)
const commentsLoading = ref(false)

async function loadComments(page = 1) {
  commentsLoading.value = true
  try {
    const res = await fetch(`/api/tasks/${props.task.id}/comments?page=${page}`)
    if (!res.ok) return
    const { data, has_more } = await res.json()
    if (page === 1) comments.value = data.reverse()
    else comments.value.unshift(...data.reverse())
    commentsPage.value = page
    commentsHasMore.value = has_more
  } catch (e) {
    console.error('Failed to load comments', e)
  } finally {
    commentsLoading.value = false
  }
}

function loadMoreComments() {
  if (!commentsHasMore.value || commentsLoading.value) return
  loadComments(commentsPage.value + 1)
}

//  Activities (paginated) 
const activities = ref([])
const activitiesPage = ref(1)
const activitiesHasMore = ref(false)
const activitiesLoading = ref(false)

async function loadActivities(page = 1) {
  activitiesLoading.value = true
  try {
    const res = await fetch(`/api/tasks/${props.task.id}/activities?page=${page}`)
    if (!res.ok) return
    const { data, has_more } = await res.json()
    if (page === 1) activities.value = data
    else activities.value.push(...data)
    activitiesPage.value = page
    activitiesHasMore.value = has_more
  } catch (e) {
    console.error('Failed to load activities', e)
  } finally {
    activitiesLoading.value = false
  }
}

function loadMoreActivities() {
  if (!activitiesHasMore.value || activitiesLoading.value) return
  loadActivities(activitiesPage.value + 1)
}

const activeTab = ref('Comments')
const newComment = ref('')
const commentLoading = ref(false)
const editingCommentId = ref(null)
const editingCommentContent = ref('')
const stagedFiles = ref([])

//  Task Attachments 
const taskAttachments = ref([])
const taskAttachmentInput = ref(null)
const attachmentUploading = ref(false)
const attachmentError = ref(null)

const MAX_FILE_BYTES = 100 * 1024 * 1024 // 100 MB

function onAttachFiles(e) {
  const files = Array.from(e.target.files || [])
  const oversized = files.filter(f => f.size > MAX_FILE_BYTES)
  if (oversized.length) {
    alert(`These files exceed 100 MB: ${oversized.map(f => f.name).join(', ')}`)
  }
  stagedFiles.value.push(...files.filter(f => f.size <= MAX_FILE_BYTES))
  e.target.value = ''
}

function formatFileSize(bytes) {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

function getFileTypeIcon(filename) {
  if (!filename) return 'DocumentIcon'
  const ext = filename.split('.').pop()?.toLowerCase() || ''
  
  // Image types
  if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext)) {
    return 'ImageIcon'
  }
  // PDF
  if (ext === 'pdf') {
    return 'PdfIcon'
  }
  // Documents
  if (['doc', 'docx', 'txt'].includes(ext)) {
    return 'DocumentIcon'
  }
  // Spreadsheets
  if (['xls', 'xlsx', 'csv'].includes(ext)) {
    return 'SpreadsheetIcon'
  }
  // Archives
  if (['zip', 'tar', 'gz', 'rar', '7z'].includes(ext)) {
    return 'ArchiveIcon'
  }
  // Videos
  if (['mp4', 'mov', 'avi', 'webm', 'mkv'].includes(ext)) {
    return 'VideoIcon'
  }
  // Audio
  if (['mp3', 'wav', 'flac', 'm4a'].includes(ext)) {
    return 'AudioIcon'
  }
  
  return 'DocumentIcon'
}

function getFileTypeIconClass(filename) {
  if (!filename) return 'text-gray-400'
  const ext = filename.split('.').pop()?.toLowerCase() || ''
  
  if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext)) return 'text-blue-500'
  if (ext === 'pdf') return 'text-red-500'
  if (['doc', 'docx', 'txt'].includes(ext)) return 'text-blue-600'
  if (['xls', 'xlsx', 'csv'].includes(ext)) return 'text-green-600'
  if (['zip', 'tar', 'gz', 'rar', '7z'].includes(ext)) return 'text-yellow-600'
  if (['mp4', 'mov', 'avi', 'webm', 'mkv'].includes(ext)) return 'text-purple-600'
  if (['mp3', 'wav', 'flac', 'm4a'].includes(ext)) return 'text-pink-600'
  
  return 'text-gray-400'
}

// SVG icon components
const DocumentIcon = () => h('svg', { fill: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { d: 'M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z' })
])

const ImageIcon = () => h('svg', { fill: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { d: 'M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5.04-6.71l-2.75-3.54c-.3-.38-.77-.62-1.3-.62-.93 0-1.69.76-1.69 1.69 0 .53.24 1 .62 1.3l2.75 3.54 1.07-1.07zm3.97 3.36l-4.39-5.64c-.3-.38-.77-.62-1.3-.62-.93 0-1.69.76-1.69 1.69 0 .53.24 1 .62 1.3l4.39 5.64 1.07-1.07z' })
])

const PdfIcon = () => h('svg', { fill: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { d: 'M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM8 15.5c0 .83-.67 1.5-1.5 1.5S5 16.33 5 15.5 5.67 14 6.5 14 8 14.67 8 15.5zm4-2h-4v4h4v-4zm4 2c0 .83-.67 1.5-1.5 1.5s-1.5-.67-1.5-1.5.67-1.5 1.5-1.5 1.5.67 1.5 1.5z' })
])

const SpreadsheetIcon = () => h('svg', { fill: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { d: 'M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-4-5h-4v4h4v-4zm-6-4h4v4h-4v-4zm6 0h4v4h-4v-4zm-6-4h4v4h-4v-4z' })
])

const ArchiveIcon = () => h('svg', { fill: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { d: 'M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 5.5h6v3.5h-6V5.5zm6 10.5h-4v4h-4v-4H6v-4h6v-3h4v11z' })
])

const VideoIcon = () => h('svg', { fill: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { d: 'M18 3H6c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 9l-5-4v8l5-4z' })
])

const AudioIcon = () => h('svg', { fill: 'currentColor', viewBox: '0 0 24 24' }, [
  h('path', { d: 'M12 3v9.28c-.47-.46-1.12-.75-1.84-.75-1.66 0-3 1.34-3 3s1.34 3 3 3c1.66 0 3-1.34 3-3V7h4V3h-4z' })
])

async function loadTaskAttachments() {
  try {
    const res = await fetch(`/api/tasks/${props.task.id}/attachments`)
    if (!res.ok) return
    const { data } = await res.json()
    taskAttachments.value = data
  } catch (e) {
    console.error('Failed to load task attachments', e)
  }
}

function onAttachTaskFiles(e) {
  const files = Array.from(e.target.files || [])
  attachmentError.value = null
  
  const oversized = files.filter(f => f.size > MAX_FILE_BYTES)
  if (oversized.length) {
    attachmentError.value = `These files exceed 100 MB: ${oversized.map(f => f.name).join(', ')}`
    e.target.value = ''
    return
  }
  
  uploadTaskAttachments(files.filter(f => f.size <= MAX_FILE_BYTES))
  e.target.value = ''
}

async function uploadTaskAttachments(files) {
  if (!files.length) return
  
  attachmentUploading.value = true
  attachmentError.value = null
  
  try {
    for (const file of files) {
      const form = new FormData()
      form.append('file', file)
      
      const res = await fetch(`/api/tasks/${props.task.id}/attachments`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body: form,
      })
      
      if (!res.ok) {
        const errorData = await res.json()
        attachmentError.value = errorData.message || errorData.errors?.file?.[0] || 'Failed to upload file'
        continue
      }
      
      const { data } = await res.json()
      taskAttachments.value.push(data)
    }
  } catch (e) {
    console.error('Failed to upload attachments', e)
    attachmentError.value = 'Failed to upload files. Please try again.'
  } finally {
    attachmentUploading.value = false
  }
}

async function deleteAttachment(attachmentId) {
  if (!confirm('Delete this attachment?')) return
  
  const idx = taskAttachments.value.findIndex(a => a.id === attachmentId)
  if (idx < 0) return
  
  const attachment = taskAttachments.value[idx]
  taskAttachments.value.splice(idx, 1)
  
  try {
    const res = await fetch(`/api/attachments/${attachmentId}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': csrfToken },
    })
    if (!res.ok) throw new Error()
  } catch {
    taskAttachments.value.splice(idx, 0, attachment)
    attachmentError.value = 'Failed to delete attachment'
  }
}

function cancelComment() {
  newComment.value = ''
  stagedFiles.value = []
}

async function loadTaskDetails() {
  try {
    const res = await fetch(`/api/tasks/${props.task.id}`)
    if (!res.ok) return
    const { data } = await res.json()
    // Merge: keep rich relations from API (subtasks, attachments, etc.)
    // but trust the initial props for core fields already loaded on page init
    localTask.value = {
      ...data,
      name: localTask.value.name,
      description: localTask.value.description,
      start_date: localTask.value.start_date ?? data.start_date,
      due_date: localTask.value.due_date ?? data.due_date,
      assignee: localTask.value.assignee ?? data.assignee,
      assignee_id: localTask.value.assignee_id ?? data.assignee_id,
      status: localTask.value.status ?? data.status,
      priority: localTask.value.priority ?? data.priority,
      dependencies: data.dependencies,
      dependents: data.dependents,
    }
  } catch (e) {
    console.error('Failed to load task details', e)
  }
  // Load comments and activities separately
  loadComments(1)
  loadActivities(1)
  loadSubtasks()
}

async function submitComment() {
  const content = newComment.value?.trim()
  if ((!content || content === '<p></p>') && !stagedFiles.value.length) return
  commentLoading.value = true

  const tempId = `temp_${Date.now()}`
  const tempComment = {
    id: tempId,
    content: content || '',
    user: props.currentUser,
    user_id: currentUserId.value,
    created_at: new Date().toISOString(),
    attachments: [],
  }
  comments.value.push(tempComment)
  const savedContent = newComment.value
  const savedFiles = [...stagedFiles.value]
  newComment.value = ''
  stagedFiles.value = []

  try {
    // 1. Post the comment
    const res = await fetch(`/api/tasks/${props.task.id}/comments`, {
      method: 'POST',
      headers: { ...apiHeaders },
      body: JSON.stringify({ content: content || '' }),
    })
    if (!res.ok) throw new Error()
    const { data: createdComment } = await res.json()

    // 2. Upload staged attachments linked to this comment
    const uploadedAttachments = []
    for (const file of savedFiles) {
      const form = new FormData()
      form.append('file', file)
      form.append('comment_id', createdComment.id)
      const uploadRes = await fetch(`/api/tasks/${props.task.id}/attachments`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body: form,
      })
      if (uploadRes.ok) {
        const uploadData = await uploadRes.json()
        uploadedAttachments.push(uploadData)
      }
    }

    // Replace temp with real comment
    const idx = comments.value.findIndex(c => c.id === tempId)
    if (idx >= 0) {
      comments.value[idx] = { ...createdComment, attachments: uploadedAttachments }
    }
  } catch {
    comments.value = comments.value.filter(c => c.id !== tempId)
    newComment.value = savedContent
    stagedFiles.value = savedFiles
  } finally {
    commentLoading.value = false
  }
}

function startEditComment(comment) { editingCommentId.value = comment.id; editingCommentContent.value = comment.content }

async function deleteComment(comment) {
  if (!confirm('Delete this comment? This cannot be undone.')) return
  // Optimistic remove
  const idx = comments.value.findIndex(c => c.id === comment.id)
  if (idx >= 0) comments.value.splice(idx, 1)
  try {
    const res = await fetch(`/api/comments/${comment.id}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': csrfToken },
    })
    if (!res.ok) throw new Error()
  } catch {
    // Restore on failure
    if (idx >= 0) comments.value.splice(idx, 0, comment)
  }
}

async function saveEditComment(commentId) {
  const content = editingCommentContent.value
  const comment = comments.value.find(c => c.id === commentId)
  if (!comment) return
  const original = comment.content
  comment.content = content
  editingCommentId.value = null
  try {
    const res = await fetch(`/api/comments/${commentId}`, {
      method: 'PUT', headers: { ...apiHeaders },
      body: JSON.stringify({ content }),
    })
    if (!res.ok) throw new Error()
    const { data } = await res.json()
    Object.assign(comment, data)
  } catch { comment.content = original }
}

function formatActivity(a) {
  const fieldLabels = {
    status: 'status', assignee_id: 'assignee', due_date: 'due date',
    priority: 'priority', name: 'title', description: 'description',
    section_id: 'section', start_date: 'start date',
  }
  switch (a.activity_type) {
    case 'created':   return 'created this task'
    case 'completed': return 'marked this task complete'
    case 'reopened':  return 'marked this task incomplete'
    case 'deleted':   return 'deleted this task'
    case 'updated': {
      const field = fieldLabels[a.field_name] || a.field_name?.replace(/_/g, ' ') || 'a field'
      if (a.field_name === 'status') {
        const statusLabel = { to_do: 'To Do', in_progress: 'In Progress', in_review: 'In Review', complete: 'Complete' }
        const from = statusLabel[a.old_value] || a.old_value
        const to   = statusLabel[a.new_value] || a.new_value
        return `changed status from "${from}" to "${to}"`
      }
      return `updated ${field}`
    }
    default: return a.activity_type
  }
}

function formatTime(ts) {
  if (!ts) return ''
  let str = typeof ts === 'string' ? ts.trim() : String(ts)
  // Normalize MySQL "YYYY-MM-DD HH:MM:SS"  ’ ISO UTC
  if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(str)) str = str.replace(' ', 'T') + 'Z'
  else if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/.test(str)) str = str + 'Z'
  const d = new Date(str)
  if (isNaN(d.getTime())) return ''
  const diff = Math.floor((Date.now() - d.getTime()) / 1000)
  if (diff < 60)   return 'Just now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  // Older than 24h   show full readable date: "23 Mar 2026 4:12am"
  const day   = d.getDate()
  const month = d.toLocaleDateString('en-US', { month: 'short' })
  const year  = d.getFullYear()
  const hours = d.getHours()
  const mins  = String(d.getMinutes()).padStart(2, '0')
  const ampm  = hours >= 12 ? 'pm' : 'am'
  const h12   = hours % 12 || 12
  return `${day} ${month} ${year} ${h12}:${mins}${ampm}`
}

const vClickOutside = {
  mounted(el, binding) { el._co = (e) => { if (!el.contains(e.target)) binding.value(e) }; document.addEventListener('mousedown', el._co) },
  unmounted(el) { document.removeEventListener('mousedown', el._co) },
}

onMounted(() => { 
  loadTaskDetails()
  loadTaskAttachments()
  nextTick(autoResizeTitle)
  if (props.project?.id) {
    fetchFields()
  }
})

async function openDependencyTask(dep) {
  try {
    const res = await fetch(`/api/tasks/${dep.id}`)
    if (!res.ok) return
    const { data } = await res.json()
    emit('open-task', data)
  } catch {}
}
onUnmounted(() => { Object.values(saveTimers).forEach(clearTimeout); clearTimeout(savedTimer); inflightRequest?.abort?.() })
</script>

<style>
.rich-content img, .tiptap img {
  cursor: zoom-in;
  max-width: 100%;
  height: auto;
  border-radius: 4px;
  margin: 4px 0;
}

/* Mention styles for rendered content */
.rich-content .mention,
.rich-content span[data-type="mention"] {
  background-color: #e0e7ff;
  color: #4f46e5;
  border-radius: 0.25rem;
  padding: 0.125rem 0.25rem;
  font-weight: 500;
  white-space: nowrap;
  cursor: pointer;
}

.rich-content .mention:hover,
.rich-content span[data-type="mention"]:hover {
  background-color: #c7d2fe;
}
</style>

