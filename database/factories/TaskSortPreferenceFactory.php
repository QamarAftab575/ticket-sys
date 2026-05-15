<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\TaskSortPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaskSortPreference>
 */
class TaskSortPreferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'project_id' => Project::factory(),
            'sort_criteria' => [
                [
                    'field' => 'due_date',
                    'direction' => 'asc',
                ],
            ],
        ];
    }
}
