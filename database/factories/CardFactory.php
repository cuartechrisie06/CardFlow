<?php

namespace Database\Factories;

use App\Models\Card;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Card>
 */
class CardFactory extends Factory
{
    protected $model = Card::class;

    public function definition(): array
    {
        $artists = ['IVE', 'Aespa', 'Le Sserafim', 'Twice', 'Itzy', 'NewJeans', 'Red Velvet'];
        $artist = fake()->randomElement($artists);

        return [
            'artist' => $artist,
            'title' => fake()->randomElement(['Broadcast', 'Lucky Draw', 'Album Ver.', 'Special Frame', 'Fansign', 'Seasonal']),
            'edition' => fake()->randomElement(['Official set', 'Lucky draw', 'Seasonal release', 'Limited shop', 'Broadcast drop']),
            'album' => fake()->randomElement(['Switch', 'Drama', 'Armageddon', 'Fancy', 'Easy', 'Born Pink']),
            'rarity' => fake()->randomElement(['Mint', 'Rare', 'Hot', 'Official', 'Wishlist']),
            'market_value' => fake()->randomFloat(2, 90, 2500),
            'thumbnail_style' => fake()->randomElement(['market-thumb-one', 'market-thumb-two', 'market-thumb-three']),
            'trend_score' => fake()->numberBetween(40, 100),
            'released_on' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
        ];
    }
}
