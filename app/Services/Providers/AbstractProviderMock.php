<?php

namespace App\Services\Providers;

use App\Contracts\NotificationProviderInterface;
use App\DTO\ProviderSendResult;
use App\Enums\NotificationChannel;
use App\Models\Notification;

abstract class AbstractProviderMock implements NotificationProviderInterface
{
    public function send(Notification $notification): ProviderSendResult
    {
        if (! $this->supports($notification->channel)) {
            return ProviderSendResult::dropped('Unsupported channel for provider');
        }

        if (! $this->hasDestination($notification)) {
            return ProviderSendResult::dropped('Subscriber has no destination for this channel');
        }

        $message = $notification->message;

        if (str_contains($message, '[[TRANSIENT_FAILURE]]')) {
            return ProviderSendResult::transient('Gateway temporarily unavailable');
        }

        if (str_contains($message, '[[PERMANENT_FAILURE]]')) {
            return ProviderSendResult::dropped('Invalid destination or rejected by gateway');
        }

        return ProviderSendResult::delivered($this->referencePrefix().'-'.fake()->uuid());
    }

    abstract protected function supports(NotificationChannel $channel): bool;

    abstract protected function hasDestination(Notification $notification): bool;

    abstract protected function referencePrefix(): string;
}
