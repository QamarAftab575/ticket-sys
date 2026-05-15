<template>
  <div class="relative inline-flex items-center" ref="containerRef">
    <!-- Trigger -->
    <button
      @click.stop="togglePicker"
      class="flex items-center gap-1 px-2 py-1 rounded hover:bg-gray-100 transition group"
      :class="triggerClass"
    >
      <!-- Calendar icon: only shown when no date set -->
      <svg v-if="!hasAnyDate" class="w-4 h-4 flex-shrink-0 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
      </svg>
      <!-- Smart date label when date(s) set -->
      <span v-if="hasAnyDate" class="text-sm" :class="isOverdue ? 'text-red-600 font-medium' : 'text-gray-700'">
        {{ dateLabel }}
      </span>
    </button>

    <!-- Popover -->
    <Teleport to="body">
      <div
        v-if="open"
        ref="popoverRef"
        class="fixed z-50 bg-white rounded-xl shadow-xl border border-gray-200 w-72 select-none"
        :style="popoverStyle"
        @click.stop
      >
        <!-- Header: start / end date inputs -->
        <div class="flex gap-2 p-3 border-b border-gray-100">
          <!-- Start date pill -->
          <button
            class="flex-1 flex items-center justify-between px-3 py-1.5 rounded-lg border text-sm transition"
            :class="activeInput === 'start'
              ? 'border-blue-500 ring-2 ring-blue-200 bg-white'
              : 'border-gray-200 bg-gray-50 hover:border-gray-300'"
            @click="setActiveInput('start')"
          >
            <span :class="localStart ? 'text-gray-800' : 'text-gray-400'">
              {{ localStart ? fmtShort(localStart) : '+ Start date' }}
            </span>
            <svg v-if="localStart" @click.stop="clearStart" class="w-3.5 h-3.5 text-gray-400 hover:text-gray-600 ml-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>

          <!-- End date pill -->
          <button
            class="flex-1 flex items-center justify-between px-3 py-1.5 rounded-lg border text-sm transition"
            :class="activeInput === 'end'
              ? 'border-blue-500 ring-2 ring-blue-200 bg-white'
              : 'border-gray-200 bg-gray-50 hover:border-gray-300'"
            @click="setActiveInput('end')"
          >
            <span :class="localEnd ? 'text-gray-800' : 'text-gray-400'">
              {{ localEnd ? fmtShort(localEnd) : '+ End date' }}
            </span>
            <svg v-if="localEnd" @click.stop="clearEnd" class="w-3.5 h-3.5 text-gray-400 hover:text-gray-600 ml-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Calendar -->
        <div class="p-3">
          <!-- Month nav -->
          <div class="flex items-center justify-between mb-3">
            <button @click="prevMonth" class="p-1 rounded hover:bg-gray-100 transition">
              <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>
            <span class="text-sm font-semibold text-gray-800">{{ monthLabel }}</span>
            <button @click="nextMonth" class="p-1 rounded hover:bg-gray-100 transition">
              <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>

          <!-- Day headers -->
          <div class="grid grid-cols-7 mb-1">
            <div
              v-for="d in ['M','T','W','T','F','S','S']"
              :key="d"
              class="text-center text-xs font-medium text-gray-400 py-1"
            >{{ d }}</div>
          </div>

          <!-- Days grid -->
          <div class="grid grid-cols-7">
            <button
              v-for="cell in calendarCells"
              :key="cell.key"
              :disabled="!cell.date"
              @click="cell.date && selectDay(cell.date)"
              @mouseenter="cell.date && (hoverDate = cell.date)"
              @mouseleave="hoverDate = null"
              class="relative h-8 flex items-center justify-center text-sm transition"
              :class="dayClass(cell)"
            >
              <span
                v-if="cell.date"
                class="relative z-10 w-7 h-7 flex items-center justify-center rounded-full"
                :class="dayInnerClass(cell)"
              >
                {{ cell.day }}
              </span>
            </button>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end px-3 py-2 border-t border-gray-100">
          <button
            @click="clearAll"
            class="text-sm text-gray-500 hover:text-gray-800 transition"
          >
            Clear
          </button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';

interface Props {
  startDate?: string | null;
  endDate?: string | null;
  completed?: boolean;
  triggerClass?: string;
}

interface Emits {
  (e: 'change', val: { startDate: string | null; endDate: string | null }): void;
}

const props = withDefaults(defineProps<Props>(), {
  startDate: null,
  endDate: null,
  completed: false,
  triggerClass: '',
});

const emit = defineEmits<Emits>();

// ── state ──────────────────────────────────────────────────────────────────
const open = ref(false);
const containerRef = ref<HTMLElement | null>(null);
const popoverRef = ref<HTMLElement | null>(null);
const popoverStyle = ref<Record<string, string>>({});

const viewYear = ref(new Date().getFullYear());
const viewMonth = ref(new Date().getMonth());

const localStart = ref<string | null>(props.startDate ?? null);
const localEnd = ref<string | null>(props.endDate ?? null);
const activeInput = ref<'start' | 'end'>('end');
const hoverDate = ref<Date | null>(null);

