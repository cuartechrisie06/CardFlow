<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WishlistItem>
 */
class WishlistItemFactory extends Factory
{
    protected $model = WishlistItem::class;

    public function definition(): array
    {
        $matched = fake()->boolean(55);

        return [
            'user_id' => User::factory(),
            'card_id' => Card::factory(),
            'priority' => fake()->randomElement(['high', 'medium', 'low']),
            'target_price' => fake()->randomFloat(2, 80, 2200),
            'matched_at' => $matched ? fake()->dateTimeBetween('-14 days', 'now') : null,
        ];
    }
}
