<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_bio_avatar_and_location(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $avatar = UploadedFile::fake()->createWithContent('avatar.jpg', 'fake-image-content');

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Updated Collector',
            'username' => 'updated_collector',
            'email' => 'updated@example.com',
            'bio' => 'Collector of broadcast and lucky draw photocards.',
            'location' => 'Manila, PH',
            'website' => 'https://cardflow.test/profile',
            'avatar' => $avatar,
        ]);

        $response->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('Updated Collector', $user->name);
        $this->assertSame('updated_collector', $user->username);
        $this->assertSame('Collector of broadcast and lucky draw photocards.', $user->bio);
        $this->assertSame('Manila, PH', $user->location);
        $this->assertSame('https://cardflow.test/profile', $user->website);
        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_updating_profile_only_changes_authenticated_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Owner User',
            'username' => 'owner_user',
            'email' => 'owner@example.com',
        ]);

        $otherUser = User::factory()->create([
            'name' => 'Other User',
            'username' => 'other_user',
            'email' => 'other@example.com',
            'bio' => 'Do not change me.',
            'location' => 'Seoul',
        ]);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Owner Updated',
            'username' => 'owner_updated',
            'email' => 'owner-updated@example.com',
            'bio' => 'Only my profile should change.',
            'location' => 'Cebu',
            'website' => 'https://cardflow.test/owner',
        ])->assertRedirect(route('profile.edit'));

        $user->refresh();
        $otherUser->refresh();

        $this->assertSame('Owner Updated', $user->name);
        $this->assertSame('owner_updated', $user->username);
        $this->assertSame('Only my profile should change.', $user->bio);

        $this->assertSame('Other User', $otherUser->name);
        $this->assertSame('other_user', $otherUser->username);
        $this->assertSame('other@example.com', $otherUser->email);
        $this->assertSame('Do not change me.', $otherUser->bio);
        $this->assertSame('Seoul', $otherUser->location);
    }
}
