<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function index()
    {
        // Fetch all active plans from database
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(function ($plan, $index) {
                return [
                    'id' => $plan->slug,
                    'name' => $plan->name,
                    'price' => $plan->price == 0 ? '$0' : '$' . number_format($plan->price, 2),
                    'period' => $plan->price == 0 ? 'forever' : 'per user/month',
                    'description' => $plan->description,
                    'features' => $plan->features,
                    'cta' => $plan->price == 0 ? 'Get Started' : 'Start Free Trial',
                    'highlighted' => $index === 1, // Highlight only the second plan (Professional/index 1)
                ];
            });

        $views = [
            [
                'id' => 'kanban',
                'title' => 'Kanban Boards',
                'description' => 'Visualize your workflow with intuitive drag-and-drop cards across customizable columns',
                'icon' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0v10'
            ],
            [
                'id' => 'list',
                'title' => 'List View',
                'description' => 'Traditional list layout with powerful filtering, sorting, and bulk operations',
                'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'
            ],
            [
                'id' => 'timeline',
                'title' => 'Timeline & Gantt',
                'description' => 'Plan projects visually with dependencies, milestones, and timeline overview',
                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
            ],
            [
                'id' => 'calendar',
                'title' => 'Calendar View',
                'description' => 'See all tasks and deadlines organized in monthly, weekly, or daily calendar format',
                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
            ]
        ];

        $features = [
            [
                'id' => 1,
                'title' => 'Task Management',
                'description' => 'Create, assign, and track tasks with subtasks, dependencies, custom fields, and priority levels.',
                'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'
            ],
            [
                'id' => 2,
                'title' => 'Team Collaboration',
                'description' => 'Real-time updates, @mentions, comments, file attachments, and activity tracking for seamless teamwork.',
                'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'
            ],
            [
                'id' => 3,
                'title' => 'Project Templates',
                'description' => 'Start faster with pre-built templates for marketing campaigns, sprints, product launches, and more.',
                'icon' => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'
            ],
            [
                'id' => 4,
                'title' => 'Time Tracking',
                'description' => 'Log time spent on tasks, generate reports, and analyze team productivity with built-in time tracking.',
                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
            ],
            [
                'id' => 5,
                'title' => 'Custom Workflows',
                'description' => 'Design workflows that match your process with custom statuses, automation rules, and approval flows.',
                'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'
            ],
            [
                'id' => 6,
                'title' => 'Advanced Reporting',
                'description' => 'Gain insights with burndown charts, velocity tracking, workload distribution, and custom reports.',
                'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
            ]
        ];

        $useCases = [
            [
                'id' => 1,
                'title' => 'Software Development',
                'description' => 'Manage sprints, track bugs, review code, and ship releases on schedule',
                'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
                'benefits' => ['Sprint planning', 'Bug tracking', 'Release management', 'Code review workflows']
            ],
            [
                'id' => 2,
                'title' => 'Marketing Teams',
                'description' => 'Plan campaigns, manage content calendars, and collaborate on creative projects',
                'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
                'benefits' => ['Campaign planning', 'Content calendar', 'Asset management', 'Approval workflows']
            ],
            [
                'id' => 3,
                'title' => 'Operations',
                'description' => 'Streamline processes, track incidents, manage SOPs, and coordinate teams',
                'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                'benefits' => ['Incident management', 'SOP tracking', 'Team coordination', 'Process automation']
            ],
            [
                'id' => 4,
                'title' => 'Product Management',
                'description' => 'Prioritize features, gather feedback, create roadmaps, and align stakeholders',
                'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                'benefits' => ['Feature prioritization', 'Roadmap planning', 'Feedback tracking', 'Stakeholder alignment']
            ]
        ];

        $appName = config('app.name');
        
        $testimonials = [
            [
                'id' => 1,
                'name' => 'Jennifer Martinez',
                'role' => 'Engineering Manager',
                'company' => 'TechFlow Solutions',
                'avatar' => 'JM',
                'content' => "{$appName} transformed how our engineering team works. The timeline view helps us visualize dependencies and the automation saves hours every week. Our sprint velocity increased by 35%.",
                'rating' => 5
            ],
            [
                'id' => 2,
                'name' => 'David Park',
                'role' => 'Marketing Director',
                'company' => 'GrowthCo',
                'avatar' => 'DP',
                'content' => "We manage 15+ campaigns simultaneously and {$appName} keeps everything organized. The custom fields and templates are game-changers. Our team collaboration has never been better.",
                'rating' => 5
            ],
            [
                'id' => 3,
                'name' => 'Rachel Stevens',
                'role' => 'Operations Lead',
                'company' => 'ServicePro Inc',
                'avatar' => 'RS',
                'content' => "After trying Asana, Jira, and others, {$appName} is the perfect balance of power and simplicity. Our response time improved by 60% and our team actually enjoys using it.",
                'rating' => 5
            ]
        ];

        $stats = [
            ['label' => 'Active Teams', 'value' => '50,000+'],
            ['label' => 'Tasks Completed', 'value' => '10M+'],
            ['label' => 'Time Saved Weekly', 'value' => '500K hrs'],
            ['label' => 'Customer Satisfaction', 'value' => '4.9/5']
        ];

        return Inertia::render('Landing', [
            'pricingPlans' => $plans,
            'views' => $views,
            'features' => $features,
            'useCases' => $useCases,
            'testimonials' => $testimonials,
            'stats' => $stats,
        ]);
    }
}

