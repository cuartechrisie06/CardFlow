<?php

namespace Tests\Feature;

use App\Models\Card;
use App\Models\MarketplaceListing;
use App\Models\User;
use App\Models\UserCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_manage_own_cards(): void
    {
        $user = User::factory()->create();
        $card = Card::factory()->create();
        $userCard = UserCard::factory()->for($user)->for($card)->create();

        $this->actingAs($user)
            ->get(route('collection.edit', $userCard))
            ->assertOk();

        $this->actingAs($user)
            ->put(route('collection.update', $userCard), [
                'artist' => 'IVE',
                'title' => 'Updated Card',
                'album' => 'Switch',
                'edition' => 'Broadcast',
                'rarity' => 'Rare',
                'market_value' => 1500,
                'condition' => 'Mint',
                'estimated_value' => 1500,
                'purchase_price' => 1100,
                'is_public' => '1',
                'is_for_trade' => '1',
                'is_for_sale' => '0',
                'listing_price' => 1500,
            ])
            ->assertRedirect(route('collection.index'));
    }

    public function test_guest_or_non_owner_cannot_edit_another_users_card(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $userCard = UserCard::factory()->for($owner)->create();

        $this->get(route('collection.edit', $userCard))
            ->assertRedirect('/login');

        $this->actingAs($otherUser)
            ->get(route('collection.edit', $userCard))
            ->assertForbidden();
    }

    public function test_fake_sample_cards_do_not_render_without_real_listing(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create(['username' => 'owner_user']);
        $sampleCard = UserCard::factory()->for($owner)->create([
            'is_public' => true,
            'is_for_trade' => true,
            'is_for_sale' => false,
        ]);

        $response = $this->actingAs($viewer)->get(route('marketplace.index'));

        $response->assertOk()
            ->assertDontSeeText($sampleCard->card->title);
    }

    public function test_cards_without_owners_do_not_appear_in_marketplace(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create(['username' => 'owner_with_orphan_check']);
        $listedCard = UserCard::factory()->for($owner)->listed(['is_public' => true])->create();
        MarketplaceListing::factory()->create([
            'user_id' => $owner->id,
            'user_card_id' => $listedCard->id,
            'card_id' => $listedCard->card_id,
        ]);
        $orphanCard = Card::factory()->create([
            'title' => 'Orphan Listing',
            'artist' => 'Ghost Owner',
        ]);

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::table('marketplace_listings')->insert([
            'user_id' => 999999,
            'user_card_id' => UserCard::factory()->for($owner)->for($orphanCard)->listed(['is_public' => true])->create()->id,
            'card_id' => $orphanCard->id,
            'status' => 'active',
            'is_visible' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::statement('PRAGMA foreign_keys = ON');

        $response = $this->actingAs($viewer)->get(route('marketplace.index'));

        $response->assertOk()
            ->assertSeeText($listedCard->card->title)
            ->assertDontSeeText($orphanCard->title);
    }

    public function test_only_active_user_listings_appear(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create(['username' => 'public_owner']);

        $visibleCard = UserCard::factory()->for($owner)->listed([
            'is_public' => true,
            'is_for_trade' => false,
            'is_for_sale' => false,
        ])->create();
        MarketplaceListing::factory()->create([
            'user_id' => $owner->id,
            'user_card_id' => $visibleCard->id,
            'card_id' => $visibleCard->card_id,
        ]);

        $inactiveCard = UserCard::factory()->for($owner)->create([
            'is_listed' => true,
            'marketplace_status' => 'archived',
            'is_public' => true,
            'is_for_trade' => false,
            'is_for_sale' => false,
        ]);
        MarketplaceListing::factory()->hidden()->create([
            'user_id' => $owner->id,
            'user_card_id' => $inactiveCard->id,
            'card_id' => $inactiveCard->card_id,
        ]);

        $this->actingAs($viewer)
            ->get(route('marketplace.index'))
            ->assertOk()
            ->assertSeeText($visibleCard->card->title)
            ->assertDontSeeText($inactiveCard->card->title);
    }

    public function test_one_user_can_view_another_users_public_collection(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create(['username' => 'public_owner']);

        $visibleCard = UserCard::factory()->for($owner)->listed([
            'is_public' => true,
            'is_for_trade' => false,
            'is_for_sale' => false,
        ])->create();
        $visibleListing = MarketplaceListing::factory()->create([
            'user_id' => $owner->id,
            'user_card_id' => $visibleCard->id,
            'card_id' => $visibleCard->card_id,
        ]);

        $hiddenCard = UserCard::factory()->for($owner)->create([
            'is_listed' => false,
            'marketplace_status' => 'draft',
            'is_public' => true,
            'is_for_trade' => false,
            'is_for_sale' => false,
        ]);

        $this->actingAs($viewer)
            ->get(route('marketplace.user', $owner))
            ->assertOk()
            ->assertSeeText($visibleCard->card->title)
            ->assertDontSeeText($hiddenCard->card->title);
    }

    public function test_unlisted_cards_do_not_render_without_listing_record(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $unlistedCard = UserCard::factory()->for($owner)->create([
            'is_public' => true,
            'is_for_trade' => true,
        ]);

        $this->actingAs($viewer)
            ->get(route('marketplace.index'))
            ->assertOk()
            ->assertDontSeeText($unlistedCard->card->title);
    }

    public function test_owner_name_on_marketplace_comes_from_real_related_user_record(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create([
            'name' => 'Ava Santos',
            'username' => 'ava_santos',
        ]);
        $listedCard = UserCard::factory()->for($owner)->listed([
            'is_public' => true,
        ])->create();

        MarketplaceListing::factory()->create([
            'user_id' => $owner->id,
            'user_card_id' => $listedCard->id,
            'card_id' => $listedCard->card_id,
        ]);

        $this->actingAs($viewer)
            ->get(route('marketplace.index'))
            ->assertOk()
            ->assertSeeText('Ava Santos');
    }
}
