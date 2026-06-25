<script setup>
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
</script>

<template>
  <GuestLayout>
    <!-- Content Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-white">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-5xl font-bold text-slate-900 mb-12">{{ $t('privacy_policy') }}</h1>
        
        <div class="space-y-8">
          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">{{ $t('privacy_introduction') }}</h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('privacy_intro_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">{{ $t('privacy_information_we_collect') }}</h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('privacy_collect_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">{{ $t('privacy_how_we_use') }}</h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('privacy_use_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">{{ $t('privacy_data_security') }}</h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('privacy_security_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">{{ $t('privacy_contact_us') }}</h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('privacy_contact_text') }}
            </p>
          </div>

          <p class="text-sm text-slate-500 mt-12 pt-8 border-t border-slate-200">
            {{ $t('privacy_last_updated') }} {{ new Date().toLocaleDateString() }}
          </p>
        </div>
      </div>
    </section>
  </GuestLayout>
</template>
