<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $totalValue = (float) $user->userCards()
            ->join('cards', 'cards.id', '=', 'user_cards.card_id')
            ->sum(DB::raw('coalesce(user_cards.estimated_value, cards.market_value)'));

        $rarityChartData = DB::table('user_cards')
            ->join('cards', 'cards.id', '=', 'user_cards.card_id')
            ->where('user_cards.user_id', $user->id)
            ->selectRaw("COALESCE(NULLIF(cards.rarity, ''), 'Standard') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'total' => (int) $row->total,
            ]);

        $artistChartData = DB::table('user_cards')
            ->join('cards', 'cards.id', '=', 'user_cards.card_id')
            ->where('user_cards.user_id', $user->id)
            ->selectRaw("COALESCE(NULLIF(cards.artist, ''), 'Unknown Artist') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'total' => (int) $row->total,
            ]);

        $tradeStats = DB::table('trades')
            ->where('user_id', $user->id)
            ->selectRaw("
                COUNT(*) as trade_total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_total,
                SUM(CASE WHEN status = 'completed' AND completed_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as completed_this_week
            ", [now()->startOfWeek(), now()->endOfWeek()])
            ->first();

        $tradeBase = (int) ($tradeStats->trade_total ?? 0);
        $completedTrades = (int) ($tradeStats->completed_total ?? 0);
        $completionRate = $tradeBase > 0 ? round(($completedTrades / $tradeBase) * 100) : 0;
        $successfulTradesThisWeek = (int) ($tradeStats->completed_this_week ?? 0);

        $averageTradeScore = $this->buildAverageTradeScore($user->id);
        $growthChart = $this->buildGrowthChart($user->id);
        $artistDistribution = $this->buildArtistDistribution($user->id);
        $rarityBreakdown = $this->buildRarityBreakdown($user->id);
        $tradeHealth = $this->buildTradeHealth($user->id);
        $quickExports = $this->buildQuickExports($user->id, $totalValue);

        return view('stats.index', [
            'metrics' => [
                'total_value' => round($totalValue),
                'completion_rate' => $completionRate,
                'successful_trades' => $successfulTradesThisWeek,
                'average_trade_score' => $averageTradeScore,
                'trade_total' => $tradeBase,
            ],
            'rarityChartData' => $rarityChartData,
            'artistChartData' => $artistChartData,
            'growthChart' => $growthChart,
            'artistDistribution' => $artistDistribution,
            'rarityBreakdown' => $rarityBreakdown,
            'tradeHealth' => $tradeHealth,
            'quickExports' => $quickExports,
        ]);
    }

    protected function buildAverageTradeScore(int $userId): float
    {
        $score = DB::table('trades')
            ->where('user_id', $userId)
            ->selectRaw("
                AVG(
                    CASE status
                        WHEN 'completed' THEN 5.0
                        WHEN 'in_progress' THEN 4.0
                        WHEN 'new_offer' THEN 3.5
                        WHEN 'pending' THEN 3.0
                        WHEN 'cancelled' THEN 1.5
                        ELSE 2.5
                    END
                ) as average_score
            ")
            ->value('average_score');

        return round((float) ($score ?? 0), 2);
    }

    protected function buildGrowthChart(int $userId): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset));

        $rows = DB::table('user_cards')
            ->where('user_id', $userId)
            ->selectRaw('COALESCE(acquired_at, created_at) as effective_date')
            ->get();

        $countsByMonth = $rows
            ->filter(fn ($row) => $row->effective_date !== null)
            ->groupBy(fn ($row) => Carbon::parse($row->effective_date)->format('Y-m'))
            ->map->count();

        $points = $months->map(function (Carbon $month) use ($countsByMonth) {
            return [
                'label' => $month->format('M'),
                'value' => (int) ($countsByMonth[$month->format('Y-m')] ?? 0),
            ];
        });

        $max = max($points->max('value'), 1);
        $min = (int) $points->min('value');

        $svgPoints = $points->values()->map(function (array $point, int $index) use ($points, $max, $min) {
            $width = 380;
            $height = 110;
            $left = 20;
            $top = 18;
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

        return [
            'points' => $points,
            'path' => $svgPoints->map(fn (array $point, int $index) => ($index === 0 ? 'M' : 'L').$point['x'].' '.$point['y'])->implode(' '),
            'latest' => (int) $points->last()['value'],
        ];
    }

    protected function buildArtistDistribution(int $userId): array
    {
        $rows = DB::table('user_cards')
            ->join('cards', 'cards.id', '=', 'user_cards.card_id')
            ->selectRaw('cards.artist as label, count(*) as total')
            ->where('user_cards.user_id', $userId)
            ->groupBy('cards.artist')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $totalCards = max((int) $rows->sum('total'), 1);

        return [
            'total_cards' => (int) $rows->sum('total'),
            'rows' => $rows->map(fn ($row) => [
                'label' => $row->label,
                'total' => (int) $row->total,
                'percentage' => (int) round(($row->total / $totalCards) * 100),
            ]),
        ];
    }

    protected function buildRarityBreakdown(int $userId): Collection
    {
        $rows = DB::table('user_cards')
            ->join('cards', 'cards.id', '=', 'user_cards.card_id')
            ->selectRaw("COALESCE(NULLIF(cards.rarity, ''), 'Standard') as label, count(*) as total")
            ->where('user_cards.user_id', $userId)
            ->groupBy('label')
            ->orderByDesc('total')
            ->get();

        $max = max((int) ($rows->max('total') ?? 0), 1);

        return $rows->map(fn ($row) => [
            'label' => $row->label,
            'total' => (int) $row->total,
            'width' => max(12, (int) round(($row->total / $max) * 100)),
        ]);
    }

    protected function buildTradeHealth(int $userId): array
    {
        $tradeStats = DB::table('trades')
            ->where('user_id', $userId)
            ->selectRaw("
                SUM(CASE WHEN status IN ('pending', 'new_offer', 'in_progress', 'completed') THEN 1 ELSE 0 END) as recent_trades,
                SUM(CASE WHEN replied_at IS NOT NULL THEN 1 ELSE 0 END) as reply_trades,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_trades,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as disputes
            ")
            ->first();

        $recentTrades = (int) ($tradeStats->recent_trades ?? 0);
        $replyTrades = (int) ($tradeStats->reply_trades ?? 0);
        $replyRate = $recentTrades > 0 ? (int) round(($replyTrades / $recentTrades) * 100) : 0;

        return [
            'blurb' => $recentTrades > 0
                ? 'Based on replies, ongoing conversations, and closed trades.'
                : 'No trade activity yet. Start listing or trading cards to build stats.',
            'avg_reply' => $recentTrades > 0 ? max(1, (int) round(($recentTrades * 18) / max($replyTrades, 1))) : 0,
            'reply_score' => $replyRate,
            'completed' => (int) ($tradeStats->completed_trades ?? 0),
            'disputes' => (int) ($tradeStats->disputes ?? 0),
        ];
    }

    protected function buildQuickExports(int $userId, float $totalValue): array
    {
        $userCardStats = DB::table('user_cards')
            ->where('user_id', $userId)
            ->selectRaw("
                COUNT(*) as portfolio_cards,
                SUM(CASE WHEN is_listed = 1 THEN 1 ELSE 0 END) as listed_cards
            ")
            ->first();

        $tradeStats = DB::table('trades')
            ->where('user_id', $userId)
            ->selectRaw("
                COUNT(*) as trade_total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_total
            ")
            ->first();

        $completionBase = (int) ($tradeStats->trade_total ?? 0);
        $completionRate = $completionBase > 0
            ? (int) round((((int) ($tradeStats->completed_total ?? 0)) / $completionBase) * 100)
            : 0;

        return [
            'portfolio_cards' => (int) ($userCardStats->portfolio_cards ?? 0),
            'listed_cards' => (int) ($userCardStats->listed_cards ?? 0),
            'portfolio_value' => round($totalValue),
            'completion_rate' => $completionRate,
        ];
    }

    public function exportPdf(Request $request)
{
    $user = $request->user();

    $totalValue = (float) $user->userCards()
        ->join('cards', 'cards.id', '=', 'user_cards.card_id')
        ->sum(\Illuminate\Support\Facades\DB::raw('coalesce(user_cards.estimated_value, cards.market_value, 0)'));

    $tradeBase = $user->trades()->count();

    $completedTrades = $user->trades()
        ->where('status', 'completed')
        ->count();

    $completionRate = $tradeBase > 0
        ? round(($completedTrades / $tradeBase) * 100)
        : 0;

    $successfulTradesThisWeek = $user->trades()
        ->where('status', 'completed')
        ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
        ->count();

    return view('stats.print', [
        'user' => $user,
        'generatedAt' => now(),
        'metrics' => [
            'total_value' => round($totalValue),
            'completion_rate' => $completionRate,
            'successful_trades' => $successfulTradesThisWeek,
            'average_trade_score' => 0,
            'trade_total' => $tradeBase,
        ],
    ]);
}

public function exportCsv(Request $request): StreamedResponse
{
    $user = $request->user();

    $filename = 'collection-data-' . now()->format('Y-m-d-His') . '.csv';

    return response()->streamDownload(function () use ($user) {
        $handle = fopen('php://output', 'w');

        // Makes the CSV more Excel-friendly.
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'Card Title',
            'Artist / Group',
            'Rarity',
            'Condition',
            'Market Value',
            'Estimated Value',
            'Acquired Date',
            'Created Date',
        ]);

        $user->userCards()
            ->with('card')
            ->latest()
            ->chunk(200, function ($userCards) use ($handle) {
                foreach ($userCards as $userCard) {
                    $card = $userCard->card;

                    fputcsv($handle, [
                        $card->title ?? $card->name ?? 'Untitled',
                        $card->artist ?? $card->group_name ?? 'Ungrouped',
                        $card->rarity ?? 'Unspecified',
                        $userCard->condition ?? 'Unspecified',
                        $card->market_value ?? 0,
                        $userCard->estimated_value ?? $card->market_value ?? 0,
                        optional($userCard->acquired_at)->format('Y-m-d'),
                        optional($userCard->created_at)->format('Y-m-d'),
                    ]);
                }
            });

        fclose($handle);
    }, $filename, [
        'Content-Type' => 'text/csv; charset=UTF-8',
    ]);
}

public function shareSnapshot(Request $request)
{
    $user = $request->user();

    $totalValue = (float) $user->userCards()
        ->join('cards', 'cards.id', '=', 'user_cards.card_id')
        ->sum(\Illuminate\Support\Facades\DB::raw('coalesce(user_cards.estimated_value, cards.market_value, 0)'));

    $totalCards = $user->userCards()->count();

    $tradeTotal = $user->trades()->count();

    $completedTrades = $user->trades()
        ->where('status', 'completed')
        ->count();

    $completionRate = $tradeTotal > 0
        ? round(($completedTrades / $tradeTotal) * 100)
        : 0;

    $successfulTradesThisWeek = $user->trades()
        ->where('status', 'completed')
        ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
        ->count();

    $snapshot = "Collection Snapshot\n"
        . "Owner: " . ($user->name ?? $user->username ?? 'User') . "\n"
        . "Generated: " . now()->format('F d, Y h:i A') . "\n\n"
        . "Total Value: PHP " . number_format($totalValue) . "\n"
        . "Total Cards: " . $totalCards . "\n"
        . "Completion Rate: " . $completionRate . "%\n"
        . "Successful Trades This Week: " . $successfulTradesThisWeek . "\n"
        . "Total Trades Tracked: " . $tradeTotal . "\n";

    return redirect()
        ->route('stats.index')
        ->with('snapshot', $snapshot);
}
}
