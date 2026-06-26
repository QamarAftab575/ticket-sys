<script setup lang="ts">
interface Props {
  message?: string
  height?: string
  width?: string
}

withDefaults(defineProps<Props>(), {
  message: '',
  height: '220px',
  width: '220px',
})
</script>

<template>
  <div class="flex flex-col items-center justify-center select-none" aria-hidden="true">
    <svg
      :style="{ height, width }"
      viewBox="0 0 220 220"
      xmlns="http://www.w3.org/2000/svg"
      class="overflow-visible"
    >
      <!-- Pulse rings behind the circle -->
      <circle cx="110" cy="106" r="62" fill="none" stroke="#E0E7FF" stroke-width="1.5" class="ring ring-1" />
      <circle cx="110" cy="106" r="76" fill="none" stroke="#E0E7FF" stroke-width="1"   class="ring ring-2" />

      <!-- Background circle -->
      <circle cx="110" cy="106" r="52" fill="#EEF2FF" />

      <!-- Bell body -->
      <path
        d="M110 62 C93 62 80 75 80 92 L80 108 L72 118 L72 122 L148 122 L148 118 L140 108 L140 92 C140 75 127 62 110 62 Z"
        fill="#6366F1"
        class="bell"
      />
      <!-- Bell clapper -->
      <ellipse cx="110" cy="126" rx="9" ry="5.5" fill="#6366F1" class="bell" />
      <!-- Bell shine -->
      <ellipse cx="100" cy="80" rx="5" ry="8" fill="white" fill-opacity="0.18" transform="rotate(-20 100 80)" />

      <!-- Checkmark badge (bottom-right of circle) -->
      <circle cx="145" cy="138" r="16" fill="white" />
      <circle cx="145" cy="138" r="13" fill="#10B981" class="check-circle" />
      <path
        d="M138 138 L143 143 L153 133"
        fill="none"
        stroke="white"
        stroke-width="2.5"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="check-path"
      />

      <!-- Small floating dot accents -->
      <circle cx="68"  cy="72"  r="4.5" fill="#F59E0B" class="dot dot-a" />
      <circle cx="155" cy="68"  r="3"   fill="#EC4899" class="dot dot-b" />
      <circle cx="62"  cy="138" r="3"   fill="#A78BFA" class="dot dot-c" />
    </svg>

    <p v-if="message" class="text-gray-500 text-sm font-medium text-center mt-2">{{ message }}</p>
  </div>
</template>

<style scoped>
/* ── Float animation for the bell ─────────────────────────────────── */
@keyframes float {
  0%, 100% { transform: translateY(0);    }
  50%       { transform: translateY(-8px); }
}

/* ── Pulse rings ──────────────────────────────────────────────────── */
@keyframes pulse-ring {
  0%   { transform: scale(0.92); opacity: 0.7; }
  50%  { transform: scale(1.06); opacity: 0.2; }
  100% { transform: scale(0.92); opacity: 0.7; }
}

/* ── Dot breathe ──────────────────────────────────────────────────── */
@keyframes breathe {
  0%, 100% { opacity: 1;   transform: scale(1);    }
  50%       { opacity: 0.5; transform: scale(0.85); }
}

/* ── Check pop-in ─────────────────────────────────────────────────── */
@keyframes pop-in {
  0%   { transform: scale(0); opacity: 0; }
  70%  { transform: scale(1.15); }
  100% { transform: scale(1);   opacity: 1; }
}

/* ── Dash draw (check path) ───────────────────────────────────────── */
@keyframes draw {
  from { stroke-dashoffset: 20; opacity: 0; }
  to   { stroke-dashoffset: 0;  opacity: 1; }
}

/* Apply */
.bell        { transform-origin: 110px 106px; animation: float 4s ease-in-out infinite; }
.ring        { transform-origin: 110px 106px; }
.ring-1      { animation: pulse-ring 3.5s ease-in-out infinite; }
.ring-2      { animation: pulse-ring 3.5s ease-in-out infinite 0.7s; }
.dot         { animation: breathe 3s ease-in-out infinite; }
.dot-a       { animation-delay: 0s; }
.dot-b       { animation-delay: 0.8s; }
.dot-c       { animation-delay: 1.4s; }
.check-circle{ transform-origin: 145px 138px; animation: pop-in 0.5s cubic-bezier(0.34,1.56,0.64,1) 0.4s both; }
.check-path  { stroke-dasharray: 20; animation: draw 0.35s ease-out 0.85s both; }

/* ── Respect reduced-motion ───────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
  .bell, .ring-1, .ring-2, .dot, .dot-a, .dot-b, .dot-c,
  .check-circle, .check-path {
    animation: none;
  }
}
</style>
