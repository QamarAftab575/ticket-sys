<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
       
        Plan::query()->delete();

      $plans = [
    [
        'name' => 'Starter',
        'slug' => 'starter',
        'description' => 'Perfect for getting started. Get a basic project management features.',
        'price' => 5.00,
        'billing_cycle' => 'monthly',
        'features' => [
            '1 Workspace',
            'Up to 5 Members',
            'Up to 3 Projects',
            'Task Management',
            'Kanban Board',
            'Email Notifications',
        ],
        'max_workspaces' => 1,
        'max_members_per_workspace' => 5,
        'max_projects_per_workspace' => 3,
        'is_active' => true,
        'sort_order' => 1,
    ],

    [
        'name' => 'Professional',
        'slug' => 'professional',
        'description' => 'Ideal for growing teams. Scale your workflow with advanced features and expanded limits.',
        'price' => 12.00,
        'billing_cycle' => 'monthly',
        'features' => [
            '2 Workspaces',
            'Up to 25 Members',
            'Up to 20 Projects',
            'Task Management',
            'Kanban Board',
            'Team Invitations',
            'Reporting Dashboard',
            'Priority Support',
        ],
        'max_workspaces' => 2,
        'max_members_per_workspace' => 25,
        'max_projects_per_workspace' => 20,
        'is_active' => true,
        'sort_order' => 2,
    ],

    [
        'name' => 'Business',
        'slug' => 'business',
        'description' => 'For enterprise teams. Unlimited workspaces, advanced analytics, and dedicated support.',
        'price' => 29.00,
        'billing_cycle' => 'monthly',
        'features' => [
            '10 Workspaces',
            'Up to 100 Members',
            'Up to 100 Projects',
            'Task Management',
            'Kanban Board',
            'Team Invitations',
            'Advanced Reporting',
            'Custom Support',
            'Priority Feature Requests',
        ],
        'max_workspaces' => 10,
        'max_members_per_workspace' => 100,
        'max_projects_per_workspace' => 100,
        'is_active' => true,
        'sort_order' => 3,
    ],
];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }

    }
}
