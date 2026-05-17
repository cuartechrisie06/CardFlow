<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceListing;
use App\Models\TradeRequest;
use App\Models\UserCard;
use App\Models\WishlistItem;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TradeRequestController extends Controller
{
    public function __construct(private ActivityLogger $activityLogger)
    {
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'listing_id' => ['required', 'integer', 'exists:marketplace_listings,id'],
            'offered_card_id' => [
                'required',
                'integer',
                Rule::exists('user_cards', 'card_id')->where(fn ($query) => $query->where('user_id', $request->user()->id)),
            ],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $listing = MarketplaceListing::query()
            ->with(['card', 'user'])
            ->activeVisible()
            ->findOrFail($validated['listing_id']);

        abort_if($listing->user_id === $request->user()->id, 422, 'You cannot request a trade on your own listing.');

        $duplicate = TradeRequest::query()
            ->where('sender_id', $request->user()->id)
            ->where('listing_id', $listing->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->exists();

        if ($duplicate) {
            return back()->withErrors([
                'trade_request' => 'You already have an active trade request for this listing.',
            ]);
        }

        $tradeRequest = TradeRequest::query()->create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $listing->user_id,
            'listing_id' => $listing->id,
            'offered_card_id' => $validated['offered_card_id'],
            'status' => 'pending',
            'message' => $validated['message'] ?? null,
        ]);

        $offeredCard = UserCard::query()
            ->with('card')
            ->where('user_id', $request->user()->id)
            ->where('card_id', $validated['offered_card_id'])
            ->first();

        $this->activityLogger->record(
            $listing->user,
            'trade_request',
            sprintf('@%s wants to trade with you', $request->user()->username ?: $request->user()->name),
            sprintf(
                'Offered %s for %s.',
                $offeredCard?->card?->title ?: 'a collection card',
                $listing->card?->title ?: 'your listing'
            ),
            [
                'trade_request_id' => $tradeRequest->id,
                'listing_id' => $listing->id,
                'sender_id' => $request->user()->id,
                'offered_card_id' => $validated['offered_card_id'],
            ]
        );

        return back()->with('status', 'Trade request sent.');
    }

    public function accept(Request $request, TradeRequest $tradeRequest): RedirectResponse
    {
        $this->authorizeReceiver($request, $tradeRequest);

        abort_unless($tradeRequest->status === 'pending', 422, 'Only pending trade requests can be accepted.');

        $tradeRequest->forceFill(['status' => 'accepted'])->save();

        $this->activityLogger->record(
            $tradeRequest->sender,
            'trade_request',
            sprintf('@%s accepted your trade request', $request->user()->username ?: $request->user()->name),
            $this->activityBody($tradeRequest),
            ['trade_request_id' => $tradeRequest->id]
        );

        return back()->with('status', 'Trade request accepted.');
    }

    public function decline(Request $request, TradeRequest $tradeRequest): RedirectResponse
    {
        $this->authorizeReceiver($request, $tradeRequest);

        abort_unless($tradeRequest->status === 'pending', 422, 'Only pending trade requests can be declined.');

        $tradeRequest->forceFill(['status' => 'declined'])->save();

        $this->activityLogger->record(
            $tradeRequest->sender,
            'trade_request',
            sprintf('@%s declined your trade request', $request->user()->username ?: $request->user()->name),
            $this->activityBody($tradeRequest),
            ['trade_request_id' => $tradeRequest->id]
        );

        return back()->with('status', 'Trade request declined.');
    }

    public function complete(Request $request, TradeRequest $tradeRequest): RedirectResponse
    {
        abort_unless(
            in_array($request->user()->id, [$tradeRequest->sender_id, $tradeRequest->receiver_id], true),
            403
        );
        abort_unless($tradeRequest->status === 'accepted', 422, 'Only accepted trade requests can be completed.');

        DB::transaction(function () use ($tradeRequest) {
            $tradeRequest->loadMissing([
                'sender',
                'receiver',
                'listing.card',
                'listing.userCard',
                'offeredCard',
            ]);

            $tradeRequest->forceFill(['status' => 'completed'])->save();

            if ($tradeRequest->listing) {
                $this->closeListing($tradeRequest->listing, 'sold');
                $this->cancelCompetingRequests($tradeRequest->listing, $tradeRequest);
            }

            $offeredListing = MarketplaceListing::query()
                ->with('userCard')
                ->where('card_id', $tradeRequest->offered_card_id)
                ->where('user_id', $tradeRequest->sender_id)
                ->where('status', 'active')
                ->first();

            if ($offeredListing) {
                $this->closeListing($offeredListing, 'traded');
                $this->cancelCompetingRequests($offeredListing, $tradeRequest);
            }

            $this->removeFulfilledWishlistItem($tradeRequest->sender_id, $tradeRequest->listing?->card);
            $this->removeFulfilledWishlistItem($tradeRequest->receiver_id, $tradeRequest->offeredCard);
        });

        $tradeRequest->refresh()->loadMissing(['sender', 'receiver', 'listing.card', 'offeredCard']);

        foreach ([$tradeRequest->sender, $tradeRequest->receiver] as $participant) {
            $other = $participant->id === $tradeRequest->sender_id
                ? $tradeRequest->receiver
                : $tradeRequest->sender;

            $this->activityLogger->record(
                $participant,
                'trade_completed',
                sprintf('Completed a trade with @%s', $other->username ?: $other->name),
                $this->activityBody($tradeRequest),
                ['trade_request_id' => $tradeRequest->id]
            );
        }

        return back()->with('status', 'Trade marked as completed.');
    }

    public function cancel(Request $request, TradeRequest $tradeRequest): RedirectResponse
    {
        abort_unless($tradeRequest->sender_id === $request->user()->id, 403);
        abort_unless($tradeRequest->status === 'pending', 422, 'Only pending trade requests can be cancelled.');

        $tradeRequest->forceFill(['status' => 'cancelled'])->save();

        return back()->with('status', 'Trade request cancelled.');
    }

    private function authorizeReceiver(Request $request, TradeRequest $tradeRequest): void
    {
        abort_unless($tradeRequest->receiver_id === $request->user()->id, 403);
    }

    private function activityBody(TradeRequest $tradeRequest): string
    {
        $tradeRequest->loadMissing(['listing.card', 'offeredCard']);

        return sprintf(
            '%s for %s.',
            $tradeRequest->offeredCard?->title ?: 'Offered card',
            $tradeRequest->listing?->card?->title ?: 'marketplace listing'
        );
    }

    private function closeListing(MarketplaceListing $listing, string $status): void
    {
        if ($listing->userCard) {
            $listing->userCard->forceFill([
                'is_public' => false,
                'is_listed' => false,
                'marketplace_status' => $status,
                'is_for_trade' => false,
                'is_for_sale' => false,
            ])->save();
        }

        $listing->forceFill([
            'status' => $status,
            'is_visible' => false,
        ])->save();
    }

    private function cancelCompetingRequests(MarketplaceListing $listing, TradeRequest $completedTrade): void
    {
        TradeRequest::query()
            ->where('listing_id', $listing->id)
            ->where('id', '!=', $completedTrade->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->with('sender')
            ->get()
            ->each(function (TradeRequest $tradeRequest) use ($listing) {
                $tradeRequest->forceFill(['status' => 'cancelled'])->save();

                if ($tradeRequest->sender) {
                    $this->activityLogger->record(
                        $tradeRequest->sender,
                        'trade_cancelled',
                        'Trade request cancelled',
                        'The listing is no longer available.',
                        [
                            'trade_request_id' => $tradeRequest->id,
                            'listing_id' => $listing->id,
                        ]
                    );
                }
            });
    }

    private function removeFulfilledWishlistItem(int $userId, mixed $receivedCard): void
    {
        if (! $receivedCard) {
            return;
        }

        $deleted = WishlistItem::query()
            ->where('user_id', $userId)
            ->where(function ($query) use ($receivedCard) {
                $query->where('card_id', $receivedCard->id)
                    ->orWhereHas('card', function ($cardQuery) use ($receivedCard) {
                        $cardQuery
                            ->where('title', 'like', '%'.$receivedCard->title.'%')
                            ->orWhere('artist', 'like', '%'.$receivedCard->artist.'%');
                    });
            })
            ->delete();

        if ($deleted > 0) {
            $user = \App\Models\User::query()->find($userId);

            if ($user) {
                $this->activityLogger->record(
                    $user,
                    'wishlist_fulfilled',
                    sprintf('Wishlist fulfilled: %s', $receivedCard->title),
                    'Removed a wishlist item after a completed trade.',
                    ['card_id' => $receivedCard->id]
                );
            }
        }
    }
}
