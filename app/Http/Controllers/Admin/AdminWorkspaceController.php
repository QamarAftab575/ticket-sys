<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;

class AdminWorkspaceController extends Controller
{
    /**
     * Display a listing of all workspaces.
     */
    public function index(Request $request)
    {
        $workspaces = Organization::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => match($request->status) {
                'active' => $q->where('is_active', true),
                'inactive' => $q->where('is_active', false),
                default => $q
            })
            ->withCount(['members', 'projects'])
            ->with('creator:id,name,email')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return inertia('Admin/Workspaces/Index', [
            'workspaces' => $workspaces,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
            ]
        ]);
    }

    /**
     * Deactivate a workspace.
     */
    public function deactivate(Organization $workspace)
    {
        $workspace->update(['is_active' => false]);
        return back()->with('success', "Workspace {$workspace->name} has been deactivated.");
    }

    /**
     * Activate a workspace.
     */
    public function activate(Organization $workspace)
    {
        $workspace->update(['is_active' => true]);
        return back()->with('success', "Workspace {$workspace->name} has been activated.");
    }

    /**
     * Permanently delete a workspace and all related data.
     */
    public function destroy(Organization $workspace)
    {
        $name = $workspace->name;
        $workspace->forceDelete();
        return back()->with('success', "Workspace {$name} has been permanently deleted.");
    }
}
