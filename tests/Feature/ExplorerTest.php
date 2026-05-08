<?php

namespace Tests\Feature;

use App\Models\Card;
use App\Models\MarketplaceListing;
use App\Models\SavedView;
use App\Models\User;
use App\Models\UserCard;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExplorerTest extends TestCase
{
    use RefreshDatabase;

    public function test_explorer_index_uses_real_database_metrics(): void
    {
        $viewer = User::factory()->create();
        $seller = User::factory()->create();

        $cardA = Card::factory()->create([
            'artist' => 'Aespa',
            'title' => 'Winter - Armageddon',
            'album' => 'Armageddon',
            'market_value' => 1200,
        ]);

        $cardB = Card::factory()->create([
            'artist' => 'IVE',
            'title' => 'Yujin - Switch',
            'album' => 'Switch',
            'market_value' => 1800,
        ]);

        $userCard = UserCard::factory()->for($seller)->for($cardA)->listed([
            'is_public' => true,
            'is_for_sale' => true,
            'listing_price' => 1500,
        ])->create();

        MarketplaceListing::factory()->create([
            'user_id' => $seller->id,
            'user_card_id' => $userCard->id,
            'card_id' => $cardA->id,
        ]);

        WishlistItem::factory()->for($viewer)->for($cardA)->create();
        WishlistItem::factory()->for(User::factory()->create())->for($cardA)->create();
        WishlistItem::factory()->for($viewer)->for($cardB)->create();

        UserCard::factory()->for($seller)->for($cardB)->create();

        $this->actingAs($viewer)
            ->get(route('explorer.index'))
            ->assertOk()
            ->assertSeeText('2')
            ->assertSeeText('PHP 1,500')
            ->assertSeeText('Aespa');
    }

    public function test_explorer_search_filters_real_cards(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();

        $matchingCard = Card::factory()->create([
            'artist' => 'Le Sserafim',
            'title' => 'Yunjin - Easy',
            'album' => 'Easy',
        ]);

        $nonMatchingCard = Card::factory()->create([
            'artist' => 'BLACKPINK',
            'title' => 'Jennie - Born Pink',
            'album' => 'Born Pink',
        ]);

        UserCard::factory()->for($owner)->for($matchingCard)->create();
        UserCard::factory()->for($owner)->for($nonMatchingCard)->create();

        $this->actingAs($viewer)
            ->get(route('explorer.index', ['q' => 'Yunjin']))
            ->assertOk()
            ->assertSeeText('Le Sserafim')
            ->assertDontSeeText('BLACKPINK');
    }

    public function test_explorer_search_can_filter_by_artist(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();

        $artistCard = Card::factory()->create([
            'artist' => 'Aespa',
            'title' => 'Winter Broadcast',
            'album' => 'Drama',
        ]);

        $otherCard = Card::factory()->create([
            'artist' => 'IVE',
            'title' => 'Yujin Lucky Draw',
            'album' => 'Switch',
        ]);

        UserCard::factory()->for($owner)->for($artistCard)->create();
        UserCard::factory()->for($owner)->for($otherCard)->create();

        $this->actingAs($viewer)
            ->get(route('explorer.index', ['q' => 'Aespa']))
            ->assertOk()
            ->assertSeeText('Aespa')
            ->assertDontSeeText('IVE');
    }

    public function test_explorer_search_can_filter_by_rarity(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();

        $rareCard = Card::factory()->create([
            'artist' => 'Aespa',
            'title' => 'Karina Rare Pull',
            'rarity' => 'Rare',
        ]);

        $commonCard = Card::factory()->create([
            'artist' => 'IVE',
            'title' => 'Liz Album Ver',
            'rarity' => 'Official',
        ]);

        UserCard::factory()->for($owner)->for($rareCard)->create();
        UserCard::factory()->for($owner)->for($commonCard)->create();

        $this->actingAs($viewer)
            ->get(route('explorer.index', ['q' => 'Rare']))
            ->assertOk()
            ->assertSeeText('Karina Rare Pull')
            ->assertDontSeeText('Liz Album Ver');
    }

    public function test_explorer_search_can_filter_by_album(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();

        $albumMatch = Card::factory()->create([
            'artist' => 'NewJeans',
            'title' => 'Hanni Broadcast',
            'album' => 'Supernatural',
        ]);

        $nonMatch = Card::factory()->create([
            'artist' => 'Twice',
            'title' => 'Nayeon Fan Sign',
            'album' => 'With You-th',
        ]);

        UserCard::factory()->for($owner)->for($albumMatch)->create();
        UserCard::factory()->for($owner)->for($nonMatch)->create();

        $this->actingAs($viewer)
            ->get(route('explorer.index', ['q' => 'Supernatural']))
            ->assertOk()
            ->assertSeeText('NewJeans')
            ->assertDontSeeText('Twice');
    }

    public function test_catalog_cards_link_to_real_catalog_detail_page(): void
    {
        $viewer = User::factory()->create();

        Card::factory()->create([
            'artist' => 'NewJeans',
            'title' => 'Hanni - Supernatural',
            'album' => 'Supernatural',
        ]);

        $this->actingAs($viewer)
            ->get(route('explorer.catalogs.show', 'newjeans'))
            ->assertOk()
            ->assertSeeText('NewJeans')
            ->assertSeeText('Catalog cards');
    }

    public function test_save_view_persists_current_search_and_filter(): void
    {
        $viewer = User::factory()->create();

        $this->actingAs($viewer)
            ->post(route('explorer.saved-views.store'), [
                'q' => 'Aespa',
                'filter' => 'high_value',
            ])
            ->assertRedirect(route('explorer.index', ['q' => 'Aespa', 'filter' => 'high_value']));

        $this->assertDatabaseHas('saved_views', [
            'user_id' => $viewer->id,
            'page' => 'explorer',
            'search' => 'Aespa',
        ]);

        $this->assertSame('high_value', SavedView::query()->firstOrFail()->filters['filter']);
    }
}
