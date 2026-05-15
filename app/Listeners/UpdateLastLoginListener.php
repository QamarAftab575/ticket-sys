<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Services\AuthService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateLastLoginListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(
        private AuthService $authService
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        // Update last_login_at timestamp
        $event->user->update([
            'last_login_at' => $event->timestamp,
        ]);

        // Reset failed login attempts counter
        $this->authService->resetFailedAttempts($event->user->email);
    }
}
