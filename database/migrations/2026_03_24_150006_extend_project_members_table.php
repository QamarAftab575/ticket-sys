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
        Schema::table('project_members', function (Blueprint $table) {
            // Modify role enum to include all project roles
            $table->enum('role', ['project_admin', 'editor', 'commenter', 'viewer'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_members', function (Blueprint $table) {
            // Revert role enum to original values
            $table->enum('role', ['project_admin', 'editor', 'commenter', 'viewer'])->default('commenter')->change();
        });
    }
};
