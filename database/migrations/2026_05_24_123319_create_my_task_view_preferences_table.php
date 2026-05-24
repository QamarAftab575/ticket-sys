<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('my_task_view_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('view_type', 50)->default('list'); // list, board, calendar, files
            $table->json('sort')->nullable();
            $table->string('grouping', 50)->nullable();
            $table->json('section_order')->nullable(); // ordered array of section ids
            $table->json('collapsed_sections')->nullable();
            $table->json('filters')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'view_type']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('my_task_view_preferences');
    }
};
