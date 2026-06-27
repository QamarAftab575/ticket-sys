<template>
  <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center px-4 py-16" role="main">

    <!-- Content card — matches dashboard card style exactly -->
    <div class="w-full max-w-lg bg-white rounded-xl shadow-sm border border-gray-100 px-10 py-10 text-center">

      <!-- Illustration icon -->
      <div class="flex justify-center mb-6">
        <div class="relative inline-flex">
          <!-- Soft tinted background circle -->
          <div
            class="w-20 h-20 rounded-full flex items-center justify-center"
            :class="iconBgColor"
            aria-hidden="true"
          >
            <component :is="errorIcon" class="w-9 h-9" :class="iconColor" />
          </div>

          <!-- Small status dot -->
          <span
            class="absolute -top-1 -right-1 w-4 h-4 rounded-full border-2 border-white flex items-center justify-center"
            :class="dotBgColor"
            aria-hidden="true"
          >
            <span class="w-1.5 h-1.5 rounded-full bg-white" />
          </span>
        </div>
      </div>

      <!-- Error code -->
      <p
        class="text-5xl font-bold tracking-tight mb-3 select-none"
        :class="codeColor"
        :aria-label="`Error ${status}`"
      >
        {{ status }}
      </p>

      <!-- Title -->
      <h1 class="text-xl font-semibold text-gray-900 mb-2">
        {{ title }}
      </h1>

      <!-- Description -->
      <p class="text-sm text-gray-500 leading-relaxed mb-0 max-w-xs mx-auto">
        {{ description }}
      </p>

      <!-- Action buttons -->
      <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-8">

        <!-- Primary CTA -->
        <a
          :href="primaryAction.href"
          class="cursor-pointer w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
          <component :is="primaryAction.icon" class="w-4 h-4 shrink-0" aria-hidden="true" />
          {{ primaryAction.label }}
        </a>

        <!-- Go Back (not shown on 500 — no prior page to return to safely) -->
        <button
          v-if="status !== 500"
          @click="goBack"
          class="cursor-pointer w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-50 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2"
        >
          <ArrowLeftIcon class="w-4 h-4 shrink-0" aria-hidden="true" />
          Go Back
        </button>

        <!-- Retry (500 only) -->
        <button
          v-if="status === 500"
          @click="retry"
          class="cursor-pointer w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-50 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2"
        >
          <ArrowPathIcon class="w-4 h-4 shrink-0" aria-hidden="true" />
          Try Again
        </button>

        <!-- Tertiary action (e.g. My Tasks for 404) -->
        <a
          v-if="secondaryAction"
          :href="secondaryAction.href"
          class="cursor-pointer w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-50 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2"
        >
          <component :is="secondaryAction.icon" class="w-4 h-4 shrink-0" aria-hidden="true" />
          {{ secondaryAction.label }}
        </a>
      </div>

      <!-- Support note (500 only) -->
      <p v-if="status === 500" class="mt-6 text-xs text-gray-400">
        If this keeps happening, please
        <a
          href="/contact"
          class="text-blue-600 hover:text-blue-700 underline underline-offset-2 transition-colors cursor-pointer"
        >
          contact support
        </a>.
      </p>

      <!-- Divider + status label -->
      <div class="mt-8 pt-6 border-t border-gray-100 flex items-center justify-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-gray-300" aria-hidden="true" />
        <span class="text-[11px] text-gray-400 uppercase tracking-widest font-medium">
          {{ statusLabel }}
        </span>
        <span class="w-1.5 h-1.5 rounded-full bg-gray-300" aria-hidden="true" />
      </div>
    </div>

    <!-- Subtle brand link below card -->
    <a
      href="/"
      class="cursor-pointer mt-6 inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-gray-600 transition-colors duration-150"
    >
      <img
        src="/assets/images/logo/default-logo-main.png"
        alt="Tasqo"
        class="h-4 w-auto opacity-60"
        @error="(e) => e.target.style.display = 'none'"
      />
      <span>Tasqo</span>
    </a>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  MagnifyingGlassIcon,
  ServerStackIcon,
  LockClosedIcon,
  ShieldExclamationIcon,
  ExclamationTriangleIcon,
  HomeIcon,
  ClipboardDocumentListIcon,
  ArrowLeftIcon,
  ArrowPathIcon,
  Squares2X2Icon,
  WrenchScrewdriverIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  status: {
    type: Number,
    default: 404,
  },
})

// ─── Per-status configuration ─────────────────────────────────────────────────

