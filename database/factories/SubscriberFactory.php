<?php

namespace Database\Factories;

use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Subscriber>
 */
class SubscriberFactory extends Factory
{
    protected $model = Subscriber::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'external_id' => 'sub-'.fake()->unique()->numerify('#####'),
            'phone' => '+7700'.fake()->numerify('#######'),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
