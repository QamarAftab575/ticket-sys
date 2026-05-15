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
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->uuid('manager_id');
            $table->enum('status', ['on_track', 'at_risk', 'off_track', 'archived'])->default('on_track');
            $table->enum('visibility', ['public_to_team', 'private_to_members'])->default('public_to_team');
            $table->date('start_date')->nullable();
            $table->date('target_date')->nullable();
            $table->uuid('created_by');
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');

            // Indexes
            $table->index('organization_id');
            $table->index('manager_id');
            $table->index('status');
            $table->index('visibility');
            $table->index('created_by');
            $table->index(['organization_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
