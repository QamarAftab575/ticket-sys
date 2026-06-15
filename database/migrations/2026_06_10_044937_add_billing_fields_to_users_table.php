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
            $table->uuid('active_plan_id')->nullable()->after('is_super_admin');
            $table->timestamp('trial_ends_at')->nullable()->after('active_plan_id');
            $table->timestamp('plan_starts_at')->nullable()->after('trial_ends_at');
            $table->string('stripe_customer_id')->nullable()->after('plan_starts_at');
            $table->string('stripe_subscription_id')->nullable()->after('stripe_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['active_plan_id', 'trial_ends_at', 'plan_starts_at', 'stripe_customer_id', 'stripe_subscription_id']);
        });
    }
};
