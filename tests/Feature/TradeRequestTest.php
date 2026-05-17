<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Card;
use App\Models\MarketplaceListing;
use App\Models\TradeRequest;
use App\Models\User;
use App\Models\UserCard;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_trade_and_seller_can_complete_it(): void
    {
        $seller = User::factory()->create(['username' => 'seller']);
        $buyer = User::factory()->create(['username' => 'buyer']);
        $listedCard = Card::factory()->create(['title' => 'Winter Listing']);
        $sellerUserCard = UserCard::factory()->listed(['is_for_trade' => true])->create([
            'user_id' => $seller->id,
            'card_id' => $listedCard->id,
        ]);
        $listing = MarketplaceListing::factory()->create([
            'user_id' => $seller->id,
            'user_card_id' => $sellerUserCard->id,
            'card_id' => $listedCard->id,
            'status' => 'active',
            'is_visible' => true,
        ]);
        $offeredCard = Card::factory()->create(['title' => 'Offered Chaewon']);
        $buyerUserCard = UserCard::factory()->listed(['is_for_trade' => true])->create([
            'user_id' => $buyer->id,
            'card_id' => $offeredCard->id,
        ]);
        $offeredListing = MarketplaceListing::factory()->create([
            'user_id' => $buyer->id,
            'user_card_id' => $buyerUserCard->id,
            'card_id' => $offeredCard->id,
            'status' => 'active',
            'is_visible' => true,
        ]);
        WishlistItem::factory()->for($buyer)->for($listedCard)->create();
        WishlistItem::factory()->for($seller)->for($offeredCard)->create();

        $this->actingAs($buyer)->post(route('trade-requests.store'), [
            'listing_id' => $listing->id,
            'offered_card_id' => $offeredCard->id,
            'message' => 'Would you trade?',
        ])->assertRedirect();

        $tradeRequest = TradeRequest::query()->firstOrFail();

        $this->assertSame('pending', $tradeRequest->status);
        $this->assertDatabaseHas('activities', [
            'user_id' => $seller->id,
            'type' => 'trade_request',
        ]);

        $this->actingAs($seller)
            ->patch(route('trade-requests.accept', $tradeRequest))
            ->assertRedirect();

        $this->assertSame('accepted', $tradeRequest->fresh()->status);

        $this->actingAs($seller)
            ->patch(route('trade-requests.complete', $tradeRequest))
            ->assertRedirect();

        $this->assertSame('completed', $tradeRequest->fresh()->status);
        $this->assertSame('sold', $listing->fresh()->status);
        $this->assertFalse((bool) $listing->fresh()->is_visible);
        $this->assertSame('traded', $offeredListing->fresh()->status);
        $this->assertFalse((bool) $offeredListing->fresh()->is_visible);
        $this->assertDatabaseMissing('wishlist_items', [
            'user_id' => $buyer->id,
            'card_id' => $listedCard->id,
        ]);
        $this->assertDatabaseMissing('wishlist_items', [
            'user_id' => $seller->id,
            'card_id' => $offeredCard->id,
        ]);
        $this->assertSame(2, Activity::query()->where('type', 'trade_completed')->count());
        $this->assertSame(2, Activity::query()->where('type', 'wishlist_fulfilled')->count());
    }

    public function test_user_cannot_request_trade_on_own_listing(): void
    {
        $seller = User::factory()->create();
        $listedCard = Card::factory()->create();
        $sellerUserCard = UserCard::factory()->listed(['is_for_trade' => true])->create([
            'user_id' => $seller->id,
            'card_id' => $listedCard->id,
        ]);
        $listing = MarketplaceListing::factory()->create([
            'user_id' => $seller->id,
            'user_card_id' => $sellerUserCard->id,
            'card_id' => $listedCard->id,
        ]);

        $this->actingAs($seller)->post(route('trade-requests.store'), [
            'listing_id' => $listing->id,
            'offered_card_id' => $listedCard->id,
        ])->assertStatus(422);
    }
}
