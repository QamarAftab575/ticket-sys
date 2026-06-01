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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('utc_offset_minutes')
                  ->nullable()
                  ->after('timezone')
                  ->comment('UTC offset in minutes (e.g., 300 for UTC+5, -300 for UTC-5)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('utc_offset_minutes');
        });
    }
};
