<?php

namespace App\Services;

use App\Enums\NotificationStatus;
use App\Exceptions\TransientProviderException;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationProcessor
{
    public function __construct(
        private readonly NotificationProviderResolver $providerResolver,
    ) {
    }

    public function process(string $notificationId): void
    {
        $notification = Notification::query()
            ->with('subscriber')
            ->findOrFail($notificationId);

        if ($notification->status->isTerminal()) {
            Log::info('Notification already processed, skipping', ['id' => $notificationId]);

            return;
        }

        if ($notification->status === NotificationStatus::Sent) {
            return;
        }

        $notification->increment('attempts');

        $provider = $this->providerResolver->resolve($notification->channel);
        $result = $provider->send($notification);

        if ($result->transientFailure) {
            throw new TransientProviderException($result->failureReason ?? 'Transient provider error');
        }

        if ($result->finalStatus === NotificationStatus::Dropped) {
            $notification->markDropped($result->failureReason ?? 'Delivery failed');

            return;
        }

        $notification->markSent($result->providerReference);

        if ($result->finalStatus === NotificationStatus::Delivered) {
            $notification->markDelivered($result->providerReference);
        }
    }
}
