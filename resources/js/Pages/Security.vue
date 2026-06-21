<script setup>
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
</script>

<template>
  <GuestLayout>
    <!-- Content Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-white">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-5xl font-bold text-slate-900 mb-12">Security</h1>
        
        <div class="space-y-8">
          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Security at Asira</h2>
            <p class="text-slate-600 leading-relaxed">
              We take security seriously and implement industry-leading practices to protect your data and ensure the safety of your account.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Encryption</h2>
            <p class="text-slate-600 leading-relaxed">
              All data in transit is encrypted using TLS 1.2 or higher. Data at rest is encrypted with AES-256 encryption to ensure maximum security.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Authentication</h2>
            <p class="text-slate-600 leading-relaxed">
              We support multi-factor authentication (MFA) to add an extra layer of security to your account. We recommend enabling MFA for all users.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Regular Audits</h2>
            <p class="text-slate-600 leading-relaxed">
              Our infrastructure undergoes regular security audits and penetration testing by third-party security experts to identify and address potential vulnerabilities.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Compliance</h2>
            <p class="text-slate-600 leading-relaxed">
              Asira complies with GDPR, CCPA, and other relevant data protection regulations to ensure your data is handled in accordance with applicable laws.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Incident Response</h2>
            <p class="text-slate-600 leading-relaxed">
              In the unlikely event of a security incident, we have a comprehensive incident response plan in place to minimize impact and notify affected users promptly.
            </p>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Security Reporting</h2>
            <p class="text-slate-600 leading-relaxed">
              If you discover a security vulnerability, please contact us immediately at security@asira.in instead of disclosing it publicly.
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

