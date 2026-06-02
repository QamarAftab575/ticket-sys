<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration populates my_tasks_section_id and my_tasks_position
     * for existing tasks that are assigned to users.
     */
    public function up(): void
    {
        // Get all tasks that are assigned to users
        $tasks = DB::table('tasks')
            ->whereNotNull('assignee_id')
            ->orderBy('assignee_id')
            ->orderBy('created_at')
            ->get();

        foreach ($tasks as $task) {
            // Get or create the user's "Recently Assigned" section
            $recentlyAssignedSection = DB::table('sections')
                ->where('user_id', $task->assignee_id)
                ->where('is_my_tasks', true)
                ->where('name', 'Recently Assigned')
                ->first();

            if (!$recentlyAssignedSection) {
                // If user doesn't have My Tasks sections yet, get their first section
                $recentlyAssignedSection = DB::table('sections')
                    ->where('user_id', $task->assignee_id)
                    ->where('is_my_tasks', true)
                    ->orderBy('position')
                    ->first();
            }

            if ($recentlyAssignedSection) {
                // Get the current max position in this section for this user (zero-based indexing)
                $maxPosition = DB::table('tasks')
                    ->where('my_tasks_section_id', $recentlyAssignedSection->id)
                    ->where('assignee_id', $task->assignee_id)
                    ->max('my_tasks_position');

                $nextPosition = $maxPosition !== null ? $maxPosition + 1 : 0;

                // Update the task with My Tasks section and position
                DB::table('tasks')
                    ->where('id', $task->id)
                    ->update([
                        'my_tasks_section_id' => $recentlyAssignedSection->id,
                        'my_tasks_position' => $nextPosition,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set my_tasks_section_id and my_tasks_position to null for all tasks
        DB::table('tasks')->update([
            'my_tasks_section_id' => null,
            'my_tasks_position' => null,
        ]);
    }
};
