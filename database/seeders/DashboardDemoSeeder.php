<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Card;
use App\Models\MarketplaceListing;
use App\Models\Trade;
use App\Models\User;
use App\Models\UserCard;
use App\Models\WishlistItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DashboardDemoSeeder extends Seeder
{
    public function run(): void
    {
        $cards = collect([
            ['artist' => 'IVE', 'title' => 'Mina - Fancy era', 'edition' => 'Official set', 'album' => 'Fancy', 'rarity' => 'Mint', 'market_value' => 1450, 'thumbnail_style' => 'market-thumb-one', 'trend_score' => 94],
            ['artist' => 'IVE', 'title' => 'Wonyoung - Switch', 'edition' => 'Lucky draw', 'album' => 'Switch', 'rarity' => 'Rare', 'market_value' => 2200, 'thumbnail_style' => 'market-thumb-two', 'trend_score' => 99],
            ['artist' => 'IVE', 'title' => 'Yujin - Frame card', 'edition' => 'Seasonal release', 'album' => 'Switch', 'rarity' => 'Hot', 'market_value' => 990, 'thumbnail_style' => 'market-thumb-three', 'trend_score' => 91],
            ['artist' => 'Aespa', 'title' => 'Winter - Armageddon', 'edition' => 'Broadcast drop', 'album' => 'Armageddon', 'rarity' => 'Wishlist', 'market_value' => 1280, 'thumbnail_style' => 'market-thumb-one', 'trend_score' => 90],
            ['artist' => 'Aespa', 'title' => 'Karina - Drama', 'edition' => 'Official set', 'album' => 'Drama', 'rarity' => 'Mint', 'market_value' => 1160, 'thumbnail_style' => 'market-thumb-two', 'trend_score' => 85],
            ['artist' => 'Le Sserafim', 'title' => 'Chaewon - Easy', 'edition' => 'Fansign', 'album' => 'Easy', 'rarity' => 'Rare', 'market_value' => 1580, 'thumbnail_style' => 'market-thumb-three', 'trend_score' => 96],
            ['artist' => 'Le Sserafim', 'title' => 'Yunjin - Easy', 'edition' => 'Official set', 'album' => 'Easy', 'rarity' => 'Mint', 'market_value' => 1040, 'thumbnail_style' => 'market-thumb-one', 'trend_score' => 84],
            ['artist' => 'Twice', 'title' => 'Sana - Ready To Be', 'edition' => 'Lucky draw', 'album' => 'Ready To Be', 'rarity' => 'Hot', 'market_value' => 870, 'thumbnail_style' => 'market-thumb-two', 'trend_score' => 80],
            ['artist' => 'Itzy', 'title' => 'Yeji - Born To Be', 'edition' => 'Official set', 'album' => 'Born To Be', 'rarity' => 'Official', 'market_value' => 760, 'thumbnail_style' => 'market-thumb-three', 'trend_score' => 72],
            ['artist' => 'NewJeans', 'title' => 'Hanni - Supernatural', 'edition' => 'Limited shop', 'album' => 'Supernatural', 'rarity' => 'Rare', 'market_value' => 1330, 'thumbnail_style' => 'market-thumb-one', 'trend_score' => 89],
        ])->map(fn (array $card) => Card::query()->updateOrCreate(
            ['artist' => $card['artist'], 'title' => $card['title']],
            $card
        ));

        $demoUser = User::query()->updateOrCreate(
            ['email' => 'chrissie@cardflow.test'],
            [
                'name' => 'Chrisie Noreen Cuarte',
                'username' => 'cuarte_chrisie',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        $secondaryUser = User::query()->updateOrCreate(
            ['email' => 'other@cardflow.test'],
            [
                'name' => 'Other Collector',
                'username' => 'other_collector',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        $thirdUser = User::query()->updateOrCreate(
            ['email' => 'seller@cardflow.test'],
            [
                'name' => 'Market Seller',
                'username' => 'market_seller',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        $demoUser->userCards()->delete();
        $demoUser->trades()->delete();
        $demoUser->wishlistItems()->delete();
        $demoUser->activities()->delete();
        $demoUser->marketplaceListings()->delete();

        $secondaryUser->userCards()->delete();
        $secondaryUser->trades()->delete();
        $secondaryUser->wishlistItems()->delete();
        $secondaryUser->activities()->delete();
        $secondaryUser->marketplaceListings()->delete();
        $thirdUser->userCards()->delete();
        $thirdUser->trades()->delete();
        $thirdUser->wishlistItems()->delete();
        $thirdUser->activities()->delete();
        $thirdUser->marketplaceListings()->delete();

        $collectionPlan = [
            [0, 1380, now()->subMonths(5)->addDays(3), true],
            [1, 2190, now()->subMonths(4)->addDays(9), false],
            [2, 980, now()->subMonths(4)->addDays(20), false],
            [3, 1260, now()->subMonths(3)->addDays(2), true],
            [4, 1120, now()->subMonths(3)->addDays(17), false],
            [5, 1600, now()->subMonths(2)->addDays(4), true],
            [6, 1020, now()->subMonths(2)->addDays(14), false],
            [7, 860, now()->subMonth()->addDays(6), false],
            [8, 740, now()->subMonth()->addDays(13), false],
            [9, 1360, now()->subDays(8), true],
        ];

        foreach ($collectionPlan as [$cardIndex, $value, $acquiredAt, $isForTrade]) {
            $listingState = UserCard::deriveListingState($isForTrade, $isForTrade, ! $isForTrade && $value >= 1200);

            $userCard = UserCard::factory()->for($demoUser)->for($cards[$cardIndex])->create([
                'estimated_value' => $value,
                'purchase_price' => round($value * 0.82, 2),
                'acquired_at' => $acquiredAt,
                'is_listed' => $listingState['is_listed'],
                'marketplace_status' => $listingState['marketplace_status'],
                'is_public' => $isForTrade,
                'is_for_trade' => $isForTrade,
                'is_for_sale' => ! $isForTrade && $value >= 1200,
                'listing_price' => $isForTrade || $value >= 1200 ? $value : null,
            ]);

            if ($userCard->is_listed) {
                MarketplaceListing::factory()->create([
                    'user_id' => $demoUser->id,
                    'user_card_id' => $userCard->id,
                    'card_id' => $userCard->card_id,
                ]);
            }
        }

        $secondaryCards = UserCard::factory(4)->for($secondaryUser)->create();
        $secondaryCards->take(2)->each(function (UserCard $userCard) use ($secondaryUser) {
            $userCard->update([
                'is_listed' => true,
                'marketplace_status' => 'active',
                'is_public' => true,
                'is_for_sale' => true,
                'listing_price' => $userCard->estimated_value,
            ]);

            MarketplaceListing::factory()->create([
                'user_id' => $secondaryUser->id,
                'user_card_id' => $userCard->id,
                'card_id' => $userCard->card_id,
            ]);
        });

        $thirdCards = UserCard::factory(3)->for($thirdUser)->create();
        $thirdCards->take(2)->each(function (UserCard $userCard) use ($thirdUser) {
            $userCard->update([
                'is_listed' => true,
                'marketplace_status' => 'active',
                'is_public' => true,
                'is_for_trade' => true,
                'listing_price' => $userCard->estimated_value,
            ]);

            MarketplaceListing::factory()->create([
                'user_id' => $thirdUser->id,
                'user_card_id' => $userCard->id,
                'card_id' => $userCard->card_id,
            ]);
        });

        UserCard::factory()->for($thirdUser)->create([
            'is_listed' => false,
            'marketplace_status' => 'draft',
            'is_public' => false,
            'is_for_trade' => false,
            'is_for_sale' => false,
            'listing_price' => null,
        ]);

        collect([
            ['card' => 0, 'status' => 'completed', 'created_at' => now()->startOfMonth()->addDays(1), 'replied_at' => now()->subDays(18), 'completed_at' => now()->subDays(14)],
            ['card' => 3, 'status' => 'pending', 'created_at' => now()->startOfMonth()->addDays(3), 'replied_at' => now()->subDays(10), 'completed_at' => null],
            ['card' => 5, 'status' => 'new_offer', 'created_at' => now()->startOfMonth()->addDays(7), 'replied_at' => now()->subDays(4), 'completed_at' => null],
            ['card' => 1, 'status' => 'cancelled', 'created_at' => now()->startOfMonth()->addDays(11), 'replied_at' => null, 'completed_at' => null],
            ['card' => 9, 'status' => 'in_progress', 'created_at' => now()->subDays(2), 'replied_at' => now()->subDay(), 'completed_at' => null],
        ])->each(function (array $trade) use ($demoUser, $cards) {
            Trade::factory()->for($demoUser)->for($cards[$trade['card']])->create([
                'partner_name' => fake()->name(),
                'partner_handle' => '@'.fake()->userName(),
                'status' => $trade['status'],
                'offered_value' => $cards[$trade['card']]->market_value,
                'created_at' => $trade['created_at'],
                'updated_at' => $trade['created_at'],
                'replied_at' => $trade['replied_at'],
                'completed_at' => $trade['completed_at'],
            ]);
        });

        collect([
            ['card' => 3, 'priority' => 'high', 'matched_at' => now()->subMinutes(2)],
            ['card' => 4, 'priority' => 'medium', 'matched_at' => now()->subMinutes(18)],
            ['card' => 5, 'priority' => 'high', 'matched_at' => now()->subHours(3)],
            ['card' => 6, 'priority' => 'medium', 'matched_at' => now()->subDay()],
            ['card' => 7, 'priority' => 'low', 'matched_at' => null],
            ['card' => 8, 'priority' => 'low', 'matched_at' => null],
            ['card' => 9, 'priority' => 'high', 'matched_at' => now()->subDays(2)],
        ])->each(function (array $item) use ($demoUser, $cards) {
            WishlistItem::factory()->for($demoUser)->for($cards[$item['card']])->create([
                'priority' => $item['priority'],
                'target_price' => round($cards[$item['card']]->market_value * 0.92, 2),
                'matched_at' => $item['matched_at'],
            ]);
        });

        collect([
            ['type' => 'trade', 'title' => '@kpop_collector wants to trade with you', 'happened_at' => now()->subMinutes(2)],
            ['type' => 'collection', 'title' => 'You added 1 new card to your collection', 'happened_at' => now()->subMinutes(18)],
            ['type' => 'wishlist', 'title' => 'Wishlist found Winter - Armageddon', 'happened_at' => now()->setTime(9, 24)],
            ['type' => 'trade', 'title' => '@aespa_stan completed a trade', 'happened_at' => now()->subDay()->setTime(16, 10)],
            ['type' => 'collection', 'title' => 'Wishlist priorities were refreshed', 'happened_at' => now()->subMinutes(55)],
        ])->each(fn (array $activity) => Activity::factory()->for($demoUser)->create($activity));
    }
}
