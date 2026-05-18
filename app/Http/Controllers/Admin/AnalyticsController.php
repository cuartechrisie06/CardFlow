<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceListing;
use App\Models\TradeRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        return view('admin.analytics.index', [
            'userGrowthTrend' => $this->buildUserGrowthTrend(),
            'listingActivity' => $this->buildListingActivity(),
            'tradeStats' => [
                'total' => TradeRequest::count(),
                'completed' => TradeRequest::where('status', 'completed')->count(),
                'pending' => TradeRequest::where('status', 'pending')->count(),
                'cancelled' => TradeRequest::whereIn('status', ['cancelled', 'declined'])->count(),
            ],
            'topCollectors' => User::withCount(['userCards', 'marketplaceListings', 'sentTradeRequests', 'receivedTradeRequests'])
                ->orderByDesc('marketplace_listings_count')
                ->limit(5)
                ->get(),
        ]);
    }

    private function buildUserGrowthTrend(): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset));

        $points = $months->map(fn (Carbon $month) => [
            'label' => $month->format('M'),
            'value' => User::whereBetween('created_at', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ])->count(),
        ]);

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

        $peakValue = $values->max();
        $peakIndex = $values->search($peakValue);

        return [
            'points' => $points,
            'svg_points' => $svgPoints,
            'path' => $svgPoints->map(fn (array $point, int $index) => ($index === 0 ? 'M' : 'L').$point['x'].' '.$point['y'])->implode(' '),
            'peak_month' => $points[$peakIndex]['label'] ?? now()->format('M'),
            'new_users' => (int) $values->last(),
            'total_users' => User::count(),
        ];
    }

    private function buildListingActivity(): array
    {
        $total = MarketplaceListing::count();
        $rows = collect([
            ['label' => 'Active', 'count' => MarketplaceListing::where('status', 'active')->count(), 'color' => '#2d6a4f'],
            ['label' => 'Sold', 'count' => MarketplaceListing::where('status', 'sold')->count(), 'color' => '#8B4513'],
            ['label' => 'Flagged', 'count' => MarketplaceListing::where('proof_status', 'pending')->whereNotNull('proof_photo')->count(), 'color' => '#c8956c'],
            ['label' => 'Expired', 'count' => MarketplaceListing::whereIn('status', ['archived', 'draft'])->count(), 'color' => '#b09070'],
        ])->map(fn (array $row) => $row + [
            'percentage' => $total > 0 ? (int) round(($row['count'] / $total) * 100) : 0,
        ]);

        return [
            'total' => $total,
            'rows' => $rows,
        ];
    }
}
