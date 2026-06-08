<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskMoved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $task;

    public function __construct(Task $task)
    {
        $this->task = $task->load([
            'assignee:id,name,email,avatar',
            'section:id,name',
            'tags:id,name,color',
        ])->toArray();
    }

    public function broadcastOn(): array
    {
        $channels = [];

        if (!empty($this->task['project_id'])) {
            $channels[] = new PrivateChannel('project.' . $this->task['project_id']);
        }

        if (!empty($this->task['assignee_id'])) {
            $channels[] = new PrivateChannel('user.' . $this->task['assignee_id']);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'TaskMoved';
    }

    public function broadcastWith(): array
    {
        return ['task' => $this->task];
    }
}
