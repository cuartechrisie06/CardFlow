<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\User;
use App\Models\UserCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserCard>
 */
class UserCardFactory extends Factory
{
    protected $model = UserCard::class;

    public function definition(): array
    {
        $value = fake()->randomFloat(2, 100, 2400);

        return [
            'user_id' => User::factory(),
            'card_id' => Card::factory(),
            'condition' => fake()->randomElement(['Mint', 'Near mint', 'Good']),
            'purchase_price' => $value * 0.85,
            'estimated_value' => $value,
            'acquired_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'is_for_trade' => fake()->boolean(25),
            'notes' => fake()->sentence(),
        ];
    }
}