// ── debounced save — single request, last-write-wins ──────────────────────
let saveTimer: ReturnType<typeof setTimeout> | null = null;

function scheduleSave() {
  if (saveTimer) clearTimeout(saveTimer);
  saveTimer = setTimeout(() => {
    emit('change', { startDate: localStart.value, endDate: localEnd.value });
    saveTimer = null;
  }, 300);
}

// ── sync props → local ─────────────────────────────────────────────────────
watch(() => props.startDate, v => { localStart.value = v ?? null; });
watch(() => props.endDate,   v => { localEnd.value   = v ?? null; });

// ── computed ───────────────────────────────────────────────────────────────
const hasAnyDate = computed(() => !!(props.startDate || props.endDate));

const isOverdue = computed(() => {
  const d = props.endDate || props.startDate;
  return !!d && new Date(d) < new Date() && !props.completed;
});

const dateLabel = computed(() => {
  const start = props.startDate;
  const end   = props.endDate;

  if (start && end) {
    const s = parseLocal(start);
    const e = parseLocal(end);
    if (isNaN(s.getTime()) || isNaN(e.getTime())) return '';
    const sameMonth = s.getMonth() === e.getMonth() && s.getFullYear() === e.getFullYear();
    if (sameMonth) {
      return `${s.getDate()} – ${e.getDate()} ${e.toLocaleDateString('en-US', { month: 'short' })}`;
    }
    return `${fmtSmart(start)} – ${fmtSmart(end)}`;
  }

  const iso = end || start;
  if (!iso) return '';
  const d = parseLocal(iso);
  if (isNaN(d.getTime())) return '';
  return fmtSmart(iso);
});

function fmtSmart(iso: string): string {
  const d = parseLocal(iso);
  if (isNaN(d.getTime())) return '';
  const today    = new Date();
  const tomorrow = new Date(); tomorrow.setDate(today.getDate() + 1);

  if (isSameDay(d, today))    return 'Today';
  if (isSameDay(d, tomorrow)) return 'Tomorrow';

  const sameYear = d.getFullYear() === today.getFullYear();
  return d.toLocaleDateString('en-US', {
    day: 'numeric',
    month: 'short',
    ...(sameYear ? {} : { year: '2-digit' }),
  });
}

const monthLabel = computed(() =>
  new Date(viewYear.value, viewMonth.value, 1)
    .toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
);

// Build calendar cells (Mon-first week)
const calendarCells = computed(() => {
  const cells: { key: string; date: Date | null; day: number | null }[] = [];
  const first = new Date(viewYear.value, viewMonth.value, 1);
  // Mon=0 … Sun=6
  let startOffset = (first.getDay() + 6) % 7;
  // pad with nulls
  for (let i = 0; i < startOffset; i++) cells.push({ key: `e${i}`, date: null, day: null });

  const daysInMonth = new Date(viewYear.value, viewMonth.value + 1, 0).getDate();
  for (let d = 1; d <= daysInMonth; d++) {
    const date = new Date(viewYear.value, viewMonth.value, d);
    cells.push({ key: `d${d}`, date, day: d });
  }
  // fill to complete last row
  while (cells.length % 7 !== 0) cells.push({ key: `t${cells.length}`, date: null, day: null });
  return cells;
});

// ── helpers ────────────────────────────────────────────────────────────────

// Build YYYY-MM-DD from a local Date without UTC shift
function toDateStr(d: Date): string {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}

// Parse YYYY-MM-DD or YYYY-MM-DDTHH:... as local date (avoids UTC midnight shift)
function parseLocal(iso: string | null | undefined): Date {
  if (!iso) return new Date(NaN);
  const datePart = iso.split('T')[0];
  const parts = datePart.split('-').map(Number);
  if (parts.length !== 3 || parts.some(isNaN)) return new Date(NaN);
  const [y, m, d] = parts;
  return new Date(y, m - 1, d);
}

function fmt(iso: string): string {
  return parseLocal(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: '2-digit' });
}

function fmtShort(iso: string): string {
  const d = parseLocal(iso);
  if (isNaN(d.getTime())) return '';
  return `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${String(d.getFullYear()).slice(2)}`;
}

function isSameDay(a: Date, b: Date): boolean {
  return a.getFullYear() === b.getFullYear() &&
         a.getMonth() === b.getMonth() &&
         a.getDate() === b.getDate();
}

function isToday(d: Date): boolean {
  return isSameDay(d, new Date());
}

