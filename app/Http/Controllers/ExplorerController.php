<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Card;
use App\Models\CardVariant;
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
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));
        $tab = $request->get('tab', 'idols');
        $tab = in_array($tab, ['idols', 'groups'], true) ? $tab : 'idols';

        $results = $tab === 'groups'
            ? \App\Models\KpopGroup::query()
                ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
                ->orderBy('name')
                ->paginate(24)
                ->withQueryString()
            : \App\Models\KpopIdol::query()
                ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $nested) use ($search) {
                    $nested->where('stage_name', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('korean_name', 'like', "%{$search}%")
                        ->orWhere('group_name', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%");
                }))
                ->orderBy('stage_name')
                ->paginate(24)
                ->withQueryString();

        return view('explorer.index', compact('results', 'search', 'tab'));
    }

    public function show(Request $request, string $catalog): View
    {
        $search = trim((string) $request->string('q'));
        $filter = (string) $request->string('filter', 'by_group');
        $artistRecord = $this->resolveArtistRecordFromSlug($catalog);
        $artist = $artistRecord?->name ?? $this->resolveArtistFromSlug($catalog);

        abort_if($artist === null, 404);

        $cardsQuery = $this->catalogCardsQuery($artist, $artistRecord, $search, $filter);

        $cards = (clone $cardsQuery)
            ->withCount([
                'wishlistItems',
                'marketplaceListings as active_listings_count' => fn (Builder $query) => $query->activeVisible(),
            ])
            ->with(['variants' => fn (Builder $query) => $query->orderByDesc('community_owned_count')->orderByDesc('average_trade_value')])
            ->orderBy('album')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $variantStatsQuery = CardVariant::query()
            ->whereHas('card', fn (Builder $query) => $this->constrainCatalogCards($query, $artist, $artistRecord));

        $averageTradeValue = (float) $variantStatsQuery->avg('average_trade_value');
        $averageListingValue = (float) MarketplaceListing::query()
            ->activeVisible()
            ->whereHas('card', fn (Builder $query) => $this->constrainCatalogCards($query, $artist, $artistRecord))
            ->join('user_cards', 'user_cards.id', '=', 'marketplace_listings.user_card_id')
            ->selectRaw('AVG(COALESCE(user_cards.listing_price, user_cards.estimated_value, 0)) as average_value')
            ->value('average_value');

        $metrics = [
            'artist' => $artist,
            'agency' => $artistRecord?->agency,
            'debut_date' => $artistRecord?->debut_date?->format('Y-m-d'),
            'aliases' => array_values(array_filter((array) ($artistRecord?->aliases ?? []))),
            'alias_count' => count((array) ($artistRecord?->aliases ?? [])),
            'idol_count' => (clone $cardsQuery)->select('title')->distinct()->count('title'),
            'era_count' => (clone $cardsQuery)->select(DB::raw("COALESCE(NULLIF(album, ''), COALESCE(NULLIF(edition, ''), 'Standalone')) as era"))->distinct()->count(),
            'card_count' => (clone $cardsQuery)->count(),
            'variant_count' => (clone $variantStatsQuery)->count(),
            'community_owned_count' => (int) (clone $variantStatsQuery)->sum('community_owned_count'),
            'community_listed_count' => (int) (clone $variantStatsQuery)->sum('community_listed_count'),
            'average_trade_value' => round($averageTradeValue > 0 ? $averageTradeValue : $averageListingValue),
            'average_value' => round($averageTradeValue > 0 ? $averageTradeValue : $averageListingValue),
            'active_wishlists' => WishlistItem::query()
                ->whereHas('card', fn (Builder $query) => $this->constrainCatalogCards($query, $artist, $artistRecord))
                ->count(),
            'marketplace_listings' => MarketplaceListing::query()
                ->activeVisible()
                ->whereHas('card', fn (Builder $query) => $this->constrainCatalogCards($query, $artist, $artistRecord))
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
        $query = Card::query()
            ->whereExists(function ($subQuery) {
                $subQuery->selectRaw(1)
                    ->from('user_cards')
                    ->whereColumn('user_cards.card_id', 'cards.id');
            });

        $this->applySearchAndFilter($query, $search, $filter);

        return $query;
    }

    protected function applySearchAndFilter(Builder $query, string $search, string $filter): Builder
    {
        if ($search !== '') {
            $query->where(function (Builder $nested) use ($search) {
                $nested->where('artist', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('member_name', 'like', "%{$search}%")
                    ->orWhere('album', 'like', "%{$search}%")
                    ->orWhere('edition', 'like', "%{$search}%")
                    ->orWhere('rarity', 'like', "%{$search}%")
                    ->orWhere('variant_type', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhereHas('aliases', fn (Builder $aliasQuery) => $aliasQuery->where('alias', 'like', "%{$search}%"));
            });
        }

        return match ($filter) {
            'by_idol' => $query->orderBy('title')->orderBy('artist'),
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

    protected function buildCategoryDemandBars(Collection $cards): Collection
    {
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

    protected function buildQuickPicks(Collection $cards): Collection
    {
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

    protected function resolveArtistRecordFromSlug(string $catalog): ?Artist
    {
        $needle = Str::slug($catalog);

        return Artist::query()
            ->get()
            ->first(function (Artist $artist) use ($needle) {
                $candidates = array_filter([
                    $artist->slug,
                    $artist->name,
                    $artist->name_original,
                ]);

                foreach ($candidates as $candidate) {
                    if (Str::slug((string) $candidate) === $needle) {
                        return true;
                    }
                }

                foreach ((array) ($artist->aliases ?? []) as $alias) {
                    if (Str::slug((string) $alias) === $needle) {
                        return true;
                    }
                }

                return false;
            });
    }

    protected function catalogCardsQuery(string $artist, ?Artist $artistRecord, string $search, string $filter): Builder
    {
        $query = $this->filteredCardsQuery($search, $filter)->where(function (Builder $nested) use ($artist, $artistRecord) {
            $nested->where('artist', $artist);

            if ($artistRecord?->exists) {
                $nested->orWhere('artist_id', $artistRecord->id);

                if ($artistRecord->name_original && $artistRecord->name_original !== $artist) {
                    $nested->orWhere('artist', $artistRecord->name_original);
                }
            }
        });

        return $query;
    }

    protected function constrainCatalogCards(Builder $query, string $artist, ?Artist $artistRecord): Builder
    {
        return $query->where(function (Builder $nested) use ($artist, $artistRecord) {
            $nested->where('artist', $artist);

            if ($artistRecord?->exists) {
                $nested->orWhere('artist_id', $artistRecord->id);

                if ($artistRecord->name_original && $artistRecord->name_original !== $artist) {
                    $nested->orWhere('artist', $artistRecord->name_original);
                }
            }
        });
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
