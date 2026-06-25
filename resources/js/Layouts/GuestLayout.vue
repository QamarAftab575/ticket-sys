<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const page = usePage();
const isScrolled = ref(false);
const mobileMenuOpen = ref(false);

// Computed properties for auth checking
const user = computed(() => page.props.auth?.user);
const isAuthenticated = computed(() => !!user.value);
const isAdmin = computed(() => user.value?.is_admin ?? false);

// Determine dashboard link
const dashboardLink = computed(() => {
  if (isAdmin.value) {
    return '/admin';
  }
  return '/dashboard';
});

// Determine button text
const buttonText = computed(() => {
  const getStarted = page.props.translations?.get_started || 'Get started';
  const myDashboard = page.props.translations?.my_dashboard || 'My Dashboard';
  
  if (!isAuthenticated.value) {
    return getStarted;
  }
  return myDashboard;
});

// Determine button href
const buttonHref = computed(() => {
  if (!isAuthenticated.value) {
    return '/register';
  }
  return dashboardLink.value;
});

onMounted(() => {
  window.addEventListener('scroll', () => {
    isScrolled.value = window.scrollY > 20;
  });
});
</script>

<template>
  <div class="min-h-screen bg-white dark:bg-slate-950">
    <!-- Navbar - Professional minimal design -->
    <nav :class="[
      'fixed top-4 left-4 right-4 z-50 transition-all duration-200 rounded-lg',
      isScrolled 
        ? 'bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border border-slate-200 dark:border-slate-800 shadow-lg' 
        : 'bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border border-slate-200 dark:border-slate-800'
    ]">
      <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between h-14">
          <!-- Logo -->
          <Link href="/" class="flex items-center space-x-3 cursor-pointer">
            <img src="/assets/images/logo/asira-logo-main.png" alt="Asira Logo" class="h-8 w-auto" />
          </Link>

          <!-- Desktop Navigation -->
          <div class="hidden lg:flex items-center space-x-1">
            <Link href="/about" class="px-3 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium transition-colors duration-200 cursor-pointer rounded-md hover:bg-slate-100 dark:hover:bg-slate-800">{{ $t('about') }}</Link>
            <a href="/#features" class="px-3 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium transition-colors duration-200 cursor-pointer rounded-md hover:bg-slate-100 dark:hover:bg-slate-800">{{ $t('features') }}</a>
            <a href="/#views" class="px-3 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium transition-colors duration-200 cursor-pointer rounded-md hover:bg-slate-100 dark:hover:bg-slate-800">{{ $t('views') }}</a>
            <a href="/#pricing" class="px-3 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium transition-colors duration-200 cursor-pointer rounded-md hover:bg-slate-100 dark:hover:bg-slate-800">{{ $t('pricing') }}</a>
          </div>

          <!-- CTA Buttons -->
          <div class="hidden lg:flex items-center space-x-3">
            <!-- Sign In (only for non-authenticated users) -->
            <Link v-if="!isAuthenticated" href="/login" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium transition-colors duration-200 cursor-pointer">
              {{ $t('sign_in') }}
            </Link>
            <!-- Single CTA Button -->
            <Link :href="buttonHref" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-medium rounded-lg transition-colors duration-200 cursor-pointer">
              {{ buttonText }}
            </Link>
          </div>

          <!-- Mobile Menu Button -->
          <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Mobile Menu -->
        <div v-if="mobileMenuOpen" class="lg:hidden py-4 border-t border-slate-200 dark:border-slate-800">
          <div class="flex flex-col space-y-2">
            <Link href="/about" class="px-3 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium cursor-pointer rounded-md hover:bg-slate-100 dark:hover:bg-slate-800">{{ $t('about') }}</Link>
            <a href="/#features" class="px-3 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium cursor-pointer rounded-md hover:bg-slate-100 dark:hover:bg-slate-800">{{ $t('features') }}</a>
            <a href="/#views" class="px-3 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium cursor-pointer rounded-md hover:bg-slate-100 dark:hover:bg-slate-800">{{ $t('views') }}</a>
            <a href="/#pricing" class="px-3 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium cursor-pointer rounded-md hover:bg-slate-100 dark:hover:bg-slate-800">{{ $t('pricing') }}</a>
            <!-- Sign In (only for non-authenticated users) -->
            <Link v-if="!isAuthenticated" href="/login" class="px-3 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium rounded-md hover:bg-slate-100 dark:hover:bg-slate-800">{{ $t('sign_in') }}</Link>
            <!-- Single CTA Button -->
            <Link :href="buttonHref" class="px-4 py-2 text-sm bg-blue-600 text-white font-medium rounded-lg text-center mt-2">
              {{ buttonText }}
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page Content -->
    <slot />

    <!-- Footer - Minimal professional design -->
    <footer class="bg-white dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 py-12 px-6">
      <div class="max-w-6xl mx-auto">
        <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-8 mb-12">
          <!-- Company Info -->
          <div>
            <Link href="/" class="flex items-center space-x-3 mb-4 cursor-pointer">
              <img src="/assets/images/logo/asira-logo-main.png" alt="Asira Logo" class="h-8 w-auto" />
            </Link>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              {{ $t('project_management_tagline') }}
            </p>
          </div>

          <!-- Product -->
          <div>
            <h4 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">{{ $t('product') }}</h4>
            <ul class="space-y-3 text-sm">
              <li><a href="/#features" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer">{{ $t('features') }}</a></li>
              <li><a href="/#pricing" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer">{{ $t('pricing') }}</a></li>
              <li><a href="/docs/api" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer">{{ $t('api_docs') }}</a></li>
            </ul>
          </div>

          <!-- Company -->
          <div>
            <h4 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">{{ $t('company') }}</h4>
            <ul class="space-y-3 text-sm">
              <li><Link href="/about" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer">{{ $t('about') }}</Link></li>
              <li><Link href="/contact" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer">{{ $t('contact') }}</Link></li>
            </ul>
          </div>

          <!-- Legal -->
          <div>
            <h4 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">{{ $t('legal') }}</h4>
            <ul class="space-y-3 text-sm">
              <li><Link href="/privacy" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer">{{ $t('privacy') }}</Link></li>
              <li><Link href="/terms" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer">{{ $t('terms') }}</Link></li>
              <li><Link href="/security" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer">{{ $t('security') }}</Link></li>
              <li><Link href="/cookie-policy" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer">{{ $t('cookie_policy') }}</Link></li>
            </ul>
          </div>
        </div>

        <div class="border-t border-slate-200 dark:border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
          <p class="text-sm text-slate-500 dark:text-slate-400">
            © {{ new Date().getFullYear() }} Asira. {{ $t('all_rights_reserved') }}
          </p>
          <div class="flex gap-6">
            <a href="#" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors cursor-pointer" :aria-label="$t('follow_facebook')">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
              </svg>
            </a>
            <a href="#" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors cursor-pointer" :aria-label="$t('follow_twitter')">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
              </svg>
            </a>
            <a href="#" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors cursor-pointer" :aria-label="$t('follow_linkedin')">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}
</style>
