<script setup>
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
</script>

<template>
  <GuestLayout>
    <!-- Content Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-white">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-5xl font-bold text-slate-900 mb-12">Privacy Policy</h1>
        
        <div class="space-y-8">
          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Introduction</h2>
            <p class="text-slate-600 leading-relaxed">
              Asira ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Information We Collect</h2>
            <p class="text-slate-600 leading-relaxed">
              We collect information you provide directly to us, such as when you create an account, use our services, or contact us for support. This may include your name, email address, and project data.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">How We Use Your Information</h2>
            <p class="text-slate-600 leading-relaxed">
              We use the information we collect to provide, maintain, and improve our services, communicate with you, and comply with legal obligations.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Data Security</h2>
            <p class="text-slate-600 leading-relaxed">
              We implement appropriate technical and organizational measures to protect your data against unauthorized access, alteration, disclosure, or destruction.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Contact Us</h2>
            <p class="text-slate-600 leading-relaxed">
              If you have questions about this Privacy Policy, please contact us at support@asira.in
            </p>
          </div>

          <p class="text-sm text-slate-500 mt-12 pt-8 border-t border-slate-200">
            Last updated: {{ new Date().toLocaleDateString() }}
          </p>
        </div>
      </div>
    </section>
  </GuestLayout>
</template>

