<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add is_active column
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('is_global');
            $table->integer('position')->default(0)->after('is_active');
        });

        // Update field_type enum to include new types
        // MySQL: modify the enum column
        DB::statement("ALTER TABLE custom_fields MODIFY COLUMN field_type ENUM(
            'text','number','date','single_select','multi_select','people',
            'dropdown','currency'
        ) NOT NULL");
    }

    public function down(): void
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'position']);
        });

        DB::statement("ALTER TABLE custom_fields MODIFY COLUMN field_type ENUM(
            'text','number','date','dropdown','multi_select','currency'
        ) NOT NULL");
    }
};
