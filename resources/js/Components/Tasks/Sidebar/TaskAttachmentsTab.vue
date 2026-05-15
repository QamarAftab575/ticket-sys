<template>
  <div class="space-y-4">
    <!-- Upload Form -->
    <div class="pb-4 border-b border-gray-200">
      <label class="block text-sm font-medium text-gray-700 mb-2">Add Attachment</label>
      <div
        class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-gray-400 transition"
        @click="$refs.fileInput?.click()"
        @drop.prevent="handleDrop"
        @dragover.prevent
      >
        <input
          ref="fileInput"
          type="file"
          multiple
          class="hidden"
          @change="handleFileSelect"
        />
        <p class="text-sm text-gray-600">Drag files here or click to upload</p>
      </div>
    </div>

    <!-- Attachments List -->
    <div v-if="task.attachments?.length === 0" class="text-center py-8">
      <p class="text-gray-500 text-sm">No attachments yet</p>
    </div>

    <div v-for="attachment in task.attachments" :key="attachment.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
      <div class="flex items-center gap-3 flex-1 min-w-0">
        <div class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center flex-shrink-0">
          <span class="text-xs font-medium text-gray-600">
            {{ getFileExtension(attachment.file_name) }}
          </span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-900 truncate">{{ attachment.file_name }}</p>
          <p class="text-xs text-gray-500">{{ formatFileSize(attachment.file_size) }}</p>
        </div>
      </div>
      <div class="flex items-center gap-2 flex-shrink-0">
        <a
          :href="attachment.file_path"
          download
          class="p-1 text-gray-500 hover:text-gray-700 transition"
        >
          ↓
        </a>
        <button
          @click="handleDeleteAttachment(attachment.id)"
          class="p-1 text-red-500 hover:text-red-700 transition"
        >
          ✕
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import type { TaskDetail } from '@/Types/tasks';
import { api } from '@/Services/api';

interface Props {
  task: TaskDetail;
}

interface Emits {
  (e: 'update'): void;
}

const props = defineProps<Props>();
defineEmits<Emits>();

const fileInput = ref<HTMLInputElement>();

function getFileExtension(filename: string): string {
  return filename.split('.').pop()?.toUpperCase() || 'FILE';
}

function formatFileSize(bytes: number): string {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
}

function handleFileSelect(event: Event) {
  const target = event.target as HTMLInputElement;
  const files = target.files;
  if (files) {
    uploadFiles(Array.from(files));
  }
}

function handleDrop(event: DragEvent) {
  const files = event.dataTransfer?.files;
  if (files) {
    uploadFiles(Array.from(files));
  }
}

async function uploadFiles(files: File[]) {
  for (const file of files) {
    const formData = new FormData();
    formData.append('file', file);

    try {
      await api.post(`/tasks/${props.task.id}/attachments`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      // Emit update event
    } catch (err) {
      console.error('Error uploading file:', err);
    }
  }
}

async function handleDeleteAttachment(attachmentId: string) {
  if (confirm('Are you sure you want to delete this attachment?')) {
    try {
      await api.delete(`/attachments/${attachmentId}`);
      // Emit update event
    } catch (err) {
      console.error('Error deleting attachment:', err);
    }
  }
}
</script>
