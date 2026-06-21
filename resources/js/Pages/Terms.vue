<script setup>
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
</script>

<template>
  <GuestLayout>
    <!-- Content Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-white">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-5xl font-bold text-slate-900 mb-12">Terms of Service</h1>
        
        <div class="space-y-8">
          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Acceptance of Terms</h2>
            <p class="text-slate-600 leading-relaxed">
              By accessing and using Asira, you accept and agree to be bound by the terms and provision of this agreement.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Use License</h2>
            <p class="text-slate-600 leading-relaxed">
              Permission is granted to temporarily download one copy of the materials (information or software) on Asira for personal, non-commercial transitory viewing only.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Disclaimer</h2>
            <p class="text-slate-600 leading-relaxed">
              The materials on Asira are provided on an 'as is' basis. Asira makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Limitations</h2>
            <p class="text-slate-600 leading-relaxed">
              In no event shall Asira or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on Asira.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Accuracy of Materials</h2>
            <p class="text-slate-600 leading-relaxed">
              The materials appearing on Asira could include technical, typographical, or photographic errors. Asira does not warrant that any of the materials on Asira are accurate, complete, or current.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Modifications</h2>
            <p class="text-slate-600 leading-relaxed">
              Asira may revise these terms of service for its website at any time without notice. By using this website, you are agreeing to be bound by the then current version of these terms of service.
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

