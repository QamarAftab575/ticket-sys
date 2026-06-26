<template>
  <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="space-y-8">
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900">{{ $t('welcome') }} {{ $t('tasqo') }}</h1>
        <p class="mt-2 text-sm text-gray-600">
          {{ $t('account_created_successfully') }}
        </p>
      </div>

      <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ $t('next_steps') }}</h2>
        <ul class="space-y-4">
          <li class="flex items-start">
            <div class="flex-shrink-0">
              <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                1
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">{{ $t('invite_workspace_members') }}</h3>
              <p class="mt-2 text-sm text-gray-600">
                {{ $t('start_building_workspace_by_inviting') }}
              </p>
              <Link href="/invitations" class="mt-2 inline-block text-blue-600 hover:text-blue-900">
                {{ $t('go_to_invitations') }}
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
              <h3 class="text-lg font-medium text-gray-900">{{ $t('configure_settings') }}</h3>
              <p class="mt-2 text-sm text-gray-600">
                {{ $t('customize_workspace_settings') }}
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
              <h3 class="text-lg font-medium text-gray-900">{{ $t('start_working') }}</h3>
              <p class="mt-2 text-sm text-gray-600">
                {{ $t('once_team_set_up_start_collaborating') }}
              </p>
            </div>
          </li>
        </ul>
      </div>

      <div class="flex space-x-4">
        <Link href="/invitations" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700">
          {{ $t('invite_members') }}
        </Link>
        <Link href="/dashboard" class="inline-block px-6 py-3 bg-gray-200 text-gray-900 rounded-md hover:bg-gray-300">
          {{ $t('go_to_dashboard') }}
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>
