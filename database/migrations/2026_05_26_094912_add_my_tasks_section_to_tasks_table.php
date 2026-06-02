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
        Schema::table('tasks', function (Blueprint $table) {
            // Add My Tasks section tracking
            $table->uuid('my_tasks_section_id')->nullable()->after('section_id');
            $table->foreign('my_tasks_section_id')
                  ->references('id')
                  ->on('sections')
                  ->onDelete('set null');
            
            // Add My Tasks position tracking (independent from project position)
            $table->integer('my_tasks_position')->nullable()->after('position');
            
            // Add index for better query performance
            $table->index(['assignee_id', 'my_tasks_section_id', 'my_tasks_position'], 'idx_my_tasks_sorting');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['my_tasks_section_id']);
            $table->dropIndex('idx_my_tasks_sorting');
            $table->dropColumn(['my_tasks_section_id', 'my_tasks_position']);
        });
    }
};
