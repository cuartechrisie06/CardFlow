<?php

namespace Tests\Feature;

use App\Models\Card;
use App\Models\MarketplaceListing;
use App\Models\User;
use App\Models\UserCard;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistMarketplaceMatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_wishlist_item_matches_a_real_marketplace_listing(): void
    {
        $viewer = User::factory()->create();
        $seller = User::factory()->create(['username' => 'real_seller']);
        $card = Card::factory()->create([
            'artist' => 'Aespa',
            'title' => 'Winter - Broadcast Card',
            'album' => 'Armageddon',
            'edition' => 'Broadcast',
            'rarity' => 'Rare',
        ]);

        $userCard = UserCard::factory()->for($seller)->for($card)->listed([
            'is_public' => true,
            'is_for_sale' => true,
            'listing_price' => 1800,
        ])->create();

        $listing = MarketplaceListing::factory()->create([
            'user_id' => $seller->id,
            'user_card_id' => $userCard->id,
            'card_id' => $card->id,
        ]);

        WishlistItem::factory()->for($viewer)->for($card)->create([
            'priority' => 'high',
        ]);

        $this->actingAs($viewer)
            ->get(route('wishlist.index'))
            ->assertOk()
            ->assertSeeText('Winter - Broadcast Card')
            ->assertSeeText('@real_seller')
            ->assertSee(route('marketplace.cards.show', $listing), false);
    }

    public function test_unlisted_cards_do_not_appear_as_matches(): void
    {
        $viewer = User::factory()->create();
        $seller = User::factory()->create();
        $card = Card::factory()->create([
            'artist' => 'Le Sserafim',
            'title' => 'Yunjin - Easy',
            'album' => 'Easy',
        ]);

        UserCard::factory()->for($seller)->for($card)->create([
            'is_public' => true,
            'is_for_trade' => true,
        ]);

        WishlistItem::factory()->for($viewer)->for($card)->create();

        $this->actingAs($viewer)
            ->get(route('wishlist.index'))
            ->assertOk()
            ->assertDontSeeText('@'.$seller->username)
            ->assertSeeText('No live matches yet');
    }

    public function test_users_own_cards_do_not_appear_as_matches(): void
    {
        $viewer = User::factory()->create(['username' => 'collector_self']);
        $card = Card::factory()->create([
            'artist' => 'IVE',
            'title' => 'Yujin - Frame Card',
            'album' => 'Switch',
        ]);

        $userCard = UserCard::factory()->for($viewer)->for($card)->listed([
            'is_public' => true,
        ])->create();

        MarketplaceListing::factory()->create([
            'user_id' => $viewer->id,
            'user_card_id' => $userCard->id,
            'card_id' => $card->id,
        ]);

        WishlistItem::factory()->for($viewer)->for($card)->create();

        $this->actingAs($viewer)
            ->get(route('wishlist.index'))
            ->assertOk()
            ->assertDontSeeText('@collector_self');
    }

    public function test_fake_sample_cards_do_not_appear(): void
    {
        $viewer = User::factory()->create();
        $seller = User::factory()->create(['username' => 'sample_seller']);
        $card = Card::factory()->create([
            'artist' => 'Twice',
            'title' => 'Sana - Ready To Be',
            'album' => 'Ready To Be',
        ]);

        UserCard::factory()->for($seller)->for($card)->create([
            'is_public' => true,
            'is_for_sale' => true,
        ]);

        WishlistItem::factory()->for($viewer)->for($card)->create();

        $this->actingAs($viewer)
            ->get(route('wishlist.index'))
            ->assertOk()
            ->assertDontSeeText('@sample_seller');
    }

    public function test_no_match_state_appears_correctly_when_there_are_no_valid_listings(): void
    {
        $viewer = User::factory()->create();
        $card = Card::factory()->create([
            'artist' => 'BLACKPINK',
            'title' => 'Jisoo - Born Pink',
            'album' => 'Born Pink',
        ]);

        WishlistItem::factory()->for($viewer)->for($card)->create();

        $this->actingAs($viewer)
            ->get(route('wishlist.index'))
            ->assertOk()
            ->assertSeeText('No active matches yet. We’ll surface real marketplace listings here when they appear.');
    }
}
