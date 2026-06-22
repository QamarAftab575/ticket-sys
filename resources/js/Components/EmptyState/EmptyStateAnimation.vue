<script setup lang="ts">
interface Props {
  message?: string
  height?: string
  width?: string
}

withDefaults(defineProps<Props>(), {
  message: '',
  height: '280px',
  width: '280px',
})
</script>

<template>
  <div class="flex flex-col items-center justify-center">
    <!-- Professional SaaS Empty State Animation -->
    <svg 
      :style="{ height, width }" 
      viewBox="0 0 280 280" 
      xmlns="http://www.w3.org/2000/svg"
      class="mb-4"
    >
      <!-- Outer envelope/container -->
      <g class="envelope-container">
        <!-- Main envelope body -->
        <rect 
          x="60" y="80" 
          width="160" height="120" 
          rx="12" ry="12"
          fill="none" 
          stroke="#4F46E5" 
          stroke-width="2.5"
          class="envelope-bg"
        />
        
        <!-- Envelope flap/top -->
        <path 
          d="M 60 80 L 140 130 L 220 80" 
          fill="none" 
          stroke="#4F46E5" 
          stroke-width="2.5"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="envelope-flap"
        />
        
        <!-- Inner letter/document -->
        <rect 
          x="75" y="100" 
          width="130" height="85" 
          rx="8" ry="8"
          fill="#EEF2FF" 
          stroke="#C7D2FE" 
          stroke-width="1.5"
          class="letter-bg"
        />
        
        <!-- Text lines on letter -->
        <line x1="90" y1="115" x2="175" y2="115" stroke="#A5B4FC" stroke-width="2" stroke-linecap="round" class="text-line"/>
        <line x1="90" y1="130" x2="175" y2="130" stroke="#A5B4FC" stroke-width="2" stroke-linecap="round" class="text-line"/>
        <line x1="90" y1="145" x2="140" y2="145" stroke="#A5B4FC" stroke-width="2" stroke-linecap="round" class="text-line"/>
      </g>

      <!-- Floating accent dots -->
      <circle cx="200" cy="65" r="5.5" fill="#EC4899" class="dot dot-1"/>
      <circle cx="235" cy="95" r="4" fill="#F59E0B" class="dot dot-2"/>
    </svg>
    
    <p v-if="message" class="text-gray-600 text-sm font-medium text-center mt-4">{{ message }}</p>
  </div>
</template>

<style scoped>
@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-15px);
  }
}

@keyframes fadeInScale {
  0% {
    opacity: 0;
    transform: scale(0.8);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes pulse-dot {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.6;
    transform: scale(1.1);
  }
}

.envelope-container {
  animation: float 3.5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

.envelope-bg,
.envelope-flap,
.letter-bg {
  animation: fadeInScale 0.8s ease-out;
}

.text-line {
  animation: fadeInScale 0.8s ease-out;
  animation-delay: 0.1s;
}

.dot {
  animation: pulse-dot 2s ease-in-out infinite;
}

.dot-1 {
  animation-delay: 0s;
}

.dot-2 {
  animation-delay: 0.5s;
}
</style>
