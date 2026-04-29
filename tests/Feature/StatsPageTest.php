<?php

namespace Tests\Feature;

use App\Models\Card;
use App\Models\Trade;
use App\Models\User;
use App\Models\UserCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_stats_page_with_real_metrics(): void
    {
        $user = User::factory()->create();
        $cardA = Card::factory()->create(['artist' => 'Aespa', 'market_value' => 1200, 'rarity' => 'Rare']);
        $cardB = Card::factory()->create(['artist' => 'IVE', 'market_value' => 1800, 'rarity' => 'Mint']);

        UserCard::factory()->for($user)->for($cardA)->create([
            'estimated_value' => 1300,
            'acquired_at' => now()->subMonths(2),
        ]);

        UserCard::factory()->for($user)->for($cardB)->create([
            'estimated_value' => 1900,
            'acquired_at' => now()->subMonth(),
        ]);

        Trade::factory()->for($user)->for($cardA)->create([
            'status' => 'completed',
            'completed_at' => now()->subDays(2),
            'replied_at' => now()->subDays(3),
        ]);

        Trade::factory()->for($user)->for($cardB)->create([
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('stats.index'))
            ->assertOk()
            ->assertSeeText('Collection insights')
            ->assertSeeText('PHP 3,200')
            ->assertSeeText('50%')
            ->assertSeeText('Aespa')
            ->assertSeeText('IVE');
    }
}
