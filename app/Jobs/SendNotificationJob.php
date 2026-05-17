<?php

namespace App\Jobs;

use App\Services\NotificationProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [5, 15, 30];

    public function __construct(
        public readonly string $notificationId,
    ) {
    }

    public function handle(NotificationProcessor $processor): void
    {
        $processor->process($this->notificationId);
    }
}
