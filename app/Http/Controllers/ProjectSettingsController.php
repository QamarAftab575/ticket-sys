<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteProjectRequest;
use App\Http\Requests\DuplicateProjectRequest;
use App\Http\Requests\UpdateProjectGeneralRequest;
use App\Http\Requests\UpdateProjectPrivacyRequest;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Inertia\Inertia;

class ProjectSettingsController extends Controller
{
    protected ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * Display the project settings page.
     */
    public function show(Project $project): Response
    {
        $this->authorize('update', $project);

        return Inertia::render('Projects/Settings', [
            'project' => $project->load('owner', 'members'),
        ]);
    }

    /**
     * Update general project settings.
     */
    public function updateGeneral(UpdateProjectGeneralRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $this->projectService->updateProject($project, $request->validated());

        return redirect()->back()->with('success', 'Project settings updated successfully.');
    }

    /**
     * Update project privacy settings.
     */
    public function updatePrivacy(UpdateProjectPrivacyRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('changePrivacy', $project);

        $validated = $request->validated();
        $memberIds = $validated['member_ids'] ?? null;

        $this->projectService->changePrivacy($project, $validated['privacy'], $memberIds, auth()->user());

        return redirect()->back()->with('success', 'Privacy settings updated successfully.');
    }

    /**
     * Archive a project.
     */
    public function archive(Project $project): RedirectResponse
    {
        $this->authorize('archive', $project);

        $this->projectService->archiveProject($project, auth()->user());

        return redirect()->route('projects.index')->with('success', 'Project archived successfully.');
    }

    /**
     * Unarchive a project.
     */
    public function unarchive(Project $project): RedirectResponse
    {
        $this->authorize('unarchive', $project);

        $this->projectService->unarchiveProject($project, auth()->user());

        return redirect()->back()->with('success', 'Project unarchived successfully.');
    }

    /**
     * Delete a project.
     */
    public function delete(DeleteProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        // Verify project name matches
        if ($request->validated()['project_name'] !== $project->name) {
            return redirect()->back()->withErrors(['project_name' => 'Project name does not match.']);
        }

        $this->projectService->deleteProject($project, auth()->user());

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }

    /**
     * Duplicate a project.
     */
    public function duplicate(DuplicateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('duplicate', $project);

        $newProject = $this->projectService->duplicateProject($project, auth()->user(), $request->validated());

        return redirect()->route('projects.show', $newProject)->with('success', 'Project duplicated successfully.');
    }
}
