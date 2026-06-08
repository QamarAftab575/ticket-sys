<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * Show the reporting dashboard.
     * Restricted to workspace owners and admins.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();

        $this->authorizeReports($user);

        $defaultFilters = $this->defaultFilters();
        $projects       = $this->getVisibleProjects($user);
        $projectIds     = $projects->pluck('id')->all();
        $members        = $this->getMembersForProjects($projectIds);
        $data           = $this->buildReportData($projects, $defaultFilters);

        return Inertia::render('Reports/Index', [
            'projects'       => $projects->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'color' => $p->color])->values(),
            'allMembers'     => $members,
            'initialData'    => $data,
            'defaultFilters' => $defaultFilters,
        ]);
    }

    /**
     * Return filtered JSON data (Apply button).
     */
    public function getData(Request $request): JsonResponse
    {
        $user = auth()->user();

        $this->authorizeReports($user);

        $validated = $request->validate([
            'date_from'      => ['required', 'date', 'before_or_equal:date_to'],
            'date_to'        => ['required', 'date', 'after_or_equal:date_from'],
            'project_id'     => ['nullable', 'uuid'],
            'assignee_id'    => ['nullable', 'uuid'],
            'project_status' => ['nullable', 'string', 'in:on_track,at_risk,off_track,on_hold,complete'],
        ]);

        $projects = $this->getVisibleProjects($user);

        return response()->json($this->buildReportData($projects, $validated));
    }

    /**
     * Return assignees for a project (cascading dropdown).
     */
    public function getAssignees(Request $request): JsonResponse
    {
        $user = auth()->user();

        $this->authorizeReports($user);

        $request->validate([
            'project_id' => ['nullable', 'uuid'],
        ]);

        $projectId      = $request->input('project_id');
        $visibleIds     = $this->getVisibleProjects($user)->pluck('id')->all();

        // If a specific project is requested, ensure the user can see it
        $scopeIds = $projectId && in_array($projectId, $visibleIds)
            ? [$projectId]
            : $visibleIds;

        if (empty($scopeIds)) {
            return response()->json([]);
        }

        $members = User::join('project_members', 'project_members.user_id', '=', 'users.id')
            ->whereIn('project_members.project_id', $scopeIds)
            ->whereNull('users.deleted_at')
            ->select('users.id', 'users.name')
            ->distinct()
            ->orderBy('users.name')
            ->get()
            ->map(fn($u) => ['id' => $u->id, 'name' => ucwords($u->name)]);

        return response()->json($members);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Authorization
    // ─────────────────────────────────────────────────────────────────────────

    private function authorizeReports(User $user): void
    {
        // Must be owner or admin in at least one workspace, or a global admin
        $isWorkspaceManager = $user->organizationMemberships()
            ->whereIn('role', ['owner', 'admin'])
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->exists();

        if (!$isWorkspaceManager && !$user->isAdmin()) {
            abort(403, 'Access to reports is restricted to workspace owners and admins.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Core Report Builder
    // ─────────────────────────────────────────────────────────────────────────

    private function buildReportData($allProjects, array $filters): array
    {
        $dateFrom      = Carbon::parse($filters['date_from'])->startOfDay();
        $dateTo        = Carbon::parse($filters['date_to'])->endOfDay();
        $projectId     = $filters['project_id']     ?? null;
        $assigneeId    = $filters['assignee_id']    ?? null;
        $projectStatus = $filters['project_status'] ?? null;

        // Narrow down projects based on filters
        $scopedProjects = $allProjects;

        if ($projectId) {
            $scopedProjects = $scopedProjects->filter(fn($p) => $p->id === $projectId);
        }

        if ($projectStatus) {
            $scopedProjects = $scopedProjects->filter(fn($p) => $p->status === $projectStatus);
        }

        $projectIds = $scopedProjects->pluck('id')->values()->all();

        // Guard: nothing to show
        if (empty($projectIds)) {
            return ['stats' => $this->emptyStats(), 'charts' => $this->emptyCharts()];
        }

        $baseTask = fn() => Task::whereIn('tasks.project_id', $projectIds)
            ->whereBetween('tasks.created_at', [$dateFrom, $dateTo])
            ->when($assigneeId, fn($q) => $q->where('tasks.assignee_id', $assigneeId))
            ->whereNull('tasks.deleted_at');

        return [
            'stats'  => $this->buildStats($baseTask, $scopedProjects, $projectIds, $dateFrom, $dateTo, $assigneeId),
            'charts' => $this->buildCharts($baseTask, $scopedProjects, $projectIds, $dateFrom, $dateTo, $assigneeId),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Stats
    // ─────────────────────────────────────────────────────────────────────────

    private function buildStats(callable $baseTask, $scopedProjects, array $projectIds, Carbon $dateFrom, Carbon $dateTo, ?string $assigneeId): array
    {
        $total      = $baseTask()->count();
        $completed  = $baseTask()->where('status', 'complete')->count();
        $incomplete = $total - $completed;

        $overdue = Task::whereIn('tasks.project_id', $projectIds)
            ->whereNull('tasks.deleted_at')
            ->where('tasks.status', '!=', 'complete')
            ->whereNotNull('tasks.due_date')
            ->where('tasks.due_date', '<', Carbon::today()->toDateString())
            ->when($assigneeId, fn($q) => $q->where('tasks.assignee_id', $assigneeId))
            ->count();

        $activeProjects = $scopedProjects
            ->filter(fn($p) => !in_array($p->status, ['complete', 'archived', 'on_hold']))
            ->count();

        $teamMembers = empty($projectIds) ? 0 : User::join('project_members', 'project_members.user_id', '=', 'users.id')
            ->whereIn('project_members.project_id', $projectIds)
            ->whereNull('users.deleted_at')
            ->distinct('users.id')
            ->count('users.id');

        // Trend vs previous equal-length period
        $periodDays = max(1, $dateFrom->diffInDays($dateTo));
        $prevFrom   = $dateFrom->copy()->subDays($periodDays)->startOfDay();
        $prevTo     = $dateFrom->copy()->subSecond();

        $prevTotal     = Task::whereIn('tasks.project_id', $projectIds)->whereNull('tasks.deleted_at')
            ->whereBetween('tasks.created_at', [$prevFrom, $prevTo])->count();
        $prevCompleted = Task::whereIn('tasks.project_id', $projectIds)->whereNull('tasks.deleted_at')
            ->where('tasks.status', 'complete')->whereBetween('tasks.created_at', [$prevFrom, $prevTo])->count();

        $trend = fn(int $now, int $prev): int =>
            $prev > 0 ? (int) round((($now - $prev) / $prev) * 100) : ($now > 0 ? 100 : 0);

        return [
            'total_tasks'      => ['value' => $total,          'trend' => $trend($total, $prevTotal),         'label' => 'Total Tasks'],
            'completed_tasks'  => ['value' => $completed,      'trend' => $trend($completed, $prevCompleted), 'label' => 'Completed Tasks', 'percent' => $total > 0 ? (int) round($completed / $total * 100) : 0],
            'incomplete_tasks' => ['value' => $incomplete,     'trend' => 0,                                  'label' => 'Incomplete Tasks'],
            'overdue_tasks'    => ['value' => $overdue,        'trend' => 0,                                  'label' => 'Overdue Tasks'],
            'active_projects'  => ['value' => $activeProjects, 'trend' => 0,                                  'label' => 'Active Projects'],
            'team_members'     => ['value' => $teamMembers,    'trend' => 0,                                  'label' => 'Team Members'],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Charts
    // ─────────────────────────────────────────────────────────────────────────

    private function buildCharts(callable $baseTask, $scopedProjects, array $projectIds, Carbon $dateFrom, Carbon $dateTo, ?string $assigneeId): array
    {
        return [
            'statusBreakdown'         => $this->chartStatusBreakdown($baseTask),
            'completedOverTime'       => $this->chartCompletedOverTime($projectIds, $dateFrom, $dateTo, $assigneeId),
            'tasksByProject'          => $this->chartTasksByProject($scopedProjects, $assigneeId),
            'workloadByAssignee'      => $this->chartWorkloadByAssignee($projectIds, $dateFrom, $dateTo, $assigneeId),
            'statusByProject'         => $this->chartStatusByProject($scopedProjects, $assigneeId),
            'createdVsCompleted'      => $this->chartCreatedVsCompleted($projectIds, $dateFrom, $dateTo, $assigneeId),
            'completionRateByProject' => $this->chartCompletionRate($scopedProjects, $assigneeId),
            'overdueByMember'         => $this->chartOverdueByMember($projectIds, $assigneeId),
        ];
    }

    // 1. Task status breakdown (donut)
    private function chartStatusBreakdown(callable $baseTask): array
    {
        $rows = $baseTask()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusMap = [
            'to_do'       => ['label' => 'To Do',       'color' => '#9ca3af'],
            'in_progress' => ['label' => 'In Progress',  'color' => '#3b82f6'],
            'blocked'     => ['label' => 'Blocked',      'color' => '#ef4444'],
            'in_review'   => ['label' => 'In Review',    'color' => '#f59e0b'],
            'complete'    => ['label' => 'Complete',     'color' => '#22c55e'],
        ];

        $labels = $data = $colors = [];

        foreach ($statusMap as $key => $meta) {
            $labels[] = $meta['label'];
            $data[]   = (int) ($rows[$key] ?? 0);
            $colors[] = $meta['color'];
        }

        return ['labels' => $labels, 'datasets' => [['data' => $data, 'backgroundColor' => $colors]]];
    }

    // 2. Tasks completed over time (line) — DB-agnostic weekly grouping
    private function chartCompletedOverTime(array $projectIds, Carbon $dateFrom, Carbon $dateTo, ?string $assigneeId): array
    {
        [$labels, $weekKeys, $bucketExpr] = $this->weekBuckets($dateFrom, $dateTo);

        $rows = Task::whereIn('tasks.project_id', $projectIds)
            ->whereNull('tasks.deleted_at')
            ->where('tasks.status', 'complete')
            ->whereNotNull('tasks.completed_at')
            ->whereBetween('tasks.completed_at', [$dateFrom, $dateTo])
            ->when($assigneeId, fn($q) => $q->where('tasks.assignee_id', $assigneeId))
            ->select(DB::raw("{$bucketExpr} as week_key"), DB::raw('count(*) as total'))
            ->groupBy('week_key')
            ->pluck('total', 'week_key');

        return [
            'labels'   => $labels,
            'datasets' => [[
                'label'           => 'Tasks Completed',
                'data'            => array_map(fn($k) => (int) ($rows[$k] ?? 0), $weekKeys),
                'borderColor'     => '#6366f1',
                'backgroundColor' => 'rgba(99,102,241,0.1)',
                'tension'         => 0.4,
                'fill'            => true,
            ]],
        ];
    }

    // 3. Tasks by project (horizontal bar)
    private function chartTasksByProject($scopedProjects, ?string $assigneeId): array
    {
        $projectIds = $scopedProjects->pluck('id')->all();

        $counts = empty($projectIds) ? collect() : Task::whereIn('tasks.project_id', $projectIds)
            ->whereNull('tasks.deleted_at')
            ->when($assigneeId, fn($q) => $q->where('tasks.assignee_id', $assigneeId))
            ->select('tasks.project_id', DB::raw('count(*) as total'))
            ->groupBy('tasks.project_id')
            ->pluck('total', 'project_id');

        $palette = ['#6366f1', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316'];
        $labels = $data = $colors = [];

        foreach ($scopedProjects->values() as $i => $project) {
            $labels[] = $project->name;
            $data[]   = (int) ($counts[$project->id] ?? 0);
            $colors[] = $project->color ?: $palette[$i % count($palette)];
        }

        return ['labels' => $labels, 'datasets' => [['label' => 'Tasks', 'data' => $data, 'backgroundColor' => $colors]]];
    }

    // 4. Workload by assignee (stacked bar)
    private function chartWorkloadByAssignee(array $projectIds, Carbon $dateFrom, Carbon $dateTo, ?string $assigneeId): array
    {
        $rows = Task::whereIn('tasks.project_id', $projectIds)
            ->whereNull('tasks.deleted_at')
            ->whereBetween('tasks.created_at', [$dateFrom, $dateTo])
            ->when($assigneeId, fn($q) => $q->where('tasks.assignee_id', $assigneeId))
            ->whereNotNull('tasks.assignee_id')
            ->join('users', 'users.id', '=', 'tasks.assignee_id')
            ->whereNull('users.deleted_at')
            ->select('users.name', 'tasks.status', DB::raw('count(*) as total'))
            ->groupBy('users.name', 'tasks.status')
            ->get();

        $members    = $rows->pluck('name')->unique()->sort()->values()->all();
        $completed  = [];
        $incomplete = [];

        foreach ($members as $name) {
            $memberRows  = $rows->where('name', $name);
            $completed[]  = (int) $memberRows->where('status', 'complete')->sum('total');
            $incomplete[] = (int) $memberRows->where('status', '!=', 'complete')->sum('total');
        }

        return [
            'labels'   => array_map('ucwords', $members),
            'datasets' => [
                ['label' => 'Complete',   'data' => $completed,  'backgroundColor' => '#22c55e'],
                ['label' => 'Incomplete', 'data' => $incomplete, 'backgroundColor' => '#3b82f6'],
            ],
        ];
    }

    // 5. Tasks by status per project (grouped bar)
    private function chartStatusByProject($scopedProjects, ?string $assigneeId): array
    {
        $projectIds = $scopedProjects->pluck('id')->all();

        $rows = empty($projectIds) ? collect() : Task::whereIn('tasks.project_id', $projectIds)
            ->whereNull('tasks.deleted_at')
            ->when($assigneeId, fn($q) => $q->where('tasks.assignee_id', $assigneeId))
            ->select('tasks.project_id', 'tasks.status', DB::raw('count(*) as total'))
            ->groupBy('tasks.project_id', 'tasks.status')
            ->get();

        $statusMap = [
            'to_do'       => ['label' => 'To Do',       'color' => '#9ca3af'],
            'in_progress' => ['label' => 'In Progress',  'color' => '#3b82f6'],
            'blocked'     => ['label' => 'Blocked',      'color' => '#ef4444'],
            'in_review'   => ['label' => 'In Review',    'color' => '#f59e0b'],
            'complete'    => ['label' => 'Complete',     'color' => '#22c55e'],
        ];

        $labels   = $scopedProjects->pluck('name')->values()->all();
        $datasets = [];

        foreach ($statusMap as $status => $meta) {
            $data = [];
            foreach ($scopedProjects->values() as $project) {
                $data[] = (int) $rows->where('project_id', $project->id)->where('status', $status)->sum('total');
            }
            $datasets[] = ['label' => $meta['label'], 'data' => $data, 'backgroundColor' => $meta['color'], 'borderRadius' => 3];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    // 6. Tasks created vs completed (dual line)
    private function chartCreatedVsCompleted(array $projectIds, Carbon $dateFrom, Carbon $dateTo, ?string $assigneeId): array
    {
        [$labels, $weekKeys, $bucketExpr] = $this->weekBuckets($dateFrom, $dateTo);

        $created = Task::whereIn('tasks.project_id', $projectIds)
            ->whereNull('tasks.deleted_at')
            ->whereBetween('tasks.created_at', [$dateFrom, $dateTo])
            ->when($assigneeId, fn($q) => $q->where('tasks.assignee_id', $assigneeId))
            ->select(DB::raw("{$bucketExpr} as week_key"), DB::raw('count(*) as total'))
            ->groupBy('week_key')->pluck('total', 'week_key');

        $completed = Task::whereIn('tasks.project_id', $projectIds)
            ->whereNull('tasks.deleted_at')
            ->where('tasks.status', 'complete')
            ->whereNotNull('tasks.completed_at')
            ->whereBetween('tasks.completed_at', [$dateFrom, $dateTo])
            ->when($assigneeId, fn($q) => $q->where('tasks.assignee_id', $assigneeId))
            ->select(DB::raw("{$bucketExpr} as week_key"), DB::raw('count(*) as total'))
            ->groupBy('week_key')->pluck('total', 'week_key');

        return [
            'labels'   => $labels,
            'datasets' => [
                ['label' => 'Created',   'data' => array_map(fn($k) => (int) ($created[$k]   ?? 0), $weekKeys), 'borderColor' => '#6366f1', 'tension' => 0.4, 'fill' => false],
                ['label' => 'Completed', 'data' => array_map(fn($k) => (int) ($completed[$k] ?? 0), $weekKeys), 'borderColor' => '#22c55e', 'tension' => 0.4, 'fill' => false],
            ],
        ];
    }

    // 7. Completion rate by project (%)
    private function chartCompletionRate($scopedProjects, ?string $assigneeId): array
    {
        $projectIds = $scopedProjects->pluck('id')->all();

        $totals = empty($projectIds) ? collect() : Task::whereIn('tasks.project_id', $projectIds)
            ->whereNull('tasks.deleted_at')
            ->when($assigneeId, fn($q) => $q->where('tasks.assignee_id', $assigneeId))
            ->select('tasks.project_id', DB::raw('count(*) as total'))
            ->groupBy('tasks.project_id')->pluck('total', 'project_id');

        $completedCounts = empty($projectIds) ? collect() : Task::whereIn('tasks.project_id', $projectIds)
            ->whereNull('tasks.deleted_at')
            ->where('tasks.status', 'complete')
            ->when($assigneeId, fn($q) => $q->where('tasks.assignee_id', $assigneeId))
            ->select('tasks.project_id', DB::raw('count(*) as total'))
            ->groupBy('tasks.project_id')->pluck('total', 'project_id');

        $labels = $data = $colors = [];

        foreach ($scopedProjects->values() as $project) {
            $t   = (int) ($totals[$project->id] ?? 0);
            $c   = (int) ($completedCounts[$project->id] ?? 0);
            $pct = $t > 0 ? (int) round($c / $t * 100) : 0;

            $labels[] = $project->name;
            $data[]   = $pct;
            $colors[] = $pct >= 75 ? '#22c55e' : ($pct >= 40 ? '#f59e0b' : '#ef4444');
        }

        return ['labels' => $labels, 'datasets' => [['label' => 'Completion %', 'data' => $data, 'backgroundColor' => $colors, 'borderRadius' => 6]]];
    }

    // 8. Overdue tasks by member (bar, sorted desc)
    private function chartOverdueByMember(array $projectIds, ?string $assigneeId): array
    {
        $rows = Task::whereIn('tasks.project_id', $projectIds)
            ->whereNull('tasks.deleted_at')
            ->where('tasks.status', '!=', 'complete')
            ->whereNotNull('tasks.due_date')
            ->where('tasks.due_date', '<', Carbon::today()->toDateString())
            ->when($assigneeId, fn($q) => $q->where('tasks.assignee_id', $assigneeId))
            ->whereNotNull('tasks.assignee_id')
            ->join('users', 'users.id', '=', 'tasks.assignee_id')
            ->whereNull('users.deleted_at')
            ->select('users.name', DB::raw('count(*) as total'))
            ->groupBy('users.name')
            ->orderByDesc('total')
            ->get();

        return [
            'labels'   => $rows->map(fn($r) => ucwords($r->name))->all(),
            'datasets' => [[
                'label'           => 'Overdue Tasks',
                'data'            => $rows->pluck('total')->map(fn($v) => (int) $v)->all(),
                'backgroundColor' => '#ef4444',
                'borderRadius'    => 6,
            ]],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function defaultFilters(): array
    {
        return [
            'date_from'      => Carbon::now()->subDays(30)->toDateString(),
            'date_to'        => Carbon::now()->toDateString(),
            'project_id'     => null,
            'assignee_id'    => null,
            'project_status' => null,
        ];
    }

    private function getVisibleProjects(User $user)
    {
        return Project::visibleTo($user)
            ->whereNull('archived_at')
            ->orderBy('name')
            ->get(['id', 'name', 'color', 'status']);
    }

    private function getMembersForProjects(array $projectIds): array
    {
        if (empty($projectIds)) return [];

        return User::join('project_members', 'project_members.user_id', '=', 'users.id')
            ->whereIn('project_members.project_id', $projectIds)
            ->whereNull('users.deleted_at')
            ->select('users.id', 'users.name')
            ->distinct()
            ->orderBy('users.name')
            ->get()
            ->map(fn($u) => ['id' => $u->id, 'name' => ucwords($u->name)])
            ->all();
    }

    /**
     * Returns [$labels, $weekKeys, $bucketSqlExpr] for weekly bucketing.
     * Uses STRFTIME for SQLite compatibility, DATE_FORMAT for MySQL.
     * Both produce a 'YYYY-WW' string that can be used as a grouping key.
     */
    private function weekBuckets(Carbon $from, Carbon $to): array
    {
        $labels   = [];
        $weekKeys = [];
        $cursor   = $from->copy()->startOfWeek();

        while ($cursor->lte($to)) {
            $labels[]   = $cursor->format('M d');
            $weekKeys[] = $cursor->format('Y-W');
            $cursor->addWeek();
        }

        if (empty($labels)) {
            $labels[]   = $from->format('M d');
            $weekKeys[] = $from->format('Y-W');
        }

        $driver     = DB::getDriverName();
        $column     = 'completed_at'; // overridden by callers via the raw expression
        $bucketExpr = $driver === 'sqlite'
            ? "strftime('%Y-%W', completed_at)"
            : "DATE_FORMAT(completed_at, '%Y-%u')";

        return [$labels, $weekKeys, $bucketExpr];
    }

    private function emptyStats(): array
    {
        $zero = ['value' => 0, 'trend' => 0];
        return [
            'total_tasks'      => array_merge($zero, ['label' => 'Total Tasks']),
            'completed_tasks'  => array_merge($zero, ['label' => 'Completed Tasks', 'percent' => 0]),
            'incomplete_tasks' => array_merge($zero, ['label' => 'Incomplete Tasks']),
            'overdue_tasks'    => array_merge($zero, ['label' => 'Overdue Tasks']),
            'active_projects'  => array_merge($zero, ['label' => 'Active Projects']),
            'team_members'     => array_merge($zero, ['label' => 'Team Members']),
        ];
    }

    private function emptyCharts(): array
    {
        $empty = ['labels' => [], 'datasets' => []];
        return [
            'statusBreakdown'         => $empty,
            'completedOverTime'       => $empty,
            'tasksByProject'          => $empty,
            'workloadByAssignee'      => $empty,
            'statusByProject'         => $empty,
            'createdVsCompleted'      => $empty,
            'completionRateByProject' => $empty,
            'overdueByMember'         => $empty,
        ];
    }
}
