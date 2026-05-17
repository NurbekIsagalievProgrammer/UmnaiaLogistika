<?php

namespace App\Services\Providers;

use App\Enums\NotificationChannel;
use App\Models\Notification;

class EmailProviderMock extends AbstractProviderMock
{
    protected function supports(NotificationChannel $channel): bool
    {
        return $channel === NotificationChannel::Email;
    }

    protected function hasDestination(Notification $notification): bool
    {
        return filled($notification->subscriber?->email);
    }

    protected function referencePrefix(): string
    {
        return 'email';
    }
}
