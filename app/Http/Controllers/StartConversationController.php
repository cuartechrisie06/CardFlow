<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\MarketplaceListing;
use App\Models\Message;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StartConversationController extends Controller
{
    public function create(Request $request): RedirectResponse
    {
        return redirect()->route('messages.index', [
            'compose' => 1,
            'recipient' => $request->integer('recipient_id') ?: null,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'recipient_id' => ['required', 'integer', 'exists:users,id'],
            'listing_id' => ['nullable', 'integer', 'exists:marketplace_listings,id'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $sender = $request->user();

        abort_if(
            $sender->id === (int) $validated['recipient_id'],
            422,
            'You cannot message yourself.'
        );

        $recipient = User::query()->findOrFail($validated['recipient_id']);

        $listing = null;

        if (! empty($validated['listing_id'])) {
            $listing = MarketplaceListing::query()
                ->activeVisible()
                ->with(['card', 'user'])
                ->findOrFail($validated['listing_id']);

            abort_unless(
                $listing->user_id === $recipient->id,
                422,
                'Selected listing does not belong to the chosen recipient.'
            );
        }

        [$firstUserId, $secondUserId] = collect([$sender->id, $recipient->id])
            ->sort()
            ->values()
            ->all();

        [$conversation, $message] = DB::transaction(function () use ($sender, $recipient, $firstUserId, $secondUserId, $validated, $listing) {
            $conversation = Conversation::query()
                ->withValidParticipants()
                ->betweenParticipants($firstUserId, $secondUserId)
                ->withCount('messages')
                ->orderByDesc('messages_count')
                ->latest('updated_at')
                ->first();

            if (! $conversation) {
                $conversation = Conversation::query()->create([
                    'user_one_id' => $firstUserId,
                    'user_two_id' => $secondUserId,
                    'marketplace_listing_id' => $listing?->id,
                ]);
            } elseif ($listing && ! $conversation->marketplace_listing_id) {
                $conversation->forceFill([
                    'marketplace_listing_id' => $listing->id,
                ])->save();
            }

            $message = Message::query()->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'receiver_id' => $recipient->id,
                'body' => trim($validated['body']),
                'message_type' => 'text',
            ]);

            $conversation->touch();

            return [$conversation, $message];
        });

        $unreadCounts = [
            (string) $sender->id => 0,
            (string) $recipient->id => Message::query()
                ->where('conversation_id', $conversation->id)
                ->where('receiver_id', $recipient->id)
                ->whereNull('read_at')
                ->count(),
        ];

        $event = new MessageSent($message, $unreadCounts);

        broadcast($event);

        app(ActivityLogger::class)->record(
            $recipient,
            'new_message',
            sprintf('@%s sent you a message', $sender->username ?: $sender->name),
            trim($message->body),
            [
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'sender_id' => $sender->id,
                'listing_id' => $listing?->id,
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'event' => $event->payload(),
            ]);
        }

        return redirect()
            ->route('messages.index', ['conversation' => $conversation->id])
            ->with('status', 'Message sent successfully.');
    }
}
