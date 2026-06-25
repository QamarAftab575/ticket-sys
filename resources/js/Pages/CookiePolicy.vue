<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
</script>

<template>
  <GuestLayout>
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-white">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-5xl font-bold text-slate-900 mb-12">
          {{ $t('cookie_policy') }}
        </h1>

        <div class="space-y-8">
          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">
              {{ $t('cookie_introduction') }}
            </h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('cookie_intro_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">
              {{ $t('cookie_what_are_cookies') }}
            </h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('cookie_what_are_cookies_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">
              {{ $t('cookie_types_we_use') }}
            </h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('cookie_types_we_use_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">
              {{ $t('cookie_essential_cookies') }}
            </h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('cookie_essential_cookies_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">
              {{ $t('cookie_third_party') }}
            </h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('cookie_third_party_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">
              {{ $t('cookie_manage_preferences') }}
            </h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('cookie_manage_preferences_text') }}
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">
              {{ $t('cookie_contact_us') }}
            </h2>
            <p class="text-slate-600 leading-relaxed">
              {{ $t('cookie_contact_us_text') }}
            </p>
          </div>

          <p class="text-sm text-slate-500 mt-12 pt-8 border-t border-slate-200">
            {{ $t('cookie_last_updated') }} {{ new Date().toLocaleDateString() }}
          </p>
        </div>
      </div>
    </section>
  </GuestLayout>
</template>