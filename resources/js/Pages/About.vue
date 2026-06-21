<script setup>
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
</script>

<template>
  <GuestLayout>
    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-orange-50 via-white to-orange-50">
      <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-5xl font-bold text-slate-900 mb-6">About Asira</h1>
        <p class="text-xl text-slate-600">The Complete Ticketing & Project Management Solution</p>
      </div>
    </section>

    <!-- Mission Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-white">
      <div class="max-w-4xl mx-auto">
        <div class="space-y-16">
          <!-- Our Mission -->
          <div>
            <h2 class="text-4xl font-bold text-slate-900 mb-6">Our Mission</h2>
            <p class="text-lg text-slate-600 leading-relaxed mb-6">
              Asira is a comprehensive ticketing and project management platform designed to empower teams, streamline workflows, and maximize productivity. We believe that organizations deserve a powerful, intuitive system that brings all their work together in one placeâ€”without unnecessary complexity.
            </p>
            <p class="text-lg text-slate-600 leading-relaxed">
              Our mission is to transform how teams collaborate, communicate, and deliver results. Whether you're managing support tickets, organizing projects, or coordinating complex workflows, Asira provides the tools you need to succeed.
            </p>
          </div>

          <!-- What is Asira -->
          <div>
            <h2 class="text-4xl font-bold text-slate-900 mb-6">What is Asira?</h2>
            <p class="text-lg text-slate-600 leading-relaxed mb-8">
              Asira is an all-in-one ticketing and project management system built for modern teams. It combines powerful ticketing capabilities with flexible project management views, making it the perfect solution for support teams, IT departments, marketing groups, and organizations of any size.
            </p>
            
            <div class="grid md:grid-cols-2 gap-8">
              <div class="bg-orange-50 rounded-xl p-8 border border-orange-100">
                <h3 class="text-xl font-bold text-slate-900 mb-4">Advanced Ticketing</h3>
                <p class="text-slate-600 leading-relaxed">
                  Handle customer inquiries, support requests, and issues with ease. Track ticket status, assign priorities, and manage workflows efficiently.
                </p>
              </div>
              <div class="bg-orange-50 rounded-xl p-8 border border-orange-100">
                <h3 class="text-xl font-bold text-slate-900 mb-4">Project Management</h3>
                <p class="text-slate-600 leading-relaxed">
                  Organize projects with multiple flexible views including Kanban boards, timeline views, and calendar layouts for complete visibility.
                </p>
              </div>
            </div>
          </div>

          <!-- Core Features -->
          <div>
            <h2 class="text-4xl font-bold text-slate-900 mb-6">Powerful Features</h2>
            <p class="text-lg text-slate-600 leading-relaxed mb-8">
              Asira comes packed with nearly all the functionality organizations need to succeed:
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
                  <h3 class="text-xl font-bold text-slate-900 mb-2">Comprehensive Ticketing System</h3>
                  <p class="text-slate-600 leading-relaxed">
                    Create, track, and manage tickets with custom fields, priority levels, due dates, and automated workflows. Support multiple ticket types and categories to match your organization's needs.
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
                  <h3 class="text-xl font-bold text-slate-900 mb-2">Multiple Flexible Views</h3>
                  <p class="text-slate-600 leading-relaxed">
                    Switch between Kanban boards, list views, timeline/Gantt charts, and calendar layouts. See your work in the way that works best for you, all from the same data.
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
                  <h3 class="text-xl font-bold text-slate-900 mb-2">Payment Gateway Integration</h3>
                  <p class="text-slate-600 leading-relaxed">
                    Easily integrate with popular payment gateways for seamless billing, subscriptions, and transaction management. Support multiple payment methods and currencies.
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
                  <h3 class="text-xl font-bold text-slate-900 mb-2">Extensive API Integrations</h3>
                  <p class="text-slate-600 leading-relaxed">
                    Connect with your favorite tools and services. Asira integrates with leading platforms for CRM, communication, documentation, and more. Build custom integrations with our comprehensive REST API.
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
                  <h3 class="text-xl font-bold text-slate-900 mb-2">Team Collaboration & Communication</h3>
                  <p class="text-slate-600 leading-relaxed">
                    Comment on tickets, mention team members, share files, and track all activities. Keep conversations organized and maintain full context of decisions.
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
                  <h3 class="text-xl font-bold text-slate-900 mb-2">Advanced Analytics & Reporting</h3>
                  <p class="text-slate-600 leading-relaxed">
                    Track key metrics, generate detailed reports, and gain insights into your team's performance. Monitor ticket resolution times, workload distribution, and productivity trends.
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
                  <h3 class="text-xl font-bold text-slate-900 mb-2">Custom Workflows & Automation</h3>
                  <p class="text-slate-600 leading-relaxed">
                    Design custom workflows that match your process. Automate repetitive tasks with rules, triggers, and actions to save time and reduce errors.
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
                  <h3 class="text-xl font-bold text-slate-900 mb-2">Enterprise Security</h3>
                  <p class="text-slate-600 leading-relaxed">
                    Bank-grade encryption, multi-factor authentication, role-based access control, and compliance with GDPR, CCPA, and other regulations to protect your data.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Why Choose Asira -->
          <div>
            <h2 class="text-4xl font-bold text-slate-900 mb-6">Why Choose Asira?</h2>
            <div class="grid md:grid-cols-2 gap-6">
              <div class="flex items-start gap-4">
                <svg class="w-6 h-6 text-orange-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div>
                  <h3 class="font-bold text-slate-900 mb-1">All-in-One Solution</h3>
                  <p class="text-slate-600">No need for multiple toolsâ€”everything you need is in one integrated platform.</p>
                </div>
              </div>
              <div class="flex items-start gap-4">
                <svg class="w-6 h-6 text-orange-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div>
                  <h3 class="font-bold text-slate-900 mb-1">Easy to Use</h3>
                  <p class="text-slate-600">Intuitive interface that requires minimal trainingâ€”your team will be productive from day one.</p>
                </div>
              </div>
              <div class="flex items-start gap-4">
                <svg class="w-6 h-6 text-orange-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div>
                  <h3 class="font-bold text-slate-900 mb-1">Scalable & Flexible</h3>
                  <p class="text-slate-600">Grows with your organization, from startups to enterprisesâ€”adapt the system to your needs.</p>
                </div>
              </div>
              <div class="flex items-start gap-4">
                <svg class="w-6 h-6 text-orange-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div>
                  <h3 class="font-bold text-slate-900 mb-1">Powerful Integrations</h3>
                  <p class="text-slate-600">Connect with your favorite tools and services through our extensive API.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Call to Action -->
          <div class="bg-gradient-to-br from-orange-50 to-white rounded-2xl p-12 text-center border border-orange-100">
            <h3 class="text-3xl font-bold text-slate-900 mb-4">Ready to Transform Your Workflow?</h3>
            <p class="text-lg text-slate-600 mb-8">
              Join thousands of teams already using Asira to manage their tickets and projects more effectively.
            </p>
            <Link href="/register" class="inline-block px-8 py-4 bg-orange-600 text-white font-semibold rounded-lg hover:bg-orange-700 transition-colors cursor-pointer">
              Start Your Free Trial
            </Link>
          </div>
        </div>
      </div>
    </section>
  </GuestLayout>
</template>

