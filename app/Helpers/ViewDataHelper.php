<?php

namespace App\Helpers;

use App\Models\User;

class ViewDataHelper
{
    /**
     * Add subscription status to Inertia shared props or view data.
     * Usage in controller: return inertia('view', ViewDataHelper::getSubscriptionData($user))
     * 
     * @param User $user
     * @return array
     */
    public static function getSubscriptionData(User $user): array
    {
        return [
            'subscriptionStatus' => BillingHelper::getExpiryStatus($user),
        ];
    }

    /**
     * Add combined data for layout: subscription + other data.
     * Merges subscription status with any other data you want to pass.
     * 
     * @param User $user
     * @param array $additionalData
     * @return array
     */
    public static function getLayoutData(User $user, array $additionalData = []): array
    {
        return array_merge(
            self::getSubscriptionData($user),
            $additionalData
        );
    }
}
