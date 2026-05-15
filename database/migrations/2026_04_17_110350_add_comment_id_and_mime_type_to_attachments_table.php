<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->uuid('comment_id')->nullable()->after('task_id');
            $table->string('mime_type', 100)->nullable()->after('file_size');

            $table->foreign('comment_id')->references('id')->on('comments')->onDelete('cascade');
            $table->index('comment_id');
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropForeign(['comment_id']);
            $table->dropIndex(['comment_id']);
            $table->dropColumn(['comment_id', 'mime_type']);
        });
    }
};
