<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Search for users by name or email.
     */
    public function search(Request $request)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $query = $request->query('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'data' => []
            ]);
        }

        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->select('id', 'name', 'email')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $users
        ]);
    }

    /**
     * Store the user's active workspace preference.
     */
    public function setActiveWorkspace(Request $request)
    {
        $request->validate([
            'workspace_id' => 'required|exists:organizations,id',
        ]);

        $user = auth()->user();
        
        // Verify user has access to this workspace
        $hasAccess = $user->organizationMemberships()
            ->where('organization_id', $request->workspace_id)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        // Store in user metadata
        $user->update([
            'active_workspace_id' => $request->workspace_id,
        ]);

        return response()->json([
            'message' => 'Active workspace updated successfully',
            'workspace_id' => $request->workspace_id,
        ]);
    }
}
