<?php

namespace Tests\Feature;

use App\Models\Card;
use App\Models\KpopGroup;
use App\Models\KpopIdol;
use App\Models\SavedView;
use App\Models\User;
use Database\Seeders\KpopIdolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExplorerTest extends TestCase
{
    use RefreshDatabase;

    public function test_explorer_index_loads_idols_from_local_database(): void
    {
        KpopIdol::query()->create([
            'stage_name' => 'Winter',
            'full_name' => 'Kim Min-jeong',
            'korean_name' => '윈터',
            'group_name' => 'aespa',
            'debut_date' => '2020-11-17',
            'birth_date' => '2001-01-01',
            'company' => 'SM Entertainment',
            'country' => 'Korea',
            'gender' => 'Female',
        ]);

        KpopGroup::query()->create([
            'name' => 'aespa',
            'debut_date' => '2020-11-17',
            'company' => 'SM Entertainment',
            'member_count' => 4,
            'gender' => 'Female',
        ]);

        $viewer = User::factory()->create();

        $this->actingAs($viewer)
            ->get(route('explorer.index', ['tab' => 'idols']))
            ->assertOk()
            ->assertSeeText('Winter')
            ->assertSeeText('Kim Min-jeong')
            ->assertSeeText('aespa');
    }

    public function test_explorer_groups_tab_loads_groups_from_local_database(): void
    {
        KpopGroup::query()->create([
            'name' => 'aespa',
            'debut_date' => '2020-11-17',
            'company' => 'SM Entertainment',
            'member_count' => 4,
            'gender' => 'Female',
        ]);

        $viewer = User::factory()->create();

        $this->actingAs($viewer)
            ->get(route('explorer.index', ['tab' => 'groups']))
            ->assertOk()
            ->assertSeeText('aespa')
            ->assertSeeText('SM Entertainment')
            ->assertSeeText('4');
    }

    public function test_explorer_index_shows_local_empty_state_when_no_results(): void
    {
        $viewer = User::factory()->create();

        $this->actingAs($viewer)
            ->get(route('explorer.index'))
            ->assertOk()
            ->assertSeeText('No idols found.');
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

    public function test_kpop_idol_seeder_imports_rows_from_csv(): void
    {
        $this->seed(KpopIdolSeeder::class);

        $this->assertDatabaseHas('kpop_idols', [
            'stage_name' => '2Soul',
            'group_name' => "7 O'clock",
        ]);
        $this->assertDatabaseHas('kpop_groups', [
            'name' => "7 O'clock",
        ]);
    }
}
