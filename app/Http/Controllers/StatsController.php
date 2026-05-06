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

        $tradeBase = $user->trades()->count();
        $completedTrades = $user->trades()->where('status', 'completed')->count();
        $completionRate = $tradeBase > 0 ? round(($completedTrades / $tradeBase) * 100) : 0;
        $successfulTradesThisWeek = $user->trades()
            ->where('status', 'completed')
            ->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

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
            'growthChart' => $growthChart,
            'artistDistribution' => $artistDistribution,
            'rarityBreakdown' => $rarityBreakdown,
            'tradeHealth' => $tradeHealth,
            'quickExports' => $quickExports,
        ]);
    }

    protected function buildAverageTradeScore(int $userId): float
    {
        $scoreMap = collect([
            'completed' => 5.0,
            'in_progress' => 4.0,
            'new_offer' => 3.5,
            'pending' => 3.0,
            'cancelled' => 1.5,
        ]);

        $scores = Trade::query()
            ->where('user_id', $userId)
            ->pluck('status')
            ->map(fn (string $status) => $scoreMap[$status] ?? 2.5);

        return $scores->isEmpty() ? 0.0 : round($scores->avg(), 2);
    }

    protected function buildGrowthChart(int $userId): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset));

        $points = $months->map(function (Carbon $month) use ($userId) {
            $count = DB::table('user_cards')
                ->where('user_id', $userId)
                ->where(function ($query) use ($month) {
                    $query->whereBetween('acquired_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                        ->orWhere(function ($nested) use ($month) {
                            $nested->whereNull('acquired_at')
                                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
                        });
                })
                ->count();

            return [
                'label' => $month->format('M'),
                'value' => (int) $count,
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
        $recentTrades = Trade::query()
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'new_offer', 'in_progress', 'completed'])
            ->count();

        $replyTrades = Trade::query()
            ->where('user_id', $userId)
            ->whereNotNull('replied_at')
            ->count();

        $replyRate = $recentTrades > 0 ? (int) round(($replyTrades / $recentTrades) * 100) : 0;

        return [
            'blurb' => $recentTrades > 0
                ? 'Based on replies, ongoing conversations, and closed trades.'
                : 'No trade activity yet. Start listing or trading cards to build stats.',
            'avg_reply' => $recentTrades > 0 ? max(1, (int) round(($recentTrades * 18) / max($replyTrades, 1))) : 0,
            'reply_score' => $replyRate,
            'completed' => Trade::query()->where('user_id', $userId)->where('status', 'completed')->count(),
            'disputes' => Trade::query()->where('user_id', $userId)->where('status', 'cancelled')->count(),
        ];
    }

    protected function buildQuickExports(int $userId, float $totalValue): array
    {
        $listedCards = DB::table('user_cards')
            ->where('user_id', $userId)
            ->where('is_listed', true)
            ->count();

        $completionBase = Trade::query()->where('user_id', $userId)->count();
        $completionRate = $completionBase > 0
            ? (int) round((Trade::query()->where('user_id', $userId)->where('status', 'completed')->count() / $completionBase) * 100)
            : 0;

        return [
            'portfolio_cards' => DB::table('user_cards')->where('user_id', $userId)->count(),
            'listed_cards' => $listedCards,
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
