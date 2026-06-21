<?php

namespace App\Console\Commands;

use App\Helpers\BillingHelper;
use App\Models\User;
use Illuminate\Console\Command;

class TestSubscriptionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:test {user_id}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Test subscription status for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User {$userId} not found");
            return 1;
        }

        $this->info("Testing subscription status for user: {$user->email}");
        $this->line('');

        // Show user dates
        $this->line('User Data:');
        $this->line("  Trial ends at:    {$user->trial_ends_at}");
        $this->line("  Active plan ID:   {$user->active_plan_id}");
        $this->line("  Stripe sub ends:  {$user->stripe_subscription_ends_at}");
        $this->line('');

        // Test getDaysRemaining
        $daysRemaining = BillingHelper::getDaysRemaining($user);
        $this->line("getDaysRemaining(): {$daysRemaining}");
        $this->line('');

        // Test getExpiryStatus
        $status = BillingHelper::getExpiryStatus($user);
        $this->table(
            ['Key', 'Value'],
            [
                ['status', $status['status']],
                ['days_remaining', $status['days_remaining']],
                ['days_in_grace_period', $status['days_in_grace_period']],
                ['expires_at', $status['expires_at']],
                ['grace_period_ends_at', $status['grace_period_ends_at']],
                ['grace_period_days', $status['grace_period_days']],
                ['message', $status['message'] ?? 'N/A'],
            ]
        );
        $this->line('');

        // Test helper methods
        $this->line('Helper Methods:');
        $this->line("  isAccountSuspended():   " . (BillingHelper::isAccountSuspended($user) ? 'YES' : 'NO'));
        $this->line("  isInGracePeriod():      " . (BillingHelper::isInGracePeriod($user) ? 'YES' : 'NO'));
        $this->line("  needsRenewalWarning():  " . (BillingHelper::needsRenewalWarning($user) ? 'YES' : 'NO'));
        $this->line("  isOnTrial():            " . (BillingHelper::isOnTrial($user) ? 'YES' : 'NO'));
        $this->line("  hasActivePlan():        " . (BillingHelper::hasActivePlan($user) ? 'YES' : 'NO'));

        return 0;
    }
}
