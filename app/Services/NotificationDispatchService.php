<?php

namespace App\Services;

use App\Enums\NotificationChannel;
use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Models\BulkDispatch;
use App\Models\Notification;
use App\Models\Subscriber;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationDispatchService
{
    public function __construct(
        private readonly IdempotencyService $idempotencyService,
    ) {
    }

    /**
     * @param  list<string>  $subscriberExternalIds
     * @return array{bulk_dispatch: BulkDispatch, notifications: Collection<int, Notification>, replayed: bool}
     */
    public function dispatchBulk(
        NotificationChannel $channel,
        string $message,
        array $subscriberExternalIds,
        NotificationPriority $priority,
        ?string $idempotencyKey = null,
    ): array {
        if ($idempotencyKey !== null) {
            $existing = $this->idempotencyService->findExisting($idempotencyKey);
            if ($existing !== null) {
                return [
                    'bulk_dispatch' => $existing,
                    'notifications' => $existing->notifications()->with('subscriber')->get(),
                    'replayed' => true,
                ];
            }
        }

        return DB::transaction(function () use ($channel, $message, $subscriberExternalIds, $priority, $idempotencyKey) {
            $bulkDispatch = BulkDispatch::query()->create([
                'id' => (string) Str::uuid(),
                'idempotency_key' => $idempotencyKey,
                'channel' => $channel,
                'message' => $message,
                'priority' => $priority,
            ]);

            $subscribers = Subscriber::query()
                ->whereIn('external_id', $subscriberExternalIds)
                ->get()
                ->keyBy('external_id');

            $notifications = collect();

            foreach ($subscriberExternalIds as $externalId) {
                $subscriber = $subscribers->get($externalId);

                if ($subscriber === null) {
                    continue;
                }

                $notification = Notification::query()->create([
                    'id' => (string) Str::uuid(),
                    'bulk_dispatch_id' => $bulkDispatch->id,
                    'subscriber_id' => $subscriber->id,
                    'channel' => $channel,
                    'priority' => $priority,
                    'message' => $message,
                    'status' => NotificationStatus::Queued,
                ]);

                SendNotificationJob::dispatch($notification->id)
                    ->onConnection(config('queue.default'))
                    ->onQueue($priority->queueName());

                $notifications->push($notification->load('subscriber'));
            }

            if ($idempotencyKey !== null) {
                $this->idempotencyService->remember($idempotencyKey, $bulkDispatch);
            }

            return [
                'bulk_dispatch' => $bulkDispatch,
                'notifications' => $notifications,
                'replayed' => false,
            ];
        });
    }
}
