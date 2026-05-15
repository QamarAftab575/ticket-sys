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
        Schema::table('projects', function (Blueprint $table) {
            // Add new columns for advanced features
            $table->string('color', 7)->nullable()->after('visibility'); // Hex color code
            $table->string('icon', 50)->nullable()->after('color'); // Icon identifier
            $table->uuid('owner_id')->nullable()->after('icon'); // Project owner
            $table->enum('privacy', ['public_to_team', 'private', 'specific_members'])->default('public_to_team')->after('owner_id');
            $table->timestamp('archived_at')->nullable()->after('privacy');

            // Modify status enum to include new statuses
            $table->enum('status', ['on_track', 'at_risk', 'off_track', 'on_hold', 'complete', 'archived'])->change();

            // Add foreign key for owner_id
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('set null');

            // Add indexes
            $table->index('owner_id');
            $table->index('privacy');
            $table->index('archived_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['owner_id']);

            // Drop indexes
            $table->dropIndex(['owner_id']);
            $table->dropIndex(['privacy']);
            $table->dropIndex(['archived_at']);

            // Drop columns
            $table->dropColumn(['color', 'icon', 'owner_id', 'privacy', 'archived_at']);

            // Revert status enum
            $table->enum('status', ['on_track', 'at_risk', 'off_track', 'archived'])->change();
        });
    }
};
