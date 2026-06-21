<template>
  <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="space-y-8">
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900">Welcome to Asira</h1>
        <p class="mt-2 text-sm text-gray-600">
          Your account has been created successfully. You can now start inviting workspace members.
        </p>
      </div>

      <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Next Steps</h2>
        <ul class="space-y-4">
          <li class="flex items-start">
            <div class="flex-shrink-0">
              <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                1
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">Invite Workspace Members</h3>
              <p class="mt-2 text-sm text-gray-600">
                Start building your workspace by inviting members to join.
              </p>
              <Link href="/invitations" class="mt-2 inline-block text-blue-600 hover:text-blue-900">
                Go to Invitations â†’
              </Link>
            </div>
          </li>
          <li class="flex items-start">
            <div class="flex-shrink-0">
              <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                2
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">Configure Settings</h3>
              <p class="mt-2 text-sm text-gray-600">
                Customize your workspace settings and preferences.
              </p>
            </div>
          </li>
          <li class="flex items-start">
            <div class="flex-shrink-0">
              <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                3
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">Start Working</h3>
              <p class="mt-2 text-sm text-gray-600">
                Once your team is set up, you can start collaborating.
              </p>
            </div>
          </li>
        </ul>
      </div>

      <div class="flex space-x-4">
        <Link href="/invitations" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700">
          Invite Members
        </Link>
        <Link href="/dashboard" class="inline-block px-6 py-3 bg-gray-200 text-gray-900 rounded-md hover:bg-gray-300">
          Go to Dashboard
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>

