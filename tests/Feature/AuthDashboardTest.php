<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Card;
use App\Models\Trade;
use App\Models\User;
use App\Models\UserCard;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_redirects_to_dashboard_and_authenticates_user(): void
    {
        $response = $this->post('/register', [
            'name' => 'Chrisie Noreen Cuarte',
            'username' => 'cuarte_chrisie',
            'email' => 'chrissie@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => '1',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'chrissie@example.com',
            'username' => 'cuarte_chrisie',
        ]);
    }

    public function test_registered_user_can_log_in(): void
    {
        $user = User::factory()->create([
            'username' => 'trade_hub',
            'email' => 'collector@example.com',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_dashboard_metrics_load_successfully_for_authenticated_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Dashboard User',
            'username' => 'dashboard_user',
        ]);

        $otherUser = User::factory()->create();

        $cardA = Card::factory()->create([
            'artist' => 'IVE',
            'title' => 'Mina - Fancy era',
            'market_value' => 1450,
        ]);

        $cardB = Card::factory()->create([
            'artist' => 'Aespa',
            'title' => 'Winter - Armageddon',
            'market_value' => 1200,
        ]);

        UserCard::factory()->for($user)->for($cardA)->create([
            'estimated_value' => 1500,
            'acquired_at' => now()->subMonths(2),
        ]);

        UserCard::factory()->for($user)->for($cardB)->create([
            'estimated_value' => 900,
            'acquired_at' => now()->subMonth(),
        ]);

        UserCard::factory()->for($otherUser)->create([
            'estimated_value' => 5000,
        ]);

        Trade::factory()->for($user)->for($cardA)->create([
            'status' => 'pending',
            'created_at' => now()->startOfMonth()->addDay(),
        ]);

        Trade::factory()->for($user)->for($cardB)->create([
            'status' => 'completed',
            'created_at' => now()->startOfMonth()->addDays(2),
            'replied_at' => now()->subDay(),
            'completed_at' => now()->subHours(12),
        ]);

        Trade::factory()->for($otherUser)->create([
            'status' => 'pending',
        ]);

        WishlistItem::factory()->for($user)->for($cardA)->create([
            'matched_at' => now()->subHour(),
        ]);

        WishlistItem::factory()->for($user)->for($cardB)->create([
            'matched_at' => null,
        ]);

        Activity::factory()->for($user)->create([
            'title' => 'Added 1 new card to your collection',
            'happened_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk()
            ->assertSeeText('Added 1 new card to your collection')
            ->assertViewHas('metrics', function (array $metrics) {
                return $metrics['total_cards'] === 2
                    && (float) $metrics['collection_value'] === 2400.0
                    && $metrics['active_trades'] === 1
                    && $metrics['wishlist_matches'] === 1;
            });
    }
}
