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
            'is_listed' => false,
            'marketplace_status' => 'draft',
            'is_public' => false,
            'is_for_trade' => false,
            'is_for_sale' => false,
            'listing_price' => null,
            'notes' => fake()->sentence(),
        ];
    }

    public function listed(array $attributes = []): static
    {
        return $this->state(function () use ($attributes) {
            $isPublic = $attributes['is_public'] ?? false;
            $isForTrade = $attributes['is_for_trade'] ?? true;
            $isForSale = $attributes['is_for_sale'] ?? false;

            return array_merge([
                'is_listed' => true,
                'marketplace_status' => 'active',
                'is_public' => $isPublic,
                'is_for_trade' => $isForTrade,
                'is_for_sale' => $isForSale,
                'listing_price' => $isForSale ? fake()->randomFloat(2, 120, 2600) : null,
            ], $attributes);
        });
    }
}
