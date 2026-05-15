<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, nextTick } from 'vue';

const isScrolled = ref(false);
const mobileMenuOpen = ref(false);
const activeView = ref('kanban');
const isVisible = ref({});

onMounted(() => {
  // Scroll listener for navbar
  window.addEventListener('scroll', () => {
    isScrolled.value = window.scrollY > 20;
  });

  // Intersection Observer for scroll animations
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          isVisible.value[entry.target.id] = true;
        }
      });
    },
    { threshold: 0.1 }
  );

  // Observe all sections
  nextTick(() => {
    document.querySelectorAll('[data-animate]').forEach((el) => {
      observer.observe(el);
    });
  });
});

const views = {
  kanban: {
    title: 'Kanban Board',
    description: 'Visualize workflow with drag-and-drop cards',
    mockup: 'kanban-mockup.png'
  },
  list: {
    title: 'Task List',
    description: 'Classic list view with powerful filtering',
    mockup: 'list-mockup.png'
  },
  timeline: {
    title: 'Timeline / Gantt',
    description: 'Plan projects with dependencies and milestones',
    mockup: 'timeline-mockup.png'
  },
  calendar: {
    title: 'Calendar View',
    description: 'See all tasks and deadlines in calendar format',
    mockup: 'calendar-mockup.png'
  }
};

const transformations = [
  {
    id: 1,
    team: 'Marketing Campaign',
    before: 'Scattered emails, missed deadlines, confused team',
    after: 'Organized campaigns, clear timelines, aligned team',
    metric: 'Saved 12 hours/week',
    icon: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'
  },
  {
    id: 2,
    team: 'Software Sprint',
    before: 'Unclear requirements, blocked developers, delayed releases',
    after: 'Clear user stories, smooth workflow, on-time delivery',
    metric: 'Improved velocity by 40%',
    icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'
  },
  {
    id: 3,
    team: 'Operations Team',
    before: 'Manual processes, lost requests, overwhelmed staff',
    after: 'Automated workflows, tracked requests, efficient team',
    metric: 'Reduced response time by 60%',
    icon: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'
  }
];

const useCases = [
  {
    id: 1,
    title: 'Marketing Teams',
    description: 'Campaign planning, content calendars, and creative workflows',
    icon: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
    features: ['Campaign timelines', 'Content approval', 'Asset management']
  },
  {
    id: 2,
    title: 'Development Teams',
    description: 'Sprint planning, bug tracking, and feature development',
    icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
    features: ['Sprint boards', 'Code reviews', 'Release planning']
  },
  {
    id: 3,
    title: 'Operations',
    description: 'Process management, incident tracking, and team coordination',
    icon: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
    features: ['SOP tracking', 'Incident management', 'Team workflows']
  },
  {
    id: 4,
    title: 'Freelancers',
    description: 'Client projects, time tracking, and personal productivity',
    icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    features: ['Client portals', 'Time tracking', 'Invoice integration']
  }
];

const testimonials = [
  {
    id: 1,
    name: 'Sarah Chen',
    role: 'Marketing Director',
    company: 'TechFlow Inc',
    avatar: 'SC',
    content: 'Asira transformed our chaotic campaign management into a well-oiled machine. We launched 3 major campaigns ahead of schedule.',
    metric: 'Saved 15 hours/week',
    rating: 5
  },
  {
    id: 2,
    name: 'Marcus Rodriguez',
    role: 'Engineering Lead',
    company: 'DevCorp',
    avatar: 'MR',
    content: 'The timeline view changed everything. Our team can finally see dependencies and plan releases with confidence.',
    metric: '40% faster delivery',
    rating: 5
  },
  {
    id: 3,
    name: 'Emily Watson',
    role: 'Operations Manager',
    company: 'ServicePro',
    avatar: 'EW',
    content: 'From 50+ scattered tools to one unified workspace. Our team productivity skyrocketed.',
    metric: '60% fewer meetings',
    rating: 5
  }
];

</script>

<style scoped>
/* Google Fonts Import */
@import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap');

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Typography */
.font-heading {
  font-family: 'Poppins', sans-serif;
}

.font-body {
  font-family: 'Open Sans', sans-serif;
}

