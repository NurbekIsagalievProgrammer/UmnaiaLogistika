<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use App\Enums\NotificationPriority;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BulkDispatch extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'idempotency_key',
        'channel',
        'message',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
            'priority' => NotificationPriority::class,
        ];
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
