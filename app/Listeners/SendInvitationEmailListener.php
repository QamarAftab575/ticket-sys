<?php

namespace App\Listeners;

use App\Events\InvitationCreated;
use App\Jobs\SendInvitationEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendInvitationEmailListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(InvitationCreated $event): void
    {
        // Dispatch SendInvitationEmail job
        SendInvitationEmail::dispatch($event->invitation, $event->user);
    }
}
