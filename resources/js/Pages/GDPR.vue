<script setup>
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
</script>

<template>
  <GuestLayout>
    <!-- Content Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-white">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-5xl font-bold text-slate-900 mb-12">GDPR Compliance</h1>
        
        <div class="space-y-8">
          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">GDPR Overview</h2>
            <p class="text-slate-600 leading-relaxed">
              Asira is fully compliant with the General Data Protection Regulation (GDPR), the EU's comprehensive data protection regulation that took effect on May 25, 2018.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Data Processing Agreement</h2>
            <p class="text-slate-600 leading-relaxed">
              We have in place a Data Processing Agreement (DPA) that complies with GDPR requirements for organizations processing the personal data of EU residents.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Your Rights</h2>
            <p class="text-slate-600 leading-relaxed mb-4">Under GDPR, you have the following rights:</p>
            <ul class="space-y-2 text-slate-600">
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 text-orange-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Right to access your personal data
              </li>
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 text-orange-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Right to rectification of inaccurate data
              </li>
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 text-orange-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Right to erasure ("right to be forgotten")
              </li>
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 text-orange-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Right to restrict processing
              </li>
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 text-orange-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Right to data portability
              </li>
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 text-orange-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Right to object to processing
              </li>
            </ul>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Exercising Your Rights</h2>
            <p class="text-slate-600 leading-relaxed">
              To exercise any of your GDPR rights, please contact us at privacy@asira.in with your request. We will respond within 30 days or as required by law.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Data Transfers</h2>
            <p class="text-slate-600 leading-relaxed">
              For any data transfers outside the EU/EEA, we rely on appropriate safeguards such as the Standard Contractual Clauses as approved by the European Commission.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Data Breach Notification</h2>
            <p class="text-slate-600 leading-relaxed">
              In the event of a data breach affecting personal data, we will notify affected individuals and relevant authorities as required under GDPR Article 33 and 34.
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

