<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->uuid('user_id');
            $table->enum('role', ['project_admin', 'editor', 'commenter', 'viewer'])->default('commenter');
            $table->enum('access_type', ['team_member', 'direct_invite', 'task_assignee'])->default('team_member');
            $table->uuid('invited_by')->nullable();
            $table->timestamp('assigned_at');
            $table->uuid('assigned_by');
            $table->timestamps();

            // Foreign keys
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('invited_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('project_id');
            $table->index('user_id');
            $table->index('role');

            // Unique constraint with shorter name
            $table->unique(['project_id', 'user_id'], 'unique_project_member');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_members');
    }
};
