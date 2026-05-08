<?php

namespace Tests\Feature;

use App\Models\Card;
use App\Models\MarketplaceListing;
use App\Models\User;
use App\Models\UserCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_card_with_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('collection.store'), [
            'artist' => 'IVE',
            'title' => 'Yujin Broadcast',
            'album' => 'Switch',
            'edition' => 'Broadcast',
            'rarity' => 'Rare',
            'market_value' => 1500,
            'condition' => 'Mint',
            'estimated_value' => 1550,
            'purchase_price' => 1200,
            'acquired_at' => '2026-05-01',
            'is_public' => '1',
            'is_for_trade' => '0',
            'is_for_sale' => '0',
            'listing_price' => null,
            'notes' => 'Sleeved and top-loaded',
        ]);

        $response->assertRedirect(route('collection.index'));

        $this->assertDatabaseHas('cards', [
            'artist' => 'IVE',
            'title' => 'Yujin Broadcast',
            'album' => 'Switch',
            'edition' => 'Broadcast',
            'rarity' => 'Rare',
        ]);

        $this->assertDatabaseHas('user_cards', [
            'user_id' => $user->id,
            'condition' => 'Mint',
            'notes' => 'Sleeved and top-loaded',
            'is_public' => 1,
            'is_for_trade' => 0,
            'is_for_sale' => 0,
        ]);
    }

    public function test_create_card_fails_with_missing_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('collection.create'))
            ->post(route('collection.store'), []);

        $response->assertRedirect(route('collection.create'));
        $response->assertSessionHasErrors([
            'artist',
            'title',
            'rarity',
            'market_value',
            'condition',
        ]);

        $this->assertDatabaseCount('user_cards', 0);
    }

    public function test_owner_can_edit_their_own_card(): void
    {
        $user = User::factory()->create();
        $userCard = UserCard::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('collection.update', $userCard), [
            'artist' => 'Aespa',
            'title' => 'Karina Special Frame',
            'album' => 'Drama',
            'edition' => 'Lucky Draw',
            'rarity' => 'Rare',
            'market_value' => 1800,
            'condition' => 'Mint',
            'estimated_value' => 1900,
            'purchase_price' => 1500,
            'acquired_at' => '2026-05-02',
            'is_public' => '0',
            'is_for_trade' => '1',
            'is_for_sale' => '0',
            'listing_price' => null,
            'notes' => 'Updated notes',
        ]);

        $response->assertRedirect(route('collection.index'));

        $userCard->refresh();

        $this->assertSame('Mint', $userCard->condition);
        $this->assertSame('Updated notes', $userCard->notes);
        $this->assertTrue((bool) $userCard->is_for_trade);
        $this->assertSame('Karina Special Frame', $userCard->card->title);
        $this->assertSame('Aespa', $userCard->card->artist);
    }

    public function test_non_owner_cannot_edit_another_users_card(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $userCard = UserCard::factory()->for($owner)->create();

        $this->actingAs($otherUser)
            ->get(route('collection.edit', $userCard))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->put(route('collection.update', $userCard), [
                'artist' => 'IVE',
                'title' => 'Blocked Update',
                'album' => 'Switch',
                'edition' => 'Broadcast',
                'rarity' => 'Rare',
                'market_value' => 1500,
                'condition' => 'Mint',
                'estimated_value' => 1500,
                'purchase_price' => 1100,
                'is_public' => '0',
                'is_for_trade' => '0',
                'is_for_sale' => '0',
                'listing_price' => null,
            ])
            ->assertForbidden();
    }

    public function test_owner_can_delete_unlisted_card(): void
    {
        $user = User::factory()->create();
        $userCard = UserCard::factory()->for($user)->create();

        $response = $this->actingAs($user)
            ->delete(route('collection.destroy', $userCard));

        $response->assertRedirect(route('collection.index'));

        $this->assertDatabaseMissing('user_cards', [
            'id' => $userCard->id,
        ]);
    }

    public function test_owner_cannot_delete_card_with_active_listing(): void
    {
        $user = User::factory()->create();
        $card = Card::factory()->create();
        $userCard = UserCard::factory()->for($user)->for($card)->listed([
            'is_public' => true,
            'is_for_trade' => true,
            'is_for_sale' => false,
        ])->create();

        MarketplaceListing::factory()->create([
            'user_id' => $user->id,
            'user_card_id' => $userCard->id,
            'card_id' => $userCard->card_id,
            'status' => 'active',
            'is_visible' => true,
        ]);

        $response = $this->actingAs($user)
            ->from(route('collection.index'))
            ->delete(route('collection.destroy', $userCard));

        $response->assertRedirect(route('collection.index'));
        $response->assertSessionHasErrors('card');

        $this->assertDatabaseHas('user_cards', [
            'id' => $userCard->id,
        ]);
    }
}
