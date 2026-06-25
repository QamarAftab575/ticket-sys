<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50">
      <!-- Page Header -->
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-xl font-semibold text-gray-900">{{ $t('reporting_dashboard') }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $t('full_view_of_team_work') }}</p>
          </div>
          <div class="flex items-center gap-2 text-xs text-gray-400">
            <span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span>
            {{ $t('live_data') }}
          </div>
        </div>
      </div>

      <div class="px-6 py-5 space-y-6 max-w-[1600px] mx-auto">

        <!-- Filter Bar -->
        <ReportFilters
          :projects="projects"
          :all-members="allMembers"
          :filters="filters"
          @update:filters="filters = $event"
          @apply="applyFilters"
          @reset="resetFilters"
        />

        <!-- Error Banner -->
        <div
          v-if="error"
          class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3"
        >
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
          {{ error }}
        </div>

        <!-- Stat Cards  skeleton while loading -->
        <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
          <div v-for="n in 6" :key="n" class="bg-white rounded-xl border border-gray-200 p-4 animate-pulse">
            <div class="h-4 bg-gray-200 rounded w-2/3 mb-3"></div>
            <div class="h-8 bg-gray-200 rounded w-1/2 mb-2"></div>
            <div class="h-3 bg-gray-200 rounded w-1/3"></div>
          </div>
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
          <StatCard
            v-for="card in statCards"
            :key="card.key"
            :label="card.label"
            :value="card.value"
            :trend="card.trend"
            :icon="card.icon"
            :accent="card.accent"
            :subtitle="card.subtitle"
          />
        </div>

        <!-- Charts Grid  skeleton while loading -->
        <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-2 gap-5">
          <div v-for="n in 8" :key="n" class="bg-white rounded-xl border border-gray-200 p-5 animate-pulse">
            <div class="h-5 bg-gray-200 rounded w-1/3 mb-1.5"></div>
            <div class="h-3 bg-gray-200 rounded w-1/2 mb-5"></div>
            <div class="h-56 bg-gray-100 rounded"></div>
          </div>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5">
          <ChartCard title="Task Status Breakdown" subtitle="Distribution across all statuses">
            <canvas ref="chartStatus"></canvas>
          </ChartCard>

          <ChartCard title="Tasks Completed Over Time" subtitle="Weekly completion trend">
            <canvas ref="chartCompletedOverTime"></canvas>
          </ChartCard>

          <ChartCard title="Tasks by Project" subtitle="Total task count per project">
            <canvas ref="chartTasksByProject"></canvas>
          </ChartCard>

          <ChartCard title="Workload by Assignee" subtitle="Completed vs incomplete per member">
            <canvas ref="chartWorkload"></canvas>
          </ChartCard>

          <ChartCard title="Tasks by Status per Project" subtitle="Status breakdown across each project">
            <canvas ref="chartStatusByProject"></canvas>
          </ChartCard>

          <ChartCard title="Tasks Created vs Completed" subtitle="Velocity trend over time">
            <canvas ref="chartVelocity"></canvas>
          </ChartCard>

          <ChartCard title="Completion Rate by Project" subtitle="% of tasks done per project">
            <canvas ref="chartCompletionRate"></canvas>
          </ChartCard>

          <ChartCard title="Overdue Tasks by Member" subtitle="Sorted by count  highest first">
            <canvas ref="chartOverdueByMember"></canvas>
          </ChartCard>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import {
  Chart,
  ArcElement, BarElement, LineElement, PointElement,
  CategoryScale, LinearScale,
  Tooltip, Legend, Filler,
  DoughnutController, BarController, LineController,
} from 'chart.js'
import AppLayout from '@/Layouts/AppLayout.vue'
import ReportFilters from '@/Components/Reports/ReportFilters.vue'
import StatCard from '@/Components/Reports/StatCard.vue'
import ChartCard from '@/Components/Reports/ChartCard.vue'