const errorConfig = computed(() => {
  const configs = {
    404: {
      title: 'Page Not Found',
      description:
        "Sorry, we couldn't find the page you're looking for. It may have been moved, deleted, or the URL may be incorrect.",
      icon: MagnifyingGlassIcon,
      iconBg:  'bg-blue-50',
      iconColor: 'text-blue-500',
      dotBg:  'bg-blue-500',
      code:   'text-blue-500',
      primaryAction: { href: '/dashboard',  label: 'Go to Dashboard', icon: HomeIcon },
      secondaryAction: { href: '/my-tasks', label: 'My Tasks',        icon: ClipboardDocumentListIcon },
      statusLabel: 'Page Not Found',
    },
    403: {
      title: 'Access Denied',
      description:
        "You don't have permission to view this page. Contact your workspace admin if you think this is a mistake.",
      icon: LockClosedIcon,
      iconBg:  'bg-orange-50',
      iconColor: 'text-orange-500',
      dotBg:  'bg-orange-500',
      code:   'text-orange-500',
      primaryAction: { href: '/dashboard', label: 'Go to Dashboard', icon: HomeIcon },
      secondaryAction: null,
      statusLabel: 'Forbidden',
    },
    401: {
      title: 'Authentication Required',
      description:
        'You need to be signed in to access this page. Please log in to continue.',
      icon: ShieldExclamationIcon,
      iconBg:  'bg-yellow-50',
      iconColor: 'text-yellow-500',
      dotBg:  'bg-yellow-500',
      code:   'text-yellow-500',
      primaryAction: { href: '/login', label: 'Sign In', icon: ShieldExclamationIcon },
      secondaryAction: null,
      statusLabel: 'Unauthorized',
    },
    419: {
      title: 'Session Expired',
      description:
        'Your session has expired. Please refresh the page and try again.',
      icon: ExclamationTriangleIcon,
      iconBg:  'bg-amber-50',
      iconColor: 'text-amber-500',
      dotBg:  'bg-amber-500',
      code:   'text-amber-500',
      primaryAction: { href: '#', label: 'Refresh Page', icon: ArrowPathIcon },
      secondaryAction: null,
      statusLabel: 'Page Expired',
    },
    500: {
      title: 'Something Went Wrong',
      description:
        "We're experiencing a temporary issue on our end. Please try again in a few moments.",
      icon: ServerStackIcon,
      iconBg:  'bg-red-50',
      iconColor: 'text-red-500',
      dotBg:  'bg-red-500',
      code:   'text-red-500',
      primaryAction: { href: '/dashboard', label: 'Go to Dashboard', icon: Squares2X2Icon },
      secondaryAction: null,
      statusLabel: 'Internal Server Error',
    },
    503: {
      title: 'Under Maintenance',
      description:
        "We're performing scheduled maintenance and will be back shortly. Thank you for your patience.",
      icon: WrenchScrewdriverIcon,
      iconBg:  'bg-purple-50',
      iconColor: 'text-purple-500',
      dotBg:  'bg-purple-500',
      code:   'text-purple-500',
      primaryAction: { href: '/', label: 'Go Home', icon: HomeIcon },
      secondaryAction: null,
      statusLabel: 'Service Unavailable',
    },
  }

  return configs[props.status] ?? {
    title: 'Unexpected Error',
    description: 'Something unexpected happened. Please try again or go back.',
    icon: ExclamationTriangleIcon,
    iconBg:  'bg-gray-100',
    iconColor: 'text-gray-500',
    dotBg:  'bg-gray-400',
    code:   'text-gray-400',
    primaryAction: { href: '/dashboard', label: 'Go to Dashboard', icon: HomeIcon },
    secondaryAction: null,
    statusLabel: 'Error',
  }
})

const title           = computed(() => errorConfig.value.title)
const description     = computed(() => errorConfig.value.description)
const errorIcon       = computed(() => errorConfig.value.icon)
const iconBgColor     = computed(() => errorConfig.value.iconBg)
const iconColor       = computed(() => errorConfig.value.iconColor)
const dotBgColor      = computed(() => errorConfig.value.dotBg)
const codeColor       = computed(() => errorConfig.value.code)
const primaryAction   = computed(() => errorConfig.value.primaryAction)
const secondaryAction = computed(() => errorConfig.value.secondaryAction)
const statusLabel     = computed(() => errorConfig.value.statusLabel)

// ─── Actions ─────────────────────────────────────────────────────────────────

function goBack() {
  window.history.back()
}

function retry() {
  window.location.reload()
}
</script>
