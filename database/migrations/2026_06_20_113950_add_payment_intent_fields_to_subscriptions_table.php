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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Add payment intent tracking
            if (!Schema::hasColumn('subscriptions', 'payment_intent_id')) {
                $table->string('payment_intent_id')->nullable()->after('stripe_subscription_id');
            }
            
            // Add payment token reference
            if (!Schema::hasColumn('subscriptions', 'payment_token_id')) {
                $table->uuid('payment_token_id')->nullable()->after('payment_intent_id');
            }

            // Add payment metadata for verification
            if (!Schema::hasColumn('subscriptions', 'payment_metadata')) {
                $table->json('payment_metadata')->nullable()->after('payment_token_id');
            }

            // Add payment attempt tracking
            if (!Schema::hasColumn('subscriptions', 'payment_attempts')) {
                $table->integer('payment_attempts')->default(1)->after('payment_metadata');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'payment_intent_id')) {
                $table->dropColumn('payment_intent_id');
            }
            if (Schema::hasColumn('subscriptions', 'payment_token_id')) {
                $table->dropColumn('payment_token_id');
            }
            if (Schema::hasColumn('subscriptions', 'payment_metadata')) {
                $table->dropColumn('payment_metadata');
            }
            if (Schema::hasColumn('subscriptions', 'payment_attempts')) {
                $table->dropColumn('payment_attempts');
            }
        });
    }
};