Chart.register(
  ArcElement, BarElement, LineElement, PointElement,
  CategoryScale, LinearScale,
  Tooltip, Legend, Filler,
  DoughnutController, BarController, LineController
)

//      Props                                                                                                                                       
const props = defineProps({
  projects:       { type: Array,  default: () => [] },
  allMembers:     { type: Array,  default: () => [] },
  initialData:    { type: Object, default: () => ({}) },
  defaultFilters: { type: Object, default: () => ({}) },
})

//      State                                                                                                                                       
const loading = ref(false)
const error   = ref(null)
const stats   = ref(props.initialData?.stats  || {})
const charts  = ref(props.initialData?.charts || {})

const filters = ref({
  date_from:      props.defaultFilters?.date_from      ?? '',
  date_to:        props.defaultFilters?.date_to        ?? '',
  project_id:     props.defaultFilters?.project_id     ?? null,
  assignee_id:    props.defaultFilters?.assignee_id    ?? null,
  project_status: props.defaultFilters?.project_status ?? null,
})

//      Stat cards                                                                                                                             
const statCards = computed(() => {
  const s = stats.value
  return [
    { key: 'total_tasks',      label: 'Total Tasks',      value: s.total_tasks?.value      ?? 0, trend: s.total_tasks?.trend      ?? 0, icon: 'clipboard-list' },
    { key: 'completed_tasks',  label: 'Completed Tasks',  value: s.completed_tasks?.value  ?? 0, trend: s.completed_tasks?.trend  ?? 0, icon: 'circle-check',  subtitle: s.completed_tasks?.percent != null ? `${s.completed_tasks.percent}% of total` : null },
    { key: 'incomplete_tasks', label: 'Incomplete Tasks', value: s.incomplete_tasks?.value ?? 0, trend: s.incomplete_tasks?.trend ?? 0, icon: 'clock' },
    { key: 'overdue_tasks',    label: 'Overdue Tasks',    value: s.overdue_tasks?.value    ?? 0, trend: s.overdue_tasks?.trend    ?? 0, icon: 'alert-triangle', accent: 'red' },
    { key: 'active_projects',  label: 'Active Projects',  value: s.active_projects?.value  ?? 0, trend: s.active_projects?.trend  ?? 0, icon: 'folder-open' },
    { key: 'team_members',     label: 'Team Members',     value: s.team_members?.value     ?? 0, trend: s.team_members?.trend     ?? 0, icon: 'users' },
  ]
})

//      Chart refs                                                                                                                             
const chartStatus            = ref(null)
const chartCompletedOverTime = ref(null)
const chartTasksByProject    = ref(null)
const chartWorkload          = ref(null)
const chartStatusByProject   = ref(null)
const chartVelocity          = ref(null)
const chartCompletionRate    = ref(null)
const chartOverdueByMember   = ref(null)

const instances = {}

function destroyChart(key) {
  if (instances[key]) {
    instances[key].destroy()
    delete instances[key]
  }
}

const sharedFont = { family: "'Figtree', 'system-ui', sans-serif", size: 12 }

function lineOpts() {
  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom', labels: { padding: 14, font: sharedFont } } },
    scales: {
      x: { grid: { color: '#f3f4f6' }, ticks: { font: sharedFont } },
      y: { grid: { color: '#f3f4f6' }, beginAtZero: true, ticks: { font: sharedFont } },
    },
  }
}

function barOpts(indexAxis = 'x') {
  return {
    indexAxis,
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom', labels: { padding: 14, font: sharedFont } } },
    scales: {
      x: { grid: { color: '#f3f4f6' }, ticks: { font: sharedFont } },
      y: { grid: { color: '#f3f4f6' }, beginAtZero: true, ticks: { font: sharedFont } },
    },
  }
}

