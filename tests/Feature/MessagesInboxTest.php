<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MessagesInboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_real_users_appear_in_inbox(): void
    {
        $viewer = User::factory()->create(['username' => 'viewer_user']);
        $other = User::factory()->create(['username' => 'real_partner']);

        $conversation = Conversation::factory()->create([
            'user_one_id' => $viewer->id,
            'user_two_id' => $other->id,
        ]);

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $other->id,
            'receiver_id' => $viewer->id,
            'body' => 'Real message from a real user.',
        ]);

        $this->actingAs($viewer)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSeeText('@real_partner')
            ->assertSeeText('Real message from a real user.');
    }

    public function test_auth_user_cannot_see_unrelated_conversations(): void
    {
        $viewer = User::factory()->create(['username' => 'viewer_user']);
        $first = User::factory()->create(['username' => 'first_user']);
        $second = User::factory()->create(['username' => 'second_user']);

        $conversation = Conversation::factory()->create([
            'user_one_id' => $first->id,
            'user_two_id' => $second->id,
        ]);

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $first->id,
            'receiver_id' => $second->id,
            'body' => 'Private unrelated message.',
        ]);

        $this->actingAs($viewer)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertDontSeeText('@first_user')
            ->assertDontSeeText('Private unrelated message.');
    }

    public function test_fake_sample_chats_do_not_render(): void
    {
        $viewer = User::factory()->create();

        $this->actingAs($viewer)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertDontSeeText('@kpop_collector')
            ->assertDontSeeText('@aespa_stan')
            ->assertDontSeeText('Would you be willing to trade for my Lisa - Lalisa?');
    }

    public function test_messages_load_only_for_valid_conversations(): void
    {
        $viewer = User::factory()->create(['username' => 'viewer_user']);
        $other = User::factory()->create(['username' => 'trade_partner']);
        $conversation = Conversation::factory()->create([
            'user_one_id' => $viewer->id,
            'user_two_id' => $other->id,
        ]);

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $viewer->id,
            'receiver_id' => $other->id,
            'body' => 'Visible thread message.',
        ]);

        $outsiderOne = User::factory()->create();
        $outsiderTwo = User::factory()->create();
        $otherConversation = Conversation::factory()->create([
            'user_one_id' => $outsiderOne->id,
            'user_two_id' => $outsiderTwo->id,
        ]);

        Message::factory()->create([
            'conversation_id' => $otherConversation->id,
            'sender_id' => $outsiderOne->id,
            'receiver_id' => $outsiderTwo->id,
            'body' => 'Hidden outsider message.',
        ]);

        $this->actingAs($viewer)
            ->get(route('messages.index', ['conversation' => $conversation->id]))
            ->assertOk()
            ->assertSeeText('Visible thread message.')
            ->assertDontSeeText('Hidden outsider message.');
    }

    public function test_orphaned_messages_and_conversations_do_not_render(): void
    {
        $viewer = User::factory()->create(['username' => 'viewer_user']);
        $other = User::factory()->create(['username' => 'valid_partner']);
        $validConversation = Conversation::factory()->create([
            'user_one_id' => $viewer->id,
            'user_two_id' => $other->id,
        ]);

        Message::factory()->create([
            'conversation_id' => $validConversation->id,
            'sender_id' => $other->id,
            'receiver_id' => $viewer->id,
            'body' => 'Valid message.',
        ]);

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::table('conversations')->insert([
            'id' => 999,
            'user_one_id' => $viewer->id,
            'user_two_id' => 999999,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('messages')->insert([
            'conversation_id' => 999,
            'sender_id' => 999999,
            'receiver_id' => $viewer->id,
            'body' => 'Orphaned ghost message.',
            'message_type' => 'text',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::statement('PRAGMA foreign_keys = ON');

        $this->actingAs($viewer)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSeeText('@valid_partner')
            ->assertSeeText('Valid message.')
            ->assertDontSeeText('Orphaned ghost message.');
    }
}
