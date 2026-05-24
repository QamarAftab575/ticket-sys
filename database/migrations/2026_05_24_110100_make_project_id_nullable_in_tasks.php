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
            // Drop the foreign key first
            $table->dropForeign(['project_id']);
            
            // Make project_id nullable
            $table->uuid('project_id')->nullable()->change();
            
            // Re-add the foreign key with onDelete cascade
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['project_id']);
            
            // Make project_id not nullable
            $table->uuid('project_id')->change();
            
            // Re-add the foreign key
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }
};
