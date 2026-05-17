<?php

namespace Tests\Integration;

use App\Enums\NotificationStatus;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class NotificationDeliveryChainTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['queue.default' => 'rabbitmq']);

        foreach (['notifications.critical', 'notifications.default'] as $queue) {
            try {
                Artisan::call('rabbitmq:queue-purge', [
                    'queue' => $queue,
                    'connection' => 'rabbitmq',
                ]);
            } catch (\Throwable) {
                // queue may not exist yet
            }
        }
    }

    public function test_message_flows_through_rabbitmq_to_delivered_status(): void
    {
        $subscriber = Subscriber::factory()->create([
            'external_id' => 'integration-user',
            'phone' => '+77009998877',
        ]);

        $response = $this->postJson('/api/v1/notifications/bulk', [
            'channel' => 'sms',
            'message' => 'Интеграционный тест',
            'subscriber_ids' => [$subscriber->external_id],
            'priority' => 'critical',
        ]);

        $response->assertAccepted();
        $notificationId = $response->json('data.notifications.0.id');

        Artisan::call('rabbitmq:consume', [
            'connection' => 'rabbitmq',
            '--queue' => 'notifications.critical',
            '--once' => true,
            '--tries' => 3,
        ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notificationId,
            'status' => NotificationStatus::Delivered->value,
        ]);
    }
}
