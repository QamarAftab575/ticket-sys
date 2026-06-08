<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $notification;
    public int $unreadCount;

    public function __construct(Notification $notification, int $unreadCount)
    {
        $this->notification = $notification->load([
            'actor:id,name,email,avatar',
            'task:id,name,project_id',
        ])->toArray();

        $this->unreadCount = $unreadCount;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->notification['user_id']),
        ];
    }

    public function broadcastAs(): string
    {
        return 'NotificationCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'notification' => $this->notification,
            'unread_count' => $this->unreadCount,
        ];
    }
}
