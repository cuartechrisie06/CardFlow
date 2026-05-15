<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceListing;
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
