<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPlanController extends Controller
{
    /**
     * Display all plans.
     */
    public function index()
    {
        return inertia('Admin/Settings/Plans', [
            'plans' => Plan::orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Create a new plan.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'max_workspaces' => 'required|integer|min:0',
            'max_members_per_workspace' => 'required|integer|min:0',
            'max_projects_per_workspace' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        Plan::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'price' => $request->price,
            'billing_cycle' => $request->billing_cycle,
            'max_workspaces' => $request->max_workspaces,
            'max_members_per_workspace' => $request->max_members_per_workspace,
            'max_projects_per_workspace' => $request->max_projects_per_workspace,
            'features' => $request->features ?? [],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Plan created successfully.');
    }

    /**
     * Update a plan.
     */
    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'max_workspaces' => 'required|integer|min:0',
            'max_members_per_workspace' => 'required|integer|min:0',
            'max_projects_per_workspace' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $plan->update([
            'name' => $request->name,
            'price' => $request->price,
            'billing_cycle' => $request->billing_cycle,
            'max_workspaces' => $request->max_workspaces,
            'max_members_per_workspace' => $request->max_members_per_workspace,
            'max_projects_per_workspace' => $request->max_projects_per_workspace,
            'features' => $request->features ?? [],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Plan updated successfully.');
    }

    /**
     * Delete a plan.
     */
    public function destroy(Plan $plan)
    {
        $plan->delete();
        return back()->with('success', 'Plan deleted successfully.');
    }
}
