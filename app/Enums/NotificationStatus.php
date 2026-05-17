<?php

namespace App\Enums;

enum NotificationStatus: string
{
    case Queued = 'queued';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Dropped = 'dropped';

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Delivered, self::Dropped => true,
            default => false,
        };
    }
}
