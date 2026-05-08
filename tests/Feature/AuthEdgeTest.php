<?php

namespace Tests\Feature;

use App\Models\Card;
use App\Models\User;
use App\Models\UserCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthEdgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_protected_routes(): void
    {
        $user = User::factory()->create(['username' => 'protected_owner']);
        $userCard = UserCard::factory()->for($user)->create();

        $routes = [
            route('dashboard'),
            route('stats.index'),
            route('profile.edit'),
            route('collection.index'),
            route('collection.create'),
            route('collection.show', $userCard),
            route('marketplace.index'),
            route('marketplace.create'),
            route('wishlist.index'),
            route('messages.index'),
            route('explorer.index'),
            route('profile.show', $user),
        ];

        foreach ($routes as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_user_cannot_access_another_users_private_card_details(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $card = Card::factory()->create([
            'artist' => 'Aespa',
            'title' => 'Private Broadcast',
        ]);
        $privateUserCard = UserCard::factory()->for($owner)->for($card)->create([
            'is_public' => false,
            'is_for_trade' => false,
            'is_for_sale' => false,
            'is_listed' => false,
        ]);

        $this->actingAs($viewer)
            ->get(route('collection.show', $privateUserCard))
            ->assertForbidden();
    }
}
