<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\TaskGroupPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaskGroupPreference>
 */
class TaskGroupPreferenceFactory extends Factory
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
            'group_by' => $this->faker->randomElement(['assignee', 'due_date', 'priority', 'status', 'section', 'project']),
            'collapsed_groups' => [],
        ];
    }
}
