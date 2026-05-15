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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignUuid('user_id')->comment('Receiver of the notification')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('actor_user_id')->nullable()->comment('User who triggered the notification')->constrained('users')->onDelete('set null');
            $table->string('type'); // mention_in_comment, mention_in_description, task_assigned
            $table->string('title');
            $table->text('message')->nullable();
            $table->foreignUuid('task_id')->nullable()->constrained('tasks')->onDelete('cascade');
            $table->foreignUuid('comment_id')->nullable()->constrained('comments')->onDelete('cascade');
            $table->json('metadata')->nullable()->comment('Additional context data');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'organization_id', 'is_read', 'created_at']);
            $table->index(['user_id', 'is_read']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
