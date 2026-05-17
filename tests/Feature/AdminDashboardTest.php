<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.index'))
            ->assertOk()
            ->assertSee('CardFlow Admin');
    }

    public function test_regular_user_cannot_view_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->get(route('admin.index'))
            ->assertForbidden();
    }

    public function test_admin_login_page_authenticates_admin_users(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('password-secret'),
            'is_admin' => true,
        ]);

        $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'password-secret',
        ])->assertRedirect(route('admin.index'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_login_rejects_regular_users(): void
    {
        $user = User::factory()->create([
            'email' => 'collector@example.test',
            'password' => Hash::make('password-secret'),
            'is_admin' => false,
        ]);

        $this->post(route('admin.login.submit'), [
            'email' => $user->email,
            'password' => 'password-secret',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_regular_login_redirects_admins_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('password-secret'),
            'is_admin' => true,
        ]);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password-secret',
        ])->assertRedirect(route('admin.index'));
    }
}
