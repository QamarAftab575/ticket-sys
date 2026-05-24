<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            // Drop the NOT NULL FK constraint on project_id so My Tasks sections can have project_id = null
            $table->dropForeign(['project_id']);
            $table->uuid('project_id')->nullable()->change();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            // Add user_id for My Tasks personal sections
            $table->uuid('user_id')->nullable()->after('project_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');

            // Mark which sections belong to My Tasks context
            $table->boolean('is_my_tasks')->default(false)->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn(['user_id', 'is_my_tasks']);

            $table->dropForeign(['project_id']);
            $table->uuid('project_id')->nullable(false)->change();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }
};
