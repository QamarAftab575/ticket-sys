<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->uuid('workspace_id'); // workspace context — used to auto-join if not a member
            $table->string('email');
            // Project-level role — independent of workspace role
            $table->enum('role', ['project_admin', 'editor', 'commenter', 'viewer'])->default('commenter');
            $table->uuid('invited_by');
            $table->string('token')->unique();
            $table->enum('status', ['pending', 'accepted', 'expired'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('workspace_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('invited_by')->references('id')->on('users')->onDelete('restrict');

            $table->index('email');
            $table->index('project_id');
            $table->index('workspace_id');
            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_invitations');
    }
};
