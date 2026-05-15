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
        Schema::create('view_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('project_id');
            $table->string('view_type', 50); // list, board, timeline, calendar, files, dashboard
            $table->json('filters')->nullable();
            $table->json('sort')->nullable();
            $table->string('grouping', 50)->nullable();
            $table->json('column_widths')->nullable();
            $table->json('hidden_columns')->nullable();
            $table->json('collapsed_sections')->nullable();
            $table->json('card_fields')->nullable();
            $table->string('zoom_level', 50)->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            // Indexes
            $table->index('user_id');
            $table->index('project_id');
            $table->index('view_type');
            $table->index(['user_id', 'project_id']);
            $table->unique(['user_id', 'project_id', 'view_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('view_preferences');
    }
};
