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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., "English", "Arabic"
            $table->string('code')->unique(); // e.g., "en", "ar", "ur"
            $table->enum('direction', ['ltr', 'rtl'])->default('ltr'); // Left-to-Right or Right-to-Left
            $table->boolean('is_active')->default(true); // Active/Inactive
            $table->boolean('is_default')->default(false); // Default language (English)
            $table->timestamps();

            $table->index('is_active');
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
