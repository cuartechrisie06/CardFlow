@extends('layouts.app')

@section('title', 'CardFlow | Stats')
@section('body_class', 'dashboard-body')

@section('topbar')
@endsection

@section('content')
@php
    $rarityChartRows = collect($rarityChartData);
    $artistChartRows = collect($artistChartData);
    $rarityTotal = max((int) $rarityChartRows->sum('total'), 1);
    $artistMax = max((int) $artistChartRows->max('total'), 1);
    $rarityColors = ['#8B4513', '#c8956c', '#e8c9a0', '#f5e6d8', '#6f4526', '#d8b391'];
    $rarityOffset = 0;
    $isPositive = $netChange >= 0;
@endphp

<div class="stats-page">
    <header class="stats-page-header">
        <div>
            <p class="stats-section-label">Collection Stats</p>
            <h1>Collection stats</h1>
            <p>Track portfolio value, rarity spread, artist concentration, and trade momentum from your real CardFlow data.</p>
        </div>

        <div class="stats-export-actions stats-export-actions-top">
            <span class="mini-chip">Live database</span>
            <a href="{{ route('stats.export.pdf') }}" class="stats-export-btn stats-export-btn-primary">
                Export summary
            </a>
        </div>
    </header>

    <section class="stats-grid-4">
        <article class="stats-card">
            <p class="stats-card-label">Total Cards</p>
            <p class="stats-card-value">{{ number_format($totalCards) }}</p>
            <p class="stats-card-note">Cards currently tracked in your collection.</p>
        </article>

        <article class="stats-card">
            <p class="stats-card-label">Collection Value</p>
            <p class="stats-card-value">{{ $formatMoney($totalValue) }}</p>
            <p class="stats-card-note">Sum of estimated values, falling back to market value.</p>
        </article>

        <article class="stats-card">
            <p class="stats-card-label">Total Spent</p>
            <p class="stats-card-value">PHP {{ number_format($totalSpent, 2) }}</p>
            <p class="stats-card-note">Based on purchase prices you entered.</p>
        </article>

        <article class="stats-card">
            <p class="stats-card-label">Net Gain/Loss</p>
            <p class="stats-card-value {{ $isPositive ? 'stats-card-positive' : 'stats-card-negative' }}">
                {{ $isPositive ? '+' : '-' }}PHP {{ number_format(abs($netChange), 2) }}
            </p>
            <p class="stats-card-note">{{ $isPositive ? '+' : '' }}{{ $netChangePercent }}% compared with total amount spent.</p>
        </article>
    </section>

    <section class="stats-grid-4">
        <article class="stats-card">
            <p class="stats-card-label">Average Card Value</p>
            <p class="stats-card-value">PHP {{ number_format($avgCardValue, 2) }}</p>
            <p class="stats-card-note">Collection value divided by tracked cards.</p>
        </article>

        <article class="stats-card">
            <p class="stats-card-label">Most Valuable</p>
            <p class="stats-card-value stats-card-value-sm">{{ $mostValuableCard?->card?->title ?? 'No card yet' }}</p>
            <p class="stats-card-note">
                {{ $mostValuableCard ? 'PHP '.number_format((float) ($mostValuableCard->estimated_value ?? $mostValuableCard->card?->market_value ?? 0), 2) : 'Add values to highlight one.' }}
            </p>
        </article>

        <article class="stats-card">
            <p class="stats-card-label">Active Listings</p>
            <p class="stats-card-value">{{ number_format($activeListings) }}</p>
            <p class="stats-card-note">{{ $soldListings }} sold listings, PHP {{ number_format($totalRevenue, 2) }} revenue.</p>
        </article>

        <article class="stats-card">
            <p class="stats-card-label">Messages</p>
            <p class="stats-card-value">{{ number_format($messagesSent) }}</p>
            <p class="stats-card-note">{{ $totalConversations }} conversations, {{ $replyRate }}% reply rate.</p>
        </article>
    </section>

    <section class="stats-grid-2">
        <article class="chart-container">
            <div class="stats-section-header">
                <div>
                    <p class="stats-section-label">Collection by rarity</p>
                    <h2 class="stats-section-title">Rarity breakdown</h2>
                </div>
            </div>

            @if ($rarityChartRows->isNotEmpty())
                <div class="stats-donut-chart" role="img" aria-label="Collection by rarity chart">
                    <svg viewBox="0 0 42 42" class="stats-donut-svg" aria-hidden="true">
                        <circle class="stats-donut-track" cx="21" cy="21" r="15.915"></circle>
                        @foreach ($rarityChartRows as $index => $row)
                            @php
                                $segment = ((int) $row['total'] / $rarityTotal) * 100;
                                $color = $rarityColors[$index % count($rarityColors)];
                            @endphp
                            <circle
                                class="stats-donut-segment"
                                cx="21"
                                cy="21"
                                r="15.915"
                                stroke="{{ $color }}"
                                stroke-dasharray="{{ $segment }} {{ 100 - $segment }}"
                                stroke-dashoffset="{{ -$rarityOffset }}"
                            ></circle>
                            @php $rarityOffset += $segment; @endphp
                        @endforeach
                    </svg>
                    <div class="stats-donut-total">
                        <strong>{{ $rarityChartRows->sum('total') }}</strong>
                        <span>cards</span>
                    </div>
                </div>

                <div class="stats-chart-legend">
                    @foreach ($rarityChartRows as $index => $row)
                        <span><i style="background: {{ $rarityColors[$index % count($rarityColors)] }}"></i>{{ $row['label'] }} ({{ $row['total'] }})</span>
                    @endforeach
                </div>
            @else
                <div class="stats-empty-state">No rarity data yet. Add a card with a rarity to unlock this chart.</div>
            @endif
        </article>

        <article class="chart-container">
            <div class="stats-section-header">
                <div>
                    <p class="stats-section-label">Cards by artist</p>
                    <h2 class="stats-section-title">Top artists</h2>
                </div>
            </div>

            @if ($artistChartRows->isNotEmpty())
                <div class="stats-bar-chart" role="img" aria-label="Cards by artist chart">
                    @foreach ($artistChartRows as $row)
                        <div class="stats-bar-chart-row">
                            <span>{{ $row['label'] }}</span>
                            <div class="stats-bar-chart-track">
                                <i style="width: {{ max(10, ((int) $row['total'] / $artistMax) * 100) }}%"></i>
                            </div>
                            <strong>{{ $row['total'] }}</strong>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="stats-empty-state">No artist data yet. Your most collected artists will appear here.</div>
            @endif
        </article>
    </section>

    <section class="stats-grid-2">
        <article class="stats-card stats-wide-card">
            <div class="stats-section-header">
                <div>
                    <p class="stats-section-label">Detailed rarity</p>
                    <h2 class="stats-section-title">Binder tiers</h2>
                </div>
            </div>

            <div class="stats-detail-list">
                @forelse ($rarityBreakdown as $row)
                    @php
                        $rarityPercent = $rarityTotal > 0 ? min(100, max(4, (int) round(($row['total'] / $rarityTotal) * 100))) : 0;
                    @endphp
                    <div class="rarity-row">
                        <span class="rarity-name">{{ $row['label'] }}</span>
                        <div class="rarity-bar-wrapper">
                            <div class="rarity-bar-fill" style="width: {{ $rarityPercent }}%"></div>
                        </div>
                        <span class="rarity-count">{{ $row['total'] }}</span>
                    </div>
                @empty
                    <div class="stats-empty-state">No rarity data yet.</div>
                @endforelse
            </div>
        </article>

        <article class="stats-card stats-wide-card">
            <div class="stats-section-header">
                <div>
                    <p class="stats-section-label">Detailed artist</p>
                    <h2 class="stats-section-title">Collection share</h2>
                </div>
            </div>

            <div class="stats-detail-list">
                @forelse ($artistDistribution['rows'] as $row)
                    <div class="rarity-row">
                        <span class="rarity-name">{{ $row['label'] }}</span>
                        <div class="rarity-bar-wrapper">
                            <div class="rarity-bar-fill" style="width: {{ max(4, $row['percentage']) }}%"></div>
                        </div>
                        <span class="rarity-count">{{ $row['percentage'] }}%</span>
                    </div>
                @empty
                    <div class="stats-empty-state">No artist data yet.</div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="stats-grid-2">
        <article class="stats-card stats-wide-card">
            <div class="stats-section-header">
                <div>
                    <p class="stats-section-label">Collection growth</p>
                    <h2 class="stats-section-title">Cards added over the last 6 months</h2>
                </div>
                <span class="mini-chip">{{ $growthChart['latest'] }} new cards</span>
            </div>

            <div class="stats-line-chart-panel">
                <svg viewBox="0 0 430 160" class="line-chart" role="img" aria-label="Collection growth chart">
                    <path d="{{ $growthChart['path'] }}" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <div class="line-chart-labels">
                    @foreach ($growthChart['points'] as $point)
                        <span>{{ $point['label'] }}</span>
                    @endforeach
                </div>
            </div>
        </article>

        <article class="stats-card stats-wide-card">
            <div class="stats-section-header">
                <div>
                    <p class="stats-section-label">Trade history</p>
                    <h2 class="stats-section-title">Trade health</h2>
                </div>
                <span class="mini-chip">{{ $metrics['trade_total'] }} tracked</span>
            </div>

            <div class="stats-highlight-card">
                <strong>Activity summary</strong>
                <p>{{ $tradeHealth['blurb'] }}</p>
            </div>

            <div class="stats-trade-grid">
                <div>
                    <span class="summary-label">Completion Rate</span>
                    <strong>{{ $completionRate }}%</strong>
                    <span class="stats-progress-bar">
                        <i style="width: {{ min(100, max(0, $completionRate)) }}%"></i>
                    </span>
                </div>
                <div>
                    <span class="summary-label">Pending</span>
                    <strong>{{ $pendingTrades }}</strong>
                </div>
                <div>
                    <span class="summary-label">Completed</span>
                    <strong>{{ $tradeHealth['completed'] }}</strong>
                </div>
                <div>
                    <span class="summary-label">Declined / Cancelled</span>
                    <strong>{{ $tradeHealth['disputes'] }}</strong>
                </div>
            </div>
        </article>
    </section>

    <section class="stats-grid-2">
        <article class="stats-card stats-wide-card">
            <div class="stats-section-header">
                <div>
                    <p class="stats-section-label">Albums</p>
                    <h2 class="stats-section-title">Top albums</h2>
                </div>
            </div>

            <div class="stats-detail-list">
                @forelse ($albumBreakdown as $album)
                    <div class="rarity-row">
                        <span class="rarity-name">{{ $album['label'] }}</span>
                        <div class="rarity-bar-wrapper">
                            <div class="rarity-bar-fill" style="width: {{ $totalCards > 0 ? max(4, min(100, round(($album['total'] / $totalCards) * 100))) : 0 }}%"></div>
                        </div>
                        <span class="rarity-count">{{ $album['total'] }}</span>
                    </div>
                @empty
                    <div class="stats-empty-state">No album data yet.</div>
                @endforelse
            </div>
        </article>

        <article class="stats-card stats-wide-card">
            <div class="stats-section-header">
                <div>
                    <p class="stats-section-label">Recently acquired</p>
                    <h2 class="stats-section-title">Latest additions</h2>
                </div>
            </div>

            <div class="stats-history-list">
                @forelse ($recentCards as $userCard)
                    <div class="stats-history-row">
                        <img src="{{ $userCard->card?->photo_url ?? asset('images/placeholder-card.png') }}"
                             alt="{{ $userCard->card?->title ?? 'Card' }}">
                        <div>
                            <strong>{{ $userCard->card?->title ?? 'Untitled card' }}</strong>
                            <span>{{ $userCard->card?->artist ?? 'Unknown artist' }} &middot; {{ $userCard->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <div class="stats-empty-state">No cards added yet.</div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="stats-card stats-wide-card">
        <div class="stats-section-header">
            <div>
                <p class="stats-section-label">Trade history</p>
                <h2 class="stats-section-title">Your trades</h2>
            </div>
            <span class="mini-chip">{{ $tradesSent }} sent &middot; {{ $tradesReceived }} received</span>
        </div>

        <div class="stats-history-list">
            @forelse ($tradeHistory as $trade)
                @php
                    $isSender = $trade->sender_id === auth()->id();
                    $myCard = $isSender
                        ? $trade->offeredCard
                        : $trade->listing?->card;
                    $theirCard = $isSender
                        ? $trade->listing?->card
                        : $trade->offeredCard;
                    $otherUser = $isSender ? $trade->receiver : $trade->sender;
                    $statusColor = match($trade->status) {
                        'completed' => '#2d6a4f',
                        'pending' => '#b09070',
                        'accepted' => '#8B4513',
                        'declined', 'cancelled' => '#c0392b',
                        default => '#b09070',
                    };
                @endphp
                <div class="stats-history-row">
                    <div class="stats-trade-card-side">
                        <img src="{{ $myCard?->photo_url ?? asset('images/placeholder-card.png') }}"
                             alt="{{ $myCard?->title ?? 'Card you gave' }}">
                        <div>
                            <span>You gave</span>
                            <strong>{{ $myCard?->title ?? 'Card unavailable' }}</strong>
                        </div>
                    </div>

                    <div class="stats-trade-swap" aria-hidden="true">&#8644;</div>

                    <div class="stats-trade-card-side">
                        <img src="{{ $theirCard?->photo_url ?? asset('images/placeholder-card.png') }}"
                             alt="{{ $theirCard?->title ?? 'Card you received' }}">
                        <div>
                            <span>You received</span>
                            <strong>{{ $theirCard?->title ?? 'Card unavailable' }}</strong>
                        </div>
                    </div>

                    <div class="stats-trade-meta">
                        <span class="stats-status-badge" style="color: {{ $statusColor }}; background: {{ $statusColor }}18;">
                            {{ ucfirst($trade->status) }}
                        </span>
                        <span>
                            with
                            @if($otherUser?->username)
                                <a href="{{ route('profile.showcase', $otherUser) }}" class="collector-profile-link">
                                    {{ '@'.$otherUser->username }}
                                </a>
                            @else
                                {{ '@collector' }}
                            @endif
                            &middot; {{ $trade->updated_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="stats-empty-state">No trades yet. Your trade history will appear here.</div>
            @endforelse
        </div>
    </section>

    <section class="stats-card stats-export-panel">
        <div class="stats-section-header">
            <div>
                <p class="stats-section-label">Export actions</p>
                <h2 class="stats-section-title">Quick exports</h2>
                <p class="stats-card-note">Download your records or generate a shareable snapshot.</p>
            </div>
        </div>

        <div class="stats-export-actions">
            <a href="{{ route('stats.export.pdf') }}" class="stats-export-btn">
                Export PDF summary
            </a>
            <a href="{{ route('stats.export.csv') }}" class="stats-export-btn">
                Download CSV data
            </a>
            <form method="POST" action="{{ route('stats.share') }}" class="stats-share-form">
                @csrf
                <button type="submit" class="stats-export-btn stats-export-btn-primary">
                    Share snapshot
                </button>
            </form>
        </div>

        @if (session('snapshot'))
            <div class="stats-snapshot-box">
                <div class="stats-snapshot-header">
                    <strong>Snapshot ready</strong>
                    <span>Copy and share this summary.</span>
                </div>
                <textarea class="stats-snapshot-text" readonly>{{ session('snapshot') }}</textarea>
            </div>
        @endif

        <div class="stats-export-summary">
            <div>
                <span class="summary-label">Completion Rate</span>
                <strong>{{ $quickExports['completion_rate'] }}%</strong>
                <p>{{ $quickExports['listed_cards'] }} listed cards across {{ $quickExports['portfolio_cards'] }} total cards.</p>
            </div>
            <div>
                <span class="summary-label">Average Listing</span>
                <strong>PHP {{ number_format($avgListingPrice, 2) }}</strong>
                <p>Average active listing value.</p>
            </div>
            <div>
                <span class="summary-label">Reply Rate</span>
                <strong>{{ $replyRate }}%</strong>
                <p>Based on sent and received messages.</p>
            </div>
        </div>
    </section>
</div>
@endsection
