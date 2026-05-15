<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Response;
use Inertia\Inertia;

class ProjectActivityController extends Controller
{
    /**
     * Display the project activity log.
     */
    public function index(Project $project): Response
    {
        $this->authorize('viewActivity', $project);

        $activities = $project->activities()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return Inertia::render('Projects/Activity', [
            'project' => $project,
            'activities' => $activities,
        ]);
    }
}
