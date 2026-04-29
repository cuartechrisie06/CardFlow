<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\WishlistItem;
use App\Services\WishlistMatchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(private WishlistMatchService $wishlistMatchService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $search = trim((string) $request->string('q'));

        $wishlistItems = WishlistItem::query()
            ->with('card')
            ->where('user_id', $user->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('card', function ($cardQuery) use ($search) {
                    $cardQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('artist', 'like', "%{$search}%")
                        ->orWhere('album', 'like', "%{$search}%");
                });
            })
            ->latest('matched_at')
            ->latest()
            ->get();

        $matchesByWishlist = $this->wishlistMatchService->buildMatchesForUser($user, $wishlistItems);

        $activeMatches = $wishlistItems
            ->filter(fn (WishlistItem $item) => $matchesByWishlist->get($item->id, collect())->isNotEmpty())
            ->values();

        return view('wishlist.index', [
            'wishlistItems' => $wishlistItems,
            'activeMatches' => $activeMatches,
            'matchesByWishlist' => $matchesByWishlist,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'artist' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'album' => ['nullable', 'string', 'max:255'],
            'priority' => ['required', 'in:high,medium,low'],
            'target_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $card = Card::query()->firstOrCreate(
            [
                'artist' => $validated['artist'],
                'title' => $validated['title'],
                'album' => $validated['album'] ?? null,
            ],
            [
                'edition' => 'Wishlist item',
                'rarity' => 'Wishlist',
                'market_value' => $validated['target_price'] ?? 0,
                'thumbnail_style' => 'market-thumb-three',
                'trend_score' => 70,
            ]
        );

        WishlistItem::query()->create([
            'user_id' => $request->user()->id,
            'card_id' => $card->id,
            'priority' => $validated['priority'],
            'target_price' => $validated['target_price'] ?? null,
            'matched_at' => null,
        ]);

        return redirect()->route('wishlist.index')
            ->with('status', 'Card added to your wishlist.');
    }

    public function destroy(Request $request, WishlistItem $wishlistItem): RedirectResponse
    {
        abort_unless($wishlistItem->user_id === $request->user()->id, 403);

        $wishlistItem->delete();

        return redirect()->route('wishlist.index')
            ->with('status', 'Wishlist item removed.');
    }
}
