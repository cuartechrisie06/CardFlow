<?php

namespace Database\Factories;

use App\Models\MarketplaceListing;
use App\Models\Card;
use App\Models\User;
use App\Models\UserCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketplaceListing>
 */
class MarketplaceListingFactory extends Factory
{
    protected $model = MarketplaceListing::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'user_card_id' => UserCard::factory()->listed(),
            'card_id' => Card::factory(),
            'status' => 'active',
            'is_visible' => true,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn () => [
            'status' => 'draft',
            'is_visible' => false,
        ]);
    }
}
