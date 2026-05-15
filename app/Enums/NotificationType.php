<?php

namespace App\Enums;

enum NotificationType: string
{
    case MENTION_IN_DESCRIPTION = 'mention_in_description';
    case MENTION_IN_COMMENT = 'mention_in_comment';
    case TASK_ASSIGNED = 'task_assigned';

    /**
     * Get human-readable label for the notification type.
     */
    public function label(): string
    {
        return match ($this) {
            self::MENTION_IN_DESCRIPTION => 'Mentioned you in task description',
            self::MENTION_IN_COMMENT => 'Mentioned you in a comment',
            self::TASK_ASSIGNED => 'Assigned you to a task',
        };
    }

    /**
     * Get icon class for the notification type.
     */
    public function icon(): string
    {
        return match ($this) {
            self::MENTION_IN_DESCRIPTION => 'at-symbol',
            self::MENTION_IN_COMMENT => 'chat-bubble-left',
            self::TASK_ASSIGNED => 'clipboard-document-check',
        };
    }
}
