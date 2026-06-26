<script setup>
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
</script>

<template>
  <GuestLayout>
    <!-- Content Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-white">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-5xl font-bold text-slate-900 mb-12">{{ $t('security') }}</h1>
        
        <div class="space-y-8">
          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">{{ $t('security_at_appname') }}</h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('security_intro_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">{{ $t('security_encryption') }}</h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('security_encryption_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">{{ $t('security_authentication') }}</h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('security_authentication_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">{{ $t('security_regular_audits') }}</h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('security_audits_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">{{ $t('security_compliance') }}</h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('security_compliance_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">{{ $t('security_incident_response') }}</h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('security_incident_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">{{ $t('security_reporting') }}</h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('security_reporting_text') }}
            </p>
          </div>

          <p class="text-sm text-slate-500 mt-12 pt-8 border-t border-slate-200">
            {{ $t('security_last_updated') }} {{ new Date().toLocaleDateString() }}
          </p>
        </div>
      </div>
    </section>
  </GuestLayout>
</template>
