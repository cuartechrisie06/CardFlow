<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['trade', 'wishlist', 'collection']),
            'title' => fake()->sentence(5),
            'body' => fake()->sentence(),
            'happened_at' => fake()->dateTimeBetween('-3 days', 'now'),
            'meta' => ['source' => fake()->word()],
        ];
    }
}
