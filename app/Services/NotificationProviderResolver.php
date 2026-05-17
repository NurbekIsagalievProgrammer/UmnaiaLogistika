<?php

namespace App\Services;

use App\Contracts\NotificationProviderInterface;
use App\Enums\NotificationChannel;
use App\Services\Providers\EmailProviderMock;
use App\Services\Providers\SmsProviderMock;
use InvalidArgumentException;

class NotificationProviderResolver
{
    public function __construct(
        private readonly SmsProviderMock $smsProvider,
        private readonly EmailProviderMock $emailProvider,
    ) {
    }

    public function resolve(NotificationChannel $channel): NotificationProviderInterface
    {
        return match ($channel) {
            NotificationChannel::Sms => $this->smsProvider,
            NotificationChannel::Email => $this->emailProvider,
            default => throw new InvalidArgumentException("Unsupported channel: {$channel->value}"),
        };
    }
}
