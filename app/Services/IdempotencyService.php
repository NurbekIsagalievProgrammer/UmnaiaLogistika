<?php

namespace App\Services;

use App\Models\BulkDispatch;
use Illuminate\Support\Facades\Cache;

class IdempotencyService
{
    private const TTL_SECONDS = 86400;

    public function findExisting(string $idempotencyKey): ?BulkDispatch
    {
        $cachedId = Cache::get($this->cacheKey($idempotencyKey));

        if ($cachedId === null) {
            return null;
        }

        return BulkDispatch::query()->find($cachedId);
    }

    public function remember(string $idempotencyKey, BulkDispatch $dispatch): void
    {
        Cache::put($this->cacheKey($idempotencyKey), $dispatch->id, self::TTL_SECONDS);
    }

    private function cacheKey(string $idempotencyKey): string
    {
        return 'idempotency:bulk:'.$idempotencyKey;
    }
}
