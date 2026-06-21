<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const submitted = ref(false);

const form = useForm({
  name: '',
  email: '',
  subject: '',
  message: ''
});

const handleSubmit = () => {
  form.post('/api/contact', {
    onSuccess() {
      submitted.value = true;
      form.reset();
      
      setTimeout(() => {
        submitted.value = false;
      }, 5000);
    },
    onError(errors) {
      console.error('Form errors:', errors);
    }
  });
};
</script>

<template>
  <GuestLayout>
    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-orange-50 via-white to-orange-50">
      <div class="max-w-5xl mx-auto text-center">
        <h1 class="text-5xl sm:text-6xl font-bold text-slate-900 mb-6">Get in Touch</h1>
        <p class="text-xl text-slate-600 max-w-2xl mx-auto">
          Have a question or feedback? We'd love to hear from you. Send us a message and we'll get back to you as soon as possible.
        </p>
      </div>
    </section>

    <!-- Contact Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-white">
      <div class="max-w-6xl mx-auto">
        <div class="grid lg:grid-cols-3 gap-12 items-start">
          <!-- Contact Info Cards -->
          <div class="space-y-6">
            <!-- Email Card -->
            <div class="bg-gradient-to-br from-orange-50 to-white rounded-2xl p-8 border border-orange-100 hover:shadow-lg transition-shadow">
              <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-orange-600 rounded-xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900">Email</h3>
              </div>
              <p class="text-slate-600 mb-2">Send us an email anytime</p>
              <a href="mailto:support@asira.in" class="text-orange-600 font-semibold hover:text-orange-700 transition-colors">support@asira.in</a>
            </div>

            <!-- Phone Card -->
            <div class="bg-gradient-to-br from-orange-50 to-white rounded-2xl p-8 border border-orange-100 hover:shadow-lg transition-shadow">
              <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-orange-600 rounded-xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900">Phone</h3>
              </div>
              <p class="text-slate-600 mb-2">Call us during business hours</p>
              <a href="tel:+1234567890" class="text-orange-600 font-semibold hover:text-orange-700 transition-colors">+1 (234) 567-890</a>
            </div>

            <!-- Location Card -->
            <div class="bg-gradient-to-br from-orange-50 to-white rounded-2xl p-8 border border-orange-100 hover:shadow-lg transition-shadow">
              <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-orange-600 rounded-xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900">Location</h3>
              </div>
              <p class="text-slate-600">
                123 Business Street<br>
                New York, NY 10001<br>
                United States
              </p>
            </div>

            <!-- Response Time Card -->
            <div class="bg-gradient-to-br from-green-50 to-white rounded-2xl p-8 border border-green-100">
              <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-green-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div>
                  <h3 class="font-bold text-slate-900 mb-1">Quick Response</h3>
                  <p class="text-sm text-slate-600">We typically respond within 24 hours</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Contact Form -->
          <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl p-8 shadow-lg border border-slate-200">
              <!-- Success Message -->
              <div v-if="submitted" class="mb-6 p-4 bg-green-50 border-l-4 border-green-600 rounded-lg animate-slide-in">
                <div class="flex items-center gap-3">
                  <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <div>
                    <h4 class="font-bold text-green-900">Message Sent Successfully!</h4>
                    <p class="text-sm text-green-700">Thank you for reaching out. We'll get back to you soon.</p>
                  </div>
                </div>
              </div>

              <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Name Field -->
                <div>
                  <label class="block text-sm font-semibold text-slate-900 mb-2">Full Name</label>
                  <input 
                    v-model="form.name" 
                    type="text" 
                    required
                    placeholder="John Doe"
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-600 focus:border-transparent outline-none transition-all placeholder-slate-400"
                  />
                  <p v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</p>
                </div>

                <!-- Email Field -->
                <div>
                  <label class="block text-sm font-semibold text-slate-900 mb-2">Email Address</label>
                  <input 
                    v-model="form.email" 
                    type="email" 
                    required
                    placeholder="john@example.com"
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-600 focus:border-transparent outline-none transition-all placeholder-slate-400"
                  />
                  <p v-if="form.errors.email" class="text-red-600 text-sm mt-1">{{ form.errors.email }}</p>
                </div>

                <!-- Subject Field -->
                <div>
                  <label class="block text-sm font-semibold text-slate-900 mb-2">Subject</label>
                  <input 
                    v-model="form.subject" 
                    type="text" 
                    required
                    placeholder="What is this about?"
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-600 focus:border-transparent outline-none transition-all placeholder-slate-400"
                  />
                  <p v-if="form.errors.subject" class="text-red-600 text-sm mt-1">{{ form.errors.subject }}</p>
                </div>

                <!-- Message Field -->
                <div>
                  <label class="block text-sm font-semibold text-slate-900 mb-2">Message</label>
                  <textarea 
                    v-model="form.message" 
                    rows="6" 
                    required
                    placeholder="Tell us more about your inquiry..."
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-600 focus:border-transparent outline-none transition-all placeholder-slate-400 resize-none"
                  ></textarea>
                  <p v-if="form.errors.message" class="text-red-600 text-sm mt-1">{{ form.errors.message }}</p>
                </div>

                <!-- Submit Button -->
                <button 
                  type="submit" 
                  :disabled="form.processing"
                  class="w-full px-6 py-3 bg-gradient-to-r from-orange-600 to-orange-700 text-white font-semibold rounded-lg hover:from-orange-700 hover:to-orange-800 transition-all duration-200 cursor-pointer disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                >
                  <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                  <span>{{ form.processing ? 'Sending...' : 'Send Message' }}</span>
                </button>

                <!-- Privacy Notice -->
                <p class="text-xs text-slate-500 text-center">
                  We respect your privacy. Your message will only be used to get back to you.
                </p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-slate-50">
      <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12">
          <h2 class="text-4xl font-bold text-slate-900 mb-4">Frequently Asked Questions</h2>
          <p class="text-lg text-slate-600">Find answers to common questions</p>
        </div>

        <div class="space-y-4">
          <details class="group bg-white rounded-lg p-6 border border-slate-200 hover:border-orange-300 transition-colors cursor-pointer">
            <summary class="flex items-center justify-between font-semibold text-slate-900">
              What is the response time?
              <svg class="w-6 h-6 text-slate-600 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
              </svg>
            </summary>
            <p class="text-slate-600 mt-4">We aim to respond to all inquiries within 24 hours during business days. For urgent matters, please call us directly.</p>
          </details>

          <details class="group bg-white rounded-lg p-6 border border-slate-200 hover:border-orange-300 transition-colors cursor-pointer">
            <summary class="flex items-center justify-between font-semibold text-slate-900">
              Do you offer technical support?
              <svg class="w-6 h-6 text-slate-600 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
              </svg>
            </summary>
            <p class="text-slate-600 mt-4">Yes! Our support team can assist with technical issues. For faster support, visit our help center or submit a support ticket in your dashboard.</p>
          </details>

          <details class="group bg-white rounded-lg p-6 border border-slate-200 hover:border-orange-300 transition-colors cursor-pointer">
            <summary class="flex items-center justify-between font-semibold text-slate-900">
              What about partnership opportunities?
              <svg class="w-6 h-6 text-slate-600 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
              </svg>
            </summary>
            <p class="text-slate-600 mt-4">We're always interested in partnerships! Please select "Partnership Inquiry" in the subject line and tell us about your idea.</p>
          </details>

          <details class="group bg-white rounded-lg p-6 border border-slate-200 hover:border-orange-300 transition-colors cursor-pointer">
            <summary class="flex items-center justify-between font-semibold text-slate-900">
              How do I report a bug?
              <svg class="w-6 h-6 text-slate-600 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
              </svg>
            </summary>
            <p class="text-slate-600 mt-4">Please send us details about the bug you found, including steps to reproduce it. You can also reach out to security@asira.in for security-related issues.</p>
          </details>
        </div>
      </div>
    </section>
  </GuestLayout>
</template>

<style scoped>
@keyframes slide-in {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-slide-in {
  animation: slide-in 0.3s ease-out;
}
</style>

