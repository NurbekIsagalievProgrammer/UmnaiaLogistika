<?php

namespace App\Http\Controllers\Api;

use App\Enums\NotificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriberNotificationController extends Controller
{
    public function index(Request $request, string $subscriberExternalId): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', Rule::enum(NotificationStatus::class)],
        ]);

        $subscriber = Subscriber::query()
            ->where('external_id', $subscriberExternalId)
            ->firstOrFail();

        $query = Notification::query()
            ->with('subscriber')
            ->where('subscriber_id', $subscriber->id)
            ->orderByDesc('created_at');

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        return response()->json([
            'data' => [
                'subscriber_id' => $subscriber->external_id,
                'notifications' => NotificationResource::collection($query->get()),
            ],
        ]);
    }
}
