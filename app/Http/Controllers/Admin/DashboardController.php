<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceListing;
use App\Models\Message;
use App\Models\Trade;
use App\Models\TradeRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $totalUsers = User::count();
        $totalListings = MarketplaceListing::count();
        $activeListings = MarketplaceListing::where('status', 'active')->count();
        $completedTrades = TradeRequest::where('status', 'completed')->count()
            + Trade::where('status', 'completed')->count();
        $totalTrades = TradeRequest::count() + Trade::count();

        $moderationQueue = MarketplaceListing::with(['user', 'card', 'userCard'])
            ->where('proof_status', 'pending')
            ->whereNotNull('proof_photo')
            ->latest()
            ->limit(3)
            ->get();

        $moderationCount = MarketplaceListing::where('proof_status', 'pending')
            ->whereNotNull('proof_photo')
            ->count();

        $stats = [
            [
                'label' => 'Total users',
                'value' => $totalUsers,
                'note' => User::whereDate('created_at', today())->count().' new today',
            ],
            [
                'label' => 'Total listings',
                'value' => $totalListings,
                'note' => $activeListings.' active',
            ],
            [
                'label' => 'Trades completed',
                'value' => $completedTrades,
                'note' => $totalTrades.' total',
            ],
            [
                'label' => 'Moderation queue',
                'value' => $moderationCount,
                'note' => $moderationCount.' pending review',
            ],
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'moderationCount' => $moderationCount,
            'quickActionsDone' => $moderationCount === 0,
            'userGrowthTrend' => $this->buildUserGrowthTrend(),
            'listingHealth' => $this->buildListingHealth(),
            'moderationQueue' => $moderationQueue,
            'moderationStats' => $this->buildModerationStats(),
            'platformActivity' => $this->buildPlatformActivity(),
            'recentListings' => MarketplaceListing::with(['card', 'userCard'])
                ->activeVisible()
                ->latest()
                ->limit(6)
                ->get(),
            'dailyActions' => $this->dailyActions(),
            'moderationRate' => $this->moderationRate(),
            'formatMoney' => fn ($value) => 'PHP '.number_format((float) $value, 0),
        ]);
    }

    private function buildUserGrowthTrend(): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset));

        $points = $months->map(function (Carbon $month) {
            return [
                'label' => $month->format('M'),
                'value' => User::whereBetween('created_at', [
                    $month->copy()->startOfMonth(),
                    $month->copy()->endOfMonth(),
                ])->count(),
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

        $path = $svgPoints
            ->map(fn (array $point, int $index) => ($index === 0 ? 'M' : 'L').$point['x'].' '.$point['y'])
            ->implode(' ');

        $peakValue = $values->max();
        $peakIndex = $values->search($peakValue);
        $first = (int) $values->first();
        $last = (int) $values->last();
        $growth = $first > 0 ? (($last - $first) / $first) * 100 : ($last > 0 ? 100 : 0);

        return [
            'points' => $points,
            'svg_points' => $svgPoints,
            'path' => $path,
            'peak_month' => $points[$peakIndex]['label'] ?? now()->format('M'),
            'new_users' => $last,
            'retention' => max(0, min(100, 100 - abs((int) round($growth / 2)))),
        ];
    }

    private function buildListingHealth(): array
    {
        $rows = collect([
            'active' => ['label' => 'Active', 'color' => '#2d6a4f'],
            'sold' => ['label' => 'Sold', 'color' => '#8B4513'],
            'flagged' => ['label' => 'Flagged', 'color' => '#c8956c'],
            'expired' => ['label' => 'Expired', 'color' => '#b09070'],
        ]);

        $total = MarketplaceListing::count();
        $counts = [
            'active' => MarketplaceListing::where('status', 'active')->count(),
            'sold' => MarketplaceListing::where('status', 'sold')->count(),
            'flagged' => MarketplaceListing::where('proof_status', 'pending')->whereNotNull('proof_photo')->count(),
            'expired' => MarketplaceListing::whereIn('status', ['archived', 'draft'])->count(),
        ];

        return [
            'total' => $total,
            'rows' => $rows->map(function (array $meta, string $status) use ($counts, $total) {
                $count = (int) ($counts[$status] ?? 0);

                return [
                    'label' => $meta['label'],
                    'count' => $count,
                    'percentage' => $total > 0 ? (int) round(($count / $total) * 100) : 0,
                    'color' => $meta['color'],
                ];
            })->values(),
        ];
    }

    private function buildModerationStats(): array
    {
        $flaggedToday = MarketplaceListing::where('proof_status', 'pending')
            ->whereNotNull('proof_photo')
            ->whereDate('created_at', today())
            ->count();

        $resolvedThisWeek = MarketplaceListing::where('proof_status', 'verified')
            ->where('updated_at', '>=', now()->startOfWeek())
            ->count();

        $reviewed = MarketplaceListing::whereIn('proof_status', ['verified', 'failed'])
            ->whereNotNull('proof_photo')
            ->whereNotNull('updated_at')
            ->latest('updated_at')
            ->limit(20)
            ->get();

        $averageResponse = $reviewed->isNotEmpty()
            ? round($reviewed->avg(fn (MarketplaceListing $listing) => max(1, $listing->created_at->diffInHours($listing->updated_at))), 1)
            : 0;

        return [
            'flagged_today' => $flaggedToday,
            'resolved_this_week' => $resolvedThisWeek,
            'average_response' => $averageResponse,
        ];
    }

    private function buildPlatformActivity(): Collection
    {
        $users = User::latest()
            ->limit(5)
            ->get()
            ->map(fn (User $user) => [
                'title' => '@'.$user->username.' joined CardFlow',
                'time' => $user->created_at->diffForHumans(),
                'created_at' => $user->created_at,
                'type' => 'user',
            ]);

        $listings = MarketplaceListing::with('user')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (MarketplaceListing $listing) => [
                'title' => '@'.($listing->user?->username ?? 'collector').' added a new listing',
                'time' => $listing->created_at->diffForHumans(),
                'created_at' => $listing->created_at,
                'type' => 'listing',
            ]);

        $trades = TradeRequest::with('sender')
            ->where('status', 'completed')
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (TradeRequest $trade) => [
                'title' => '@'.($trade->sender?->username ?? 'collector').' completed a trade',
                'time' => $trade->updated_at->diffForHumans(),
                'created_at' => $trade->updated_at,
                'type' => 'trade',
            ]);

        return $users
            ->merge($listings)
            ->merge($trades)
            ->sortByDesc('created_at')
            ->take(6)
            ->values();
    }

    private function dailyActions(): int
    {
        return User::whereDate('created_at', today())->count()
            + MarketplaceListing::whereDate('created_at', today())->count()
            + TradeRequest::whereDate('updated_at', today())->count()
            + Message::whereDate('created_at', today())->count();
    }

    private function moderationRate(): int
    {
        $resolvedToday = MarketplaceListing::whereIn('proof_status', ['verified', 'failed'])
            ->whereDate('updated_at', today())
            ->count();

        $flaggedToday = MarketplaceListing::whereNotNull('proof_photo')
            ->whereDate('created_at', today())
            ->count();

        return $flaggedToday > 0 ? (int) round(($resolvedToday / $flaggedToday) * 100) : 100;
    }
}
