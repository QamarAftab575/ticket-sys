<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Section;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OnboardingDataService
{
    public function __construct(private ProjectService $projectService) {}

    /**
     * Seed a new user's workspace with a starter project, sections, and tasks.
     * Called once after workspace creation during onboarding.
     */
    public function seedWorkspace(Organization $organization, User $user): void
    {
        DB::transaction(function () use ($organization, $user) {
            // Create the starter project (no default sections — we add them manually below)
            $project = $this->projectService->createProject([
                'organization_id' => $organization->id,
                'name'            => 'My first Project',
                'manager_id'      => $user->id,
                'owner_id'        => $user->id,
                'status'          => 'on_track',
                'visibility'      => 'public_to_team',
                'privacy'         => 'public_to_team',
            ], $user);

            // Create the three starter sections
            $sections = ['To Do', 'Doing', 'Done'];
            $createdSections = [];

            foreach ($sections as $position => $name) {
                $createdSections[$name] = Section::create([
                    'project_id' => $project->id,
                    'name'       => $name,
                    'position'   => $position,
                ]);
            }

            // Create two default tasks inside "To Do"
            $todoSection = $createdSections['To Do'];
            $defaultTasks = ['My first task', 'My second task'];

            foreach ($defaultTasks as $position => $taskName) {
                Task::create([
                    'project_id'  => $project->id,
                    'section_id'  => $todoSection->id,
                    'name'        => $taskName,
                    'creator_id'  => $user->id,
                    'assignee_id' => $user->id,
                    'status'      => 'to_do',
                    'priority'    => 'medium',
                    'visibility'  => 'everyone',
                    'position'    => $position,
                ]);
            }
        });
    }
}
