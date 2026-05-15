<?php

namespace Tests\Feature;

use App\Models\Card;
use App\Models\MarketplaceListing;
use App\Models\User;
use App\Models\UserCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_listing_as_draft(): void
    {
        $user = User::factory()->create();
        $card = Card::factory()->create([
            'artist' => 'IVE',
            'title' => 'Wonyoung Broadcast',
            'rarity' => 'Rare',
        ]);
        $userCard = UserCard::factory()->for($user)->for($card)->create();

        $this->actingAs($user)->post(route('marketplace.store'), [
            'user_card_id' => $userCard->id,
            'title' => 'Wonyoung Broadcast',
            'artist' => 'IVE',
            'rarity' => 'Rare',
            'type' => 'sale',
            'listing_price' => 2200,
            'description' => 'Mint condition, stored in sleeve.',
            'status' => 'draft',
        ])->assertRedirect(route('marketplace.index', ['filter' => 'my_listings']));

        $this->assertDatabaseHas('marketplace_listings', [
            'user_id' => $user->id,
            'user_card_id' => $userCard->id,
            'status' => 'draft',
            'is_visible' => 0,
        ]);
    }

    public function test_create_listing_form_prefills_from_selected_collection_item(): void
    {
        $user = User::factory()->create();
        $card = Card::factory()->create([
            'artist' => 'Enhypen',
            'title' => 'Sunoo Charybdis Holo',
            'rarity' => 'Mint',
            'market_value' => 2100,
        ]);
        $userCard = UserCard::factory()->for($user)->for($card)->create([
            'estimated_value' => 2300,
            'photo_path' => null,
        ]);

        $this->actingAs($user)
            ->get(route('marketplace.create', ['user_card_id' => $userCard->id]))
            ->assertOk()
            ->assertSee('data-title="Sunoo Charybdis Holo"', false)
            ->assertSee('data-artist="Enhypen"', false)
            ->assertSee('data-rarity="Mint"', false)
            ->assertSee('value="Sunoo Charybdis Holo"', false)
            ->assertSee('value="Enhypen"', false)
            ->assertSee('value="Mint"', false)
            ->assertSee('value="2300.00"', false);
    }

    public function test_create_listing_form_defaults_to_active_status(): void
    {
        $user = User::factory()->create();
        $card = Card::factory()->create([
            'artist' => 'NCT',
            'title' => 'Jaehyun Polaroid',
        ]);
        UserCard::factory()->for($user)->for($card)->create();

        $this->actingAs($user)
            ->get(route('marketplace.create'))
            ->assertOk()
            ->assertSee('value="active" selected', false);
    }

    public function test_marketplace_open_listings_metric_counts_current_users_active_listings(): void
    {
        $user = User::factory()->create();
        $card = Card::factory()->create();
        $userCard = UserCard::factory()->for($user)->for($card)->listed([
            'is_public' => true,
            'is_for_sale' => true,
            'listing_price' => 2000,
        ])->create();
        MarketplaceListing::factory()->create([
            'user_id' => $user->id,
            'user_card_id' => $userCard->id,
            'card_id' => $card->id,
            'status' => 'active',
            'is_visible' => true,
        ]);

        $this->actingAs($user)
            ->get(route('marketplace.index'))
            ->assertOk()
            ->assertViewHas('marketMetrics', function (array $metrics): bool {
                return $metrics['open_listings'] === 1;
            });
    }

    public function test_user_can_publish_listing(): void
    {
        $user = User::factory()->create();
        $card = Card::factory()->create();
        $userCard = UserCard::factory()->for($user)->for($card)->create();
        $listing = MarketplaceListing::factory()->hidden()->create([
            'user_id' => $user->id,
            'user_card_id' => $userCard->id,
            'card_id' => $card->id,
            'status' => 'draft',
            'is_visible' => false,
        ]);

        $this->actingAs($user)->put(route('marketplace.update', $listing), [
            'title' => 'Published Listing',
            'artist' => 'Aespa',
            'rarity' => 'Rare',
            'type' => 'sale',
            'listing_price' => 1800,
            'description' => 'Ready to publish.',
            'status' => 'active',
        ])->assertRedirect(route('marketplace.index', ['filter' => 'my_listings']));

        $listing->refresh();
        $userCard->refresh();

        $this->assertSame('active', $listing->status);
        $this->assertTrue((bool) $listing->is_visible);
        $this->assertTrue((bool) $userCard->is_public);
        $this->assertSame('active', $userCard->marketplace_status);
    }

    public function test_owner_can_mark_listing_as_sold(): void
    {
        $user = User::factory()->create();
        $card = Card::factory()->create();
        $userCard = UserCard::factory()->for($user)->for($card)->listed([
            'is_public' => true,
            'is_for_sale' => true,
            'listing_price' => 2400,
        ])->create();
        $listing = MarketplaceListing::factory()->create([
            'user_id' => $user->id,
            'user_card_id' => $userCard->id,
            'card_id' => $card->id,
            'status' => 'active',
            'is_visible' => true,
        ]);

        $this->actingAs($user)
            ->patch(route('marketplace.sold', $listing))
            ->assertRedirect(route('marketplace.index', ['filter' => 'my_listings']));

        $listing->refresh();
        $userCard->refresh();

        $this->assertSame('sold', $listing->status);
        $this->assertFalse((bool) $listing->is_visible);
        $this->assertSame('sold', $userCard->marketplace_status);
        $this->assertFalse((bool) $userCard->is_public);
        $this->assertFalse((bool) $userCard->is_listed);
    }

    public function test_unauthorized_user_cannot_edit_listing(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $card = Card::factory()->create();
        $userCard = UserCard::factory()->for($owner)->for($card)->listed()->create();
        $listing = MarketplaceListing::factory()->create([
            'user_id' => $owner->id,
            'user_card_id' => $userCard->id,
            'card_id' => $card->id,
        ]);

        $this->actingAs($otherUser)
            ->get(route('marketplace.edit', $listing))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->put(route('marketplace.update', $listing), [
                'title' => 'Hijacked Listing',
                'artist' => 'IVE',
                'rarity' => 'Rare',
                'type' => 'sale',
                'listing_price' => 1900,
                'description' => 'Blocked update.',
                'status' => 'active',
            ])
            ->assertForbidden();
    }
}
