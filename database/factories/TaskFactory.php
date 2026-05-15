<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Section;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'section_id' => null,
            'parent_task_id' => null,
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'assignee_id' => null,
            'creator_id' => User::factory(),
            'status' => 'todo',
            'priority' => 'medium',
            'visibility' => 'normal',
            'start_date' => null,
            'due_date' => null,
            'completed_at' => null,
            'completed_by' => null,
            'is_milestone' => false,
            'position' => 0,
        ];
    }
}
