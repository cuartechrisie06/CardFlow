<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\MarketplaceListing;
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

        $pairQuery = Conversation::query()
            ->withValidParticipants()
            ->betweenParticipants($firstUserId, $secondUserId);

        $conversation = (clone $pairQuery)
            ->where('marketplace_listing_id', $listing->id)
            ->first();

        if (! $conversation) {
            $conversation = (clone $pairQuery)->first();
        }

        if (! $conversation) {
            $conversation = Conversation::query()->create([
                'user_one_id' => $firstUserId,
                'user_two_id' => $secondUserId,
                'marketplace_listing_id' => $listing->id,
            ]);
        } elseif (! $conversation->marketplace_listing_id && ! $conversation->messages()->exists()) {
            $conversation->forceFill([
                'marketplace_listing_id' => $listing->id,
            ])->save();
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
