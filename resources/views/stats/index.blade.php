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
@endphp
<header class="dashboard-header marketplace-header">
                    <div>
                        <p class="dashboard-kicker">Collection Stats</p>
                        <h1>Collection stats</h1>
                    </div>

                    <div class="dashboard-actions">
                        <span class="mini-chip">This month</span>

                            <a href="{{ route('stats.export.pdf') }}" class="dashboard-add-card">
                            Export summary
                        </a>
                    </div>
                </header>

                <section class="dashboard-card stats-shell">
                    <section class="stats-chart-grid">
                        <article class="dashboard-card stats-chart-card">
                            <div class="card-topline">
                                <div>
                                    <p class="mini-label">Collection by Rarity</p>
                                    <h2>Rarity Breakdown</h2>
                                </div>
                            </div>

                            <div class="stats-chart-canvas-wrap">
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
                                    <div class="collection-empty">No rarity data yet.</div>
                                @endif
                            </div>
                        </article>

                        <article class="dashboard-card stats-chart-card">
                            <div class="card-topline">
                                <div>
                                    <p class="mini-label">Cards by Artist</p>
                                    <h2>Top Artists</h2>
                                </div>
                            </div>

                            <div class="stats-chart-canvas-wrap">
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
                                    <div class="collection-empty">No artist data yet.</div>
                                @endif
                            </div>
                        </article>
                    </section>

                    <section class="stats-grid explorer-stats">
                        <article class="stat-card">
                            <span class="stat-label">Total value</span>
                            <div class="stat-value">{{ $formatMoney($metrics['total_value']) }}</div>
                            <div class="stat-note">Sum of your estimated card values.</div>
                        </article>
                        <article class="stat-card">
                            <span class="stat-label">Completion rate</span>
                            <div class="stat-value">{{ $metrics['completion_rate'] }}%</div>
                            <div class="progress-line">
                                <i style="width: {{ max(8, $metrics['completion_rate']) }}%"></i>
                            </div>
                            <div class="stat-note">Completed trades divided by {{ $metrics['trade_total'] }} total tracked trades.</div>
                        </article>
                        <article class="stat-card">
                            <span class="stat-label">Successful trades</span>
                            <div class="stat-value">{{ $metrics['successful_trades'] }}</div>
                            <div class="stat-note">Trades marked completed during the current week.</div>
                        </article>
                        <article class="stat-card">
                            <span class="stat-label">Avg. trade score</span>
                            <div class="stat-value">{{ number_format($metrics['average_trade_score'], 2) }}</div>
                            <div class="stat-note">A weighted score derived from your trade outcomes.</div>
                        </article>
                    </section>

                    <section class="stats-main-grid">
                        <article class="dashboard-card stats-growth-card">
                            <div class="card-topline">
                                <div>
                                    <p class="mini-label">Collection growth</p>
                                    <h2>Cards added over the last 6 months</h2>
                                </div>
                                <span class="mini-chip">{{ $growthChart['latest'] }} new cards</span>
                            </div>

                            <div class="line-chart-panel">
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

                        <article class="dashboard-card stats-distribution-card">
                            <div class="card-topline">
                                <div>
                                    <p class="mini-label">Cards by group</p>
                                    <h2>Distribution of your collection</h2>
                                </div>
                            </div>

                            <div class="donut-stat">
                                <div class="donut-stat-center">
                                    <strong>{{ $artistDistribution['total_cards'] }}</strong>
                                    <span>Total cards</span>
                                </div>
                            </div>

                            <div class="stats-list">
                                @forelse ($artistDistribution['rows'] as $row)
                                    <div class="stats-list-row">
                                        <span>{{ $row['label'] }}</span>
                                        <strong>{{ $row['percentage'] }}%</strong>
                                    </div>
                                @empty
                                    <div class="collection-empty">No collection data yet.</div>
                                @endforelse
                            </div>
                        </article>
                    </section>

                    <section class="stats-bottom-grid">
                        <article class="dashboard-card stats-rarity-card">
                            <div class="card-topline">
                                <div>
                                    <p class="mini-label">Rarity breakdown</p>
                                    <h2>Which tiers dominate your binders</h2>
                                </div>
                            </div>

                            <div class="stats-bar-list">
                                @forelse ($rarityBreakdown as $row)
                                    <div class="stats-bar-row">
                                        <div class="stats-bar-copy">
                                            <span>{{ $row['label'] }}</span>
                                            <strong>{{ $row['total'] }} cards</strong>
                                        </div>
                                        <div class="stats-bar-track">
                                            <i style="width: {{ $row['width'] }}%"></i>
                                        </div>
                                    </div>
                                @empty
                                    <div class="collection-empty">No rarity data yet.</div>
                                @endforelse
                            </div>
                        </article>

                        <article class="dashboard-card stats-trade-card">
                            <div class="card-topline">
                                <div>
                                    <p class="mini-label">Trade health</p>
                                    <h2>How active and reliable your trades feel</h2>
                                </div>
                            </div>

                            <div class="stats-highlight-card">
                                <strong>Activity summary</strong>
                                <p>{{ $tradeHealth['blurb'] }}</p>
                            </div>

                            <div class="stats-trade-grid">
                                <div>
                                    <span class="summary-label">Avg reply</span>
                                    <strong>{{ $tradeHealth['avg_reply'] }} min</strong>
                                </div>
                                <div>
                                    <span class="summary-label">Reply score</span>
                                    <strong>{{ $tradeHealth['reply_score'] }}%</strong>
                                </div>
                                <div>
                                    <span class="summary-label">Completed</span>
                                    <strong>{{ $tradeHealth['completed'] }}</strong>
                                </div>
                                <div>
                                    <span class="summary-label">Disputes</span>
                                    <strong>{{ $tradeHealth['disputes'] }}</strong>
                                </div>
                            </div>
                        </article>

                        <article class="dashboard-card stats-export-card">
                            <div class="card-topline">
                                <div>
                                    <p class="mini-label">Report actions</p>
                                    <h2>Quick exports</h2>
                                </div>
                            </div>

                            <div class="stats-action-list">
                                <a href="{{ route('stats.export.pdf') }}" class="dashboard-add-card dashboard-add-card-secondary">
                                    Export PDF summary
                                 </a>

                                <a href="{{ route('stats.export.csv') }}" class="dashboard-search-submit">
                                    Download CSV data
                                </a>

                            <form method="POST" action="{{ route('stats.share') }}" class="stats-share-form">
                                @csrf
                            <button type="submit" class="dashboard-search-submit">
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

                            <div class="stats-highlight-card stats-highlight-dark">
                                <span class="summary-label">Completion Rate</span>
                                <strong>{{ $quickExports['completion_rate'] }}%</strong>
                                <p>{{ $quickExports['listed_cards'] }} listed cards across {{ $quickExports['portfolio_cards'] }} total cards.</p>
                            </div>
                        </article>
                    </section>
                </section>
@endsection
