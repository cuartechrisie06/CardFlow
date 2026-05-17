<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\MarketplaceListing;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OpenMarketplaceConversationController extends Controller
{
    public function store(Request $request, MarketplaceListing $marketplaceListing): RedirectResponse
    {
        $buyer = $request->user();

        $listing = MarketplaceListing::query()
            ->with(['card', 'user', 'userCard'])
            ->activeVisible()
            ->whereKey($marketplaceListing->id)
            ->firstOrFail();

        abort_if($buyer->id === $listing->user_id, 403, 'You cannot message yourself about your own listing.');

        [$firstUserId, $secondUserId] = collect([$buyer->id, $listing->user_id])->sort()->values()->all();

        $conversation = Conversation::query()
            ->withValidParticipants()
            ->betweenParticipants($firstUserId, $secondUserId)
            ->withCount('messages')
            ->orderByDesc('messages_count')
            ->latest('updated_at')
            ->first();

        $createdConversation = false;

        if (! $conversation) {
            $conversation = Conversation::query()->create([
                'user_one_id' => $firstUserId,
                'user_two_id' => $secondUserId,
                'marketplace_listing_id' => $listing->id,
            ]);
            $createdConversation = true;
        } elseif (! $conversation->marketplace_listing_id) {
            $conversation->forceFill([
                'marketplace_listing_id' => $listing->id,
            ])->save();
        }

        if ($createdConversation) {
            app(ActivityLogger::class)->record(
                $listing->user,
                'trade_request',
                sprintf('@%s wants to trade with you', $buyer->username ?: $buyer->name),
                sprintf('Started a conversation about %s.', $listing->card?->title ?: 'your listing'),
                [
                    'conversation_id' => $conversation->id,
                    'listing_id' => $listing->id,
                    'sender_id' => $buyer->id,
                ]
            );
        }

        $draftMessage = sprintf(
            "Hi, I'm interested in your %s listing.",
            $listing->card?->title ?: 'photocard'
        );

        return redirect()->route('messages.index', [
            'conversation' => $conversation->id,
            'listing' => $listing->id,
            'draft' => $draftMessage,
        ]);
    }
}
