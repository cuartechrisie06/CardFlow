<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceListing;
use App\Models\TradeRequest;
use App\Models\UserCard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarketplaceCardController extends Controller
{
    public function show(Request $request, MarketplaceListing $marketplaceListing): View
    {
        $viewer = $request->user();

        $listing = MarketplaceListing::query()
            ->with([
                'card',
                'user' => fn ($query) => $query->withCount([
                    'marketplaceListings as active_listings_count' => fn ($listingQuery) => $listingQuery->activeVisible(),
                    'trades as completed_trades_count' => fn ($tradeQuery) => $tradeQuery->where(function ($nested) {
                        $nested->whereNotNull('completed_at')
                            ->orWhere('status', 'completed');
                    }),
                ]),
                'userCard',
            ])
            ->activeVisible()
            ->whereKey($marketplaceListing->id)
            ->firstOrFail();

        $userCard = $listing->userCard;
        $owner = $listing->user;
        $photoUrl = $this->publicStorageUrl($userCard->photo_path);
        $marketValue = (float) ($userCard->card->market_value ?? 0);
        $purchasePrice = (float) ($userCard->purchase_price ?? 0);
        $estimatedValue = (float) ($userCard->estimated_value ?? $marketValue);
        $valueDelta = $estimatedValue - $purchasePrice;
        $isPositiveDelta = $valueDelta >= 0;
        $myTradeCards = $viewer->id === $owner->id
            ? collect()
            : UserCard::query()
                ->with('card')
                ->where('user_id', $viewer->id)
                ->latest('updated_at')
                ->get();
        $tradeRequests = TradeRequest::query()
            ->with(['sender', 'receiver', 'offeredCard'])
            ->where('listing_id', $listing->id)
            ->where(function ($query) use ($viewer) {
                $query->where('sender_id', $viewer->id)
                    ->orWhere('receiver_id', $viewer->id);
            })
            ->latest()
            ->get();

        return view('marketplace.show', [
            'listing' => $listing,
            'userCard' => $userCard,
            'owner' => $owner,
            'viewer' => $viewer,
            'photoUrl' => $photoUrl,
            'marketValue' => $marketValue,
            'purchasePrice' => $purchasePrice,
            'estimatedValue' => $estimatedValue,
            'valueDelta' => $valueDelta,
            'isPositiveDelta' => $isPositiveDelta,
            'myTradeCards' => $myTradeCards,
            'tradeRequests' => $tradeRequests,
        ]);
    }

    private function publicStorageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $normalizedPath = str_replace('\\', '/', $path);
        $normalizedPath = preg_replace('#^.*storage/app/public/#', '', $normalizedPath) ?: $normalizedPath;
        $normalizedPath = preg_replace('#^.*public/storage/#', '', $normalizedPath) ?: $normalizedPath;
        $normalizedPath = preg_replace('#^/?storage/#', '', $normalizedPath) ?: $normalizedPath;
        $normalizedPath = ltrim($normalizedPath, '/');

        return Storage::disk('public')->exists($normalizedPath)
            ? Storage::url($normalizedPath)
            : null;
    }
}
