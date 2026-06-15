<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\LoginToken;
use App\Models\OrganizationMembership;
use App\Models\User;
use App\Services\AuthService;
use App\Services\OrganizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

    // Registration endpoints
    public function showRegister()
    {
        $token = request()->query('token');
        $email = request()->query('email');

        // Validate token if provided
        if ($token) {
            try {
                $invitationService = app(\App\Services\OrganizationInvitationService::class);
                $invitationService->validateToken($token);
            } catch (\Exception $e) {
                return redirect()->route('register')->withErrors(['token' => $e->getMessage()]);
            }
        }

        return Inertia::render('Auth/Register', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function register(RegisterRequest $request)
    {
        try {
            $user = $this->authService->register($request->validated());
            auth()->login($user);

            // Handle invitation acceptance if token provided
            $token = $request->input('token');
            if ($token) {
                try {
                    // Check if it's an organization or project invitation
                    $type = $request->input('type');
                    
                    if ($type === 'project') {
                        // Handle project invitation
                        $projectInvitationService = app(\App\Services\ProjectInvitationService::class);
                        $projectMember = $projectInvitationService->accept($token, $user);
                        return redirect()->route('projects.show', $projectMember->project_id)
                            ->with('status', 'Welcome! Your account has been created and you have joined the project.');
                    } else {
                        // Handle organization invitation
                        $invitationService = app(\App\Services\OrganizationInvitationService::class);
                        $membership = $invitationService->acceptForNewUser($token, $user);
                        return redirect()->route('workspace.dashboard', $membership->organization)
                            ->with('status', 'Welcome! Your account has been created and you have joined the workspace.');
                    }
                } catch (\Exception $e) {
                    \Log::warning('Invitation acceptance failed during registration: ' . $e->getMessage());
                    return redirect()->route('onboarding');
                }
            }

            return redirect()->route('onboarding');
        } catch (\Exception $e) {
            \Log::error('Registration failed: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Registration failed. Please try again.'])->withInput();
        }
    }

    // Login endpoints
    public function showLogin()
    {
        $googleAuthService = app(\App\Services\GoogleAuthService::class);
        
        return Inertia::render('Auth/Login', [
            'googleLoginEnabled' => $googleAuthService->isEnabled(),
        ]);
    }

    public function login(LoginRequest $request)
    {
        $email = $request->validated('email');
        $password = $request->validated('password');
        $remember = $request->input('remember', true); // Default to true for lengthy sessions

        // Check rate limiting
        if (!$this->authService->checkRateLimit($email)) {
            return back()->withErrors(['email' => 'Too many login attempts. Please try again later.']);
        }

        // Attempt login with remember me
        if ($this->authService->login($email, $password, $remember)) {
            $request->session()->regenerate();

            $user = auth()->user();

            // Redirect to verification if email not verified
            if (!$user->isEmailVerified()) {
                return redirect()->route('email.verify');
            }

            // Check if user is super admin - redirect to admin dashboard
            if ($user->is_super_admin) {
                return redirect()->route('admin.index');
            }

            // Handle org invitation token in session
            $token = session('invitation_token');
            if ($token) {
                try {
                    $invitationService = app(\App\Services\OrganizationInvitationService::class);
                    $membership = $invitationService->acceptForExistingUser($token, $user);
                    session()->forget('invitation_token');
                    return redirect()->route('workspace.dashboard', $membership->organization)
                        ->with('status', 'Welcome! You have been added to ' . $membership->organization->name);
                } catch (\Exception $e) {
                    \Log::warning('Invitation acceptance failed during login: ' . $e->getMessage());
                    session()->forget('invitation_token');
                }
            }

            // Handle project invitation token in session
            $projectToken = session('project_invitation_token');
            if ($projectToken) {
                try {
                    $projectInvitationService = app(\App\Services\ProjectInvitationService::class);
                    $projectMember = $projectInvitationService->accept($projectToken, $user);
                    session()->forget('project_invitation_token');
                    return redirect()->route('projects.show', $projectMember->project_id)
                        ->with('status', 'You have joined the project.');
                } catch (\Exception $e) {
                    \Log::warning('Project invitation acceptance failed during login: ' . $e->getMessage());
                    session()->forget('project_invitation_token');
                }
            }

            return redirect()->intended(route('dashboard'));
        }

        // Increment failed attempts
        $this->authService->incrementFailedAttempts($email);

        return back()->withErrors(['email' => 'The provided credentials are invalid.']);
    }

    // Auto-login via welcome email token
    public function autoLogin(string $token)
    {
        $loginToken = LoginToken::where('token', $token)->with('user')->first();

        if (!$loginToken || !$loginToken->isValid()) {
            return redirect()->route('login')->withErrors(['email' => 'This login link has expired or already been used.']);
        }

        // Mark token as used
        $loginToken->update(['used_at' => now()]);

        // Log the user in if not already authenticated
        if (!auth()->check()) {
            auth()->login($loginToken->user);
        }

        return redirect()->route('dashboard');
    }

    // Logout endpoint
    public function logout(Request $request)
    {
        $this->authService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // Password reset endpoints
    public function showForgotPassword()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(string $token)
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    // Onboarding completion
    public function completeOnboarding(Request $request)
    {
        $validated = $request->validate([
            'workspace' => 'required|array',
            'workspace.name' => 'nullable|string|max:255',
            'workspace.types' => 'nullable|array',
            'workspace.types.*' => 'string|in:Design,HR,Engineering,Education / Teacher,IT Company,Marketing,Finance,Sales,Other',
            'workspace.description' => 'nullable|string|max:1000',
            'invites' => 'nullable|array',
            'invites.*.email' => 'required|email',
            'invites.*.role' => 'required|in:member',
        ]);

        $user = auth()->user();
        $organizationService = app(OrganizationService::class);

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $user, $organizationService) {
                // Step 1: Create workspace
                $workspaceName = $validated['workspace']['name'] ?? 'Personal Workspace';
                $workspaceDescription = $this->buildWorkspaceDescription(
                    $validated['workspace']['types'] ?? [],
                    $validated['workspace']['description'] ?? null
                );

                $organization = $organizationService->createOrganization([
                    'name' => $workspaceName,
                    'description' => $workspaceDescription,
                    'types' => $validated['workspace']['types'] ?? null,
                    'avatar_color' => '#3B82F6',
                ], $user);

                // Assign user as Owner
                $organizationService->addMember($organization, $user, 'workspace_owner');

                // Seed starter project, sections, and tasks for new user
                app(\App\Services\OnboardingDataService::class)->seedWorkspace($organization, $user);

                // Step 2: Send invitations (after workspace creation)
                if (!empty($validated['invites'])) {
                    $invitationService = app(\App\Services\OrganizationInvitationService::class);
                    
                    foreach ($validated['invites'] as $invite) {
                        $invitedUser = User::where('email', $invite['email'])->first();
                        
                        if ($invitedUser && $invitedUser->id !== $user->id) {
                            // User exists, add them directly
                            $organizationService->addMember($organization, $invitedUser, $invite['role']);
                        } elseif (!$invitedUser) {
                            // User doesn't exist, create an invitation via service (sends email)
                            $invitationService->createInvitation(
                                $organization,
                                $user,
                                $invite['email'],
                                $invite['role']
                            );
                        }
                    }
                }

                return redirect()->route('workspace.dashboard', $organization)->with('status', 'Onboarding completed successfully!');
            });
        } catch (\Exception $e) {
            \Log::error('Onboarding failed: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
            ]);
            return back()->withErrors(['workspace' => 'Onboarding failed: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Build workspace description from types and custom description.
     *
     * @param array $types
     * @param string|null $description
     * @return string|null
     */
    private function buildWorkspaceDescription(array $types, ?string $description): ?string
    {
        $parts = [];

        if (!empty($types)) {
            $parts[] = "Industries: " . implode(", ", $types);
        }

        if ($description) {
            $parts[] = $description;
        }

        return !empty($parts) ? implode("\n\n", $parts) : null;
    }

    // Profile management
    public function showProfile()
    {
        $user = auth()->user();

        // Get user workspaces (only active workspaces where user is active)
        $userWorkspaces = $user->organizations()
            ->where('organizations.is_active', true)
            ->wherePivot('is_active', true)
            ->whereNull('organization_memberships.deleted_at')
            ->select('organizations.id', 'organizations.name', 'organizations.avatar_color')
            ->get();

        return Inertia::render('Profile/ManageAccount', [
            'user' => [
                'id'                 => $user->id,
                'name'               => $user->name,
                'email'              => $user->email,
                'avatar'             => $user->avatar,
                'timezone'           => $user->timezone,
                'utc_offset_minutes' => $user->utc_offset_minutes,
                'email_verified_at'  => $user->email_verified_at,
                'created_at'         => $user->created_at,
            ],
            'timezones'       => config('timezones'),
            'userWorkspaces'  => $userWorkspaces,
            'currentWorkspace' => null,
            'userRole'        => 'member',
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'timezone' => ['nullable', 'string', 'max:100', function ($attribute, $value, $fail) {
                if ($value && !in_array($value, array_column(config('timezones'), 'timezone'))) {
                    $fail('The selected timezone is invalid.');
                }
            }],
        ]);

        $user = auth()->user();

        $updateData = ['name' => $validated['name']];

        // Resolve utc_offset_minutes from the central config when timezone is provided
        if (!empty($validated['timezone'])) {
            $timezones = config('timezones');
            $match = collect($timezones)->firstWhere('timezone', $validated['timezone']);

            $updateData['timezone']           = $validated['timezone'];
            $updateData['utc_offset_minutes'] = $match ? $match['offset_minutes'] : null;
        }

        $user->update($updateData);

        return back()->with('status', 'Profile updated successfully!');
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        $user = auth()->user();

        try {
            // Ensure avatars directory exists with correct permissions
            $avatarsDir = storage_path('app/public/avatars');
            if (!is_dir($avatarsDir)) {
                mkdir($avatarsDir, 0755, true);
            }

            // Delete old avatar if exists (use getRawOriginal to bypass URL accessor)
            $rawAvatar = $user->getRawOriginal('avatar');
            if ($rawAvatar && \Storage::disk('public')->exists($rawAvatar)) {
                \Storage::disk('public')->delete($rawAvatar);
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');

            // Set proper permissions on the uploaded file
            $fullPath = storage_path('app/public/' . $path);
            if (file_exists($fullPath)) {
                chmod($fullPath, 0644);
            }

            // Update user avatar
            $user->update(['avatar' => $path]);

            return back()->with('status', 'Profile picture updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Failed to upload avatar: ' . $e->getMessage());
            return back()->withErrors(['avatar' => 'Failed to upload profile picture. Please try again.']);
        }
    }

    public function removeAvatar(Request $request)
    {
        $user = auth()->user();

        try {
            // Delete avatar file if exists (use getRawOriginal to bypass URL accessor)
            $rawAvatar = $user->getRawOriginal('avatar');
            if ($rawAvatar && \Storage::disk('public')->exists($rawAvatar)) {
                \Storage::disk('public')->delete($rawAvatar);
            }

            // Remove avatar from database
            $user->update(['avatar' => null]);

            return back()->with('status', 'Profile picture removed successfully!');
        } catch (\Exception $e) {
            \Log::error('Failed to remove avatar: ' . $e->getMessage());
            return back()->withErrors(['avatar' => 'Failed to remove profile picture. Please try again.']);
        }
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
            'password_confirmation' => 'required|same:password',
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
            'password_confirmation.same' => 'Passwords do not match.',
        ]);

        $user = auth()->user();

        try {
            // Update password
            $user->update([
                'password' => bcrypt($validated['password']),
            ]);

            // Send confirmation email
            Mail::to($user->email)->queue(new \App\Mail\PasswordChangedEmail($user));

            return back()->with('status', 'Password changed successfully!');
        } catch (\Exception $e) {
            \Log::error('Failed to change password: ' . $e->getMessage());
            return back()->withErrors(['password' => 'Failed to change password. Please try again later.']);
        }
    }

    public function deleteAccount(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = auth()->user();

        // Verify email matches
        if ($validated['email'] !== $user->email) {
            return back()->withErrors(['deleteEmail' => 'Email does not match']);
        }

        // Verify password
        if (!\Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['deletePassword' => 'Password is incorrect']);
        }

        // Delete user
        $user->delete();

        // Logout
        auth()->logout();

        return redirect('/')->with('status', 'Account deleted successfully');
    }

    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'workspaceName' => 'required|string|max:255',
            'defaultView' => 'required|in:kanban,list,timeline,calendar',
            'emailNotifications' => 'boolean',
            'mentionNotifications' => 'boolean',
            'commentNotifications' => 'boolean',
        ]);

        // Store settings in user preferences or a settings table
        // For now, we'll just return success
        return back()->with('status', 'Settings saved successfully!');
    }

    // Password setup for new users created via invitation
    public function showSetPassword()
    {
        $user = auth()->user();

        // If user doesn't need to set password, redirect to dashboard
        if (!$user->must_set_password) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/SetPassword', [
            'email' => $user->email,
            'name' => $user->name,
        ]);
    }

    public function savePassword(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
        ]);

        $user = auth()->user();

        $user->update([
            'password'          => Hash::make($validated['password']),
            'must_set_password' => false,
        ]);

        // Accept pending project invitation if one was stored in session
        $projectToken = session('project_invitation_token');
        if ($projectToken) {
            try {
                $projectMember = app(\App\Services\ProjectInvitationService::class)->accept($projectToken, $user);
                session()->forget('project_invitation_token');
                return redirect()->route('projects.show', $projectMember->project_id)
                    ->with('status', 'Password set! Welcome to the project.');
            } catch (\Exception $e) {
                session()->forget('project_invitation_token');
                \Log::warning('Project invitation acceptance failed after set-password: ' . $e->getMessage());
            }
        }

        $workspace = $user->organizations()
            ->where('organizations.is_active', true)
            ->wherePivot('is_active', true)
            ->first();

        if ($workspace) {
            return redirect()->route('workspace.dashboard', $workspace)
                ->with('status', 'Password set successfully! Welcome to your workspace.');
        }

        // External project-only user — redirect to their projects list
        return redirect()->route('projects.index')
            ->with('status', 'Password set successfully!');
    }
}
