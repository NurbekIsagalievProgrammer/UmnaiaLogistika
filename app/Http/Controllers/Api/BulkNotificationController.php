<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkNotificationRequest;
use App\Http\Resources\BulkDispatchResource;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationDispatchService;
use Illuminate\Http\JsonResponse;

class BulkNotificationController extends Controller
{
    public function __construct(
        private readonly NotificationDispatchService $dispatchService,
    ) {
    }

    public function store(BulkNotificationRequest $request): JsonResponse
    {
        $result = $this->dispatchService->dispatchBulk(
            channel: $request->channel(),
            message: $request->validated('message'),
            subscriberExternalIds: $request->validated('subscriber_ids'),
            priority: $request->priority(),
            idempotencyKey: $request->header('Idempotency-Key'),
        );

        return response()->json([
            'data' => [
                'bulk_dispatch' => new BulkDispatchResource($result['bulk_dispatch']),
                'notifications' => NotificationResource::collection($result['notifications']),
                'idempotency_replayed' => $result['replayed'],
            ],
        ], $result['replayed'] ? 200 : 202);
    }
}
