<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $projects = DB::table('projects')->whereNull('deleted_at')->get();

        foreach ($projects as $project) {
            $hasSection = DB::table('sections')->where('project_id', $project->id)->exists();
            if ($hasSection) {
                continue;
            }

            foreach (['To do', 'Doing', 'Done'] as $position => $name) {
                DB::table('sections')->insert([
                    'id'         => Str::orderedUuid(),
                    'project_id' => $project->id,
                    'name'       => $name,
                    'position'   => $position,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Non-destructive — do not remove sections on rollback
    }
};
