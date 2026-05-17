<?php

namespace App\Http\Requests;

use App\Enums\NotificationChannel;
use App\Enums\NotificationPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel' => ['required', Rule::enum(NotificationChannel::class)],
            'message' => ['required', 'string', 'max:5000'],
            'subscriber_ids' => ['required', 'array', 'min:1'],
            'subscriber_ids.*' => ['required', 'string', 'max:255'],
            'priority' => ['sometimes', Rule::enum(NotificationPriority::class)],
        ];
    }

    public function channel(): NotificationChannel
    {
        return $this->enum('channel', NotificationChannel::class);
    }

    public function priority(): NotificationPriority
    {
        return $this->enum('priority', NotificationPriority::class)
            ?? NotificationPriority::Normal;
    }
}
