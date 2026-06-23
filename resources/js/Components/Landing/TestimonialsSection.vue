<script setup>
import { ref } from 'vue';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Pagination, Autoplay, EffectCoverflow } from 'swiper/modules';

// Import Swiper styles
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-coverflow';

defineProps({
  testimonials: {
    type: Array,
    required: true
  }
});

// Swiper modules
const modules = [Navigation, Pagination, Autoplay, EffectCoverflow];

// Swiper configuration
const swiperOptions = {
  modules,
  slidesPerView: 1,
  spaceBetween: 30,
  loop: true,
  autoplay: {
    delay: 5000,
    disableOnInteraction: false,
    pauseOnMouseEnter: true,
  },
  speed: 800,
  effect: 'coverflow',
  coverflowEffect: {
    rotate: 0,
    stretch: 0,
    depth: 100,
    modifier: 1,
    slideShadows: false,
  },
  pagination: {
    clickable: true,
    dynamicBullets: true,
  },
  navigation: false, // Disable Swiper navigation, we'll use custom buttons
};

// Swiper instance ref
const swiperInstance = ref(null);

const onSwiper = (swiper) => {
  swiperInstance.value = swiper;
};

const slidePrev = () => {
  if (swiperInstance.value) {
    swiperInstance.value.slidePrev();
  }
};

const slideNext = () => {
  if (swiperInstance.value) {
    swiperInstance.value.slideNext();
  }
};
</script>

<style scoped>
/* Google Fonts - Inter for testimonials */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

/* Swiper custom styling */
.testimonials-swiper {
  padding: 2rem 3rem;
}

/* Navigation buttons - Hide default Swiper arrows */
:deep(.swiper-button-next),
:deep(.swiper-button-prev) {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: white;
  border: 2px solid #e2e8f0;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

:deep(.swiper-button-next):hover,
:deep(.swiper-button-prev):hover {
  border-color: #9333ea;
  background: #faf5ff;
  transform: scale(1.1);
}

/* Hide the default Swiper arrow icons completely */
:deep(.swiper-button-next::after),
:deep(.swiper-button-prev::after) {
  display: none;
}

/* Dark mode navigation */
:deep(.dark .swiper-button-next),
:deep(.dark .swiper-button-prev) {
  background: #0f172a;
  border-color: #334155;
}

:deep(.dark .swiper-button-next):hover,
:deep(.dark .swiper-button-prev):hover {
  border-color: #9333ea;
  background: #1e1b4b;
}

/* Custom arrow icons */
.custom-swiper-button-prev,
.custom-swiper-button-next {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 10;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: white;
  border: 2px solid #e2e8f0;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.custom-swiper-button-prev {
  left: -60px;
}

.custom-swiper-button-next {
  right: -60px;
}

.custom-swiper-button-prev:hover,
.custom-swiper-button-next:hover {
  border-color: #9333ea;
  background: #faf5ff;
  transform: translateY(-50%) scale(1.1);
}

.dark .custom-swiper-button-prev,
.dark .custom-swiper-button-next {
  background: #0f172a;
  border-color: #334155;
}

.dark .custom-swiper-button-prev:hover,
.dark .custom-swiper-button-next:hover {
  border-color: #9333ea;
  background: #1e1b4b;
}

@media (max-width: 1024px) {
  .custom-swiper-button-prev {
    left: -24px;
  }
  
  .custom-swiper-button-next {
    right: -24px;
  }
}

/* Pagination dots */
:deep(.swiper-pagination) {
  bottom: 0 !important;
  padding-top: 2rem;
}

:deep(.swiper-pagination-bullet) {
  width: 8px;
  height: 8px;
  background: #cbd5e1;
  opacity: 1;
  transition: all 0.3s ease;
}

:deep(.swiper-pagination-bullet-active) {
  background: #9333ea;
  width: 24px;
  border-radius: 4px;
}

:deep(.dark .swiper-pagination-bullet) {
  background: #475569;
}

:deep(.dark .swiper-pagination-bullet-active) {
  background: #a855f7;
}

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {
  .testimonials-swiper,
  :deep(.swiper-button-next),
  :deep(.swiper-button-prev),
  :deep(.swiper-pagination-bullet),
  * {
    transition: none !important;
  }
  
  :deep(.swiper) {
    --swiper-transition-speed: 0ms !important;
  }
}
</style>

<template>
  <!-- Testimonials Section - Swiper Slider -->
  <section id="testimonials" class="py-24 px-6 bg-white dark:bg-slate-950">
    <div class="max-w-7xl mx-auto">
      <!-- Section header -->
      <div class="text-center mb-16">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 rounded-full text-sm font-semibold mb-6 border border-purple-100 dark:border-purple-900/50">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
          </svg>
          <span>{{ $t('messages.testimonials') }}</span>
        </div>
        <h2 class="text-5xl sm:text-6xl font-bold text-slate-900 dark:text-white mb-6 tracking-tight">
          {{ $t('messages.loved_by_teams') }}
        </h2>
        <p class="text-xl text-slate-600 dark:text-slate-400 max-w-3xl mx-auto leading-relaxed">
          {{ $t('messages.workflow_transformation') }}
        </p>
      </div>

      <!-- Swiper Slider -->
      <div class="testimonials-swiper relative">
        <Swiper v-bind="swiperOptions" @swiper="onSwiper">
          <SwiperSlide 
            v-for="testimonial in testimonials" 
            :key="testimonial.id"
          >
            <div class="max-w-4xl mx-auto p-8 lg:p-12 bg-slate-50 dark:bg-slate-900 rounded-3xl border-2 border-slate-200 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700 transition-all duration-300">
              <!-- Quote icon -->
              <div class="w-14 h-14 rounded-2xl bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center mb-6">
                <svg class="w-7 h-7 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                </svg>
              </div>

              <!-- Rating stars -->
              <div class="flex gap-1 mb-6">
                <svg 
                  v-for="i in testimonial.rating" 
                  :key="i" 
                  class="w-5 h-5 text-yellow-500" 
                  fill="currentColor" 
                  viewBox="0 0 20 20"
                >
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </div>

              <!-- Testimonial content -->
              <p class="text-lg text-slate-700 dark:text-slate-300 mb-10 leading-relaxed font-medium">
                "{{ testimonial.content }}"
              </p>

              <!-- Author info -->
              <div class="flex items-center gap-4 pt-8 border-t-2 border-slate-200 dark:border-slate-800">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white font-bold text-lg">
                  {{ testimonial.avatar }}
                </div>
                <div>
                  <div class="font-bold text-slate-900 dark:text-white text-lg">
                    {{ testimonial.name }}
                  </div>
                  <div class="text-sm text-slate-600 dark:text-slate-400 font-medium">
                    {{ testimonial.role }}
                  </div>
                  <div class="text-sm text-slate-500 dark:text-slate-500">
                    {{ testimonial.company }}
                  </div>
                </div>
              </div>
            </div>
          </SwiperSlide>
        </Swiper>

        <!-- Custom Navigation Buttons -->
        <button
          @click="slidePrev"
          class="custom-swiper-button-prev"
          aria-label="Previous testimonial"
        >
          <svg class="w-6 h-6 text-slate-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <button
          @click="slideNext"
          class="custom-swiper-button-next"
          aria-label="Next testimonial"
        >
          <svg class="w-6 h-6 text-slate-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>
  </section>
</template>
