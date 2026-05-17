<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'bulk_dispatch_id',
        'subscriber_id',
        'channel',
        'priority',
        'message',
        'status',
        'provider_reference',
        'failure_reason',
        'attempts',
        'sent_at',
        'delivered_at',
        'dropped_at',
    ];

    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
            'priority' => NotificationPriority::class,
            'status' => NotificationStatus::class,
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'dropped_at' => 'datetime',
        ];
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function bulkDispatch(): BelongsTo
    {
        return $this->belongsTo(BulkDispatch::class);
    }

    public function markSent(?string $providerReference = null): void
    {
        $this->update([
            'status' => NotificationStatus::Sent,
            'provider_reference' => $providerReference,
            'sent_at' => now(),
        ]);
    }

    public function markDelivered(?string $providerReference = null): void
    {
        $this->update([
            'status' => NotificationStatus::Delivered,
            'provider_reference' => $providerReference ?? $this->provider_reference,
            'delivered_at' => now(),
        ]);
    }

    public function markDropped(string $reason): void
    {
        $this->update([
            'status' => NotificationStatus::Dropped,
            'failure_reason' => $reason,
            'dropped_at' => now(),
        ]);
    }
}
