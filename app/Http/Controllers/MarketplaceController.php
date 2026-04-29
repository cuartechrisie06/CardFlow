<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceListing;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketplaceController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $search = trim((string) $request->string('q'));
        $filter = (string) $request->string('filter', 'all');

        $listingsQuery = MarketplaceListing::query()
            ->with(['card', 'user', 'userCard'])
            ->where('user_id', '!=', $user->id)
            ->activeVisible()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->whereHas('card', function ($cardQuery) use ($search) {
                        $cardQuery->where('title', 'like', "%{$search}%")
                            ->orWhere('artist', 'like', "%{$search}%")
                            ->orWhere('album', 'like', "%{$search}%")
                            ->orWhere('edition', 'like', "%{$search}%")
                            ->orWhere('rarity', 'like', "%{$search}%");
                    })->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
                });
            })
            ->when($filter !== 'all', function ($query) use ($filter) {
                $query->where(function ($nested) use ($filter) {
                    if ($filter === 'trade') {
                        $nested->whereHas('userCard', fn ($query) => $query->where('is_for_trade', true));
                    }

                    if ($filter === 'sale') {
                        $nested->whereHas('userCard', fn ($query) => $query->where('is_for_sale', true));
                    }

                    if ($filter === 'rare_finds') {
                        $nested->whereHas('card', fn ($cardQuery) => $cardQuery->whereIn('rarity', ['Rare', 'Hot', 'Wishlist']));
                    }

                    if ($filter === 'price_range') {
                        $nested->whereHas('userCard', function ($priceQuery) {
                            $priceQuery->where('listing_price', '<=', 1500)
                                ->orWhereNull('listing_price');
                        });
                    }
                });
            });

        $featuredListings = $listingsQuery->latest('updated_at')->paginate(4)->withQueryString();

        $publicListingsBase = MarketplaceListing::query()
            ->where('user_id', '!=', $user->id)
            ->activeVisible();

        $marketMetrics = [
            'open_listings' => (clone $publicListingsBase)->count(),
            'open_trades' => (clone $publicListingsBase)->whereHas('userCard', fn ($query) => $query->where('is_for_trade', true))->count(),
            'sale_offers' => (clone $publicListingsBase)->whereHas('userCard', fn ($query) => $query->where('is_for_sale', true))->count(),
            'quick_actions' => (int) DB::table('users')
                ->join('marketplace_listings', 'users.id', '=', 'marketplace_listings.user_id')
                ->where('users.id', '!=', $user->id)
                ->where('marketplace_listings.status', 'active')
                ->where('marketplace_listings.is_visible', true)
                ->distinct('users.id')
                ->count('users.id'),
        ];

        return view('marketplace.index', [
            'marketMetrics' => $marketMetrics,
            'featuredListings' => $featuredListings,
            'filters' => [
                'search' => $search,
                'active' => $filter,
                'items' => [
                    'all' => 'All listings',
                    'trade' => 'Trade',
                    'sale' => 'Sale',
                    'rare_finds' => 'Rare finds',
                    'price_range' => 'Price range',
                ],
            ],
        ]);
    }
}
