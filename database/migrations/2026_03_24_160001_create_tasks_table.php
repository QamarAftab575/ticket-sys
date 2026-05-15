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
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->uuid('section_id')->nullable();
            $table->uuid('parent_task_id')->nullable();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->uuid('assignee_id')->nullable();
            $table->uuid('creator_id');
            $table->enum('status', ['to_do', 'in_progress', 'blocked', 'in_review', 'complete'])->default('to_do');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('visibility', ['everyone', 'private'])->default('everyone');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->uuid('completed_by')->nullable();
            $table->boolean('is_milestone')->default(false);
            $table->integer('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('set null');
            $table->foreign('parent_task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('assignee_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('completed_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('project_id');
            $table->index('section_id');
            $table->index('parent_task_id');
            $table->index('assignee_id');
            $table->index('status');
            $table->index('priority');
            $table->index('due_date');
            $table->index('completed_at');
            $table->index('is_milestone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
