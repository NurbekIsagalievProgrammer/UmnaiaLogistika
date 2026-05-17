<?php

namespace App\DTO;

use App\Enums\NotificationStatus;

final readonly class ProviderSendResult
{
    public function __construct(
        public bool $success,
        public NotificationStatus $finalStatus,
        public ?string $providerReference = null,
        public ?string $failureReason = null,
        public bool $transientFailure = false,
    ) {
    }

    public static function delivered(string $reference): self
    {
        return new self(true, NotificationStatus::Delivered, $reference);
    }

    public static function dropped(string $reason): self
    {
        return new self(false, NotificationStatus::Dropped, failureReason: $reason);
    }

    public static function transient(string $reason): self
    {
        return new self(false, NotificationStatus::Queued, failureReason: $reason, transientFailure: true);
    }
}
