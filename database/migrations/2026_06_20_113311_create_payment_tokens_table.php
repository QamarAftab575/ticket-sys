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
        Schema::create('payment_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('plan_id');
            $table->string('token', 64)->unique(); // Reduced from 255 to 64 for index length
            $table->string('stripe_session_id')->nullable(); // Stripe session ID
            $table->string('status')->default('pending'); // pending, used, expired
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3); // USD, EUR, etc
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->text('metadata')->nullable(); // JSON data
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');
            $table->index(['user_id', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_tokens');
    }
};
