<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\BillingHelper;
use Illuminate\Http\Request;

class SubscriptionStatusController extends Controller
{
    /**
     * Get the authenticated user's subscription expiry status.
     * Includes grace period information and warning messages.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExpiryStatus(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get comprehensive expiry status
        $status = BillingHelper::getExpiryStatus($user);

        return response()->json($status);
    }

    /**
     * Get all owned workspaces that are currently suspended.
     * Only returns workspaces if user's account is suspended.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSuspendedWorkspaces(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $suspendedWorkspaces = BillingHelper::getSuspendedOwnedWorkspaces($user);

        return response()->json([
            'suspended' => $suspendedWorkspaces->count() > 0,
            'count' => $suspendedWorkspaces->count(),
            'workspaces' => $suspendedWorkspaces->map(function ($workspace) {
                return [
                    'id' => $workspace->id,
                    'name' => $workspace->name,
                    'slug' => $workspace->slug,
                ];
            }),
        ]);
    }

    /**
     * Check if user needs renewal warning.
     * Returns true if subscription needs attention (warning, grace period, or suspended).
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function needsRenewalWarning(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'needs_warning' => BillingHelper::needsRenewalWarning($user),
            'status' => BillingHelper::getExpiryStatus($user)['status'],
        ]);
    }
}
