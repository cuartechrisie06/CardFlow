<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Card;
use App\Models\MarketplaceListing;
use App\Models\Trade;
use App\Models\TradeRequest;
use App\Models\UserOnboarding;
use App\Models\UserCard;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\WishlistMatchService;

class DashboardController extends Controller
{
    public function __construct(private WishlistMatchService $wishlistMatchService)
    {
    }

    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.index');
        }

        $searchQuery = trim((string) $request->string('q'));
        $wishlistItems = $user->wishlistItems()->with('card')->get();
        $wishlistMatches = $this->wishlistMatchService->buildMatchesForUser($user, $wishlistItems);

        $metrics = [
            'total_cards' => $user->userCards()->count(),
            'collection_value' => (float) $user->userCards()
                ->sum(DB::raw('coalesce(user_cards.estimated_value, 0)')),
            'active_trades' => TradeRequest::query()
                ->where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)
                        ->orWhere('receiver_id', $user->id);
                })
                ->whereIn('status', ['pending', 'accepted'])
                ->count()
                + $user->trades()
                    ->whereIn('status', ['pending', 'new_offer', 'in_progress'])
                    ->count(),
            'wishlist_matches' => max(
                $wishlistMatches->filter(fn (Collection $matches) => $matches->isNotEmpty())->count(),
                $wishlistItems->whereNotNull('matched_at')->count()
            ),
        ];

        $onboarding = UserOnboarding::query()->firstOrCreate(['user_id' => $user->id]);
        $visitCount = (int) session('dashboard_visits', 0);
        session(['dashboard_visits' => $visitCount + 1]);
        $showOnboardingBanner = ! $user->onboarding_completed && $visitCount < 3;

        $valueTrend = $this->buildValueTrend($user->id);
        $tradeDistribution = $this->buildTradeDistribution($user->id);
        $wishlistMomentum = $this->buildWishlistMomentum($user->id, $wishlistItems, $wishlistMatches);
        $activityFeed = $this->buildActivityFeed($user->id);
        $trendingCards = $this->buildTrendingCards($user->id);
        $searchResults = $this->buildSearchResults($user->id, $searchQuery);

        return view('dashboard.index', compact(
            'metrics',
            'valueTrend',
            'tradeDistribution',
            'wishlistMomentum',
            'activityFeed',
            'trendingCards',
            'searchResults',
            'searchQuery',
            'onboarding',
            'showOnboardingBanner'
        ));
    }

    private function buildValueTrend(int $userId): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset));

        $cardValues = DB::table('user_cards')
            ->where('user_cards.user_id', $userId)
            ->selectRaw('COALESCE(user_cards.acquired_at, user_cards.created_at) as effective_date')
            ->selectRaw('COALESCE(user_cards.estimated_value, 0) as effective_value')
            ->get();

        $points = $months->map(function (Carbon $month) use ($cardValues) {
            $monthEnd = $month->copy()->endOfMonth();

            $value = (float) $cardValues
                ->filter(fn ($row) => $row->effective_date && Carbon::parse($row->effective_date)->lessThanOrEqualTo($monthEnd))
                ->sum('effective_value');

            return [
                'label' => $month->format('M'),
                'value' => round($value, 2),
            ];
        });

        $values = $points->pluck('value');
        $max = max($values->max(), 1);
        $min = $values->min();

        $svgPoints = $points->values()->map(function (array $point, int $index) use ($max, $min, $points) {
            $width = 380;
            $height = 110;
            $left = 20;
            $top = 20;
            $count = max($points->count() - 1, 1);
            $x = $left + ($index * ($width / $count));
            $normalized = $max === $min ? 0.5 : (($point['value'] - $min) / ($max - $min));
            $y = $top + ($height - ($normalized * $height));

            return [
                'x' => round($x, 2),
                'y' => round($y, 2),
                'label' => $point['label'],
                'value' => $point['value'],
            ];
        });

        $path = $svgPoints->map(fn (array $point, int $index) => ($index === 0 ? 'M' : 'L').$point['x'].' '.$point['y'])
            ->implode(' ');

        $peakValue = $values->max();
        $peakIndex = $values->search($peakValue);
        $first = (float) $values->first();
        $last = (float) $values->last();
        $growth = $first > 0 ? (($last - $first) / $first) * 100 : ($last > 0 ? 100 : 0);
        $spread = $max > 0 ? (($max - $min) / $max) * 100 : 0;

        return [
            'points' => $points,
            'svg_points' => $svgPoints,
            'path' => $path,
            'peak_month' => $points[$peakIndex]['label'] ?? now()->format('M'),
            'growth' => round($growth),
            'stability' => $spread < 25 ? 'Steady' : ($spread < 50 ? 'Moderate' : 'Volatile'),
        ];
    }

    private function buildTradeDistribution(int $userId): array
    {
        $statuses = collect([
            'completed' => ['label' => 'Completed', 'color' => '#2d6a4f'],
            'pending' => ['label' => 'Pending', 'color' => '#8B4513'],
            'new_offer' => ['label' => 'New offers', 'color' => '#c8956c'],
            'cancelled' => ['label' => 'Cancelled', 'color' => '#c0392b'],
        ]);

        $allTrades = TradeRequest::query()
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->get();

        $legacyTrades = Trade::query()
            ->where('user_id', $userId)
            ->get();

        $counts = collect([
            'completed' => $allTrades->where('status', 'completed')->count()
                + $legacyTrades->where('status', 'completed')->count(),
            'pending' => $allTrades->where('status', 'pending')->count()
                + $legacyTrades->where('status', 'pending')->count(),
            'new_offer' => $allTrades
                ->where('receiver_id', $userId)
                ->where('status', 'pending')
                ->count()
                + $legacyTrades->where('status', 'new_offer')->count(),
            'cancelled' => $allTrades
                ->whereIn('status', ['declined', 'cancelled'])
                ->count()
                + $legacyTrades->where('status', 'cancelled')->count(),
        ]);

        $total = $allTrades->count() + $legacyTrades->count();

        return [
            'total' => $total,
            'rows' => $statuses->map(function (array $meta, string $status) use ($counts, $total) {
                $count = (int) ($counts[$status] ?? 0);
                $percentage = $total > 0 ? (int) round(($count / $total) * 100) : 0;

                return [
                    'label' => $meta['label'],
                    'count' => $count,
                    'percentage' => $percentage,
                    'color' => $meta['color'],
                ];
            })->values(),
        ];
    }

    private function buildWishlistMomentum(int $userId, Collection $wishlistItems, Collection $wishlistMatches): array
    {
        $matchedListings = $wishlistMatches
            ->flatten(1)
            ->map(function ($match) {
                if ($match instanceof MarketplaceListing) {
                    return $match;
                }

                if (is_array($match) && ($match['listing'] ?? null) instanceof MarketplaceListing) {
                    return $match['listing'];
                }

                return null;
            })
            ->filter()
            ->unique('id')
            ->values();

        $groups = $matchedListings
            ->groupBy(fn ($listing) => trim((string) $listing->card?->artist))
            ->map(fn (Collection $group, string $artist) => [
                'label' => $artist !== '' ? $artist : 'Unknown',
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->take(6)
            ->values();

        $max = max((int) $groups->max('count'), 1);

        $bars = $groups->map(fn (array $group) => [
            'label' => $group['label'],
            'count' => (int) $group['count'],
            'height' => max((int) round(($group['count'] / $max) * 82), 16),
        ]);

        $freshMatches = $wishlistItems
            ->filter(fn ($item) => $item->matched_at && $item->matched_at->isToday())
            ->count();

        $averagePrice = $matchedListings->avg(fn ($listing) => (float) ($listing->userCard?->listing_price ?? $listing->card?->market_value ?? 0));

        return [
            'bars' => $bars,
            'matches' => $matchedListings,
            'strongest' => $matchedListings->first()?->card?->title
                ?? $bars->first()['label']
                ?? 'No matches yet',
            'fresh_matches' => max(
                $freshMatches,
                $matchedListings->filter(fn ($listing) => $listing->created_at?->isToday())->count()
            ),
            'average_price' => round((float) $averagePrice, 2),
        ];
    }

    private function buildActivityFeed(int $userId): array
    {
        $items = Activity::query()
            ->where('user_id', $userId)
            ->latest('happened_at')
            ->limit(4)
            ->get()
            ->map(fn (Activity $activity) => [
                'type' => $activity->type,
                'title' => $activity->title,
                'time' => $activity->happened_at->diffForHumans(),
            ]);

        $dailyActions = Activity::query()
            ->where('user_id', $userId)
            ->whereDate('happened_at', today())
            ->count();

        $incomingConversationIds = DB::table('messages')
            ->where('receiver_id', $userId)
            ->distinct()
            ->pluck('conversation_id');

        $replyBase = $incomingConversationIds->count();
        $replyCount = $replyBase > 0
            ? DB::table('messages')
                ->where('sender_id', $userId)
                ->whereIn('conversation_id', $incomingConversationIds)
                ->distinct()
                ->count('conversation_id')
            : 0;
        $replyRate = $replyBase > 0 ? (int) round(($replyCount / $replyBase) * 100) : 0;

        return [
            'items' => $items,
            'daily_actions' => $dailyActions,
            'reply_rate' => $replyRate,
        ];
    }

    private function buildTrendingCards(int $userId): Collection
    {
        $cardsHavePhotoColumn = Schema::hasColumn('cards', 'photo');

        return Card::query()
            ->where(function ($query) use ($cardsHavePhotoColumn) {
                if ($cardsHavePhotoColumn) {
                    $query->where(function ($cardQuery) {
                        $cardQuery->whereNotNull('photo')
                            ->where('photo', '!=', 'photo');
                    })->orWhereNotNull('official_image_url');
                } else {
                    $query->whereNotNull('official_image_url');
                }

                $query->orWhereHas('userCards', function ($userCardQuery) {
                    $userCardQuery->whereNotNull('photo_path');
                });
            })
            ->orderByDesc('trend_score')
            ->orderBy('artist')
            ->limit(3)
            ->get();
    }

    private function buildSearchResults(int $userId, string $searchQuery): array
    {
        if ($searchQuery === '') {
            return [
                'cards' => collect(),
                'trades' => collect(),
            ];
        }

        $cards = UserCard::query()
            ->with('card')
            ->where('user_id', $userId)
            ->whereHas('card', function ($query) use ($searchQuery) {
                $query->where('title', 'like', "%{$searchQuery}%")
                    ->orWhere('artist', 'like', "%{$searchQuery}%")
                    ->orWhere('album', 'like', "%{$searchQuery}%")
                    ->orWhere('edition', 'like', "%{$searchQuery}%");
            })
            ->latest('acquired_at')
            ->limit(5)
            ->get();

        $trades = Trade::query()
            ->with('card')
            ->where('user_id', $userId)
            ->where(function ($query) use ($searchQuery) {
                $query->where('partner_name', 'like', "%{$searchQuery}%")
                    ->orWhere('partner_handle', 'like', "%{$searchQuery}%")
                    ->orWhereHas('card', function ($cardQuery) use ($searchQuery) {
                        $cardQuery->where('title', 'like', "%{$searchQuery}%")
                            ->orWhere('artist', 'like', "%{$searchQuery}%");
                    });
            })
            ->latest()
            ->limit(3)
            ->get();

        return compact('cards', 'trades');
    }
}
