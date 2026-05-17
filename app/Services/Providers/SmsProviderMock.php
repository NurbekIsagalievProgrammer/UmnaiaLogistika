<?php

namespace App\Services\Providers;

use App\Enums\NotificationChannel;
use App\Models\Notification;

class SmsProviderMock extends AbstractProviderMock
{
    protected function supports(NotificationChannel $channel): bool
    {
        return $channel === NotificationChannel::Sms;
    }

    protected function hasDestination(Notification $notification): bool
    {
        return filled($notification->subscriber?->phone);
    }

    protected function referencePrefix(): string
    {
        return 'sms';
    }
}
