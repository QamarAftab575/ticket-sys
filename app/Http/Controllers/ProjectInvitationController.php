<?php

namespace App\Http\Controllers;

use App\Helpers\BillingHelper;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Services\ProjectInvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ProjectInvitationController extends Controller
{
    public function __construct(private ProjectInvitationService $invitationService)
    {
    }

    /**
     * Send a project invitation.
     * - Workspace member → auto-accepted, added directly to project_members
     * - External user → pending invitation with token email
     */
    public function store(Request $request, Project $project)
    {
        Gate::authorize('manageMembers', $project);

        $validated = $request->validate([
            'email' => 'required|email',
            'role'  => 'required|in:project_admin,editor,commenter,viewer',
        ]);

        // Check if user can add a member (workspace quota check)
        if (!BillingHelper::canAddMember($project->organization)) {
            $errorMessage = BillingHelper::isTemplateMode()
                ? 'This application is in demo mode. Inviting members is restricted.'
                : 'Your member quota has been reached. Please upgrade your plan to add more members.';
            
            return response()->json([
                'message' => $errorMessage,
                'error' => BillingHelper::isTemplateMode() ? 'TEMPLATE_MODE_RESTRICTION' : 'MEMBER_QUOTA_EXCEEDED',
            ], 403);
        }

        try {
            $result = $this->invitationService->createInvitation(
                $project,
                auth()->user(),
                $validated['email'],
                $validated['role']
            );

            // Workspace member was added directly — return member data
            if ($result instanceof \App\Models\ProjectMember) {
                return response()->json([
                    'message' => 'Member added successfully.',
                    'member'  => [
                        'id'          => $result->user->id,
                        'name'        => $result->user->name,
                        'email'       => $result->user->email,
                        'role'        => $result->role,
                        'access_type' => $result->access_type,
                        'is_fixed'    => false,
                    ],
                ], 201);
            }

            // External user — pending invitation
            return response()->json([
                'message'    => 'Invitation sent successfully.',
                'invitation' => $result,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Show the accept invitation page.
     * - Logged in + matching email → auto-accept → redirect to project
     * - Existing user, not logged in → redirect to login with token in session
     * - New user (no account) → create stub account, log in, redirect to set-password
     */
    public function showAccept(string $token)
    {
        try {
            $invitation = $this->invitationService->validateToken($token);

            if (auth()->check()) {
                if (auth()->user()->email === $invitation->email) {
                    $projectMember = $this->invitationService->accept($token, auth()->user());
                    return redirect()->route('projects.show', $projectMember->project_id)
                        ->with('status', 'Welcome! You have joined ' . $invitation->project->name);
                }

                return Inertia::render('Invitations/InvalidInvitation', [
                    'message' => 'You are logged in with a different email address.',
                ]);
            }

            $user = \App\Models\User::where('email', $invitation->email)->first();

            if ($user) {
                // Existing user — redirect to login, token stored in session
                return redirect()->route('login')
                    ->with('project_invitation_token', $token);
            }

            // New user — create stub account, log in, redirect to set-password
            $stub = \App\Models\User::create([
                'name'              => explode('@', $invitation->email)[0],
                'email'             => $invitation->email,
                'password'          => \Illuminate\Support\Str::random(32), // random, unusable
                'must_set_password' => true,
                'email_verified_at' => now(),
            ]);

            auth()->login($stub);

            // Store token so savePassword can accept it after password is set
            session(['project_invitation_token' => $token]);

            return redirect()->route('password.set');
        } catch (\Exception $e) {
            return Inertia::render('Invitations/InvalidInvitation', ['message' => $e->getMessage()]);
        }
    }

    /**
     * Show confirmation page (logged-in user).
     */
    public function showConfirm(string $token)
    {
        try {
            $invitation = $this->invitationService->validateToken($token);

            if (!auth()->check() || auth()->user()->email !== $invitation->email) {
                return redirect()->route('project-invitations.accept', ['token' => $token]);
            }

            return Inertia::render('Invitations/ConfirmProjectInvitation', [
                'token'       => $token,
                'projectName' => $invitation->project->name,
                'role'        => $invitation->role,
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Invitations/InvalidInvitation', ['message' => $e->getMessage()]);
        }
    }

    /**
     * Accept a project invitation (logged-in user).
     */
    public function accept(Request $request)
    {
        try {
            $token = $request->input('token');
            $projectMember = $this->invitationService->accept($token, auth()->user());

            return redirect()->route('projects.show', $projectMember->project_id)
                ->with('status', 'You have joined the project.');
        } catch (\Exception $e) {
            return back()->withErrors(['token' => $e->getMessage()]);
        }
    }

    /**
     * Resend a project invitation.
     */
    public function resend(ProjectInvitation $invitation)
    {
        Gate::authorize('manageMembers', $invitation->project);

        try {
            $this->invitationService->resend($invitation);

            return response()->json(['message' => 'Invitation resent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Cancel a project invitation.
     */
    public function destroy(ProjectInvitation $invitation)
    {
        Gate::authorize('manageMembers', $invitation->project);

        try {
            $this->invitationService->cancel($invitation);

            return response()->json(['message' => 'Invitation cancelled.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * List pending invitations for a project.
     */
    public function index(Project $project)
    {
        Gate::authorize('manageMembers', $project);

        $invitations = $project->invitations()
            ->pending()
            ->orderBy('created_at', 'desc')
            ->get(['id', 'email', 'role', 'created_at', 'expires_at']);

        return response()->json($invitations);
    }
}