// ── day styling ────────────────────────────────────────────────────────────
function dayClass(cell: { date: Date | null }): string[] {
  if (!cell.date) return [];
  const classes: string[] = [];
  const s = localStart.value ? parseLocal(localStart.value) : null;
  const e = localEnd.value   ? parseLocal(localEnd.value)   : null;
  const d = cell.date;

  let rangeStart = s;
  let rangeEnd   = e;

  if (hoverDate.value) {
    if (activeInput.value === 'start') {
      rangeStart = hoverDate.value;
      rangeEnd   = e;
    } else {
      rangeStart = s;
      rangeEnd   = hoverDate.value;
    }
  }

  if (rangeStart && rangeEnd) {
    const lo = rangeStart < rangeEnd ? rangeStart : rangeEnd;
    const hi = rangeStart < rangeEnd ? rangeEnd   : rangeStart;
    if (d > lo && d < hi)   classes.push('bg-blue-100');
    if (isSameDay(d, lo))   classes.push('rounded-l-full bg-blue-100');
    if (isSameDay(d, hi))   classes.push('rounded-r-full bg-blue-100');
  }

  return classes;
}

function dayInnerClass(cell: { date: Date | null }): string[] {
  if (!cell.date) return [];
  const classes: string[] = [];
  const d = cell.date;
  const s = localStart.value ? parseLocal(localStart.value) : null;
  const e = localEnd.value   ? parseLocal(localEnd.value)   : null;

  const isStart = s && isSameDay(d, s);
  const isEnd   = e && isSameDay(d, e);

  if (isStart || isEnd) {
    classes.push('bg-blue-600 text-white font-semibold');
  } else if (isToday(d)) {
    classes.push('border-2 border-blue-500 text-blue-600 font-semibold');
  } else {
    classes.push('text-gray-700 hover:bg-gray-100');
  }

  return classes;
}

// ── actions ────────────────────────────────────────────────────────────────
function setActiveInput(input: 'start' | 'end') {
  activeInput.value = input;
}

function selectDay(date: Date) {
  const iso = toDateStr(date);
  if (activeInput.value === 'start') {
    localStart.value = iso;
    if (!localEnd.value || iso > localEnd.value) {
      activeInput.value = 'end';
    }
  } else {
    if (localStart.value && iso < localStart.value) {
      localEnd.value   = localStart.value;
      localStart.value = iso;
    } else {
      localEnd.value = iso;
    }
  }
  scheduleSave();
}

function clearStart() {
  localStart.value = null;
  scheduleSave();
}

function clearEnd() {
  localEnd.value = null;
  scheduleSave();
}

function clearAll() {
  localStart.value = null;
  localEnd.value   = null;
  if (saveTimer) { clearTimeout(saveTimer); saveTimer = null; }
  emit('change', { startDate: null, endDate: null });
  open.value = false;
}

function prevMonth() {
  if (viewMonth.value === 0) { viewMonth.value = 11; viewYear.value--; }
  else viewMonth.value--;
}

function nextMonth() {
  if (viewMonth.value === 11) { viewMonth.value = 0; viewYear.value++; }
  else viewMonth.value++;
}

// ── popover positioning ────────────────────────────────────────────────────
function positionPopover() {
  if (!containerRef.value) return;
  const rect = containerRef.value.getBoundingClientRect();
  
  // Calculate available space
  const spaceBelow = window.innerHeight - rect.bottom;
  const spaceAbove = rect.top;
  
  // Popover height is approximately 340px
  const popoverHeight = 340;
  
  // Position below if there's enough space, otherwise above
  let top: number;
  if (spaceBelow >= popoverHeight || spaceBelow > spaceAbove) {
    top = rect.bottom + 4;
  } else {
    top = rect.top - popoverHeight - 4;
  }
  
  // Ensure popover stays within viewport vertically
  if (top < 8) top = 8;
  if (top + popoverHeight > window.innerHeight - 8) {
    top = window.innerHeight - popoverHeight - 8;
  }
  
  // Calculate horizontal position
  let left = rect.left;
  const popoverWidth = 288; // w-72 = 18rem = 288px
  
  // Ensure popover stays within viewport horizontally
  if (left + popoverWidth > window.innerWidth - 8) {
    left = window.innerWidth - popoverWidth - 8;
  }
  if (left < 8) left = 8;
  
  popoverStyle.value = { 
    top: `${top}px`, 
    left: `${left}px` 
  };
}

function togglePicker() {
  open.value = !open.value;
  if (open.value) {
    const seed = localEnd.value || localStart.value;
    const d = seed ? parseLocal(seed) : new Date();
    viewYear.value  = d.getFullYear();
    viewMonth.value = d.getMonth();
    activeInput.value = 'end';
    setTimeout(positionPopover, 0);
  }
}

function handleOutsideClick(e: MouseEvent) {
  if (
    open.value &&
    !containerRef.value?.contains(e.target as Node) &&
    !popoverRef.value?.contains(e.target as Node)
  ) {
    open.value = false;
  }
}

function handleScroll() {
  if (open.value) {
    positionPopover();
  }
}

onMounted(() => {
  document.addEventListener('mousedown', handleOutsideClick);
  window.addEventListener('scroll', handleScroll, true); // true = capture phase to catch all scrolls
  window.addEventListener('resize', handleScroll);
});

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleOutsideClick);
  window.removeEventListener('scroll', handleScroll, true);
  window.removeEventListener('resize', handleScroll);
  if (saveTimer) clearTimeout(saveTimer);
});
</script>
