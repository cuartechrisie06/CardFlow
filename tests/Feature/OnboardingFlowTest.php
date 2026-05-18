<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_redirects_new_user_to_onboarding(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Noreen Cuarte',
            'username' => 'noreen',
            'email' => 'noreen@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => '1',
        ])->assertRedirect(route('onboarding.start'));

        $this->assertAuthenticated();
        $this->assertFalse(User::where('email', 'noreen@example.test')->first()->onboarding_completed);
    }

    public function test_user_can_view_onboarding_step_and_step_is_tracked(): void
    {
        $user = User::factory()->create([
            'onboarding_completed' => false,
            'onboarding_step' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('onboarding.step', 3))
            ->assertOk()
            ->assertSee('Never miss a card you want');

        $this->assertSame(3, $user->fresh()->onboarding_step);
    }

    public function test_user_can_complete_onboarding(): void
    {
        $user = User::factory()->create([
            'onboarding_completed' => false,
        ]);

        $this->actingAs($user)
            ->post(route('onboarding.complete'))
            ->assertRedirect(route('dashboard'));

        $this->assertTrue($user->fresh()->onboarding_completed);
        $this->assertSame(5, $user->fresh()->onboarding_step);
    }

    public function test_user_can_skip_onboarding(): void
    {
        $user = User::factory()->create([
            'onboarding_completed' => false,
        ]);

        $this->actingAs($user)
            ->post(route('onboarding.skip'))
            ->assertRedirect(route('dashboard'));

        $this->assertTrue($user->fresh()->onboarding_completed);
    }
}
