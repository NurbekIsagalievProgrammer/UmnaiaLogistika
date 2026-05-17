<?php

namespace App\Contracts;

use App\DTO\ProviderSendResult;
use App\Models\Notification;

interface NotificationProviderInterface
{
    public function send(Notification $notification): ProviderSendResult;
}