/* Animated gradient background */
.gradient-bg {
  background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #4facfe, #00f2fe);
  background-size: 400% 400%;
  animation: gradientShift 20s ease infinite;
}

@keyframes gradientShift {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

/* Floating animation */
@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  33% { transform: translateY(-10px) rotate(1deg); }
  66% { transform: translateY(-5px) rotate(-1deg); }
}

.float {
  animation: float 8s ease-in-out infinite;
}

/* Gentle lift on hover */
.lift-hover {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.lift-hover:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

/* Glassmorphism */
.glass {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Button glow effect */
.btn-glow {
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
}

.btn-glow:hover {
  transform: scale(1.05);
  box-shadow: 0 10px 30px rgba(37, 99, 235, 0.4);
}

.btn-glow::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transition: left 0.5s;
}

.btn-glow:hover::before {
  left: 100%;
}

/* Scroll animations */
.fade-in-up {
  opacity: 0;
  transform: translateY(30px);
  transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.fade-in-up.visible {
  opacity: 1;
  transform: translateY(0);
}

/* Stagger animation delays */
.delay-100 { transition-delay: 0.1s; }
.delay-200 { transition-delay: 0.2s; }
.delay-300 { transition-delay: 0.3s; }
.delay-400 { transition-delay: 0.4s; }

/* Glowing dots */
.glow-dot {
  background: radial-gradient(circle, rgba(37, 99, 235, 0.3) 0%, transparent 70%);
  animation: pulse 4s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 0.3; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(1.1); }
}

/* View transition */
.view-transition {
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>

<template>
  <div class="min-h-screen bg-white font-body antialiased overflow-x-hidden">
    <!-- Navbar -->
    <nav :class="['fixed top-0 left-0 right-0 z-50 transition-all duration-300', isScrolled ? 'bg-white/95 backdrop-blur-md shadow-lg' : 'bg-transparent']">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Logo -->
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <span class="text-2xl font-heading font-bold text-gray-900">Asira</span>
          </div>

          <!-- Desktop Navigation -->
          <div class="hidden lg:flex items-center space-x-8">
            <a href="#views" class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200">Views</a>
            <a href="#features" class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200">Features</a>
            <a href="#use-cases" class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200">Use Cases</a>
            <a href="#testimonials" class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200">Reviews</a>
            <a href="#pricing" class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200">Pricing</a>
          </div>

          <!-- CTA Buttons -->
          <div class="hidden lg:flex items-center space-x-4">
            <Link href="/login" class="text-gray-700 hover:text-gray-900 font-medium transition-colors duration-200">
              Login
            </Link>
            <Link href="/register" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-teal-500 text-white font-semibold rounded-xl btn-glow">
              Start Free
            </Link>
          </div>

          <!-- Mobile Menu Button -->
          <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 text-gray-700 hover:text-gray-900 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Mobile Menu -->
        <div v-if="mobileMenuOpen" class="lg:hidden py-4 border-t border-gray-100 bg-white/95 backdrop-blur-md">
          <div class="flex flex-col space-y-4">
            <a href="#views" class="text-gray-700 hover:text-blue-600 font-medium">Views</a>
            <a href="#features" class="text-gray-700 hover:text-blue-600 font-medium">Features</a>
            <a href="#use-cases" class="text-gray-700 hover:text-blue-600 font-medium">Use Cases</a>
            <a href="#testimonials" class="text-gray-700 hover:text-blue-600 font-medium">Reviews</a>
            <a href="#pricing" class="text-gray-700 hover:text-blue-600 font-medium">Pricing</a>
            <Link href="/login" class="text-gray-700 hover:text-gray-900 font-medium">Login</Link>
            <Link href="/register" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-teal-500 text-white font-semibold rounded-xl text-center">
              Start Free
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden gradient-bg">
      <!-- Animated Background Elements -->
      <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <!-- Glowing dots -->
        <div class="absolute top-20 left-10 w-32 h-32 glow-dot rounded-full"></div>
        <div class="absolute top-40 right-20 w-24 h-24 glow-dot rounded-full" style="animation-delay: 2s;"></div>
        <div class="absolute bottom-32 left-1/4 w-40 h-40 glow-dot rounded-full" style="animation-delay: 4s;"></div>
        <div class="absolute bottom-20 right-1/3 w-28 h-28 glow-dot rounded-full" style="animation-delay: 6s;"></div>
        
        <!-- Flowing lines -->
        <svg class="absolute inset-0 w-full h-full opacity-20" viewBox="0 0 1000 1000">
          <path d="M0,300 Q250,100 500,300 T1000,300" stroke="url(#gradient)" stroke-width="2" fill="none" opacity="0.3">
            <animate attributeName="d" dur="20s" repeatCount="indefinite" 
              values="M0,300 Q250,100 500,300 T1000,300;M0,350 Q250,150 500,350 T1000,350;M0,300 Q250,100 500,300 T1000,300"/>
          </path>
          <path d="M0,600 Q250,400 500,600 T1000,600" stroke="url(#gradient)" stroke-width="2" fill="none" opacity="0.2">
            <animate attributeName="d" dur="25s" repeatCount="indefinite" 
              values="M0,600 Q250,400 500,600 T1000,600;M0,650 Q250,450 500,650 T1000,650;M0,600 Q250,400 500,600 T1000,600"/>
          </path>
          <defs>
            <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" style="stop-color:#2563EB;stop-opacity:0" />
              <stop offset="50%" style="stop-color:#0891B2;stop-opacity:1" />
              <stop offset="100%" style="stop-color:#2563EB;stop-opacity:0" />
            </linearGradient>
          </defs>
        </svg>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
          <!-- Left Column: Content -->
          <div class="text-center lg:text-left">
            <!-- Story-driven headline -->
            <div class="mb-8">
              <div class="inline-flex items-center px-4 py-2 glass rounded-full text-white text-sm font-medium mb-6">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Trusted by 50,000+ teams worldwide
              </div>
            </div>

            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-heading font-bold text-white leading-tight mb-8">
              Turn chaos into
              <span class="block bg-gradient-to-r from-yellow-200 via-pink-200 to-cyan-200 bg-clip-text text-transparent">
                organized, trackable work
              </span>
            </h1>

            <p class="text-xl sm:text-2xl text-white/90 mb-10 leading-relaxed max-w-2xl">
              Stop juggling scattered tools and missed deadlines. Asira brings your team together with tasks, Kanban boards, timelines, and real-time collaboration — all in one beautiful workspace.
            </p>

            <!-- Before/After Story -->
            <div class="grid sm:grid-cols-2 gap-6 mb-12 max-w-2xl">
              <div class="glass rounded-2xl p-6 text-center">
                <div class="text-red-200 text-sm font-medium mb-2">❌ Before Asira</div>
                <div class="text-white/80 text-sm">Scattered emails, missed deadlines, confused team members</div>
              </div>
              <div class="glass rounded-2xl p-6 text-center">
                <div class="text-green-200 text-sm font-medium mb-2">✅ After Asira</div>
                <div class="text-white/80 text-sm">Clear workflows, on-time delivery, aligned team</div>
              </div>
            </div>

            <!-- CTAs -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start mb-12">
              <Link href="/register" class="px-8 py-4 bg-white text-blue-600 font-bold rounded-xl btn-glow shadow-2xl">
                Start Free Trial
              </Link>
              <button class="px-8 py-4 glass text-white font-semibold rounded-xl hover:bg-white/30 transition-all duration-300 flex items-center justify-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Watch Demo
              </button>
            </div>

            <!-- Trust indicators -->
            <div class="text-center lg:text-left">
              <p class="text-white/70 text-sm mb-4">No credit card required • Free 14-day trial • Setup in 2 minutes</p>
              <div class="flex items-center justify-center lg:justify-start gap-6 text-white/60 text-sm">
                <div class="flex items-center gap-1">
                  <svg class="w-4 h-4 text-green-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  SOC 2 Compliant
                </div>
                <div class="flex items-center gap-1">
                  <svg class="w-4 h-4 text-green-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  99.9% Uptime
                </div>
                <div class="flex items-center gap-1">
                  <svg class="w-4 h-4 text-green-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  4.9/5 Rating
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Floating Product Mockup -->
          <div class="relative">
            <div class="relative float lift-hover">
              <!-- Main mockup container -->
              <div class="relative rounded-2xl shadow-2xl overflow-hidden border border-white/20 bg-white/10 backdrop-blur-sm">
                <!-- Browser bar -->
                <div class="bg-white/20 px-6 py-4 flex items-center gap-3 border-b border-white/10">
                  <div class="flex gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-400/80"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-400/80"></div>
                    <div class="w-3 h-3 rounded-full bg-green-400/80"></div>
                  </div>
                  <div class="flex-1 text-center text-white/70 text-sm font-medium">asira.app/projects</div>
                </div>
                
                <!-- Mockup content -->
                <div class="aspect-[4/3] bg-gradient-to-br from-white/10 to-white/5 flex items-center justify-center p-8">
                  <div class="text-center">
                    <svg class="w-24 h-24 mx-auto text-white/60 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0v10" />
                    </svg>
                    <p class="text-white/80 font-medium text-lg mb-2">Kanban Board Preview</p>
                    <p class="text-white/60 text-sm">Replace with actual screenshot</p>
                  </div>
                </div>
              </div>

              <!-- Floating elements around mockup -->
              <div class="absolute -top-6 -right-6 w-16 h-16 bg-gradient-to-br from-teal-400 to-blue-500 rounded-2xl opacity-80 blur-sm float" style="animation-delay: 1s;"></div>
              <div class="absolute -bottom-4 -left-4 w-12 h-12 bg-gradient-to-br from-pink-400 to-purple-500 rounded-full opacity-60 blur-sm float" style="animation-delay: 3s;"></div>
              <div class="absolute top-1/2 -right-8 w-8 h-8 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-lg opacity-70 blur-sm float" style="animation-delay: 5s;"></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Interactive Views Demo -->
    <section id="views" class="py-24 px-4 sm:px-6 lg:px-8 bg-white" data-animate>
      <div class="max-w-7xl mx-auto">
        <div :class="['text-center mb-16 fade-in-up', isVisible.views ? 'visible' : '']">
          <span class="inline-block px-4 py-2 bg-blue-100 text-blue-600 text-sm font-semibold rounded-full mb-4">
            FLEXIBLE VIEWS
          </span>
          <h2 class="text-4xl sm:text-5xl font-heading font-bold text-gray-900 mb-6">
            Work the way you think
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Switch between List, Kanban, Timeline, and Calendar views instantly. Same data, different perspectives for every workflow.
          </p>
        </div>

        <!-- Interactive View Selector -->
        <div class="bg-gray-50 rounded-3xl p-8 lg:p-12">
          <!-- View Tabs -->
          <div class="flex flex-wrap justify-center gap-4 mb-12">
            <button 
              v-for="(view, key) in views" 
              :key="key"
              @click="activeView = key"
              :class="[
                'px-6 py-3 rounded-xl font-semibold transition-all duration-300',
                activeView === key 
                  ? 'bg-blue-600 text-white shadow-lg transform scale-105' 
                  : 'bg-white text-gray-700 hover:bg-gray-100 hover:scale-105'
              ]"
            >
              {{ view.title }}
            </button>
          </div>

          <!-- Dynamic View Display -->
          <div class="relative">
            <div class="view-transition bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
              <!-- Browser bar -->
              <div class="bg-gray-100 px-6 py-4 flex items-center gap-3 border-b border-gray-200">
                <div class="flex gap-2">
                  <div class="w-3 h-3 rounded-full bg-red-400"></div>
                  <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                  <div class="w-3 h-3 rounded-full bg-green-400"></div>
                </div>
                <div class="flex-1 text-center text-gray-500 text-sm font-medium">
                  asira.app/{{ activeView }}
                </div>
              </div>
              
              <!-- View Content -->
              <div class="aspect-[16/10] bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-12">
                <div class="text-center">
                  <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-blue-600 to-teal-500 rounded-2xl flex items-center justify-center">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path v-if="activeView === 'kanban'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0v10" />
                      <path v-else-if="activeView === 'list'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                      <path v-else-if="activeView === 'timeline'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <h3 class="text-2xl font-heading font-bold text-gray-900 mb-3">{{ views[activeView].title }}</h3>
                  <p class="text-gray-600 mb-6">{{ views[activeView].description }}</p>
                  <p class="text-sm text-gray-500">Interactive {{ activeView }} mockup coming soon</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Real Team Transformation -->
    <section class="py-24 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-blue-50 to-teal-50" data-animate>
      <div class="max-w-7xl mx-auto">
        <div :class="['text-center mb-16 fade-in-up', isVisible.transformations ? 'visible' : '']">
          <span class="inline-block px-4 py-2 bg-teal-100 text-teal-700 text-sm font-semibold rounded-full mb-4">
            REAL TRANSFORMATIONS
          </span>
          <h2 class="text-4xl sm:text-5xl font-heading font-bold text-gray-900 mb-6">
            See how teams transformed their chaos
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Real stories from real teams who turned scattered work into organized success.
          </p>
        </div>

        <!-- Transformation Cards -->
        <div class="grid lg:grid-cols-3 gap-8">
          <div 
            v-for="(transformation, index) in transformations" 
            :key="transformation.id"
            :class="[
              'bg-white rounded-2xl p-8 shadow-lg lift-hover fade-in-up',
              `delay-${(index + 1) * 100}`,
              isVisible.transformations ? 'visible' : ''
            ]"
          >
            <!-- Icon -->
            <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-teal-500 rounded-2xl flex items-center justify-center mb-6">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="transformation.icon" />
              </svg>
            </div>

            <!-- Team Type -->
            <h3 class="text-2xl font-heading font-bold text-gray-900 mb-4">{{ transformation.team }}</h3>

            <!-- Before/After -->
            <div class="space-y-4 mb-6">
              <div class="p-4 bg-red-50 rounded-xl border-l-4 border-red-400">
                <div class="text-red-700 text-sm font-semibold mb-1">❌ Before</div>
                <p class="text-red-600 text-sm">{{ transformation.before }}</p>
              </div>
              <div class="p-4 bg-green-50 rounded-xl border-l-4 border-green-400">
                <div class="text-green-700 text-sm font-semibold mb-1">✅ After</div>
                <p class="text-green-600 text-sm">{{ transformation.after }}</p>
              </div>
            </div>

            <!-- Metric -->
            <div class="text-center p-4 bg-gradient-to-r from-blue-600 to-teal-500 rounded-xl">
              <div class="text-white font-bold text-lg">{{ transformation.metric }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section id="views" class="py-20 px-4 sm:px-6 lg:px-8 bg-white">
      <div class="max-w-7xl mx-auto">
        <!-- Section Header -->
        <div class="text-center mb-16">
          <span class="inline-block px-4 py-2 bg-blue-100 text-blue-600 text-sm font-semibold rounded-full mb-4">
            FLEXIBLE VIEWS
          </span>
          <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
            Work the way you want
          </h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Switch between List, Kanban, Timeline, and Calendar views instantly. Same data, different perspectives.
          </p>
        </div>

        <!-- Views Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="view in views" :key="view.id" class="group">
            <div class="bg-white border-2 border-gray-200 rounded-xl p-6 hover:border-blue-600 hover:shadow-lg transition-all duration-200">
              <!-- Icon -->
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4 group-hover:bg-blue-600 transition-colors">
                <svg class="w-6 h-6 text-blue-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="view.icon" />
                </svg>
              </div>

              <!-- Content -->
              <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ view.title }}</h3>
              <p class="text-sm text-gray-600 leading-relaxed">{{ view.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
      <div class="max-w-7xl mx-auto">
        <!-- Section Header -->
        <div class="text-center mb-16">
          <span class="inline-block px-4 py-2 bg-cyan-100 text-cyan-700 text-sm font-semibold rounded-full mb-4">
            POWERFUL FEATURES
          </span>
          <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
            Everything you need to succeed
          </h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            From task management to time tracking, Asira has all the tools your team needs to stay productive.
          </p>
        </div>

        <!-- Features Grid -->
        <div class="grid md:grid-cols-2 gap-8">
          <div v-for="feature in features" :key="feature.id" class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow">
            <!-- Icon -->
            <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-cyan-500 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.icon" />
              </svg>
            </div>

            <!-- Content -->
            <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ feature.title }}</h3>
            <p class="text-gray-600 leading-relaxed">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-white">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
          <span class="inline-block px-4 py-2 bg-blue-100 text-blue-600 text-sm font-semibold rounded-full mb-4">
            HOW IT WORKS
          </span>
          <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
            Get started in minutes
          </h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            No complex setup. No training required. Just sign up and start organizing your work.
          </p>
        </div>

        <!-- Steps -->
        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
          <div class="text-center">
            <div class="w-16 h-16 bg-blue-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">
              1
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Create your workspace</h3>
            <p class="text-gray-600">Sign up and set up your team in under 2 minutes.</p>
          </div>

          <div class="text-center">
            <div class="w-16 h-16 bg-cyan-500 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">
              2
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Add your projects</h3>
            <p class="text-gray-600">Create projects, add tasks, and invite team members.</p>
          </div>

          <div class="text-center">
            <div class="w-16 h-16 bg-blue-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">
              3
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Start collaborating</h3>
            <p class="text-gray-600">Track progress, communicate, and get work done together.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
      <div class="max-w-7xl mx-auto">
        <!-- Section Header -->
        <div class="text-center mb-16">
          <span class="inline-block px-4 py-2 bg-blue-100 text-blue-600 text-sm font-semibold rounded-full mb-4">
            TESTIMONIALS
          </span>
          <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
            Loved by teams worldwide
          </h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Join thousands of teams who have transformed their workflow with Asira.
          </p>
        </div>

        <!-- Testimonials Grid -->
        <div class="grid md:grid-cols-3 gap-8">
          <div v-for="testimonial in testimonials" :key="testimonial.id" class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow">
            <!-- Rating Stars -->
            <div class="flex gap-1 mb-4">
              <svg v-for="i in testimonial.rating" :key="i" class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </div>

            <!-- Content -->
            <p class="text-gray-700 mb-6 leading-relaxed">"{{ testimonial.content }}"</p>

            <!-- Author -->
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center text-white font-semibold">
                {{ testimonial.avatar }}
              </div>
              <div>
                <div class="font-semibold text-gray-900">{{ testimonial.name }}</div>
                <div class="text-sm text-gray-600">{{ testimonial.role }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-blue-600 to-cyan-500">
      <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-6">
          Ready to transform your workflow?
        </h2>
        <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
          Join 50,000+ teams using Asira to manage projects, track tasks, and collaborate better.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <Link href="/register" class="px-8 py-4 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-50 transition-colors shadow-lg">
            Start Free Trial
          </Link>
          <button class="px-8 py-4 border-2 border-white text-white font-semibold rounded-lg hover:bg-white/10 transition-colors">
            Request Demo
          </button>
        </div>
        <p class="text-blue-100 text-sm mt-6">No credit card required • Free 14-day trial • Cancel anytime</p>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <div class="grid md:grid-cols-4 gap-8 mb-8">
          <!-- Brand -->
          <div>
            <div class="flex items-center space-x-2 mb-4">
              <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-cyan-500 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
              <span class="text-xl font-semibold text-white">Asira</span>
            </div>
            <p class="text-sm">Turn chaos into organized, trackable work.</p>
          </div>

          <!-- Product -->
          <div>
            <h3 class="text-white font-semibold mb-4">Product</h3>
            <ul class="space-y-2 text-sm">
              <li><a href="#features" class="hover:text-white transition-colors">Features</a></li>
              <li><a href="#views" class="hover:text-white transition-colors">Views</a></li>
              <li><a href="#pricing" class="hover:text-white transition-colors">Pricing</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Integrations</a></li>
            </ul>
          </div>

          <!-- Company -->
          <div>
            <h3 class="text-white font-semibold mb-4">Company</h3>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-white transition-colors">About</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Blog</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Careers</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Contact</a></li>
            </ul>
          </div>

          <!-- Resources -->
          <div>
            <h3 class="text-white font-semibold mb-4">Resources</h3>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-white transition-colors">Help Center</a></li>
              <li><a href="#" class="hover:text-white transition-colors">API Docs</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Community</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Status</a></li>
            </ul>
          </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-sm">
          <p>© 2026 Asira. All rights reserved.</p>
          <div class="flex gap-6">
            <a href="#" class="hover:text-white transition-colors">Privacy</a>
            <a href="#" class="hover:text-white transition-colors">Terms</a>
            <a href="#" class="hover:text-white transition-colors">Security</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>