function buildCharts() {
  const cd = charts.value
  if (!cd) return

  destroyChart('status')
  if (chartStatus.value && cd.statusBreakdown) {
    instances.status = new Chart(chartStatus.value, {
      type: 'doughnut',
      data: cd.statusBreakdown,
      options: { cutout: '65%', responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { padding: 14, font: sharedFont } } } },
    })
  }

  destroyChart('completedOverTime')
  if (chartCompletedOverTime.value && cd.completedOverTime) {
    instances.completedOverTime = new Chart(chartCompletedOverTime.value, { type: 'line', data: cd.completedOverTime, options: lineOpts() })
  }

  destroyChart('tasksByProject')
  if (chartTasksByProject.value && cd.tasksByProject) {
    instances.tasksByProject = new Chart(chartTasksByProject.value, { type: 'bar', data: cd.tasksByProject, options: barOpts('y') })
  }

  destroyChart('workload')
  if (chartWorkload.value && cd.workloadByAssignee) {
    instances.workload = new Chart(chartWorkload.value, {
      type: 'bar',
      data: cd.workloadByAssignee,
      options: {
        ...barOpts('x'),
        scales: {
          x: { stacked: true, grid: { color: '#f3f4f6' }, ticks: { font: sharedFont } },
          y: { stacked: true, grid: { color: '#f3f4f6' }, beginAtZero: true, ticks: { font: sharedFont } },
        },
      },
    })
  }

  destroyChart('statusByProject')
  if (chartStatusByProject.value && cd.statusByProject) {
    instances.statusByProject = new Chart(chartStatusByProject.value, { type: 'bar', data: cd.statusByProject, options: barOpts('x') })
  }

  destroyChart('velocity')
  if (chartVelocity.value && cd.createdVsCompleted) {
    instances.velocity = new Chart(chartVelocity.value, { type: 'line', data: cd.createdVsCompleted, options: lineOpts() })
  }

  destroyChart('completionRate')
  if (chartCompletionRate.value && cd.completionRateByProject) {
    instances.completionRate = new Chart(chartCompletionRate.value, {
      type: 'bar',
      data: cd.completionRateByProject,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { color: '#f3f4f6' }, ticks: { font: sharedFont } },
          y: { grid: { color: '#f3f4f6' }, beginAtZero: true, min: 0, max: 100, ticks: { callback: (v) => v + '%', font: sharedFont } },
        },
      },
    })
  }

  destroyChart('overdueByMember')
  if (chartOverdueByMember.value && cd.overdueByMember) {
    instances.overdueByMember = new Chart(chartOverdueByMember.value, {
      type: 'bar',
      data: cd.overdueByMember,
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { color: '#f3f4f6' }, ticks: { font: sharedFont } }, y: { grid: { color: '#f3f4f6' }, beginAtZero: true, ticks: { font: sharedFont } } } },
    })
  }
}

//      HTTP                                                                                                                                         
async function httpPost(url, data) {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content
  const resp = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrf ?? '',
      'X-Requested-With': 'XMLHttpRequest',
      Accept: 'application/json',
    },
    body: JSON.stringify(data),
  })

  if (!resp.ok) {
    const body = await resp.json().catch(() => ({}))
    throw new Error(body.message || `Server error (${resp.status})`)
  }

  return resp.json()
}

//      Filters                                                                                                                                   
async function applyFilters() {
  loading.value = true
  error.value   = null
  try {
    const result  = await httpPost('/reports/data', filters.value)
    stats.value   = result.stats
    charts.value  = result.charts
    await nextTick()
    buildCharts()
  } catch (e) {
    error.value = e.message || 'Failed to load report data. Please try again.'
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.value = {
    date_from:      props.defaultFilters?.date_from ?? '',
    date_to:        props.defaultFilters?.date_to   ?? '',
    project_id:     null,
    assignee_id:    null,
    project_status: null,
  }
  error.value = null
  applyFilters()
}

//      Init                                                                                                                                           
onMounted(async () => {
  await nextTick()
  buildCharts()
})
</script>

