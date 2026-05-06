<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceListing;
use App\Models\UserCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
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
    ->activeVisible()
    ->when($filter === 'my_listings', function ($query) use ($user) {
        $query->where('user_id', $user->id);
    })
    ->when($filter !== 'my_listings', function ($query) use ($user) {
        $query->where('user_id', '!=', $user->id);
    })
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
            ->when(! in_array($filter, ['all', 'my_listings'], true), function ($query) use ($filter) {
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
                'my_listings' => 'My listings',
                'trade' => 'Trade',
                'sale' => 'Sale',
                'rare_finds' => 'Rare finds',
                'price_range' => 'Price range',
],
            ],
        ]);
    }

    public function create(Request $request): View
{
    $userCards = $request->user()
        ->userCards()
        ->with('card')
        ->latest()
        ->get();

    return view('marketplace.create', [
        'userCards' => $userCards,
    ]);
}

public function store(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'user_card_id' => [
            'required',
            Rule::exists('user_cards', 'id')->where(function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            }),
        ],
        'listing_mode' => ['required', 'in:trade,sale,both'],
        'listing_price' => ['nullable', 'numeric', 'min:0'],
    ]);

    if (in_array($validated['listing_mode'], ['sale', 'both'], true) && blank($validated['listing_price'])) {
        return back()
            ->withErrors(['listing_price' => 'Please enter a price for sale listings.'])
            ->withInput();
    }

    DB::transaction(function () use ($request, $validated) {
        $userCard = UserCard::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($validated['user_card_id']);

        $userCard->is_for_trade = in_array($validated['listing_mode'], ['trade', 'both'], true);
        $userCard->is_for_sale = in_array($validated['listing_mode'], ['sale', 'both'], true);
        $userCard->listing_price = $validated['listing_price'] ?? null;
        $userCard->save();

        MarketplaceListing::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'user_card_id' => $userCard->id,
            ],
            [
                'status' => 'active',
                'is_visible' => true,
            ]
        );
    });

    return redirect()
        ->route('marketplace.index', ['filter' => 'my_listings'])
        ->with('status', 'Listing posted successfully.');
}

public function edit(Request $request, MarketplaceListing $marketplaceListing): View
{
    abort_unless($marketplaceListing->user_id === $request->user()->id, 403);

    $marketplaceListing->load(['userCard.card']);

    return view('marketplace.edit', [
        'listing' => $marketplaceListing,
        'userCard' => $marketplaceListing->userCard,
    ]);
}

public function update(Request $request, MarketplaceListing $marketplaceListing): RedirectResponse
{
    abort_unless($marketplaceListing->user_id === $request->user()->id, 403);

    $validated = $request->validate([
        'listing_mode' => ['required', 'in:trade,sale,both'],
        'listing_price' => ['nullable', 'numeric', 'min:0'],
    ]);

    if (in_array($validated['listing_mode'], ['sale', 'both'], true) && blank($validated['listing_price'])) {
        return back()
            ->withErrors(['listing_price' => 'Please enter a price for sale listings.'])
            ->withInput();
    }

    DB::transaction(function () use ($marketplaceListing, $validated) {
        $userCard = $marketplaceListing->userCard;

        $userCard->is_for_trade = in_array($validated['listing_mode'], ['trade', 'both'], true);
        $userCard->is_for_sale = in_array($validated['listing_mode'], ['sale', 'both'], true);
        $userCard->listing_price = in_array($validated['listing_mode'], ['sale', 'both'], true)
            ? $validated['listing_price']
            : null;
        $userCard->save();

        $marketplaceListing->forceFill([
            'status' => 'active',
            'is_visible' => true,
        ])->save();
    });

    return redirect()
        ->route('marketplace.index', ['filter' => 'my_listings'])
        ->with('status', 'Listing updated successfully.');
}

public function destroy(Request $request, MarketplaceListing $marketplaceListing): RedirectResponse
{
    abort_unless($marketplaceListing->user_id === $request->user()->id, 403);

    DB::transaction(function () use ($marketplaceListing) {
        $userCard = $marketplaceListing->userCard;

        if ($userCard) {
            $userCard->forceFill([
                'is_for_trade' => false,
                'is_for_sale' => false,
                'listing_price' => null,
            ])->save();
        }

        $marketplaceListing->forceFill([
            'status' => 'inactive',
            'is_visible' => false,
        ])->save();
    });

    return redirect()
        ->route('marketplace.index', ['filter' => 'my_listings'])
        ->with('status', 'Listing removed from marketplace.');
}
}
