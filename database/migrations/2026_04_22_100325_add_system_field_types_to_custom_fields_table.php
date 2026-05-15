<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ENUM = "ENUM(
        'text','number','date',
        'single_select','multi_select','people',
        'dropdown','currency',
        'system_assignee','system_blocked_by','system_blocking',
        'system_completed_on','system_last_modified_on',
        'system_created_on','system_created_by','system_collaborators'
    ) NOT NULL";

    public function up(): void
    {
        DB::statement('ALTER TABLE custom_fields MODIFY COLUMN field_type ' . self::ENUM);
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE custom_fields MODIFY COLUMN field_type ENUM(
            'text','number','date','single_select','multi_select',
            'people','dropdown','currency'
        ) NOT NULL");
    }
};
