<?php

namespace Tests\Feature;

use App\Models\Card;
use App\Models\SavedView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExplorerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * @return array<string, mixed>
     */
    protected function kpopnetSamplePayload(): array
    {
        return [
            'groups' => [
                [
                    'id' => 'g-test-1',
                    'name' => 'aespa',
                    'debut_date' => '2020-11-17',
                    'members' => [
                        ['idol_id' => 'i1', 'current' => true],
                        ['idol_id' => 'i2', 'current' => true],
                        ['idol_id' => 'i3', 'current' => true],
                        ['idol_id' => 'i4', 'current' => true],
                    ],
                ],
            ],
            'idols' => [
                [
                    'id' => 'i-winter',
                    'name' => 'Winter',
                    'birth_date' => '2001-01-01',
                    'debut_date' => '2020-11-17',
                    'groups' => ['g-test-1'],
                ],
            ],
        ];
    }

    public function test_explorer_index_loads_idols_from_kpopnet(): void
    {
        Http::fake([
            'https://unpkg.com/kpopnet.json/*' => Http::response($this->kpopnetSamplePayload(), 200),
        ]);

        $viewer = User::factory()->create();

        $this->actingAs($viewer)
            ->get(route('explorer.index', ['tab' => 'idols']))
            ->assertOk()
            ->assertSeeText('Winter')
            ->assertSeeText('2001-01-01')
            ->assertSeeText('aespa')
            ->assertSeeText('kpopnet');
    }

    public function test_explorer_groups_tab_loads_groups_from_kpopnet(): void
    {
        Http::fake([
            'https://unpkg.com/kpopnet.json/*' => Http::response($this->kpopnetSamplePayload(), 200),
        ]);

        $viewer = User::factory()->create();

        $this->actingAs($viewer)
            ->get(route('explorer.index', ['tab' => 'groups']))
            ->assertOk()
            ->assertSeeText('aespa')
            ->assertSeeText('Members')
            ->assertSeeText('4');
    }

    public function test_explorer_index_shows_unavailable_when_dataset_empty(): void
    {
        Http::fake([
            'https://unpkg.com/kpopnet.json/*' => Http::response(['groups' => [], 'idols' => []], 200),
        ]);

        $viewer = User::factory()->create();

        $this->actingAs($viewer)
            ->get(route('explorer.index'))
            ->assertOk()
            ->assertSeeText('K-Pop database is currently unavailable.');
    }

    public function test_explorer_index_shows_unavailable_when_fetch_fails(): void
    {
        Http::fake([
            'https://unpkg.com/kpopnet.json/*' => Http::response(null, 503),
        ]);

        $viewer = User::factory()->create();

        $this->actingAs($viewer)
            ->get(route('explorer.index'))
            ->assertOk()
            ->assertSeeText('K-Pop database is currently unavailable.');
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
