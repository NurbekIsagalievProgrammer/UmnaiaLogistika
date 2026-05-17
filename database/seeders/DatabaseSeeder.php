<?php

namespace Database\Seeders;

use App\Models\Subscriber;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Subscriber::query()->firstOrCreate(
            ['external_id' => 'demo-sms'],
            ['phone' => '+77001234567', 'email' => null],
        );

        Subscriber::query()->firstOrCreate(
            ['external_id' => 'demo-email'],
            ['phone' => null, 'email' => 'user@example.com'],
        );
    }
}
