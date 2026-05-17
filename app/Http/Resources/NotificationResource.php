<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Notification */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subscriber_id' => $this->subscriber?->external_id,
            'channel' => $this->channel->value,
            'priority' => $this->priority->value,
            'message' => $this->message,
            'status' => $this->status->value,
            'provider_reference' => $this->provider_reference,
            'failure_reason' => $this->failure_reason,
            'attempts' => $this->attempts,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'dropped_at' => $this->dropped_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
