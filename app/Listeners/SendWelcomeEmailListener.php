<?php

namespace App\Listeners;

use App\Events\InvitationAccepted;
use App\Events\UserRegistered;
use App\Mail\WelcomeEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmailListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $isNewRegistration = $event instanceof UserRegistered;

        Mail::send(new WelcomeEmail($event->user, isNewRegistration: $isNewRegistration));
    }
}
