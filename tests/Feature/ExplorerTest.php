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
        WishlistItem::factory()->for($viewer)->for($cardA)->create();
        WishlistItem::factory()->for($viewer)->for($cardB)->create();

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

        Card::factory()->create([
            'artist' => 'Le Sserafim',
            'title' => 'Yunjin - Easy',
            'album' => 'Easy',
        ]);

        Card::factory()->create([
            'artist' => 'BLACKPINK',
            'title' => 'Jennie - Born Pink',
            'album' => 'Born Pink',
        ]);

        $this->actingAs($viewer)
            ->get(route('explorer.index', ['q' => 'Yunjin']))
            ->assertOk()
            ->assertSeeText('Le Sserafim')
            ->assertDontSeeText('BLACKPINK');
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
