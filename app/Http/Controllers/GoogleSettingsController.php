<?php

namespace App\Http\Controllers;

use App\Services\GoogleSettingsService;
use App\Http\Requests\GoogleSettingsRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class GoogleSettingsController extends Controller
{
    protected GoogleSettingsService $googleSettingsService;

    public function __construct(GoogleSettingsService $googleSettingsService)
    {
        $this->googleSettingsService = $googleSettingsService;
    }

    /**
     * Show Google settings form
     *
     * @return Response|\Illuminate\Http\RedirectResponse
     */
    public function show(): Response|\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        $canAccess = $user->hasRole(['super-admin', 'admin'])
            || $user->organizations()
                ->wherePivot('is_active', true)
                ->wherePivotIn('role', ['owner', 'admin'])
                ->exists();

        if (!$canAccess) {
            abort(403, 'You do not have permission to access Google settings.');
        }

        $status = $this->googleSettingsService->getStatus();
        
        // Get user workspaces for sidebar (with role information)
        $userWorkspaces = $user->getAccessibleOrganizations()
            ->map(function ($organization) use ($user) {
                return [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'avatar_color' => $organization->avatar_color,
                    'description' => $organization->description,
                    'role' => $user->getWorkspaceRole($organization->id),
                    'is_owner' => $user->isWorkspaceOwner($organization->id),
                    'is_admin' => $user->isWorkspaceAdmin($organization->id),
                    'is_member' => $user->isWorkspaceMember($organization->id),
                ];
            });

        return Inertia::render('Settings/Integrations/GoogleSettings', [
            'status' => $status,
            'userWorkspaces' => $userWorkspaces,
            'currentWorkspace' => null,
            'userRole' => 'member',
        ]);
    }

    /**
     * Update Google settings
     *
     * @param GoogleSettingsRequest $request
     * @return RedirectResponse
     */
    public function update(GoogleSettingsRequest $request): RedirectResponse
    {
        $credentials = [
            'enabled' => $request->boolean('enabled'),
            'client_id' => $request->input('client_id'),
            'client_secret' => $request->input('client_secret'),
            'redirect_uri' => $request->input('redirect_uri'),
        ];

        // Validate credentials
        if (!$this->googleSettingsService->validateCredentials($credentials)) {
            return back()->withErrors(['credentials' => 'Invalid Google credentials']);
        }

        // Save credentials
        $this->googleSettingsService->saveCredentials($credentials);

        return back()->with('success', 'Google login settings updated successfully');
    }

    /**
     * Test Google credentials
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function testCredentials(Request $request): JsonResponse
    {
        $credentials = [
            'client_id' => $request->input('client_id'),
            'client_secret' => $request->input('client_secret'),
            'redirect_uri' => $request->input('redirect_uri'),
        ];

        $isValid = $this->googleSettingsService->testConnection($credentials);

        return response()->json([
            'success' => $isValid,
            'message' => $isValid ? 'Google credentials validated successfully' : 'Invalid Google credentials',
        ]);
    }
}
