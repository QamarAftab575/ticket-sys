<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddProjectMemberRequest;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectMemberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ProjectMemberController extends Controller
{
    public function __construct(private ProjectMemberService $memberService)
    {
    }

    /**
     * Display a listing of project members (JSON for modal).
     */
    public function index(Project $project)
    {
        Gate::authorize('view', $project);

        if (request()->wantsJson() || request()->expectsJson()) {
            $members = $project->members()
                ->select('users.id', 'users.name', 'users.email', 'users.avatar')
                ->withPivot('role', 'access_type')
                ->get()
                ->map(fn($u) => [
                    'id'          => $u->id,
                    'name'        => $u->name,
                    'email'       => $u->email,
                    'avatar'      => $u->avatar,
                    'role'        => $u->pivot->role,
                    'access_type' => $u->pivot->access_type,
                ]);

            return response()->json($members);
        }

        $members = $this->memberService->getMembersWithRoles($project);

        return Inertia::render('Projects/Members/Index', [
            'project' => $project,
            'members' => $members,
        ]);
    }

    /**
     * Add a member to the project.
     */
    public function store(AddProjectMemberRequest $request, Project $project)
    {
        Gate::authorize('addMember', $project);

        $user = User::findOrFail($request->user_id);

        try {
            DB::transaction(fn() => $this->memberService->addMember($project, $user, auth()->user()));
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withErrors(['user_id' => $e->getMessage()]);
        }

        return redirect()->route('projects.members.index', $project)
            ->with('success', 'Member added successfully.');
    }

    /**
     * Remove a member from the project.
     */
    public function destroy(Project $project, User $user)
    {
        Gate::authorize('removeMember', $project);

        $this->memberService->removeMember($project, $user);

        return redirect()->route('projects.members.index', $project)
            ->with('success', 'Member removed successfully.');
    }

    /**
     * Change member role.
     */
    public function changeRole(Project $project, User $user)
    {
        Gate::authorize('manageMembers', $project);

        $role = request()->input('role');

        if (!in_array($role, ['project_admin', 'editor', 'commenter', 'viewer'])) {
            return redirect()->back()->withErrors(['role' => 'Invalid role selected.']);
        }

        try {
            $this->memberService->changeRole($project, $user, $role, auth()->user());
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['role' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Member role updated successfully.');
    }

    /**
     * Member leaves project.
     */
    public function leave(Project $project)
    {
        $user = auth()->user();

        try {
            if (!$this->memberService->canMemberLeave($project, $user)) {
                return redirect()->back()->withErrors(['leave' => 'You are the sole project admin. Assign another admin before leaving.']);
            }

            $this->memberService->removeMemberByMember($project, $user);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['leave' => $e->getMessage()]);
        }

        return redirect()->route('projects.index')->with('success', 'You have left the project.');
    }
}
