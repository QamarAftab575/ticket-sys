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
        Schema::table('custom_fields', function (Blueprint $table) {
            // Add scope field to determine where the custom field applies
            $table->enum('field_scope', ['project', 'personal', 'workspace'])->default('project')->after('is_active');
            
            // Add user_id for personal scoped fields (creator of the field)
            $table->uuid('user_id')->nullable()->after('project_id');
            
            // Add foreign key for user_id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Add index for efficient querying
            $table->index(['field_scope', 'project_id', 'user_id']);
        });

        // Make project_id nullable separately to avoid issues
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->uuid('project_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['field_scope', 'project_id', 'user_id']);
            $table->dropColumn(['field_scope', 'user_id']);
            
            // Restore project_id as non-nullable
            $table->uuid('project_id')->nullable(false)->change();
        });
    }
};
