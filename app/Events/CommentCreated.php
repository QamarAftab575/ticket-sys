<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// reverb functionality disabled
// class CommentCreated implements ShouldBroadcastNow
class CommentCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $comment;
    public string $taskId;
    public ?string $projectId;

    public function __construct(Comment $comment)
    {
        $comment->load('user:id,name,email,avatar');

        $this->taskId   = $comment->task_id;
        $this->projectId = $comment->task?->project_id;
        $this->comment  = $comment->toArray();
    }

    public function broadcastOn(): array
    {
        $channels = [];

        if ($this->projectId) {
            $channels[] = new PrivateChannel('project.' . $this->projectId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'CommentCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'comment'    => $this->comment,
            'task_id'    => $this->taskId,
            'project_id' => $this->projectId,
        ];
    }
}
