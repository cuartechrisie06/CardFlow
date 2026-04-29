<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CardFlow | Stats</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dashboard-body">
        @php
            $user = auth()->user();
            $username = $user->username ?: 'collector';
            $formatMoney = fn (float|int $value) => 'PHP '.number_format((float) $value, 0);
        @endphp
        <main class="dashboard-shell">
            <aside class="dashboard-sidebar">
                <div class="sidebar-brand">
                    <div class="sidebar-avatar"></div>
                    <div>
                        <p>{{ $user->name }}</p>
                        <span>{{ '@'.$username }}</span>
                    </div>
                </div>

                <nav class="sidebar-nav" aria-label="Primary">
                    <a href="{{ route('dashboard') }}" class="sidebar-link">Dashboard</a>
                    <a href="{{ route('collection.index') }}" class="sidebar-link">My Collection</a>
                    <a href="{{ route('marketplace.index') }}" class="sidebar-link">Marketplace</a>
                    <a href="{{ route('wishlist.index') }}" class="sidebar-link">Wishlist</a>
                    <a href="{{ route('messages.index') }}" class="sidebar-link">Messages</a>
                    <a href="{{ route('explorer.index') }}" class="sidebar-link">Explorer</a>
                    <a href="{{ route('stats.index') }}" class="sidebar-link is-active">Stats</a>
                </nav>

                @include('partials.sidebar-collector', ['user' => $user])
            </aside>

            <section class="dashboard-main">
                <header class="dashboard-header marketplace-header">
                    <div>
                        <p class="dashboard-kicker">Collection insights</p>
                        <h1>Collection insights</h1>
                    </div>

                    <div class="dashboard-actions">
                        <span class="mini-chip">This month</span>
                        <button type="button" class="dashboard-add-card">Export summary</button>
                    </div>
                </header>

                <section class="dashboard-card stats-shell">
                    <section class="stats-grid explorer-stats">
                        <article class="stat-card">
                            <span class="stat-label">Total value</span>
                            <div class="stat-value">{{ $formatMoney($metrics['total_value']) }}</div>
                            <div class="stat-note">estimated collection value</div>
                        </article>
                        <article class="stat-card">
                            <span class="stat-label">Completion rate</span>
                            <div class="stat-value">{{ $metrics['completion_rate'] }}%</div>
                            <div class="progress-line">
                                <i style="width: {{ max(8, $metrics['completion_rate']) }}%"></i>
                            </div>
                            <div class="stat-note">{{ $metrics['trade_total'] }} total trades tracked</div>
                        </article>
                        <article class="stat-card">
                            <span class="stat-label">Successful trades</span>
                            <div class="stat-value">{{ $metrics['successful_trades'] }}</div>
                            <div class="stat-note">completed this week</div>
                        </article>
                        <article class="stat-card">
                            <span class="stat-label">Avg. trade score</span>
                            <div class="stat-value">{{ number_format($metrics['average_trade_score'], 2) }}</div>
                            <div class="stat-note">derived from trade outcomes</div>
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
                                <button type="button" class="dashboard-add-card dashboard-add-card-secondary">Export PDF summary</button>
                                <button type="button" class="dashboard-search-submit">Download CSV data</button>
                                <button type="button" class="dashboard-search-submit">Share snapshot</button>
                            </div>

                            <div class="stats-highlight-card stats-highlight-dark">
                                <span class="summary-label">Next milestone</span>
                                <strong>{{ $quickExports['completion_rate'] }}%</strong>
                                <p>{{ $quickExports['listed_cards'] }} listed cards across {{ $quickExports['portfolio_cards'] }} total cards.</p>
                            </div>
                        </article>
                    </section>
                </section>
            </section>
        </main>
    </body>
</html>
