<?php

namespace App\Providers;

use App\Events\InvitationAccepted;
use App\Events\InvitationCreated;
use App\Events\PasswordResetRequested;
use App\Events\UserRegistered;
use App\Listeners\SendInvitationEmailListener;
use App\Listeners\SendPasswordResetEmailListener;
use App\Listeners\SendWelcomeEmailListener;
use App\Listeners\UpdateLastLoginListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        UserRegistered::class => [
            UpdateLastLoginListener::class,
            SendWelcomeEmailListener::class,
        ],
        InvitationCreated::class => [
            SendInvitationEmailListener::class,
        ],
        InvitationAccepted::class => [
            SendWelcomeEmailListener::class,
        ],
        PasswordResetRequested::class => [
            SendPasswordResetEmailListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
