<?php

namespace Tests\Feature;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\MarketplaceListing;
use App\Models\Message;
use App\Models\User;
use App\Models\UserCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MessagesRealtimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_send_message_and_broadcast_event_is_dispatched(): void
    {
        Event::fake([MessageSent::class]);

        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $conversation = Conversation::factory()->create([
            'user_one_id' => $sender->id,
            'user_two_id' => $receiver->id,
        ]);

        $this->actingAs($sender)
            ->postJson(route('messages.store'), [
                'conversation_id' => $conversation->id,
                'body' => 'Realtime hello',
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'body' => 'Realtime hello',
        ]);

        Event::assertDispatched(MessageSent::class);
    }

    public function test_blade_send_form_creates_a_message_record(): void
    {
        Event::fake([MessageSent::class]);

        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $conversation = Conversation::factory()->create([
            'user_one_id' => min($sender->id, $receiver->id),
            'user_two_id' => max($sender->id, $receiver->id),
        ]);

        $this->actingAs($sender)
            ->post(route('messages.store'), [
                'conversation_id' => $conversation->id,
                'body' => 'Blade send works',
            ])
            ->assertRedirect(route('messages.index', [
                'conversation' => $conversation->id,
            ]));

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'body' => 'Blade send works',
        ]);
    }

    public function test_send_message_response_contains_event_payload_for_sender_ui_append(): void
    {
        Event::fake([MessageSent::class]);

        $sender = User::factory()->create();
        $receiver = User::factory()->create(['username' => 'receiver_user']);
        $conversation = Conversation::factory()->create([
            'user_one_id' => min($sender->id, $receiver->id),
            'user_two_id' => max($sender->id, $receiver->id),
        ]);

        $this->actingAs($sender)
            ->postJson(route('messages.store'), [
                'conversation_id' => $conversation->id,
                'body' => 'Append me instantly',
            ])
            ->assertOk()
            ->assertJsonPath('event.message.body', 'Append me instantly')
            ->assertJsonPath('event.conversation.id', $conversation->id)
            ->assertJsonPath('event.preview', 'Append me instantly')
            ->assertJsonPath('event.unread_counts.'.$sender->id, 0)
            ->assertJsonPath('event.unread_counts.'.$receiver->id, 1);
    }

    public function test_unrelated_user_cannot_send_message_into_other_conversation(): void
    {
        $ownerA = User::factory()->create();
        $ownerB = User::factory()->create();
        $outsider = User::factory()->create();

        $conversation = Conversation::factory()->create([
            'user_one_id' => $ownerA->id,
            'user_two_id' => $ownerB->id,
        ]);

        $this->actingAs($outsider)
            ->postJson(route('messages.store'), [
                'conversation_id' => $conversation->id,
                'body' => 'Intrusion',
            ])
            ->assertNotFound();
    }

    public function test_active_conversation_messages_are_marked_read_when_opened(): void
    {
        $viewer = User::factory()->create();
        $other = User::factory()->create();
        $conversation = Conversation::factory()->create([
            'user_one_id' => $viewer->id,
            'user_two_id' => $other->id,
        ]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $other->id,
            'receiver_id' => $viewer->id,
            'body' => 'Unread message',
            'read_at' => null,
        ]);

        $this->actingAs($viewer)
            ->get(route('messages.index', ['conversation' => $conversation->id]))
            ->assertOk();

        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_conversations_with_messages_render_their_message_history(): void
    {
        $viewer = User::factory()->create(['username' => 'viewer_user']);
        $other = User::factory()->create(['username' => 'other_user']);
        $conversation = Conversation::factory()->create([
            'user_one_id' => min($viewer->id, $other->id),
            'user_two_id' => max($viewer->id, $other->id),
        ]);

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $other->id,
            'receiver_id' => $viewer->id,
            'body' => 'First saved message',
            'created_at' => now()->subMinute(),
        ]);

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $viewer->id,
            'receiver_id' => $other->id,
            'body' => 'Second saved message',
            'created_at' => now(),
        ]);

        $this->actingAs($viewer)
            ->get(route('messages.index', ['conversation' => $conversation->id]))
            ->assertOk()
            ->assertSeeText('First saved message')
            ->assertSeeText('Second saved message')
            ->assertDontSeeText('No messages in this conversation yet.');
    }

    public function test_blade_send_removes_empty_state_and_shows_message_in_thread(): void
    {
        $sender = User::factory()->create(['username' => 'sender_user']);
        $receiver = User::factory()->create(['username' => 'receiver_user']);
        $conversation = Conversation::factory()->create([
            'user_one_id' => min($sender->id, $receiver->id),
            'user_two_id' => max($sender->id, $receiver->id),
        ]);

        $this->actingAs($sender)
            ->post(route('messages.store'), [
                'conversation_id' => $conversation->id,
                'body' => 'Visible in thread',
            ])
            ->assertRedirect(route('messages.index', [
                'conversation' => $conversation->id,
            ]));

        $this->actingAs($sender)
            ->get(route('messages.index', ['conversation' => $conversation->id]))
            ->assertOk()
            ->assertSeeText('Visible in thread')
            ->assertDontSeeText('No messages in this conversation yet.');
    }

    public function test_empty_state_only_appears_when_selected_conversation_has_zero_messages(): void
    {
        $viewer = User::factory()->create();
        $other = User::factory()->create();
        $conversation = Conversation::factory()->create([
            'user_one_id' => min($viewer->id, $other->id),
            'user_two_id' => max($viewer->id, $other->id),
        ]);

        $this->actingAs($viewer)
            ->get(route('messages.index', ['conversation' => $conversation->id]))
            ->assertOk()
            ->assertSeeText('No messages in this conversation yet.');
    }

    public function test_only_conversations_for_authenticated_user_are_listed_for_realtime_screen(): void
    {
        $viewer = User::factory()->create(['username' => 'viewer']);
        $other = User::factory()->create(['username' => 'partner']);
        $outsiderA = User::factory()->create(['username' => 'outsider_a']);
        $outsiderB = User::factory()->create(['username' => 'outsider_b']);

        $visibleConversation = Conversation::factory()->create([
            'user_one_id' => $viewer->id,
            'user_two_id' => $other->id,
        ]);

        $hiddenConversation = Conversation::factory()->create([
            'user_one_id' => $outsiderA->id,
            'user_two_id' => $outsiderB->id,
        ]);

        Message::factory()->create([
            'conversation_id' => $visibleConversation->id,
            'sender_id' => $other->id,
            'receiver_id' => $viewer->id,
            'body' => 'Visible message',
        ]);

        Message::factory()->create([
            'conversation_id' => $hiddenConversation->id,
            'sender_id' => $outsiderA->id,
            'receiver_id' => $outsiderB->id,
            'body' => 'Hidden message',
        ]);

        $this->actingAs($viewer)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSeeText('@partner')
            ->assertDontSee('data-conversation-link-id="'.$hiddenConversation->id.'"', false)
            ->assertDontSeeText('Hidden message');
    }

    public function test_new_message_button_no_longer_redirects_to_marketplace(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertDontSee('<a href="'.route('marketplace.index').'" class="dashboard-add-card">New message</a>', false)
            ->assertSee('data-compose-open', false);
    }

    public function test_user_can_start_conversation_with_existing_user(): void
    {
        Event::fake([MessageSent::class]);

        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $response = $this->actingAs($sender)
            ->postJson(route('messages.start'), [
                'recipient_id' => $recipient->id,
                'body' => 'Hello from compose flow',
            ])
            ->assertOk()
            ->assertJson(['ok' => true])
            ->assertJsonPath('event.message.body', 'Hello from compose flow');

        $conversationId = $response->json('conversation_id');

        $this->assertDatabaseHas('conversations', [
            'id' => $conversationId,
        ]);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversationId,
            'sender_id' => $sender->id,
            'receiver_id' => $recipient->id,
            'body' => 'Hello from compose flow',
        ]);
    }

    public function test_user_cannot_start_conversation_with_non_existent_user(): void
    {
        $sender = User::factory()->create();

        $this->actingAs($sender)
            ->postJson(route('messages.start'), [
                'recipient_id' => 999999,
                'body' => 'Hello ghost',
            ])
            ->assertStatus(422);
    }

    public function test_new_conversation_appears_in_inbox(): void
    {
        $sender = User::factory()->create(['username' => 'sender_user']);
        $recipient = User::factory()->create(['username' => 'recipient_user']);

        $this->actingAs($sender)
            ->postJson(route('messages.start'), [
                'recipient_id' => $recipient->id,
                'body' => 'Hello inbox',
            ])
            ->assertOk();

        $this->actingAs($sender)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSeeText('@recipient_user')
            ->assertSeeText('Hello inbox');
    }

    public function test_blade_send_updates_inbox_preview_to_latest_message(): void
    {
        $sender = User::factory()->create(['username' => 'sender_user']);
        $recipient = User::factory()->create(['username' => 'recipient_user']);
        $conversation = Conversation::factory()->create([
            'user_one_id' => min($sender->id, $recipient->id),
            'user_two_id' => max($sender->id, $recipient->id),
        ]);

        $this->actingAs($sender)
            ->post(route('messages.store'), [
                'conversation_id' => $conversation->id,
                'body' => 'Latest inbox preview',
            ])
            ->assertRedirect(route('messages.index', [
                'conversation' => $conversation->id,
            ]));

        $this->actingAs($sender)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSeeText('@recipient_user')
            ->assertSeeText('Latest inbox preview')
            ->assertDontSeeText('No messages yet.');
    }

    public function test_newly_created_conversation_with_first_message_displays_correctly(): void
    {
        $sender = User::factory()->create(['username' => 'sender_user']);
        $recipient = User::factory()->create(['username' => 'recipient_user']);

        $response = $this->actingAs($sender)
            ->postJson(route('messages.start'), [
                'recipient_id' => $recipient->id,
                'body' => 'This is the first chat message',
            ])
            ->assertOk();

        $conversationId = $response->json('conversation_id');

        $this->actingAs($sender)
            ->get(route('messages.index', ['conversation' => $conversationId]))
            ->assertOk()
            ->assertSeeText('This is the first chat message')
            ->assertDontSeeText('No messages in this conversation yet.');
    }

    public function test_marketplace_listing_detail_has_message_seller_button_for_sale_listing(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create(['username' => 'seller_user']);
        $userCard = UserCard::factory()->listed([
            'user_id' => $owner->id,
            'is_for_trade' => false,
            'is_for_sale' => true,
        ])->create();

        $listing = MarketplaceListing::factory()->create([
            'user_id' => $owner->id,
            'user_card_id' => $userCard->id,
            'card_id' => $userCard->card_id,
            'status' => 'active',
            'is_visible' => true,
        ]);

        $this->actingAs($viewer)
            ->get(route('marketplace.cards.show', $listing))
            ->assertOk()
            ->assertSeeText('Message seller')
            ->assertSee('action="'.route('messages.listings.store', $listing).'"', false);
    }

    public function test_clicking_message_seller_opens_seller_chat(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create([
            'name' => 'Seller User',
            'username' => 'seller_user',
        ]);
        $userCard = UserCard::factory()->listed([
            'user_id' => $owner->id,
            'is_for_trade' => true,
            'is_for_sale' => false,
        ])->create();

        $listing = MarketplaceListing::factory()->create([
            'user_id' => $owner->id,
            'user_card_id' => $userCard->id,
            'card_id' => $userCard->card_id,
            'status' => 'active',
            'is_visible' => true,
        ]);

        $this->actingAs($viewer)
            ->post(route('messages.listings.store', $listing))
            ->assertRedirect();

        $conversation = Conversation::query()->firstOrFail();

        $this->actingAs($viewer)
            ->get(route('messages.index', [
                'conversation' => $conversation->id,
                'listing' => $listing->id,
                'draft' => "Hi, I'm interested in your {$userCard->card->title} listing.",
            ]))
            ->assertOk()
            ->assertSeeText('@seller_user')
            ->assertSeeText($userCard->card->title)
            ->assertSee('value="Hi, I&#039;m interested in your '.$userCard->card->title.' listing."', false);
    }

    public function test_existing_conversation_is_reused_for_message_seller_flow(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $userCard = UserCard::factory()->listed([
            'user_id' => $seller->id,
            'is_for_sale' => true,
            'is_for_trade' => false,
        ])->create();
        $listing = MarketplaceListing::factory()->create([
            'user_id' => $seller->id,
            'user_card_id' => $userCard->id,
            'card_id' => $userCard->card_id,
            'status' => 'active',
            'is_visible' => true,
        ]);

        [$firstUserId, $secondUserId] = collect([$buyer->id, $seller->id])->sort()->values()->all();

        $conversation = Conversation::factory()->create([
            'user_one_id' => $firstUserId,
            'user_two_id' => $secondUserId,
            'marketplace_listing_id' => $listing->id,
        ]);

        $this->actingAs($buyer)
            ->post(route('messages.listings.store', $listing))
            ->assertRedirect(route('messages.index', [
                'conversation' => $conversation->id,
                'listing' => $listing->id,
                'draft' => "Hi, I'm interested in your {$userCard->card->title} listing.",
            ]));

        $this->assertDatabaseCount('conversations', 1);
    }

    public function test_new_conversation_is_created_if_missing_for_message_seller_flow(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $userCard = UserCard::factory()->listed([
            'user_id' => $seller->id,
            'is_for_sale' => true,
            'is_for_trade' => false,
        ])->create();
        $listing = MarketplaceListing::factory()->create([
            'user_id' => $seller->id,
            'user_card_id' => $userCard->id,
            'card_id' => $userCard->card_id,
            'status' => 'active',
            'is_visible' => true,
        ]);

        $this->actingAs($buyer)
            ->post(route('messages.listings.store', $listing))
            ->assertRedirect();

        $this->assertDatabaseHas('conversations', [
            'user_one_id' => min($buyer->id, $seller->id),
            'user_two_id' => max($buyer->id, $seller->id),
            'marketplace_listing_id' => $listing->id,
        ]);
    }

    public function test_users_cannot_message_themselves_on_their_own_listing(): void
    {
        $owner = User::factory()->create();
        $userCard = UserCard::factory()->listed([
            'user_id' => $owner->id,
            'is_for_sale' => true,
            'is_for_trade' => false,
        ])->create();
        $listing = MarketplaceListing::factory()->create([
            'user_id' => $owner->id,
            'user_card_id' => $userCard->id,
            'card_id' => $userCard->card_id,
            'status' => 'active',
            'is_visible' => true,
        ]);

        $this->actingAs($owner)
            ->post(route('messages.listings.store', $listing))
            ->assertForbidden();

        $this->assertDatabaseCount('conversations', 0);
    }

    public function test_only_valid_participants_can_access_a_listing_conversation(): void
    {
        $buyer = User::factory()->create(['username' => 'buyer_user']);
        $seller = User::factory()->create(['username' => 'seller_user']);
        $outsider = User::factory()->create(['username' => 'outsider_user']);
        $userCard = UserCard::factory()->listed([
            'user_id' => $seller->id,
            'is_for_sale' => true,
            'is_for_trade' => false,
        ])->create();
        $listing = MarketplaceListing::factory()->create([
            'user_id' => $seller->id,
            'user_card_id' => $userCard->id,
            'card_id' => $userCard->card_id,
            'status' => 'active',
            'is_visible' => true,
        ]);

        $conversation = Conversation::factory()->create([
            'user_one_id' => min($buyer->id, $seller->id),
            'user_two_id' => max($buyer->id, $seller->id),
            'marketplace_listing_id' => $listing->id,
        ]);

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $seller->id,
            'receiver_id' => $buyer->id,
            'body' => 'Listing conversation',
        ]);

        $this->actingAs($outsider)
            ->get(route('messages.index', ['conversation' => $conversation->id]))
            ->assertOk()
            ->assertSeeText('No chat selected yet.')
            ->assertDontSee('data-conversation-link-id="'.$conversation->id.'"', false)
            ->assertDontSeeText('Listing conversation');
    }

    public function test_non_participants_do_not_receive_conversation_channel_auth_signature(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $outsider = User::factory()->create();
        $conversation = Conversation::factory()->create([
            'user_one_id' => min($buyer->id, $seller->id),
            'user_two_id' => max($buyer->id, $seller->id),
        ]);

        $response = $this->actingAs($outsider)
            ->post('/broadcasting/auth', [
                'socket_id' => '1234.5678',
                'channel_name' => 'presence-conversation.'.$conversation->id,
            ])
            ->assertOk();

        $this->assertStringNotContainsString('"auth"', $response->getContent());
    }

    public function test_participant_can_authenticate_to_inbox_channel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/broadcasting/auth', [
                'socket_id' => '1234.5678',
                'channel_name' => 'private-users.'.$user->id.'.inbox',
            ])
            ->assertOk();
    }
}
