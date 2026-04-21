<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trade>
 */
class TradeFactory extends Factory
{
    protected $model = Trade::class;

    public function definition(): array
    {
        $status = fake()->randomElement(['completed', 'pending', 'new_offer', 'cancelled', 'in_progress']);

        return [
            'user_id' => User::factory(),
            'card_id' => Card::factory(),
            'partner_name' => fake()->name(),
            'partner_handle' => '@'.fake()->unique()->userName(),
            'status' => $status,
            'offered_value' => fake()->randomFloat(2, 100, 1800),
            'replied_at' => fake()->boolean(80) ? fake()->dateTimeBetween('-30 days', 'now') : null,
            'completed_at' => $status === 'completed' ? fake()->dateTimeBetween('-30 days', 'now') : null,
            'created_at' => fake()->dateTimeBetween('-45 days', 'now'),
            'updated_at' => now(),
        ];
    }
}
