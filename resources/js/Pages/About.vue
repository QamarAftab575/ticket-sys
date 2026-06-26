<script setup>
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
</script>

<template>
  <GuestLayout>
    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-orange-50 via-white to-orange-50">
      <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-5xl font-bold text-slate-900 mb-6">{{ $t('about_appname') }}</h1>
        <p class="text-xl text-slate-600">{{ $t('complete_ticketing_solution') }}</p>
      </div>
    </section>

    <!-- Mission Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-white">
      <div class="max-w-4xl mx-auto">
        <div class="space-y-16">
          <!-- Our Mission -->
          <div>
            <h2 class="text-4xl font-bold text-slate-900 mb-6">{{ $t('our_mission') }}</h2>
            <p class="text-lg text-slate-600 leading-relaxed mb-6">
              {{ $t('mission_description_1') }}
            </p>
            <p class="text-lg text-slate-600 leading-relaxed">
              {{ $t('mission_description_2') }}
            </p>
          </div>

          <!-- What is this -->
          <div>
            <h2 class="text-4xl font-bold text-slate-900 mb-6">{{ $t('what_is_appname') }}</h2>
            <p class="text-lg text-slate-600 leading-relaxed mb-8">
              {{ $t('appname_description') }}
            </p>
            
            <div class="grid md:grid-cols-2 gap-8">
              <div class="bg-orange-50 rounded-xl p-8 border border-orange-100">
                <h3 class="text-xl font-bold text-slate-900 mb-4">{{ $t('advanced_ticketing') }}</h3>
                <p class="text-slate-600 leading-relaxed">
                  {{ $t('ticketing_description') }}
                </p>
              </div>
              <div class="bg-orange-50 rounded-xl p-8 border border-orange-100">
                <h3 class="text-xl font-bold text-slate-900 mb-4">{{ $t('project_management') }}</h3>
                <p class="text-slate-600 leading-relaxed">
                  {{ $t('project_mgmt_description') }}
                </p>
              </div>
            </div>
          </div>

          <!-- Core Features -->
          <div>
            <h2 class="text-4xl font-bold text-slate-900 mb-6">{{ $t('powerful_features') }}</h2>
            <p class="text-lg text-slate-600 leading-relaxed mb-8">
              {{ $t('features_intro') }}
            </p>
            
            <div class="space-y-6">
              <!-- Feature 1 -->
              <div class="flex gap-4">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center h-12 w-12 rounded-md bg-orange-600 text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                  </div>
                </div>
                <div>
                  <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $t('comprehensive_ticketing_system') }}</h3>
                  <p class="text-slate-600 leading-relaxed">
                    {{ $t('ticketing_system_desc') }}
                  </p>
                </div>
              </div>

              <!-- Feature 2 -->
              <div class="flex gap-4">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center h-12 w-12 rounded-md bg-orange-600 text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                </div>
                <div>
                  <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $t('multiple_flexible_views') }}</h3>
                  <p class="text-slate-600 leading-relaxed">
                    {{ $t('flexible_views_desc') }}
                  </p>
                </div>
              </div>

              <!-- Feature 3 -->
              <div class="flex gap-4">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center h-12 w-12 rounded-md bg-orange-600 text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                <div>
                  <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $t('payment_gateway_integration') }}</h3>
                  <p class="text-slate-600 leading-relaxed">
                    {{ $t('payment_gateway_desc') }}
                  </p>
                </div>
              </div>

              <!-- Feature 4 -->
              <div class="flex gap-4">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center h-12 w-12 rounded-md bg-orange-600 text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                  </div>
                </div>
                <div>
                  <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $t('extensive_api_integrations') }}</h3>
                  <p class="text-slate-600 leading-relaxed">
                    {{ $t('api_integrations_desc') }}
                  </p>
                </div>
              </div>

              <!-- Feature 5 -->
              <div class="flex gap-4">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center h-12 w-12 rounded-md bg-orange-600 text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 8.048M7 9H3m14 0h4M9 20H7a2 2 0 01-2-2v-6a2 2 0 012-2h10a2 2 0 012 2v6a2 2 0 01-2 2h-2m0 0H9m0 0a2 2 0 002 2h2a2 2 0 002-2" />
                    </svg>
                  </div>
                </div>
                <div>
                  <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $t('team_collaboration') }}</h3>
                  <p class="text-slate-600 leading-relaxed">
                    {{ $t('collaboration_desc') }}
                  </p>
                </div>
              </div>

              <!-- Feature 6 -->
              <div class="flex gap-4">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center h-12 w-12 rounded-md bg-orange-600 text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                  </div>
                </div>
                <div>
                  <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $t('advanced_analytics') }}</h3>
                  <p class="text-slate-600 leading-relaxed">
                    {{ $t('analytics_desc') }}
                  </p>
                </div>
              </div>

              <!-- Feature 7 -->
              <div class="flex gap-4">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center h-12 w-12 rounded-md bg-orange-600 text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    </svg>
                  </div>
                </div>
                <div>
                  <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $t('custom_workflows') }}</h3>
                  <p class="text-slate-600 leading-relaxed">
                    {{ $t('workflows_desc') }}
                  </p>
                </div>
              </div>

              <!-- Feature 8 -->
              <div class="flex gap-4">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center h-12 w-12 rounded-md bg-orange-600 text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                  </div>
                </div>
                <div>
                  <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $t('enterprise_security') }}</h3>
                  <p class="text-slate-600 leading-relaxed">
                    {{ $t('security_desc') }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Why Choose us -->
          <div>
            <h2 class="text-4xl font-bold text-slate-900 mb-6">{{ $t('why_choose_appname') }}</h2>
            <div class="grid md:grid-cols-2 gap-6">
              <div class="flex items-start gap-4">
                <svg class="w-6 h-6 text-orange-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div>
                  <h3 class="font-bold text-slate-900 mb-1">{{ $t('all_in_one_solution') }}</h3>
                  <p class="text-slate-600">{{ $t('all_in_one_desc') }}</p>
                </div>
              </div>
              <div class="flex items-start gap-4">
                <svg class="w-6 h-6 text-orange-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div>
                  <h3 class="font-bold text-slate-900 mb-1">{{ $t('easy_to_use') }}</h3>
                  <p class="text-slate-600">{{ $t('easy_to_use_desc') }}</p>
                </div>
              </div>
              <div class="flex items-start gap-4">
                <svg class="w-6 h-6 text-orange-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div>
                  <h3 class="font-bold text-slate-900 mb-1">{{ $t('scalable_flexible') }}</h3>
                  <p class="text-slate-600">{{ $t('scalable_desc') }}</p>
                </div>
              </div>
              <div class="flex items-start gap-4">
                <svg class="w-6 h-6 text-orange-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div>
                  <h3 class="font-bold text-slate-900 mb-1">{{ $t('powerful_integrations') }}</h3>
                  <p class="text-slate-600">{{ $t('integrations_desc') }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Call to Action -->
          <div class="bg-gradient-to-br from-orange-50 to-white rounded-2xl p-12 text-center border border-orange-100">
            <h3 class="text-3xl font-bold text-slate-900 mb-4">{{ $t('ready_to_transform_workflow') }}</h3>
            <p class="text-lg text-slate-600 mb-8">
              {{ $t('join_thousands_teams') }}
            </p>
            <Link href="/register" class="inline-block px-8 py-4 bg-orange-600 text-white font-semibold rounded-lg hover:bg-orange-700 transition-colors cursor-pointer">
              {{ $t('start_free_trial_button') }}
            </Link>
          </div>
        </div>
      </div>
    </section>
  </GuestLayout>
</template>
