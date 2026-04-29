<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\MarketplaceListing;
use App\Models\SavedView;
use App\Models\Trade;
use App\Models\WishlistItem;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExplorerController extends Controller
{
    protected array $girlGroups = [
        'BLACKPINK',
        'Aespa',
        'NewJeans',
        'IVE',
        'Le Sserafim',
        'Itzy',
        'NMIXX',
        'Twice',
        'Red Velvet',
        '(G)I-DLE',
        'ILLIT',
        'BABYMONSTER',
    ];

    protected array $fourthGenGroups = [
        'Aespa',
        'IVE',
        'Le Sserafim',
        'Itzy',
        'NMIXX',
        'ILLIT',
        'BABYMONSTER',
    ];

    public function __invoke(Request $request): View
    {
        $search = trim((string) $request->string('q'));
        $filter = (string) $request->string('filter', 'by_group');

        $cardsQuery = $this->filteredCardsQuery($search, $filter);
        $catalogs = $this->catalogMetrics((clone $cardsQuery)->get());

        $averageTradeValue = (float) MarketplaceListing::query()
            ->activeVisible()
            ->whereHas('card', fn (Builder $query) => $this->applySearchAndFilter($query, $search, $filter))
            ->join('user_cards', 'user_cards.id', '=', 'marketplace_listings.user_card_id')
            ->selectRaw('AVG(COALESCE(user_cards.listing_price, user_cards.estimated_value, 0)) as average_value')
            ->value('average_value');

        $hottestTrend = $catalogs
            ->sortByDesc(fn (array $catalog) => [$catalog['wishlist_count'], $catalog['listing_count'], $catalog['trade_count']])
            ->first();

        $featuredCatalogs = $catalogs
            ->sortByDesc(fn (array $catalog) => [$catalog['wishlist_count'], $catalog['listing_count'], $catalog['average_value']])
            ->take(3)
            ->values()
            ->map(function (array $catalog, int $index) {
                $styles = ['market-thumb-one', 'market-thumb-two', 'market-thumb-three'];

                return $catalog + [
                    'style' => $styles[$index % count($styles)],
                    'blurb' => $this->catalogBlurb($catalog),
                ];
            });

        $categoryBars = $this->buildCategoryDemandBars(clone $cardsQuery);
        $quickPicks = $this->buildQuickPicks(clone $cardsQuery);

        return view('explorer.index', [
            'search' => $search,
            'filters' => [
                'active' => $filter,
                'items' => [
                    'by_group' => 'By Group',
                    'by_idol' => 'By Idol',
                    'girl_groups' => 'Girl Groups',
                    '4th_gen' => '4th Gen',
                    'high_value' => 'High Value',
                ],
            ],
            'metrics' => [
                'groups_indexed' => $catalogs->count(),
                'total_cards' => (clone $cardsQuery)->count(),
                'average_trade_value' => round($averageTradeValue),
                'hottest_artist' => $hottestTrend['artist'] ?? 'No data yet',
            ],
            'featuredCatalogs' => $featuredCatalogs,
            'categoryBars' => $categoryBars,
            'quickPicks' => $quickPicks,
            'saveViewAction' => route('explorer.saved-views.store'),
        ]);
    }

    public function show(Request $request, string $catalog): View
    {
        $search = trim((string) $request->string('q'));
        $filter = (string) $request->string('filter', 'by_group');
        $artist = $this->resolveArtistFromSlug($catalog);

        abort_if($artist === null, 404);

        $cardsQuery = $this->filteredCardsQuery($search, $filter)->where('artist', $artist);

        $cards = (clone $cardsQuery)
            ->withCount([
                'wishlistItems',
                'marketplaceListings as active_listings_count' => fn (Builder $query) => $query->activeVisible(),
            ])
            ->orderBy('album')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $metrics = [
            'artist' => $artist,
            'idol_count' => (clone $cardsQuery)->select('title')->distinct()->count('title'),
            'era_count' => (clone $cardsQuery)->select(DB::raw("COALESCE(NULLIF(album, ''), COALESCE(NULLIF(edition, ''), 'Standalone')) as era"))->distinct()->count(),
            'card_count' => (clone $cardsQuery)->count(),
            'average_value' => round((float) MarketplaceListing::query()
                ->activeVisible()
                ->whereHas('card', fn (Builder $query) => $query->where('artist', $artist))
                ->join('user_cards', 'user_cards.id', '=', 'marketplace_listings.user_card_id')
                ->selectRaw('AVG(COALESCE(user_cards.listing_price, user_cards.estimated_value, 0)) as average_value')
                ->value('average_value')),
            'active_wishlists' => WishlistItem::query()
                ->whereHas('card', fn (Builder $query) => $query->where('artist', $artist))
                ->count(),
            'marketplace_listings' => MarketplaceListing::query()
                ->activeVisible()
                ->whereHas('card', fn (Builder $query) => $query->where('artist', $artist))
                ->count(),
        ];

        $eras = (clone $cardsQuery)
            ->selectRaw("COALESCE(NULLIF(album, ''), COALESCE(NULLIF(edition, ''), 'Standalone')) as era")
            ->distinct()
            ->orderBy('era')
            ->pluck('era');

        return view('explorer.show', [
            'search' => $search,
            'filters' => [
                'active' => $filter,
                'items' => [
                    'by_group' => 'By Group',
                    'by_idol' => 'By Idol',
                    'girl_groups' => 'Girl Groups',
                    '4th_gen' => '4th Gen',
                    'high_value' => 'High Value',
                ],
            ],
            'catalog' => $metrics,
            'eras' => $eras,
            'cards' => $cards,
        ]);
    }

    public function storeSavedView(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'filter' => ['nullable', 'string', 'max:50'],
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $filter = (string) ($validated['filter'] ?? 'by_group');

        SavedView::query()->create([
            'user_id' => $request->user()->id,
            'page' => 'explorer',
            'name' => $this->savedViewName($search, $filter),
            'search' => $search !== '' ? $search : null,
            'filters' => ['filter' => $filter],
        ]);

        return redirect()
            ->route('explorer.index', array_filter([
                'q' => $search !== '' ? $search : null,
                'filter' => $filter,
            ]))
            ->with('status', 'Explorer view saved.');
    }

    protected function filteredCardsQuery(string $search, string $filter): Builder
    {
        $query = Card::query();

        $this->applySearchAndFilter($query, $search, $filter);

        return $query;
    }

    protected function applySearchAndFilter(Builder $query, string $search, string $filter): Builder
    {
        if ($search !== '') {
            $query->where(function (Builder $nested) use ($search) {
                $nested->where('artist', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('album', 'like', "%{$search}%")
                    ->orWhere('edition', 'like', "%{$search}%")
                    ->orWhere('rarity', 'like', "%{$search}%");
            });
        }

        return match ($filter) {
            'by_idol' => $query->orderBy('title')->orderBy('artist'),
            'girl_groups' => $query->whereIn('artist', $this->girlGroups)->orderBy('artist')->orderBy('title'),
            '4th_gen' => $query->whereIn('artist', $this->fourthGenGroups)->orderBy('artist')->orderBy('title'),
            'high_value' => $query->where(function (Builder $nested) {
                $nested->where('market_value', '>=', 1500)
                    ->orWhereHas('marketplaceListings', fn (Builder $listingQuery) => $listingQuery->activeVisible()->whereHas('userCard', fn (Builder $userCardQuery) => $userCardQuery->where('listing_price', '>=', 1500)));
            })->orderByDesc('market_value')->orderBy('artist'),
            default => $query->orderBy('artist')->orderBy('title'),
        };
    }

    protected function catalogMetrics(Collection $cards): Collection
    {
        $cards = $cards->groupBy('artist');

        $wishlistCounts = WishlistItem::query()
            ->join('cards', 'cards.id', '=', 'wishlist_items.card_id')
            ->selectRaw('cards.artist, COUNT(*) as total')
            ->groupBy('cards.artist')
            ->pluck('total', 'artist');

        $listingCounts = MarketplaceListing::query()
            ->activeVisible()
            ->join('cards', 'cards.id', '=', 'marketplace_listings.card_id')
            ->selectRaw('cards.artist, COUNT(*) as total')
            ->groupBy('cards.artist')
            ->pluck('total', 'artist');

        $tradeCounts = Trade::query()
            ->join('cards', 'cards.id', '=', 'trades.card_id')
            ->selectRaw('cards.artist, COUNT(*) as total')
            ->groupBy('cards.artist')
            ->pluck('total', 'artist');

        $listingAverages = MarketplaceListing::query()
            ->activeVisible()
            ->join('cards', 'cards.id', '=', 'marketplace_listings.card_id')
            ->join('user_cards', 'user_cards.id', '=', 'marketplace_listings.user_card_id')
            ->selectRaw('cards.artist, AVG(COALESCE(user_cards.listing_price, user_cards.estimated_value, 0)) as average_value')
            ->groupBy('cards.artist')
            ->pluck('average_value', 'artist');

        return $cards->map(function (Collection $artistCards, string $artist) use ($wishlistCounts, $listingCounts, $tradeCounts, $listingAverages) {
            $eras = $artistCards
                ->map(fn (Card $card) => $card->album ?: ($card->edition ?: null))
                ->filter()
                ->unique()
                ->count();

            return [
                'artist' => $artist,
                'slug' => Str::slug($artist),
                'total_cards' => $artistCards->count(),
                'idol_count' => $artistCards->pluck('title')->unique()->count(),
                'era_count' => $eras,
                'wishlist_count' => (int) ($wishlistCounts[$artist] ?? 0),
                'listing_count' => (int) ($listingCounts[$artist] ?? 0),
                'trade_count' => (int) ($tradeCounts[$artist] ?? 0),
                'average_value' => round((float) ($listingAverages[$artist] ?? $artistCards->avg('market_value') ?? 0)),
            ];
        })->values();
    }

    protected function buildCategoryDemandBars(Builder $cardsQuery): Collection
    {
        $cards = (clone $cardsQuery)
            ->withCount([
                'wishlistItems',
                'marketplaceListings as active_listings_count' => fn (Builder $query) => $query->activeVisible(),
                'trades',
            ])
            ->get();

        $grouped = $cards->groupBy(fn (Card $card) => $card->rarity ?: 'Standard')
            ->map(function (Collection $groupCards, string $label) {
                return [
                    'label' => $label,
                    'total' => (int) $groupCards->sum(fn (Card $card) => $card->wishlist_items_count + $card->active_listings_count + $card->trades_count),
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();

        $max = max(1, (int) $grouped->max('total'));

        return $grouped->map(fn (array $item) => $item + [
            'height' => max(18, (int) round(($item['total'] / $max) * 100)),
        ]);
    }

    protected function buildQuickPicks(Builder $cardsQuery): Collection
    {
        $cards = (clone $cardsQuery)
            ->withCount([
                'wishlistItems',
                'marketplaceListings as active_listings_count' => fn (Builder $query) => $query->activeVisible(),
            ])
            ->get();

        $bestValue = $cards->groupBy('artist')
            ->map(fn (Collection $artistCards, string $artist) => [
                'label' => 'Best value catalog',
                'title' => $artist,
                'subtitle' => 'Average listing value',
                'meta' => 'PHP '.number_format((float) $artistCards->avg('market_value'), 0),
            ])
            ->sortByDesc(fn (array $item) => (int) preg_replace('/\D/', '', $item['meta']))
            ->first();

        $newlyActive = $cards
            ->sortByDesc(fn (Card $card) => optional($card->released_on)?->timestamp ?? 0)
            ->first();

        $trending = $cards
            ->sortByDesc(fn (Card $card) => $card->wishlist_items_count + $card->active_listings_count)
            ->first();

        return collect(array_filter([
            $bestValue,
            $newlyActive ? [
                'label' => 'Newly active era',
                'title' => $newlyActive->album ?: $newlyActive->edition ?: $newlyActive->title,
                'subtitle' => $newlyActive->artist,
                'meta' => $newlyActive->released_on?->format('M j, Y') ?? 'Current release',
            ] : null,
            $trending ? [
                'label' => 'Trending card',
                'title' => $trending->title,
                'subtitle' => $trending->artist,
                'meta' => $trending->wishlist_items_count.' wishlists • '.$trending->active_listings_count.' listings',
            ] : null,
        ]));
    }

    protected function savedViewName(string $search, string $filter): string
    {
        $filterName = [
            'by_group' => 'By Group',
            'by_idol' => 'By Idol',
            'girl_groups' => 'Girl Groups',
            '4th_gen' => '4th Gen',
            'high_value' => 'High Value',
        ][$filter] ?? 'Explorer';

        return $search !== ''
            ? "Explorer: {$search} ({$filterName})"
            : "Explorer: {$filterName}";
    }

    protected function resolveArtistFromSlug(string $catalog): ?string
    {
        return Card::query()
            ->select('artist')
            ->distinct()
            ->get()
            ->first(fn ($card) => Str::slug($card->artist) === $catalog)
            ?->artist;
    }

    protected function catalogBlurb(array $catalog): string
    {
        if ($catalog['wishlist_count'] > 0 && $catalog['listing_count'] > 0) {
            return 'Strong wishlist demand backed by active marketplace supply.';
        }

        if ($catalog['listing_count'] > 0) {
            return 'Visible marketplace activity with active collector pricing.';
        }

        if ($catalog['trade_count'] > 0) {
            return 'Trade activity is carrying this catalog right now.';
        }

        return 'Catalog coverage built from real artist and photocard records.';
    }
}
