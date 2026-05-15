<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For fresh installations, just alter the column
        // Since you mentioned you'll do fresh seed, this is safe
        
        DB::statement("ALTER TABLE project_members MODIFY COLUMN access_type ENUM('workspace_member', 'direct_invite') DEFAULT 'workspace_member'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE project_members MODIFY COLUMN access_type ENUM('team_member', 'direct_invite', 'task_assignee') DEFAULT 'team_member'");
    }
};
