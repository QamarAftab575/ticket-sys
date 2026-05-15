<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteMemberRequest;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Services\OrganizationInvitationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrganizationInvitationController extends Controller
{
    public function __construct(private OrganizationInvitationService $invitationService)
    {
    }

    /**
     * Show the accept invitation page.
     */
    public function showAccept(string $token)
    {
        try {
            $invitation = $this->invitationService->validateToken($token);

            // Check if user is logged in
            if (auth()->check()) {
                // User is logged in - check if email matches
                if (auth()->user()->email === $invitation->email) {
                    // Email matches, auto-accept and redirect
                    $this->invitationService->acceptForExistingUser($token, auth()->user());
                    return redirect()->route('workspace.dashboard', $invitation->organization)
                        ->with('status', 'Welcome! You have been added to ' . $invitation->organization->name);
                } else {
                    // Email doesn't match - show error
                    return Inertia::render('Invitations/InvalidInvitation', [
                        'message' => 'You are logged in with a different email address. Please log out and try again.',
                    ]);
                }
            }

            // User not logged in - check if email exists in system
            $userExists = \App\Models\User::where('email', $invitation->email)->exists();

            if ($userExists) {
                // Existing user - redirect to login with token in session
                return redirect()->route('login')->with('invitation_token', $token);
            } else {
                // New user - redirect to register with token and email
                return redirect()->route('register', ['token' => $token, 'email' => $invitation->email]);
            }
        } catch (\Exception $e) {
            return Inertia::render('Invitations/InvalidInvitation', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Show confirmation page for accepting invitation (logged-in user).
     */
    public function showConfirm(string $token)
    {
        try {
            $invitation = $this->invitationService->validateToken($token);

            if (!auth()->check() || auth()->user()->email !== $invitation->email) {
                return redirect()->route('invitations.show-accept', ['token' => $token]);
            }

            return Inertia::render('Invitations/ConfirmInvitation', [
                'token' => $token,
                'organizationName' => $invitation->organization->name,
                'role' => $invitation->role,
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Invitations/InvalidInvitation', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Accept invitation (for logged-in user).
     */
    public function accept(Request $request)
    {
        try {
            $token = $request->input('token');
            $this->invitationService->acceptForExistingUser($token, auth()->user());

            return redirect()->route('dashboard')->with('status', 'Invitation accepted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['token' => $e->getMessage()]);
        }
    }

    /**
     * Send invitation (admin action).
     */
    public function store(InviteMemberRequest $request)
    {
        try {
            $organization = Organization::findOrFail($request->input('organization_id'));

            if (!$organization->isAdmin(auth()->user())) {
                return response()->json(['message' => 'You do not have permission to invite members.'], 403);
            }

            $invitation = $this->invitationService->createInvitation(
                $organization,
                auth()->user(),
                $request->validated('email'),
                $request->validated('role', 'member')
            );

            // TODO: Send email with invitation link

            return response()->json([
                'message' => 'Invitation sent successfully',
                'invitation' => $invitation,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Resend invitation.
     */
    public function resend(OrganizationInvitation $invitation)
    {
        try {
            if (!$invitation->organization->isAdmin(auth()->user())) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            $this->invitationService->resendInvitation($invitation);

            return response()->json(['message' => 'Invitation resent successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy(OrganizationInvitation $invitation)
    {
        try {
            if (!$invitation->organization->isAdmin(auth()->user())) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            $this->invitationService->cancelInvitation($invitation);

            return response()->json(['message' => 'Invitation cancelled successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function listPending(Organization $organization)
    {
        if (!$organization->hasMember(auth()->user())) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $invitations = $organization->invitations()
            ->pending()
            ->orderBy('created_at', 'desc')
            ->get(['id', 'email', 'role', 'created_at', 'expires_at', 'accepted_at']);

        return response()->json($invitations);
    }

    public function expire(OrganizationInvitation $invitation)
    {
        try {
            if (!$invitation->organization->isAdmin(auth()->user())) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            $invitation->update(['expires_at' => now()]);

            return response()->json(['message' => 'Invitation expired successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
