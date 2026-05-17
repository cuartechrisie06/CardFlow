<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\MarketplaceListing;
use App\Models\Message;
use App\Models\Trade;
use App\Models\TradeRequest;
use App\Models\UserCard;
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

        $collectionRows = UserCard::query()
            ->with('card')
            ->where('user_id', $user->id)
            ->get();

        $totalCards = $collectionRows->count();
        $estimatedTotal = (float) $collectionRows->sum(fn (UserCard $userCard) => (float) ($userCard->estimated_value ?? 0));
        $marketTotal = (float) $collectionRows->sum(fn (UserCard $userCard) => (float) ($userCard->card?->market_value ?? 0));
        $totalValue = $estimatedTotal > 0 ? $estimatedTotal : $marketTotal;
        $totalSpent = (float) $collectionRows->sum(fn (UserCard $userCard) => (float) ($userCard->purchase_price ?? 0));
        $netChange = $totalValue - $totalSpent;
        $netChangePercent = $totalSpent > 0 ? round(($netChange / $totalSpent) * 100, 1) : 0;
        $avgCardValue = $totalCards > 0 ? round($totalValue / $totalCards, 2) : 0;
        $mostValuableCard = $collectionRows
            ->sortByDesc(fn (UserCard $userCard) => (float) ($userCard->estimated_value ?? $userCard->card?->market_value ?? 0))
            ->first();
        $recentCards = UserCard::query()
            ->with('card')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        $rarityBreakdown = DB::table('user_cards')
            ->join('cards', 'cards.id', '=', 'user_cards.card_id')
            ->where('user_cards.user_id', $user->id)
            ->selectRaw("
                COALESCE(NULLIF(cards.rarity, ''), 'Standard') as label,
                COUNT(*) as total,
                SUM(COALESCE(user_cards.estimated_value, cards.market_value, 0)) as total_value
            ")
            ->groupBy('label')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                return [
                    'label' => $row->label,
                    'rarity' => $row->label,
                    'count' => (int) $row->total,
                    'total' => (int) $row->total,
                    'total_value' => (float) $row->total_value,
                    'width' => 0,
                ];
            });

        $rarityMax = max((int) ($rarityBreakdown->max('total') ?? 0), 1);
        $rarityBreakdown = $rarityBreakdown->map(fn (array $row) => array_merge($row, [
            'width' => max(12, (int) round(($row['total'] / $rarityMax) * 100)),
        ]));

        $rarityChartData = $rarityBreakdown->map(fn (array $row) => [
            'label' => $row['label'],
            'total' => $row['total'],
        ]);

        $artistBreakdown = DB::table('user_cards')
            ->join('cards', 'cards.id', '=', 'user_cards.card_id')
            ->where('user_cards.user_id', $user->id)
            ->selectRaw("COALESCE(NULLIF(cards.artist, ''), 'Unknown Artist') as artist, COUNT(*) as total")
            ->groupBy('artist')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'artist' => $row->artist,
                'label' => $row->artist,
                'count' => (int) $row->total,
                'total' => (int) $row->total,
            ]);

        $artistChartData = $artistBreakdown->map(fn (array $row) => [
            'label' => $row['label'],
            'total' => $row['total'],
        ]);

        $albumBreakdown = DB::table('user_cards')
            ->join('cards', 'cards.id', '=', 'user_cards.card_id')
            ->where('user_cards.user_id', $user->id)
            ->whereNotNull('cards.album')
            ->where('cards.album', '!=', '')
            ->selectRaw('cards.album as album, COUNT(*) as total')
            ->groupBy('cards.album')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'album' => $row->album,
                'label' => $row->album,
                'count' => (int) $row->total,
                'total' => (int) $row->total,
            ]);

        $tradeBaseQuery = TradeRequest::query()
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            });
        $tradesSent = TradeRequest::where('sender_id', $user->id)->count();
        $tradesReceived = TradeRequest::where('receiver_id', $user->id)->count();
        $totalTrades = $tradesSent + $tradesReceived;
        $completedTrades = (clone $tradeBaseQuery)->where('status', 'completed')->count();
        $declinedTrades = (clone $tradeBaseQuery)->where('status', 'declined')->count();
        $pendingTrades = (clone $tradeBaseQuery)->where('status', 'pending')->count();
        $acceptedTrades = (clone $tradeBaseQuery)->where('status', 'accepted')->count();
        $cancelledTrades = (clone $tradeBaseQuery)->where('status', 'cancelled')->count();
        $completionRate = $totalTrades > 0 ? round(($completedTrades / $totalTrades) * 100, 1) : 0;
        $successfulTradesThisWeek = (clone $tradeBaseQuery)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        $tradeHistory = (clone $tradeBaseQuery)
            ->with(['sender', 'receiver', 'listing.card', 'offeredCard'])
            ->latest('updated_at')
            ->limit(10)
            ->get();

        $activeListings = MarketplaceListing::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();
        $soldListings = MarketplaceListing::where('user_id', $user->id)
            ->where('status', 'sold')
            ->count();
        $totalRevenue = (float) MarketplaceListing::query()
            ->join('user_cards', 'user_cards.id', '=', 'marketplace_listings.user_card_id')
            ->where('marketplace_listings.user_id', $user->id)
            ->where('marketplace_listings.status', 'sold')
            ->sum(DB::raw('coalesce(user_cards.listing_price, user_cards.estimated_value, 0)'));
        $avgListingPrice = (float) (MarketplaceListing::query()
            ->join('user_cards', 'user_cards.id', '=', 'marketplace_listings.user_card_id')
            ->where('marketplace_listings.user_id', $user->id)
            ->where('marketplace_listings.status', 'active')
            ->avg(DB::raw('coalesce(user_cards.listing_price, user_cards.estimated_value, 0)')) ?? 0);

        $totalConversations = Conversation::query()
            ->forUser($user)
            ->count();
        $messagesSent = Message::where('sender_id', $user->id)->count();
        $messagesReceived = Message::where('receiver_id', $user->id)->count();
        $replyRate = $messagesReceived > 0 ? min(100, (int) round(($messagesSent / $messagesReceived) * 100)) : 0;

        $growthChart = $this->buildGrowthChart($user->id);
        $artistDistribution = $this->buildArtistDistributionFromBreakdown($artistBreakdown);
        $tradeHealth = $this->buildTradeHealthFromRequests(
            $totalTrades,
            $completedTrades,
            $declinedTrades + $cancelledTrades,
            $replyRate
        );
        $quickExports = [
            'portfolio_cards' => $totalCards,
            'listed_cards' => $activeListings,
            'portfolio_value' => round($totalValue),
            'completion_rate' => $completionRate,
        ];

        return view('stats.index', [
            'totalCards' => $totalCards,
            'totalValue' => $totalValue,
            'totalSpent' => $totalSpent,
            'netChange' => $netChange,
            'netChangePercent' => $netChangePercent,
            'avgCardValue' => $avgCardValue,
            'mostValuableCard' => $mostValuableCard,
            'artistBreakdown' => $artistBreakdown,
            'albumBreakdown' => $albumBreakdown,
            'recentCards' => $recentCards,
            'tradesSent' => $tradesSent,
            'tradesReceived' => $tradesReceived,
            'completedTrades' => $completedTrades,
            'declinedTrades' => $declinedTrades,
            'pendingTrades' => $pendingTrades,
            'acceptedTrades' => $acceptedTrades,
            'cancelledTrades' => $cancelledTrades,
            'completionRate' => $completionRate,
            'tradeHistory' => $tradeHistory,
            'activeListings' => $activeListings,
            'soldListings' => $soldListings,
            'totalRevenue' => $totalRevenue,
            'avgListingPrice' => $avgListingPrice,
            'totalConversations' => $totalConversations,
            'messagesSent' => $messagesSent,
            'messagesReceived' => $messagesReceived,
            'replyRate' => $replyRate,
            'metrics' => [
                'total_value' => round($totalValue),
                'completion_rate' => $completionRate,
                'successful_trades' => $successfulTradesThisWeek,
                'average_trade_score' => $completionRate,
                'trade_total' => $totalTrades,
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

    protected function buildArtistDistributionFromBreakdown(Collection $artistBreakdown): array
    {
        $totalCards = max((int) $artistBreakdown->sum('total'), 1);

        return [
            'total_cards' => (int) $artistBreakdown->sum('total'),
            'rows' => $artistBreakdown->map(fn (array $row) => [
                'label' => $row['label'],
                'total' => (int) $row['total'],
                'percentage' => (int) round(($row['total'] / $totalCards) * 100),
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

    protected function buildTradeHealthFromRequests(
        int $totalTrades,
        int $completedTrades,
        int $unsuccessfulTrades,
        int $replyRate
    ): array {
        return [
            'blurb' => $totalTrades > 0
                ? 'Based on trade requests sent, received, and completed.'
                : 'No trade activity yet. Start listing or trading cards to build stats.',
            'avg_reply' => 0,
            'reply_score' => $replyRate,
            'completed' => $completedTrades,
            'disputes' => $unsuccessfulTrades,
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

        $cardsQuery = $user->userCards();
        $totalCards = (int) (clone $cardsQuery)->count();
        $totalValue = (float) (clone $cardsQuery)
            ->sum(\Illuminate\Support\Facades\DB::raw('coalesce(user_cards.estimated_value, 0)'));
        $totalSpent = (float) $user->userCards()
            ->sum(\Illuminate\Support\Facades\DB::raw('coalesce(user_cards.purchase_price, 0)'));
        $netChange = $totalValue - $totalSpent;

        $legacyTradeTotal = $user->trades()->count();
        $tradeRequestScope = TradeRequest::query()
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            });

        $requestTradeTotal = (clone $tradeRequestScope)->count();
        $tradeBase = $legacyTradeTotal + $requestTradeTotal;

        $legacyCompletedTrades = $user->trades()
            ->where('status', 'completed')
            ->count();
        $requestCompletedTrades = (clone $tradeRequestScope)
            ->where('status', 'completed')
            ->count();
        $completedTrades = $legacyCompletedTrades + $requestCompletedTrades;

        $completionRate = $tradeBase > 0
            ? round(($completedTrades / $tradeBase) * 100)
            : 0;

        $successfulTradesThisWeek = $user->trades()
            ->where('status', 'completed')
            ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        $successfulTradesThisWeek += (clone $tradeRequestScope)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $activeListings = MarketplaceListing::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('is_visible', true)
            ->count();
        $averageCardValue = $totalCards > 0 ? round($totalValue / $totalCards) : 0;

        return view('stats.print', [
            'user' => $user,
            'generatedAt' => now(),
            'metrics' => [
                'total_cards' => $totalCards,
                'total_value' => round($totalValue),
                'total_spent' => round($totalSpent),
                'net_change' => round($netChange),
                'average_card_value' => $averageCardValue,
                'completion_rate' => $completionRate,
                'successful_trades' => $successfulTradesThisWeek,
                'average_trade_score' => $tradeBase > 0 ? round(($completionRate / 100) * 5, 2) : 0,
                'completed_trades' => $completedTrades,
                'trade_total' => $tradeBase,
                'active_listings' => $activeListings,
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
        ->sum(\Illuminate\Support\Facades\DB::raw('coalesce(user_cards.estimated_value, 0)'));

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
