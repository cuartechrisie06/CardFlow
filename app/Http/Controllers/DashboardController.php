<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Card;
use App\Models\Trade;
use App\Models\UserCard;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $searchQuery = trim((string) $request->string('q'));

        $metrics = [
            'total_cards' => $user->userCards()->count(),
            'collection_value' => (float) $user->userCards()
                ->join('cards', 'cards.id', '=', 'user_cards.card_id')
                ->sum(DB::raw('coalesce(user_cards.estimated_value, cards.market_value)')),
            'active_trades' => $user->trades()
                ->whereIn('status', ['pending', 'new_offer', 'in_progress'])
                ->count(),
            'wishlist_matches' => $user->wishlistItems()
                ->whereNotNull('matched_at')
                ->count(),
        ];

        $valueTrend = $this->buildValueTrend($user->id);
        $tradeDistribution = $this->buildTradeDistribution($user->id);
        $wishlistMomentum = $this->buildWishlistMomentum($user->id);
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
            'searchQuery'
        ));
    }

    private function buildValueTrend(int $userId): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset));

        $points = $months->map(function (Carbon $month) use ($userId) {
            $value = (float) DB::table('user_cards')
                ->join('cards', 'cards.id', '=', 'user_cards.card_id')
                ->where('user_cards.user_id', $userId)
                ->where(function ($query) use ($month) {
                    $query->whereDate('user_cards.acquired_at', '<=', $month->copy()->endOfMonth())
                        ->orWhere(function ($nested) use ($month) {
                            $nested->whereNull('user_cards.acquired_at')
                                ->whereDate('user_cards.created_at', '<=', $month->copy()->endOfMonth());
                        });
                })
                ->sum(DB::raw('coalesce(user_cards.estimated_value, cards.market_value)'));

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
            'completed' => 'Completed',
            'pending' => 'Pending',
            'new_offer' => 'New offers',
            'cancelled' => 'Cancelled',
        ]);

        $counts = DB::table('trades')
            ->selectRaw('status, count(*) as aggregate')
            ->where('user_id', $userId)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $total = (int) $counts->sum();

        return [
            'total' => $total,
            'rows' => $statuses->map(function (string $label, string $status) use ($counts, $total) {
                $count = (int) ($counts[$status] ?? 0);
                $percentage = $total > 0 ? (int) round(($count / $total) * 100) : 0;

                return [
                    'label' => $label,
                    'count' => $count,
                    'percentage' => $percentage,
                ];
            })->values(),
        ];
    }

    private function buildWishlistMomentum(int $userId): array
    {
        $groups = DB::table('wishlist_items')
            ->join('cards', 'cards.id', '=', 'wishlist_items.card_id')
            ->selectRaw('cards.artist as label, count(*) as aggregate')
            ->where('wishlist_items.user_id', $userId)
            ->whereNotNull('wishlist_items.matched_at')
            ->groupBy('cards.artist')
            ->orderByDesc('aggregate')
            ->limit(6)
            ->get();

        $max = max($groups->max('aggregate') ?? 0, 1);

        $bars = $groups->map(fn ($group) => [
            'label' => $group->label,
            'count' => (int) $group->aggregate,
            'height' => max((int) round(($group->aggregate / $max) * 82), 16),
        ]);

        $freshMatches = DB::table('wishlist_items')
            ->where('user_id', $userId)
            ->whereDate('matched_at', today())
            ->count();

        $averagePrice = DB::table('wishlist_items')
            ->join('cards', 'cards.id', '=', 'wishlist_items.card_id')
            ->where('wishlist_items.user_id', $userId)
            ->whereNotNull('wishlist_items.matched_at')
            ->avg('cards.market_value');

        return [
            'bars' => $bars,
            'strongest' => $bars->first()['label'] ?? 'No matches yet',
            'fresh_matches' => $freshMatches,
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
                'title' => $activity->title,
                'time' => $activity->happened_at->isToday()
                    ? $activity->happened_at->format('g:i A')
                    : $activity->happened_at->diffForHumans(),
            ]);

        $dailyActions = Activity::query()
            ->where('user_id', $userId)
            ->whereDate('happened_at', today())
            ->count();

        $replyBase = DB::table('trades')
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'new_offer', 'in_progress', 'completed'])
            ->count();

        $replyCount = DB::table('trades')
            ->where('user_id', $userId)
            ->whereNotNull('replied_at')
            ->count();

        $replyRate = $replyBase > 0 ? (int) round(($replyCount / $replyBase) * 100) : 0;

        return [
            'items' => $items,
            'daily_actions' => $dailyActions,
            'reply_rate' => $replyRate,
        ];
    }

    private function buildTrendingCards(int $userId): Collection
    {
        $interestArtists = DB::table('cards')
            ->selectRaw('cards.artist, count(*) as aggregate')
            ->join('user_cards', 'user_cards.card_id', '=', 'cards.id')
            ->where('user_cards.user_id', $userId)
            ->groupBy('cards.artist')
            ->unionAll(
                DB::table('cards')
                    ->selectRaw('cards.artist, count(*) as aggregate')
                    ->join('wishlist_items', 'wishlist_items.card_id', '=', 'cards.id')
                    ->where('wishlist_items.user_id', $userId)
                    ->groupBy('cards.artist')
            )
            ->get()
            ->groupBy('artist')
            ->map(fn (Collection $rows) => $rows->sum('aggregate'))
            ->sortDesc()
            ->keys()
            ->take(3);

        $query = Card::query()->orderByDesc('trend_score');

        if ($interestArtists->isNotEmpty()) {
            $query->whereIn('artist', $interestArtists);
        }

        return $query->limit(3)->get();
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
