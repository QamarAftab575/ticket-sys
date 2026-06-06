<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    /**
     * Get user by ID
     * 
     * Returns public profile information for a specific user.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'created_at' => $user->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Search users
     * 
     * Search for users by name or email. Useful for adding members to workspaces or projects.
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $query = $validated['query'];
        $limit = $validated['limit'] ?? 10;

        $users = User::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%");
        })
            ->select(['id', 'name', 'email', 'avatar'])
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $users,
        ]);
    }
}
