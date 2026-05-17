<?php

namespace Tests\Feature;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkNotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_dispatch_creates_queued_notifications_and_processes_via_sync_queue(): void
    {
        $subscriber = Subscriber::factory()->create([
            'external_id' => 'user-1',
            'phone' => '+77001112233',
            'email' => null,
        ]);

        $response = $this->postJson('/api/v1/notifications/bulk', [
            'channel' => 'sms',
            'message' => 'Ваш код доступа: 1234',
            'subscriber_ids' => [$subscriber->external_id],
            'priority' => 'critical',
        ], [
            'Idempotency-Key' => 'bulk-001',
        ]);

        $response->assertAccepted()
            ->assertJsonPath('data.idempotency_replayed', false);

        $notificationId = $response->json('data.notifications.0.id');

        $this->assertDatabaseHas('notifications', [
            'id' => $notificationId,
            'status' => NotificationStatus::Delivered->value,
        ]);
    }

    public function test_idempotency_key_returns_same_bulk_without_duplicates(): void
    {
        $subscriber = Subscriber::factory()->create(['external_id' => 'user-2']);

        $payload = [
            'channel' => 'email',
            'message' => 'Маршрут изменён',
            'subscriber_ids' => [$subscriber->external_id],
            'priority' => 'normal',
        ];

        $first = $this->postJson('/api/v1/notifications/bulk', $payload, [
            'Idempotency-Key' => 'bulk-002',
        ])->assertAccepted();

        $second = $this->postJson('/api/v1/notifications/bulk', $payload, [
            'Idempotency-Key' => 'bulk-002',
        ])->assertOk()
            ->assertJsonPath('data.idempotency_replayed', true);

        $this->assertSame(
            $first->json('data.bulk_dispatch.id'),
            $second->json('data.bulk_dispatch.id'),
        );

        $this->assertSame(1, Notification::query()->count());
    }

    public function test_subscriber_notifications_endpoint_returns_history(): void
    {
        $subscriber = Subscriber::factory()->create(['external_id' => 'user-3']);

        $this->postJson('/api/v1/notifications/bulk', [
            'channel' => 'sms',
            'message' => 'Тест',
            'subscriber_ids' => [$subscriber->external_id],
            'priority' => NotificationPriority::Normal->value,
        ])->assertAccepted();

        $this->getJson('/api/v1/subscribers/user-3/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'data.notifications')
            ->assertJsonPath('data.notifications.0.status', NotificationStatus::Delivered->value);
    }

    public function test_permanent_failure_marks_notification_as_dropped(): void
    {
        $subscriber = Subscriber::factory()->create(['external_id' => 'user-4']);

        $this->postJson('/api/v1/notifications/bulk', [
            'channel' => 'sms',
            'message' => '[[PERMANENT_FAILURE]]',
            'subscriber_ids' => [$subscriber->external_id],
        ])->assertAccepted();

        $this->assertDatabaseHas('notifications', [
            'subscriber_id' => $subscriber->id,
            'status' => NotificationStatus::Dropped->value,
        ]);
    }
}
