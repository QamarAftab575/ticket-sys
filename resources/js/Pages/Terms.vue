<script setup>
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
</script>

<template>
  <GuestLayout>
    <!-- Content Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-white">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-5xl font-bold text-slate-900 mb-12">{{ $t('terms_of_service') }}</h1>
        
        <div class="space-y-8">
          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">{{ $t('terms_acceptance') }}</h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('terms_acceptance_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">{{ $t('terms_use_license') }}</h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('terms_use_license_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">{{ $t('terms_disclaimer') }}</h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('terms_disclaimer_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">{{ $t('terms_limitations') }}</h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('terms_limitations_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">{{ $t('terms_accuracy') }}</h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('terms_accuracy_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">{{ $t('terms_modifications') }}</h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('terms_modifications_text') }}
            </p>
          </div>

          <p class="text-sm text-slate-500 mt-12 pt-8 border-t border-slate-200">
            {{ $t('terms_last_updated') }} {{ new Date().toLocaleDateString() }}
          </p>
        </div>
      </div>
    </section>
  </GuestLayout>
</template>
